<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SecretaryController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/clinic/select', [DashboardController::class, 'selectClinic'])->name('clinic.select');

    // Doctor-only (admin)
    Route::middleware('role:doctor')->group(function () {
        Route::resource('clinics', ClinicController::class)->except(['show']);
        Route::resource('secretaries', SecretaryController::class);
        Route::patch('/secretaries/{secretary}/toggle', [SecretaryController::class, 'toggle'])->name('secretaries.toggle');
    });

    // Patients & Appointments (doctor + secretary)
    Route::resource('patients', PatientController::class);
    Route::get('/patients/{patient}/history', [PatientController::class, 'history'])->name('patients.history');
    Route::resource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');

    // Consultations (doctor only)
    Route::middleware('role:doctor')->group(function () {
        Route::resource('consultations', ConsultationController::class)->except(['destroy']);
        Route::post('/appointments/{appointment}/consultation', [ConsultationController::class, 'createFromAppointment'])->name('consultations.from-appointment');
    });
});
