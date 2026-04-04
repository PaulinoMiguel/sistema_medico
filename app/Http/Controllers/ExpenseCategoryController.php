<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $clinicId = session('active_clinic_id');

        $categories = ExpenseCategory::where('clinic_id', $clinicId)
            ->withCount('expenses')
            ->orderBy('name')
            ->get();

        return view('expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ExpenseCategory::create([
            ...$validated,
            'clinic_id' => session('active_clinic_id'),
        ]);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Categoria creada exitosamente.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        abort_if($expenseCategory->clinic_id != session('active_clinic_id'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Categoria actualizada.');
    }

    public function toggle(ExpenseCategory $expenseCategory)
    {
        abort_if($expenseCategory->clinic_id != session('active_clinic_id'), 403);

        $expenseCategory->update(['is_active' => ! $expenseCategory->is_active]);

        $status = $expenseCategory->is_active ? 'activada' : 'desactivada';

        return redirect()->route('expense-categories.index')
            ->with('success', "Categoria {$status}.");
    }
}
