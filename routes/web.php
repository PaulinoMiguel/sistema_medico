<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDoctorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// Super Admin Auth
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('doctors', AdminDoctorController::class)->except(['show', 'destroy']);
        Route::patch('/doctors/{doctor}/toggle', [AdminDoctorController::class, 'toggle'])->name('doctors.toggle');
    });
});

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/clinic/select', [DashboardController::class, 'selectClinic'])->name('clinic.select');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

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

    // Financial - Services & Expense Categories (doctor only)
    Route::middleware('role:doctor')->group(function () {
        Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
        Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');
        Route::resource('expense-categories', ExpenseCategoryController::class)->only(['index', 'store', 'update']);
        Route::patch('/expense-categories/{expenseCategory}/toggle', [ExpenseCategoryController::class, 'toggle'])->name('expense-categories.toggle');
    });

    // Financial - Expenses (doctor only)
    Route::middleware('role:doctor')->group(function () {
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::get('/financial-summary', [ExpenseController::class, 'summary'])->name('expenses.summary');
    });

    // Financial - Payments & Cash Register (doctor + secretary)
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/cash-registers', [CashRegisterController::class, 'index'])->name('cash-registers.index');
    Route::post('/cash-registers/open', [CashRegisterController::class, 'open'])->name('cash-registers.open');
    Route::get('/cash-registers/{cashRegister}', [CashRegisterController::class, 'show'])->name('cash-registers.show');
    Route::post('/cash-registers/{cashRegister}/close', [CashRegisterController::class, 'close'])->name('cash-registers.close');

    // Consultations (doctor only)
    Route::middleware('role:doctor')->group(function () {
        Route::resource('consultations', ConsultationController::class)->except(['destroy']);
        Route::post('/appointments/{appointment}/consultation', [ConsultationController::class, 'createFromAppointment'])->name('consultations.from-appointment');
    });

    // Prescriptions (doctor only)
    Route::middleware('role:doctor')->group(function () {
        Route::resource('prescriptions', PrescriptionController::class);
        Route::get('/prescriptions/{prescription}/pdf', [PrescriptionController::class, 'pdf'])->name('prescriptions.pdf');
        Route::post('/consultations/{consultation}/prescription', [PrescriptionController::class, 'createFromConsultation'])->name('prescriptions.from-consultation');
    });
});
