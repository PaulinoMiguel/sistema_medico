<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminDoctorController extends Controller
{
    public function index()
    {
        $doctors = User::where('role', 'doctor')
            ->with('clinics')
            ->latest()
            ->get();

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'specialty' => 'required|string|max:255',
            'professional_license' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
            'specialty' => $validated['specialty'],
            'professional_license' => $validated['professional_license'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor creado exitosamente.');
    }

    public function edit(User $doctor)
    {
        abort_if($doctor->role !== 'doctor', 404);

        return view('admin.doctors.edit', compact('doctor'));
    }

    public function update(Request $request, User $doctor)
    {
        abort_if($doctor->role !== 'doctor', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $doctor->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'specialty' => 'required|string|max:255',
            'professional_license' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $doctor->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'specialty' => $validated['specialty'],
            'professional_license' => $validated['professional_license'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $doctor->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor actualizado exitosamente.');
    }

    public function toggle(User $doctor)
    {
        abort_if($doctor->role !== 'doctor', 404);

        $doctor->update(['is_active' => ! $doctor->is_active]);

        $status = $doctor->is_active ? 'activado' : 'desactivado';

        return redirect()->route('admin.doctors.index')
            ->with('success', "Doctor {$status}.");
    }
}
