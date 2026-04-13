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
        $channel = $request->query('channel');
        $user = $request->user();

        $query = Payment::where('clinic_id', $clinicId)
            ->with(['patient', 'service', 'receivedBy']);

        if ($channel === 'doctor_direct') {
            // "Mis cobros" — only doctor's own direct payments
            abort_unless($user->isDoctor(), 403);
            $query->where('channel', 'doctor_direct')
                  ->where('doctor_id', $user->id);
        } else {
            // Cobros de caja — secretaries should never see doctor_direct
            if (!$user->isDoctor()) {
                $query->where('channel', 'cash_register');
            }
        }

        $total = (clone $query)->sum('amount');
        $payments = $query->latest()->paginate(20);

        return view('payments.index', compact('payments', 'channel', 'total'));
    }

    public function create(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $channel = $request->query('channel', 'cash_register');
        $user = $request->user();

        if ($channel === 'doctor_direct') {
            abort_unless($user->isDoctor(), 403);
        } else {
            abort_if($user->isDoctor(), 403, 'Los cobros de caja son exclusivos del personal.');
        }

        $patients = Patient::whereHas('clinics', function ($q) use ($clinicId) {
            $q->where('clinics.id', $clinicId);
        })->orderBy('first_name')->get();

        $services = Service::where('is_active', true)
            ->whereHas('doctor', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $selectedPatientId = $request->query('patient_id');
        $selectedAppointmentId = $request->query('appointment_id');

        return view('payments.create', compact('patients', 'services', 'selectedPatientId', 'selectedAppointmentId', 'channel'));
    }

    public function store(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $user = $request->user();
        $channel = $request->input('channel', 'cash_register');

        if ($channel === 'doctor_direct') {
            abort_unless($user->isDoctor(), 403);
        } else {
            abort_if($user->isDoctor(), 403, 'Los cobros de caja son exclusivos del personal.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'service_id' => 'nullable|exists:services,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($channel === 'doctor_direct') {
            // Doctor personal payment — no cash register, doctor owns it
            $doctorId = $user->id;
            $cashRegisterId = null;
        } else {
            // Cash register payment — resolve doctor from context
            $doctorId = $this->resolveDoctorId($validated);

            if (!$doctorId) {
                throw ValidationException::withMessages([
                    'patient_id' => 'No se pudo determinar a que doctor pertenece este cobro. '
                        . 'Vincule el cobro a un turno o asegurese de que el paciente '
                        . 'tenga un doctor responsable asignado.',
                ]);
            }

            $cashRegisterId = CashRegister::where('clinic_id', $clinicId)
                ->where('status', 'open')
                ->value('id');
        }

        $payment = Payment::create([
            ...$validated,
            'clinic_id' => $clinicId,
            'doctor_id' => $doctorId,
            'channel' => $channel,
            'received_by' => auth()->id(),
            'cash_register_id' => $cashRegisterId,
            'receipt_number' => $this->generateReceiptNumber($clinicId),
        ]);

        if ($channel === 'doctor_direct') {
            return redirect()->route('payments.index', ['channel' => 'doctor_direct'])
                ->with('success', 'Cobro personal registrado exitosamente.');
        }

        // Cash register payments always return to the cash register panel
        return redirect()->route('cash-registers.index')
            ->with('success', "Cobro registrado exitosamente. Recibo: {$payment->receipt_number}");
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
