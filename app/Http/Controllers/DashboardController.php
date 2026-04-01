<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $clinics = $user->clinics;
        $clinicId = session('active_clinic_id');

        // If no clinics yet (doctor just registered), show setup prompt
        if ($clinics->isEmpty() && $user->isDoctor()) {
            return view('dashboard-setup');
        }

        // If clinic not selected or invalid, select first available
        if (! $clinicId || ! $clinics->contains('id', $clinicId)) {
            $clinicId = $clinics->first()?->id;
            session(['active_clinic_id' => $clinicId]);
        }

        $todayAppointments = collect();
        $totalPatients = 0;
        $pendingAppointments = 0;

        if ($clinicId) {
            $todayAppointments = Appointment::where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', today())
                ->with(['patient', 'doctor'])
                ->orderBy('scheduled_at')
                ->get();

            $totalPatients = Patient::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinics.id', $clinicId);
            })->count();

            $pendingAppointments = Appointment::where('clinic_id', $clinicId)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->where('scheduled_at', '>=', now())
                ->count();
        }

        return view('dashboard', compact(
            'todayAppointments',
            'totalPatients',
            'pendingAppointments',
            'clinics',
            'clinicId',
        ));
    }

    public function selectClinic(Request $request)
    {
        $request->validate(['clinic_id' => 'required|exists:clinics,id']);

        $user = $request->user();
        $clinicId = $request->clinic_id;

        // Verify user belongs to this clinic
        if (! $user->clinics()->where('clinics.id', $clinicId)->exists()) {
            abort(403);
        }

        session(['active_clinic_id' => $clinicId]);

        return redirect()->back();
    }
}
