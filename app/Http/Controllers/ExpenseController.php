<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\User;
use App\Services\FinancialSummaryService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $month = $request->get('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $expenses = Expense::where('clinic_id', $clinicId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with(['category', 'registeredBy'])
            ->orderBy('expense_date', 'desc')
            ->get();

        $categories = ExpenseCategory::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // Group by category for summary
        $byCategory = $expenses->groupBy('category.name')->map(function ($items) {
            return $items->sum('amount');
        })->sortDesc();

        return view('expenses.index', compact('expenses', 'categories', 'totalExpenses', 'byCategory', 'month'));
    }

    public function create()
    {
        $clinicId = session('active_clinic_id');

        $categories = ExpenseCategory::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $doctors = $this->clinicDoctors($clinicId);

        return view('expenses.create', compact('categories', 'doctors'));
    }

    public function store(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'owner_doctor_id' => 'nullable|exists:users,id',
        ]);

        Expense::create([
            ...$validated,
            'clinic_id' => $clinicId,
            'registered_by' => auth()->id(),
            'is_recurring' => $request->boolean('is_recurring'),
            'owner_doctor_id' => $this->resolveOwnerDoctorId($request, $clinicId),
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto registrado exitosamente.');
    }

    public function edit(Expense $expense)
    {
        abort_if($expense->clinic_id != session('active_clinic_id'), 403);

        $categories = ExpenseCategory::where('clinic_id', session('active_clinic_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $doctors = $this->clinicDoctors(session('active_clinic_id'));

        return view('expenses.edit', compact('expense', 'categories', 'doctors'));
    }

    public function update(Request $request, Expense $expense)
    {
        abort_if($expense->clinic_id != session('active_clinic_id'), 403);

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'owner_doctor_id' => 'nullable|exists:users,id',
        ]);

        $expense->update([
            ...$validated,
            'is_recurring' => $request->boolean('is_recurring'),
            'owner_doctor_id' => $this->resolveOwnerDoctorId($request, $expense->clinic_id),
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto actualizado.');
    }

    public function destroy(Expense $expense)
    {
        abort_if($expense->clinic_id != session('active_clinic_id'), 403);

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto eliminado.');
    }

    public function summary(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $month = $request->get('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $prevStart = $startDate->copy()->subMonth()->startOfMonth();
        $prevEnd = $startDate->copy()->subMonth()->endOfMonth();

        // Income (payments)
        $totalIncome = Payment::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $prevIncome = Payment::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('amount');

        // Expenses
        $totalExpenses = Expense::where('clinic_id', $clinicId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $prevExpenses = Expense::where('clinic_id', $clinicId)
            ->whereBetween('expense_date', [$prevStart, $prevEnd])
            ->sum('amount');

        // Balance
        $balance = $totalIncome - $totalExpenses;
        $prevBalance = $prevIncome - $prevExpenses;

        // Expenses by category
        $expensesByCategory = Expense::where('clinic_id', $clinicId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(fn($items) => $items->sum('amount'))
            ->sortDesc();

        // Daily income for chart data
        $dailyIncome = Payment::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyExpenses = Expense::where('clinic_id', $clinicId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('expense_date as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        return view('expenses.summary', compact(
            'month', 'totalIncome', 'totalExpenses', 'balance',
            'prevIncome', 'prevExpenses', 'prevBalance',
            'expensesByCategory', 'dailyIncome', 'dailyExpenses',
            'startDate', 'endDate'
        ));
    }

    public function mySummary(Request $request, FinancialSummaryService $service)
    {
        $clinic = Clinic::findOrFail(session('active_clinic_id'));
        $month = $request->get('month', now()->format('Y-m'));

        if (! auth()->user()->isDoctor()) {
            abort(403, 'Solo disponible para doctores.');
        }

        $data = $service->personalSummary(auth()->user(), $clinic, $month);

        return view('expenses.my-summary', $data);
    }

    public function sharedPool(Request $request, FinancialSummaryService $service)
    {
        $clinic = Clinic::findOrFail(session('active_clinic_id'));
        $month = $request->get('month', now()->format('Y-m'));

        $data = $service->sharedPoolSummary($clinic, $month);

        return view('expenses.shared-pool', $data);
    }

    private function clinicDoctors(int $clinicId)
    {
        return Clinic::find($clinicId)?->doctors()->get() ?? collect();
    }

    /**
     * Solo el doctor_admin puede marcar un gasto como personal de OTRO doctor.
     * Un doctor_associate solo puede marcarlo como propio o compartido.
     * Secretarias no pueden asignar owner_doctor_id (siempre compartido).
     */
    private function resolveOwnerDoctorId(Request $request, int $clinicId): ?int
    {
        $user = $request->user();
        $requested = $request->input('owner_doctor_id');

        if ($requested === null || $requested === '') {
            return null;
        }

        $requested = (int) $requested;

        if ($user->hasRole('doctor_admin')) {
            $validIds = $this->clinicDoctors($clinicId)->pluck('id')->all();
            return in_array($requested, $validIds, true) ? $requested : null;
        }

        if ($user->isDoctor()) {
            return $requested === $user->id ? $user->id : null;
        }

        return null;
    }
}
