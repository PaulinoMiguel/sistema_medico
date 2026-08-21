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
        $from = $request->query('from');
        $to = $request->query('to');

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

        // Filtro por rango de fechas (sobre la fecha del cobro).
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $total = (clone $query)->sum('amount');
        $count = (clone $query)->count();
        $payments = $query->latest()->paginate(20);

        return view('payments.index', compact('payments', 'channel', 'total', 'count', 'from', 'to'));
    }

    public function create(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $channel = $request->query('channel', 'cash_register');
        $user = $request->user();

        // "Mis cobros" sigue siendo exclusivo de los doctores. El canal de caja
        // se le permite al doctor solo si la caja abierta es suya.
        if ($channel === 'doctor_direct') {
            abort_unless($user->isDoctor(), 403);
        } else {
            $this->assertCanUseCashRegister($user, $clinicId);
        }

        $patients = Patient::whereHas('clinics', function ($q) use ($clinicId) {
            $q->where('clinics.id', $clinicId);
        })->orderBy('first_name')->get();

        $services = Service::where('is_active', true)
            ->whereHas('doctor', fn ($q) => $q->whereIn('status', ['active', 'passive']))
            ->orderBy('name')
            ->get();

        $selectedPatientId = $request->query('patient_id');
        $selectedAppointmentId = $request->query('appointment_id');

        $unpaidAppointments = Appointment::where('clinic_id', $clinicId)
            ->where('is_paid', false)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->with(['patient', 'doctor'])
            ->orderByDesc('scheduled_at')
            ->get();

        return view('payments.create', compact('patients', 'services', 'selectedPatientId', 'selectedAppointmentId', 'channel', 'unpaidAppointments'));
    }

    public function store(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $user = $request->user();
        $channel = $request->input('channel', 'cash_register');

        if ($channel === 'doctor_direct') {
            abort_unless($user->isDoctor(), 403);
        } else {
            $this->assertCanUseCashRegister($user, $clinicId);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'service_id' => 'nullable|exists:services,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            // Se admite 0: todos los turnos se cobran, aunque sea de forma
            // simbolica, para que no quede ninguno pendiente de cierre.
            'amount' => 'required|numeric|min:0',
            'concept' => 'required|string|max:255',
            'payment_method' => 'required|in:cash,transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->assertAppointmentMatchesPatient($validated);

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

        if (!empty($validated['appointment_id'])) {
            Appointment::withoutGlobalScopes()
                ->where('id', $validated['appointment_id'])
                ->update(['is_paid' => true]);
        }

        if ($channel === 'doctor_direct') {
            return redirect()->route('payments.index', ['channel' => 'doctor_direct'])
                ->with('success', 'Cobro personal registrado exitosamente.');
        }

        // Cash register payments always return to the cash register panel
        return redirect()->route('cash-registers.index')
            ->with('success', "Cobro registrado exitosamente. Recibo: {$payment->receipt_number}");
    }

    /**
     * Cobro rápido desde la pantalla del turno: el paciente, turno y doctor ya
     * se conocen, así que solo se ingresa servicio/concepto/monto y se vuelve
     * al turno (sin pasar por Caja). Canal cash_register.
     *
     * Lo normal es que lo use la secretaria, pero no se le niega al doctor:
     * el día que no hay secretaria es él quien cobra.
     */
    public function storeForAppointment(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        $clinicId = session('active_clinic_id');

        $this->assertCanUseCashRegister($user, $clinicId);

        if ($appointment->is_paid) {
            return redirect()->route('appointments.show', $appointment)
                ->with('info', 'Este turno ya fue cobrado.');
        }

        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            // Se admite 0: todos los turnos se cobran, aunque sea de forma
            // simbolica, para que no quede ninguno pendiente de cierre.
            'amount' => 'required|numeric|min:0',
            'concept' => 'required|string|max:255',
            'payment_method' => 'required|in:cash,transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        $cashRegisterId = CashRegister::where('clinic_id', $clinicId)
            ->where('status', 'open')
            ->value('id');

        $payment = Payment::create([
            'patient_id' => $appointment->patient_id,
            'service_id' => $validated['service_id'] ?? null,
            'appointment_id' => $appointment->id,
            'amount' => $validated['amount'],
            'concept' => $validated['concept'],
            'notes' => $validated['notes'] ?? null,
            'clinic_id' => $clinicId,
            'doctor_id' => $appointment->doctor_id,
            'channel' => 'cash_register',
            'payment_method' => $validated['payment_method'],
            'received_by' => $user->id,
            'cash_register_id' => $cashRegisterId,
            'receipt_number' => $this->generateReceiptNumber($clinicId),
        ]);

        $appointment->update(['is_paid' => true]);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "Cobro registrado. Recibo: {$payment->receipt_number}");
    }

    /**
     * Determine which doctor owns this payment based on available context.
     * Returns null if no doctor can be resolved.
     */
    /**
     * La caja es de quien la abrio.
     *
     * Un doctor no cobra en la caja de la secretaria: ese dinero entraria a
     * una gaveta que el no maneja y le descuadraria el cierre a ella. Si
     * quiere cobrar por caja, abre la suya; si no, tiene "Mis cobros".
     *
     * Entre personal de caja no aplica: comparten la misma gaveta, que es
     * justamente para lo que existe.
     */
    private function assertCanUseCashRegister($user, int $clinicId): void
    {
        if (! $user->isDoctor()) {
            return;
        }

        $caja = CashRegister::where('clinic_id', $clinicId)->where('status', 'open')->first();

        if ($caja && (int) $caja->opened_by !== (int) $user->id) {
            abort(403, 'Esta caja la abrió ' . ($caja->openedBy->name ?? 'otra persona')
                . '. Para cobrar por caja abre la tuya, o registra el cobro en "Mis cobros".');
        }
    }

    /**
     * Si el cobro viene de un turno, el paciente tiene que ser el del turno.
     * El formulario rellena el paciente al elegir turno, pero nada impedia
     * cambiarlo despues y guardar el cobro de un paciente contra el turno de
     * otro.
     */
    private function assertAppointmentMatchesPatient(array $validated): void
    {
        if (empty($validated['appointment_id'])) {
            return;
        }

        $appointment = Appointment::withoutGlobalScopes()->find($validated['appointment_id']);

        if ($appointment && (int) $appointment->patient_id !== (int) $validated['patient_id']) {
            throw ValidationException::withMessages([
                'patient_id' => 'El paciente seleccionado no es el del turno elegido. '
                    . 'Corrige el paciente o quita el turno.',
            ]);
        }
    }

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

        // El personal sin payments.view (secretaria) solo puede ver recibos de
        // caja; los cobros directos del doctor (cirugías, etc.) quedan privados.
        $user = auth()->user();
        if (! $user->isDoctor() && ! $user->can('payments.view')) {
            abort_if($payment->channel !== 'cash_register', 403);
        }

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
