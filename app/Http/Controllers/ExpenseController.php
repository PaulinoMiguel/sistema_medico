<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
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

        return view('expenses.create', compact('categories'));
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
        ]);

        Expense::create([
            ...$validated,
            'clinic_id' => $clinicId,
            'registered_by' => auth()->id(),
            'is_recurring' => $request->boolean('is_recurring'),
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

        return view('expenses.edit', compact('expense', 'categories'));
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
        ]);

        $expense->update([
            ...$validated,
            'is_recurring' => $request->boolean('is_recurring'),
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
}
