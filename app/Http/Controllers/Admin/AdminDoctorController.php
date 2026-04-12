<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminDoctorController extends Controller
{
    private const DOCTOR_ROLES = ['doctor_admin', 'doctor_associate'];

    public function index()
    {
        $doctors = User::role(self::DOCTOR_ROLES)
            ->with('clinics', 'roles')
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
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'specialty' => 'required|string|max:255',
            'professional_license' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $doctor = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'specialty' => $validated['specialty'],
            'professional_license' => $validated['professional_license'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
        ]);
        // Doctors created from the super admin panel are admins of their
        // own install by default.
        $doctor->assignRole('doctor_admin');

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor creado exitosamente.');
    }

    public function edit(User $doctor)
    {
        abort_if(!$doctor->hasAnyRole(self::DOCTOR_ROLES), 404);

        return view('admin.doctors.edit', compact('doctor'));
    }

    public function update(Request $request, User $doctor)
    {
        abort_if(!$doctor->hasAnyRole(self::DOCTOR_ROLES), 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $doctor->id,
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
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
        abort_if(!$doctor->hasAnyRole(self::DOCTOR_ROLES), 404);

        // Toggle only switches between active and inactive. Setting a doctor
        // to "passive" requires the dedicated UI (Fase 7) since it has
        // accounting implications (still counts in expense splits).
        $newStatus = $doctor->isActive() ? 'inactive' : 'active';
        $doctor->update(['status' => $newStatus]);

        $msg = $doctor->isActive() ? 'activado' : 'desactivado';

        return redirect()->route('admin.doctors.index')
            ->with('success', "Doctor {$msg}.");
    }
}
