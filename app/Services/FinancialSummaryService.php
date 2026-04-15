<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class FinancialSummaryService
{
    /**
     * Neto personal del doctor en la clinica para un mes (YYYY-MM).
     *
     *   mis_ingresos       = SUM(payments WHERE doctor_id = me)
     *   mis_gastos_propios = SUM(expenses WHERE owner_doctor_id = me)
     *   mi_pool_compartido = SUM(expenses WHERE owner_doctor_id IS NULL) * mi_porcentaje
     *   mi_neto            = ingresos - gastos_propios - pool_compartido
     */
    public function personalSummary(User $doctor, Clinic $clinic, string $month): array
    {
        [$start, $end] = $this->monthRange($month);

        $income = (float) Payment::where('clinic_id', $clinic->id)
            ->where('doctor_id', $doctor->id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $ownExpenses = (float) Expense::where('clinic_id', $clinic->id)
            ->personalOf($doctor->id)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $sharedTotal = (float) Expense::where('clinic_id', $clinic->id)
            ->shared()
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $percentage = $clinic->splitPercentageFor($doctor);
        $sharedPortion = round($sharedTotal * $percentage, 2);

        return [
            'month' => $month,
            'doctor' => $doctor,
            'clinic' => $clinic,
            'income' => $income,
            'own_expenses' => $ownExpenses,
            'shared_total' => $sharedTotal,
            'shared_percentage' => $percentage,
            'shared_portion' => $sharedPortion,
            'net' => round($income - $ownExpenses - $sharedPortion, 2),
            'period_start' => $start,
            'period_end' => $end,
        ];
    }

    /**
     * Desglose del pool compartido: total, por doctor (cuanto le toca),
     * por categoria y lista detallada de gastos compartidos del mes.
     */
    public function sharedPoolSummary(Clinic $clinic, string $month): array
    {
        [$start, $end] = $this->monthRange($month);

        $expenses = Expense::where('clinic_id', $clinic->id)
            ->shared()
            ->whereBetween('expense_date', [$start, $end])
            ->with(['category', 'registeredBy'])
            ->orderBy('expense_date', 'desc')
            ->get();

        $total = (float) $expenses->sum('amount');

        $byCategory = $expenses->groupBy('category.name')
            ->map(fn ($items) => (float) $items->sum('amount'))
            ->sortDesc();

        $doctors = $clinic->doctors()->get();
        $byDoctor = $doctors->map(function (User $d) use ($clinic, $total) {
            $pct = $clinic->splitPercentageFor($d);
            return [
                'doctor' => $d,
                'percentage' => $pct,
                'amount' => round($total * $pct, 2),
            ];
        });

        return [
            'month' => $month,
            'clinic' => $clinic,
            'total' => $total,
            'by_category' => $byCategory,
            'by_doctor' => $byDoctor,
            'expenses' => $expenses,
            'split_method' => $clinic->expense_split_method,
            'period_start' => $start,
            'period_end' => $end,
        ];
    }

    private function monthRange(string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        return [$start, $end];
    }
}
