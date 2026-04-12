<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $view = $request->get('view', 'week'); // week, month, day
        $date = \Carbon\Carbon::parse($request->get('date', today()->toDateString()));

        if ($view === 'month') {
            $startOfPeriod = $date->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
            $endOfPeriod = $date->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
        } elseif ($view === 'week') {
            $startOfPeriod = $date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $endOfPeriod = $date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        } else {
            $startOfPeriod = $date->copy()->startOfDay();
            $endOfPeriod = $date->copy()->endOfDay();
        }

        $appointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startOfPeriod, $endOfPeriod])
            ->with(['patient', 'doctor'])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn ($a) => $a->scheduled_at->toDateString());

        // Build calendar days
        $days = [];
        $current = $startOfPeriod->copy();
        while ($current <= $endOfPeriod) {
            $key = $current->toDateString();
            $days[$key] = [
                'date' => $current->copy(),
                'appointments' => $appointments->get($key, collect()),
                'isToday' => $current->isToday(),
                'isCurrentMonth' => $current->month === $date->month,
            ];
            $current->addDay();
        }

        return view('appointments.index', compact('days', 'date', 'view'));
    }

    public function create(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $user = $request->user();

        $patients = Patient::whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        // Doctors create appointments only for themselves — the doctor field
        // is hidden in the view and auto-filled in store(). Secretaries see
        // a dropdown filtered to active doctors of the current clinic.
        $showDoctorSelect = !$user->isDoctor();
        $doctors = $showDoctorSelect
            ? User::role(['doctor_admin', 'doctor_associate'])
                ->whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
                ->where('status', 'active')
                ->get()
            : collect();

        return view('appointments.create', compact('patients', 'doctors', 'showDoctorSelect'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'scheduled_at' => 'required|date|after_or_equal:today',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'type' => 'required|in:first_visit,follow_up,pre_operative,post_operative,urodynamic_study,procedure,emergency,surgical',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ];

        // Doctors auto-assign themselves; only secretaries (and other staff)
        // need to pick a doctor explicitly.
        if (!$user->isDoctor()) {
            $rules['doctor_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules, [
            'patient_id.required' => 'Debes seleccionar un paciente.',
            'patient_id.exists' => 'El paciente seleccionado no existe.',
            'doctor_id.required' => 'Debes seleccionar un doctor.',
            'doctor_id.exists' => 'El doctor seleccionado no existe.',
            'scheduled_at.required' => 'La fecha y hora son obligatorias.',
            'scheduled_at.date' => 'La fecha y hora no son validas.',
            'scheduled_at.after_or_equal' => 'La fecha del turno no puede ser anterior a hoy.',
            'type.required' => 'Debes seleccionar un tipo de turno.',
            'type.in' => 'El tipo de turno seleccionado no es valido.',
        ]);

        if ($user->isDoctor()) {
            $validated['doctor_id'] = $user->id;
        }

        $this->checkOverlap($validated['doctor_id'], $validated['scheduled_at'], $validated['duration_minutes'] ?? 30);

        $validated['clinic_id'] = session('active_clinic_id');
        $validated['created_by'] = $user->id;
        $validated['status'] = 'scheduled';

        $appointment = Appointment::create($validated);

        return redirect()->route('appointments.index', ['date' => $appointment->scheduled_at->toDateString()])
            ->with('success', 'Turno creado exitosamente.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'clinic']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Request $request, Appointment $appointment)
    {
        $clinicId = session('active_clinic_id');
        $user = $request->user();

        $patients = Patient::whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        $showDoctorSelect = !$user->isDoctor();
        $doctors = $showDoctorSelect
            ? User::role(['doctor_admin', 'doctor_associate'])
                ->whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
                ->where('status', 'active')
                ->get()
            : collect();

        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'showDoctorSelect'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'type' => 'required|in:first_visit,follow_up,pre_operative,post_operative,urodynamic_study,procedure,emergency,surgical',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ];

        if (!$user->isDoctor()) {
            $rules['doctor_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules, [
            'patient_id.required' => 'Debes seleccionar un paciente.',
            'doctor_id.required' => 'Debes seleccionar un doctor.',
            'scheduled_at.required' => 'La fecha y hora son obligatorias.',
            'scheduled_at.date' => 'La fecha y hora no son validas.',
            'type.required' => 'Debes seleccionar un tipo de turno.',
        ]);

        if ($user->isDoctor()) {
            $validated['doctor_id'] = $user->id;
        }

        $this->checkOverlap($validated['doctor_id'], $validated['scheduled_at'], $validated['duration_minutes'] ?? $appointment->duration_minutes ?? 30, $appointment->id);

        $appointment->update($validated);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Turno actualizado exitosamente.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('appointments.index')
            ->with('success', 'Turno cancelado exitosamente.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,confirmed,in_waiting_room,in_progress,completed,cancelled,no_show',
            'cancellation_reason' => 'nullable|required_if:status,cancelled|string',
        ]);

        $appointment->status = $validated['status'];

        if ($validated['status'] === 'cancelled') {
            $appointment->cancelled_at = now();
            $appointment->cancellation_reason = $validated['cancellation_reason'] ?? null;
        }

        $appointment->save();

        return redirect()->back()->with('success', 'Estado del turno actualizado.');
    }

    private function checkOverlap(int $doctorId, string $scheduledAt, int $durationMinutes, ?int $excludeId = null): void
    {
        $start = Carbon::parse($scheduledAt);
        $end = $start->copy()->addMinutes($durationMinutes);

        $overlap = Appointment::withoutGlobalScopes()
            ->where('doctor_id', $doctorId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($start, $end, $durationMinutes) {
                $q->where(function ($q) use ($start, $end, $durationMinutes) {
                    // El turno existente empieza antes de que termine el nuevo
                    // Y el turno existente termina después de que empiece el nuevo
                    $q->where('scheduled_at', '<', $end)
                       ->whereRaw(
                           'DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_minutes, 30) MINUTE) > ?',
                           [$start]
                       );
                });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Ya existe un turno programado para este doctor en ese horario.',
            ]);
        }
    }
}
