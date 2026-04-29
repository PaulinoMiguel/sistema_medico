<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
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
        $clinics = Clinic::where('is_active', true)->get();
        $templates = config('consultation_templates');

        return view('admin.doctors.create', compact('clinics', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'specialty' => 'required|string|max:255',
            'consultation_template' => 'nullable|string|max:255',
            'professional_license' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:doctor_admin,doctor_associate',
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id',
        ]);

        $doctor = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'specialty' => $validated['specialty'],
            'consultation_template' => $validated['consultation_template'] ?? null,
            'professional_license' => $validated['professional_license'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
        ]);

        $doctor->assignRole($validated['role']);

        foreach ($validated['clinic_ids'] as $index => $clinicId) {
            $doctor->clinics()->attach($clinicId, [
                'is_primary' => $index === 0,
            ]);
        }

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor creado exitosamente.');
    }

    public function edit(User $doctor)
    {
        abort_if(! $doctor->hasAnyRole(self::DOCTOR_ROLES), 404);

        $clinics = Clinic::where('is_active', true)->get();
        $doctorClinicIds = $doctor->clinics()->pluck('clinics.id')->toArray();
        $templates = config('consultation_templates');

        return view('admin.doctors.edit', compact('doctor', 'clinics', 'doctorClinicIds', 'templates'));
    }

    public function update(Request $request, User $doctor)
    {
        abort_if(! $doctor->hasAnyRole(self::DOCTOR_ROLES), 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $doctor->id,
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'specialty' => 'required|string|max:255',
            'consultation_template' => 'nullable|string|max:255',
            'professional_license' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:doctor_admin,doctor_associate',
            'status' => 'required|in:active,passive,inactive',
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id',
        ]);

        $doctor->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'specialty' => $validated['specialty'],
            'consultation_template' => $validated['consultation_template'] ?? null,
            'professional_license' => $validated['professional_license'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (! empty($validated['password'])) {
            $doctor->update(['password' => Hash::make($validated['password'])]);
        }

        $doctor->syncRoles([$validated['role']]);

        $syncData = [];
        foreach ($validated['clinic_ids'] as $index => $clinicId) {
            $syncData[$clinicId] = ['is_primary' => $index === 0];
        }
        $doctor->clinics()->sync($syncData);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor actualizado exitosamente.');
    }

    public function toggle(User $doctor)
    {
        abort_if(! $doctor->hasAnyRole(self::DOCTOR_ROLES), 404);

        $newStatus = $doctor->isActive() ? 'inactive' : 'active';
        $doctor->update(['status' => $newStatus]);

        $msg = $doctor->isActive() ? 'activado' : 'desactivado';

        return redirect()->route('admin.doctors.index')
            ->with('success', "Doctor {$msg}.");
    }
}
