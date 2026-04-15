<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function index()
    {
        $clinics = Clinic::withCount(['users', 'patients'])->get();

        return view('clinics.index', compact('clinics'));
    }

    public function create()
    {
        return view('clinics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'tax_id' => 'nullable|string|max:50',
            'type' => 'required|in:office,hospital,surgical_center',
        ]);

        $clinic = Clinic::create($validated);

        // Auto-assign the doctor (current user) to this clinic
        $clinic->users()->attach($request->user()->id, ['is_primary' => true]);

        // Set as active clinic if none selected
        if (! session('active_clinic_id')) {
            session(['active_clinic_id' => $clinic->id]);
        }

        return redirect()->route('clinics.index')
            ->with('success', 'Clínica creada exitosamente.');
    }

    public function edit(Clinic $clinic)
    {
        return view('clinics.edit', compact('clinic'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'tax_id' => 'nullable|string|max:50',
            'type' => 'required|in:office,hospital,surgical_center',
            'is_active' => 'boolean',
            'expense_split_method' => 'nullable|in:equal,percentage,by_income',
            'expense_split_config' => 'nullable|array',
            'expense_split_config.*' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->user()->can('expenses.manage-split')) {
            $validated['expense_split_method'] = $request->input('expense_split_method', $clinic->expense_split_method);
            $validated['expense_split_config'] = $validated['expense_split_method'] === 'percentage'
                ? array_filter($request->input('expense_split_config', []), fn ($v) => $v !== null && $v !== '')
                : null;
        } else {
            unset($validated['expense_split_method'], $validated['expense_split_config']);
        }

        $clinic->update($validated);

        return redirect()->route('clinics.index')
            ->with('success', 'Clínica actualizada exitosamente.');
    }

    public function destroy(Clinic $clinic)
    {
        // Soft deactivate - don't delete data
        $clinic->update(['is_active' => false]);

        return redirect()->route('clinics.index')
            ->with('success', 'Clínica desactivada.');
    }
}
