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
        Route::get('/profile', [AdminAuthController::class, 'showProfile'])->name('profile');
        Route::put('/profile/password', [AdminAuthController::class, 'updatePassword'])->name('profile.password');
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

    // Clinics management
    Route::middleware('permission:clinics.manage')->group(function () {
        Route::resource('clinics', ClinicController::class)->except(['show']);
    });

    // Staff (secretaries, nurses) management — explicitly NOT for managing
    // doctors, which is exclusive to the super admin via /admin.
    Route::middleware('permission:staff.manage')->group(function () {
        Route::resource('secretaries', SecretaryController::class);
        Route::patch('/secretaries/{secretary}/toggle', [SecretaryController::class, 'toggle'])->name('secretaries.toggle');
    });

    // Patients (gated by patients.view; finer permissions enforced in controller)
    Route::middleware('permission:patients.view')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('/patients/{patient}/history', [PatientController::class, 'history'])->name('patients.history');
    });

    // Appointments (gated by appointments.view)
    Route::middleware('permission:appointments.view')->group(function () {
        Route::resource('appointments', AppointmentController::class);
        Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    });

    // Services catalog (income side)
    Route::middleware('permission:services.manage')->group(function () {
        Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
        Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');
    });

    // Expense categories
    Route::middleware('permission:expense-categories.manage')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->only(['index', 'store', 'update']);
        Route::patch('/expense-categories/{expenseCategory}/toggle', [ExpenseCategoryController::class, 'toggle'])->name('expense-categories.toggle');
    });

    // Expenses
    Route::middleware('permission:expenses.view')->group(function () {
        Route::resource('expenses', ExpenseController::class)->except(['show']);
    });
    Route::middleware('permission:expenses.view-summary')->group(function () {
        Route::get('/financial-summary', [ExpenseController::class, 'summary'])->name('expenses.summary');
    });

    // Payments — view and create are gated independently:
    //   secretaries can register cobros (create/store) via the cash register
    //   flow, but cannot navigate to the global payments index (which would
    //   expose income from outside the office, e.g. surgeries).
    // IMPORTANT: /payments/create must be declared BEFORE /payments/{payment}
    // so the literal "create" segment is not captured as a payment id.
    Route::get('/payments/create', [PaymentController::class, 'create'])
        ->middleware('permission:payments.create')->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])
        ->middleware('permission:payments.create')->name('payments.store');
    Route::get('/payments', [PaymentController::class, 'index'])
        ->middleware('permission:payments.view')->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])
        ->middleware('permission:payments.view')->name('payments.show');

    // Cash register
    Route::middleware('permission:cash-register.view')->group(function () {
        Route::get('/cash-registers', [CashRegisterController::class, 'index'])->name('cash-registers.index');
        Route::get('/cash-registers/{cashRegister}', [CashRegisterController::class, 'show'])->name('cash-registers.show');
    });
    Route::middleware('permission:cash-register.open')->group(function () {
        Route::post('/cash-registers/open', [CashRegisterController::class, 'open'])->name('cash-registers.open');
    });
    Route::middleware('permission:cash-register.close')->group(function () {
        Route::post('/cash-registers/{cashRegister}/close', [CashRegisterController::class, 'close'])->name('cash-registers.close');
    });

    // Consultations (write access requires consultations.create)
    Route::middleware('permission:consultations.create')->group(function () {
        Route::resource('consultations', ConsultationController::class)->except(['destroy']);
        Route::post('/appointments/{appointment}/consultation', [ConsultationController::class, 'createFromAppointment'])->name('consultations.from-appointment');
    });

    // Prescriptions (write access requires prescriptions.create)
    Route::middleware('permission:prescriptions.create')->group(function () {
        Route::resource('prescriptions', PrescriptionController::class);
        Route::get('/prescriptions/{prescription}/pdf', [PrescriptionController::class, 'pdf'])->name('prescriptions.pdf');
        Route::post('/consultations/{consultation}/prescription', [PrescriptionController::class, 'createFromConsultation'])->name('prescriptions.from-consultation');
    });
});
