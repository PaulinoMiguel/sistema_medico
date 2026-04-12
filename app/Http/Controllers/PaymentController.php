<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashRegister;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $payments = Payment::where('clinic_id', $clinicId)
            ->with(['patient', 'service', 'receivedBy'])
            ->latest()
            ->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $patients = Patient::whereHas('clinics', function ($q) use ($clinicId) {
            $q->where('clinics.id', $clinicId);
        })->orderBy('first_name')->get();

        $services = Service::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedPatientId = $request->query('patient_id');
        $selectedAppointmentId = $request->query('appointment_id');

        return view('payments.create', compact('patients', 'services', 'selectedPatientId', 'selectedAppointmentId'));
    }

    public function store(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'service_id' => 'nullable|exists:services,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Resolve the doctor that owns this income. Priority:
        //   1) Doctor from the linked appointment (most explicit)
        //   2) Patient's primary doctor (fallback for cobros without turno)
        //   3) Currently logged-in user, only if they themselves are a doctor
        $doctorId = $this->resolveDoctorId($validated);

        if (!$doctorId) {
            throw ValidationException::withMessages([
                'patient_id' => 'No se pudo determinar a que doctor pertenece este cobro. '
                    . 'Vincule el cobro a un turno o asegurese de que el paciente '
                    . 'tenga un doctor responsable asignado.',
            ]);
        }

        // Find open cash register
        $cashRegister = CashRegister::where('clinic_id', $clinicId)
            ->where('status', 'open')
            ->first();

        $payment = Payment::create([
            ...$validated,
            'clinic_id' => $clinicId,
            'doctor_id' => $doctorId,
            'received_by' => auth()->id(),
            'cash_register_id' => $cashRegister?->id,
            'receipt_number' => $this->generateReceiptNumber($clinicId),
        ]);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Cobro registrado exitosamente.');
    }

    /**
     * Determine which doctor owns this payment based on available context.
     * Returns null if no doctor can be resolved.
     */
    private function resolveDoctorId(array $validated): ?int
    {
        if (!empty($validated['appointment_id'])) {
            $appointment = Appointment::withoutGlobalScopes()->find($validated['appointment_id']);
            if ($appointment?->doctor_id) {
                return $appointment->doctor_id;
            }
        }

        $patient = Patient::withoutGlobalScopes()->find($validated['patient_id']);
        if ($patient?->primary_doctor_id) {
            return $patient->primary_doctor_id;
        }

        $user = auth()->user();
        if ($user?->isDoctor()) {
            return $user->id;
        }

        return null;
    }

    public function show(Payment $payment)
    {
        abort_if($payment->clinic_id != session('active_clinic_id'), 403);

        $payment->load(['patient', 'service', 'receivedBy', 'appointment']);

        return view('payments.show', compact('payment'));
    }

    private function generateReceiptNumber(int $clinicId): string
    {
        $count = Payment::where('clinic_id', $clinicId)
            ->whereYear('created_at', now()->year)
            ->count();

        return 'REC-' . now()->format('Y') . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
