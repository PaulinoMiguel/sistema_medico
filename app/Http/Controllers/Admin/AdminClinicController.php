<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class AdminClinicController extends Controller
{
    public function index()
    {
        $clinics = Clinic::withCount(['users', 'patients'])->get();

        return view('admin.clinics.index', compact('clinics'));
    }

    public function create()
    {
        return view('admin.clinics.create');
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
        ]);

        Clinic::create($validated);

        return redirect()->route('admin.clinics.index')
            ->with('success', 'Clinica creada exitosamente.');
    }

    public function edit(Clinic $clinic)
    {
        return view('admin.clinics.edit', compact('clinic'));
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
            'is_active' => 'boolean',
        ]);

        $clinic->update($validated);

        return redirect()->route('admin.clinics.index')
            ->with('success', 'Clinica actualizada exitosamente.');
    }
}
