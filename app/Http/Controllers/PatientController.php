<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $search = $request->get('search');

        // The MedicalRecordScope already enforces visibility (doctor sees own,
        // secretary sees clinic-assigned). The clinic filter below is an
        // optional UI refinement, not a security boundary.
        $patients = Patient::query()
            ->when($clinicId, function ($q) use ($clinicId) {
                $q->whereHas('clinics', function ($q2) use ($clinicId) {
                    $q2->where('clinics.id', $clinicId);
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('document_number', 'like', "%{$search}%")
                      ->orWhere('medical_record_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        return view('patients.index', compact('patients', 'search'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $doctors = collect();

        // Non-doctors (secretary, nurse, ...) must pick which doctor owns
        // the new patient. Doctors get auto-assigned to themselves.
        if (!$user->isDoctor()) {
            $clinicIds = $user->clinics()->pluck('clinics.id');
            $doctors = User::role(['doctor_admin', 'doctor_associate'])
                ->where('status', 'active')
                ->whereHas('clinics', fn ($q) => $q->whereIn('clinics.id', $clinicIds))
                ->orderBy('name')
                ->get();
        }

        return view('patients.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Defense in depth: even though clinic.required middleware blocks
        // doctors without clinics, this guard prevents creating orphan
        // patients in any other code path.
        $clinicId = session('active_clinic_id');
        if (!$clinicId) {
            return redirect()->route('clinics.create')
                ->with('warning', 'Necesitas crear una clinica antes de registrar pacientes.');
        }

        $isAdult = $request->date_of_birth
            && now()->diffInYears($request->date_of_birth) >= 18;

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'second_last_name' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:male,female,other',
            'document_type' => 'nullable|string',
            'document_number' => $isAdult ? 'required|string|max:50' : 'nullable|string|max:50',
            'blood_type' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:100',
            'referred_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];

        if (!$user->isDoctor()) {
            $rules['primary_doctor_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        $validated['registered_by'] = $user->id;
        $validated['primary_doctor_id'] = $user->isDoctor()
            ? $user->id
            : $validated['primary_doctor_id'];

        $patient = Patient::create($validated);

        // Attach to current clinic (guaranteed not null by the guard above)
        $patient->clinics()->attach($clinicId);

        // Create empty medical history
        $patient->medicalHistory()->create();

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Paciente registrado exitosamente.');
    }

    public function show(Patient $patient)
    {
        $patient->load(['medicalHistory', 'clinics', 'appointments' => function ($q) {
            $q->latest('scheduled_at')->limit(10);
        }]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $isAdult = $request->date_of_birth
            && now()->diffInYears($request->date_of_birth) >= 18;

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'second_last_name' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:male,female,other',
            'document_type' => 'nullable|string',
            'document_number' => $isAdult ? 'required|string|max:50' : 'nullable|string|max:50',
            'blood_type' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:100',
            'referred_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Paciente actualizado exitosamente.');
    }

    public function destroy(Patient $patient)
    {
        $patient->update(['is_active' => false]);

        return redirect()->route('patients.index')
            ->with('success', 'Paciente desactivado exitosamente.');
    }

    public function history(Patient $patient)
    {
        $patient->load(['medicalHistory']);

        $consultations = \App\Models\Consultation::withoutGlobalScopes()
            ->where('patient_id', $patient->id)
            ->with('doctor')
            ->orderByDesc('consultation_date')
            ->get();

        $prescriptions = \App\Models\Prescription::withoutGlobalScopes()
            ->where('patient_id', $patient->id)
            ->with('items')
            ->orderByDesc('prescription_date')
            ->get();

        return view('patients.history', compact('patient', 'consultations', 'prescriptions'));
    }
}
