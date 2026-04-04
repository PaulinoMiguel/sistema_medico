<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalDoctors = User::where('role', 'doctor')->count();
        $activeDoctors = User::where('role', 'doctor')->where('is_active', true)->count();
        $totalClinics = Clinic::count();

        return view('admin.dashboard', compact('totalDoctors', 'activeDoctors', 'totalClinics'));
    }
}
