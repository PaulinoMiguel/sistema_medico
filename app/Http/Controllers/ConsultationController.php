<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $search = $request->get('search');

        $consultations = Consultation::where('clinic_id', $clinicId)
            ->with(['patient', 'doctor'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('consultation_date')
            ->paginate(20)
            ->withQueryString();

        return view('consultations.index', compact('consultations', 'search'));
    }

    /**
     * Start a consultation from an appointment.
     */
    public function createFromAppointment(Appointment $appointment)
    {
        // Check if consultation already exists for this appointment
        $existing = Consultation::where('appointment_id', $appointment->id)->first();
        if ($existing) {
            return redirect()->route('consultations.edit', $existing);
        }

        $consultation = Consultation::create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'clinic_id' => $appointment->clinic_id,
            'appointment_id' => $appointment->id,
            'consultation_date' => now(),
            'type' => $this->mapAppointmentType($appointment->type),
            'status' => 'in_progress',
        ]);

        // Update appointment status to in_progress
        if ($appointment->status !== 'in_progress') {
            $appointment->update(['status' => 'in_progress']);
        }

        return redirect()->route('consultations.edit', $consultation);
    }

    /**
     * Create a consultation without an appointment.
     */
    public function create(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $patients = Patient::whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        return view('consultations.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'type' => 'required|in:initial,follow_up,pre_operative,post_operative,emergency,urodynamic,procedure',
        ]);

        $consultation = Consultation::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $request->user()->id,
            'clinic_id' => session('active_clinic_id'),
            'consultation_date' => now(),
            'type' => $validated['type'],
            'status' => 'in_progress',
        ]);

        return redirect()->route('consultations.edit', $consultation);
    }

    public function show(Consultation $consultation)
    {
        $consultation->load(['patient.medicalHistory', 'doctor', 'clinic', 'appointment']);

        // Previous consultations for this patient
        $previousConsultations = Consultation::where('patient_id', $consultation->patient_id)
            ->where('id', '!=', $consultation->id)
            ->where('status', '!=', 'in_progress')
            ->orderByDesc('consultation_date')
            ->limit(5)
            ->get();

        return view('consultations.show', compact('consultation', 'previousConsultations'));
    }

    public function edit(Consultation $consultation)
    {
        if ($consultation->isSigned()) {
            return redirect()->route('consultations.show', $consultation)
                ->with('error', 'Esta consulta ya fue firmada y no puede editarse.');
        }

        $consultation->load(['patient.medicalHistory', 'doctor', 'clinic']);

        // Previous consultations
        $previousConsultations = Consultation::where('patient_id', $consultation->patient_id)
            ->where('id', '!=', $consultation->id)
            ->orderByDesc('consultation_date')
            ->limit(5)
            ->get();

        return view('consultations.edit', compact('consultation', 'previousConsultations'));
    }

    public function update(Request $request, Consultation $consultation)
    {
        if ($consultation->isSigned()) {
            return redirect()->back()->with('error', 'Consulta firmada, no se puede modificar.');
        }

        $validated = $request->validate([
            // Subjective
            'chief_complaint' => 'nullable|string',
            'history_present_illness' => 'nullable|string',
            'urinary_symptoms' => 'nullable|array',
            'sexual_function' => 'nullable|array',
            'specialty_data' => 'nullable|array',
            'review_of_systems' => 'nullable|string',
            // Objective
            'vital_signs' => 'nullable|array',
            'physical_exam' => 'nullable|string',
            'genitourinary_exam' => 'nullable|string',
            'rectal_exam' => 'nullable|string',
            'neurological_exam' => 'nullable|string',
            'abdomen_exam' => 'nullable|string',
            // Assessment
            'assessment' => 'nullable|string',
            'diagnoses' => 'nullable|array',
            // Plan
            'treatment_plan' => 'nullable|string',
            'diagnostic_orders' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'follow_up_days' => 'nullable|integer',
            'surgical_recommendation' => 'nullable|string',
            'referrals' => 'nullable|string',
            // Notes
            'private_notes' => 'nullable|string',
        ]);

        $consultation->update($validated);

        $action = $request->input('action', 'save');

        if ($action === 'sign') {
            $consultation->update([
                'status' => 'signed',
                'signed_at' => now(),
            ]);

            // Complete the appointment if linked
            if ($consultation->appointment) {
                $consultation->appointment->update(['status' => 'completed']);
            }

            return redirect()->route('consultations.show', $consultation)
                ->with('success', 'Consulta firmada exitosamente.');
        }

        return redirect()->back()->with('success', 'Consulta guardada.');
    }

    private function mapAppointmentType(string $type): string
    {
        return match ($type) {
            'first_visit' => 'initial',
            'follow_up' => 'follow_up',
            'pre_operative' => 'pre_operative',
            'post_operative' => 'post_operative',
            'urodynamic_study' => 'urodynamic',
            'procedure' => 'procedure',
            'emergency' => 'emergency',
            default => 'follow_up',
        };
    }
}
