<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $search = $request->get('search');

        $user = $request->user();

        $patients = Patient::query()
            ->when(!$user->isDoctor() && $clinicId, function ($q) use ($clinicId) {
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

        if (!$user->isDoctor()) {
            $clinicIds = $user->clinics()->pluck('clinics.id');
            $doctors = User::role(['doctor_admin', 'doctor_associate'])
                ->whereIn('status', ['active', 'passive'])
                ->whereHas('clinics', fn ($q) => $q->whereIn('clinics.id', $clinicIds))
                ->orderBy('name')
                ->get();
        }

        return view('patients.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $clinicId = session('active_clinic_id');
        if (!$clinicId) {
            return redirect()->route('dashboard')
                ->with('warning', 'No tienes clinicas asignadas. Contacta al administrador.');
        }

        $isAdult = false;
        if ($request->date_of_birth) {
            try {
                $dob = \Carbon\Carbon::parse($request->date_of_birth);
                $isAdult = $dob->age >= 18;
            } catch (\Exception $e) {}
        }

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
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        if (!$user->isDoctor()) {
            $rules['doctor_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        $doctorId = $user->isDoctor() ? $user->id : $validated['doctor_id'];

        // Server-side duplicate check by document number
        $docNumber = $validated['document_number'] ?? null;
        if ($docNumber) {
            $existing = Patient::withoutGlobalScopes()
                ->where('document_number', $docNumber)
                ->first();

            if ($existing) {
                $alreadyLinked = $existing->doctors()->where('doctor_id', $doctorId)->exists();
                if ($alreadyLinked) {
                    return redirect()->route('patients.show', $existing)
                        ->with('info', 'Este paciente ya existe y esta asociado a este doctor.');
                }

                return redirect()->route('patients.create')
                    ->with('duplicate_patient_id', $existing->id)
                    ->with('duplicate_patient_name', $existing->full_name)
                    ->with('duplicate_patient_doc', $existing->document_number)
                    ->with('duplicate_doctor_id', $doctorId)
                    ->withInput();
            }
        }

        $validated['registered_by'] = $user->id;
        unset($validated['doctor_id']);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('patient-photos', 'public');
        }
        unset($validated['photo']);

        $patient = Patient::withoutGlobalScopes()->create($validated);

        $patient->doctors()->attach($doctorId, ['is_primary' => true]);
        $patient->clinics()->attach($clinicId);
        $patient->medicalHistory()->create();

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Paciente registrado exitosamente.');
    }

    /**
     * Associate an existing patient with the current doctor.
     * Used when duplicate detection finds the patient already exists.
     */
    public function associate(Request $request, int $patient)
    {
        $patient = Patient::withoutGlobalScopes()->findOrFail($patient);
        $user = $request->user();
        $doctorId = $user->isDoctor() ? $user->id : $request->input('doctor_id');

        if (!$doctorId) {
            return back()->with('error', 'Debe seleccionar un doctor.');
        }

        $alreadyLinked = $patient->doctors()->where('doctor_id', $doctorId)->exists();
        if ($alreadyLinked) {
            return redirect()->route('patients.show', $patient)
                ->with('info', 'Este paciente ya esta asociado a este doctor.');
        }

        $patient->doctors()->attach($doctorId, ['is_primary' => false]);

        $clinicId = session('active_clinic_id');
        if ($clinicId && !$patient->clinics()->where('clinics.id', $clinicId)->exists()) {
            $patient->clinics()->attach($clinicId);
        }

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Paciente asociado exitosamente.');
    }

    /**
     * AJAX: search for existing patient by document number.
     * Bypasses MedicalRecordScope to find patients from any doctor.
     */
    public function checkDuplicate(Request $request)
    {
        $docNumber = $request->input('document_number');

        if (!$docNumber || strlen($docNumber) < 3) {
            return response()->json(['found' => false]);
        }

        $patient = Patient::withoutGlobalScopes()
            ->where('document_number', $docNumber)
            ->first();

        if (!$patient) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'patient' => [
                'id' => $patient->id,
                'full_name' => $patient->full_name,
                'document_number' => $patient->document_number,
                'date_of_birth' => $patient->date_of_birth->format('d/m/Y'),
                'gender' => $patient->gender,
            ],
        ]);
    }

    public function show(Patient $patient)
    {
        $patient->load(['medicalHistory', 'clinics', 'doctors', 'appointments' => function ($q) {
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
        $isAdult = false;
        if ($request->date_of_birth) {
            try {
                $dob = \Carbon\Carbon::parse($request->date_of_birth);
                $isAdult = $dob->age >= 18;
            } catch (\Exception $e) {}
        }

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
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gestational_age_weeks' => 'nullable|integer|min:22|max:44',
            'birth_weight_kg' => 'nullable|numeric|min:0.3|max:8',
            'birth_length_cm' => 'nullable|numeric|min:20|max:80',
        ]);

        if ($request->hasFile('photo')) {
            if ($patient->photo_path) {
                Storage::disk('public')->delete($patient->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('patient-photos', 'public');
        }
        unset($validated['photo']);

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

    public function updatePhoto(Request $request, Patient $patient)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($patient->photo_path) {
            Storage::disk('public')->delete($patient->photo_path);
        }

        $path = $request->file('photo')->store('patient-photos', 'public');
        $patient->update(['photo_path' => $path]);

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Foto del paciente actualizada.');
    }

    public function deletePhoto(Patient $patient)
    {
        if ($patient->photo_path) {
            Storage::disk('public')->delete($patient->photo_path);
            $patient->update(['photo_path' => null]);
        }

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Foto del paciente eliminada.');
    }

    public function lastConsultation(Patient $patient)
    {
        $consultation = \App\Models\Consultation::withoutGlobalScopes()
            ->where('patient_id', $patient->id)
            ->with('doctor')
            ->orderByDesc('consultation_date')
            ->first();

        if (!$consultation) {
            return response()->json(['consultation' => null]);
        }

        return response()->json([
            'consultation' => [
                'id' => $consultation->id,
                'date' => $consultation->consultation_date->format('d/m/Y') . ' — ' . $consultation->doctor->name,
                'is_signed' => $consultation->status === 'signed',
            ],
        ]);
    }
}
