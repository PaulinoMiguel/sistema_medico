<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function index()
    {
        $clinics = auth()->user()->clinics()->withCount(['users', 'patients'])->get();

        return view('clinics.index', compact('clinics'));
    }
}
