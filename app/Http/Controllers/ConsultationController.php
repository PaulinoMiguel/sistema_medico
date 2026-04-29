<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PediatricMeasurement;
use App\Services\GrowthService;
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

        $doctor = $appointment->doctor;
        $template = $this->resolveTemplate($doctor);

        $consultation = Consultation::create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'clinic_id' => $appointment->clinic_id,
            'appointment_id' => $appointment->id,
            'consultation_date' => now(),
            'type' => $this->mapAppointmentType($appointment->type),
            'consultation_template' => $template,
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

        $doctor = $request->user();
        $template = $this->resolveTemplate($doctor);

        $consultation = Consultation::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $doctor->id,
            'clinic_id' => session('active_clinic_id'),
            'consultation_date' => now(),
            'type' => $validated['type'],
            'consultation_template' => $template,
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

        // Si el doctor es pediatra y la consulta trae antropometria, sincroniza
        // la tabla pediatric_measurements (crea una o actualiza la que ya exista
        // para esta consulta). Asi los gastos de crecimiento se nutren de la
        // consulta automaticamente.
        $this->syncPediatricMeasurement($consultation, $request);

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

    /**
     * Si la especialidad del doctor es pediatria y la consulta trae peso/talla/PC
     * en specialty_data, crea o actualiza la medicion pediatrica vinculada.
     */
    private function syncPediatricMeasurement(Consultation $consultation, Request $request): void
    {
        $doctor = $consultation->doctor;
        if (! $doctor || $doctor->specialty !== 'pediatrics') {
            return;
        }

        $sd = (array) $request->input('specialty_data', []);
        $weight = $this->nullIfEmpty($sd['weight_kg'] ?? null);
        $height = $this->nullIfEmpty($sd['height_cm'] ?? null);
        $hc = $this->nullIfEmpty($sd['head_circumference_cm'] ?? null);

        // Sin datos antropometricos no hay nada que guardar; y si ya existia, la
        // borramos para evitar mediciones vacias huerfanas.
        if ($weight === null && $height === null && $hc === null) {
            PediatricMeasurement::where('consultation_id', $consultation->id)->delete();
            return;
        }

        $patient = $consultation->patient;
        if (! $patient || ! $patient->date_of_birth || ! in_array($patient->gender, ['male', 'female'])) {
            return;
        }

        $growth = app(GrowthService::class);
        $measuredAt = $consultation->consultation_date ?? now();
        $ageMonths = $patient->ageInMonthsAt($measuredAt);
        $correctedAge = $patient->isPreterm()
            ? $growth->correctedAgeMonths($patient->gestational_age_weeks, $ageMonths)
            : null;
        $zAge = $correctedAge ?? $ageMonths;
        $bmi = ($weight && $height) ? round($weight / (($height / 100) ** 2), 2) : null;

        PediatricMeasurement::updateOrCreate(
            ['consultation_id' => $consultation->id],
            [
                'patient_id' => $patient->id,
                'recorded_by' => $request->user()->id,
                'measured_at' => $measuredAt,
                'age_months' => $ageMonths,
                'corrected_age_months' => $correctedAge,
                'weight_kg' => $weight,
                'height_cm' => $height,
                'head_circumference_cm' => $hc,
                'bmi' => $bmi,
                'weight_z' => $weight ? $growth->zScore('weight_for_age', $patient->gender, $zAge, (float) $weight) : null,
                'height_z' => $height ? $growth->zScore('height_for_age', $patient->gender, $zAge, (float) $height) : null,
                'head_circumference_z' => $hc ? $growth->zScore('head_circumference_for_age', $patient->gender, $zAge, (float) $hc) : null,
                'bmi_z' => $bmi ? $growth->zScore('bmi_for_age', $patient->gender, $zAge, $bmi) : null,
            ],
        );
    }

    private function nullIfEmpty($v): ?float
    {
        return ($v === null || $v === '') ? null : (float) $v;
    }

    private function resolveTemplate($doctor): string
    {
        if ($doctor->consultation_template) {
            return $doctor->consultation_template;
        }

        $specialtyMap = [
            'urologia' => 'urology', 'pediatria' => 'pediatrics',
            'neurologia' => 'neurology', 'medicina_general' => 'general',
        ];

        $key = strtolower(str_replace(' ', '_', $doctor->specialty ?? 'general'));
        $key = $specialtyMap[$key] ?? $key;

        return $key . '_generic';
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
