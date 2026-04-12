<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $doctorRoles = ['doctor_admin', 'doctor_associate'];
        $totalDoctors = User::role($doctorRoles)->count();
        $activeDoctors = User::role($doctorRoles)->where('status', 'active')->count();
        $totalClinics = Clinic::count();

        return view('admin.dashboard', compact('totalDoctors', 'activeDoctors', 'totalClinics'));
    }
}
