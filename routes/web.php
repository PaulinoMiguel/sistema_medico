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
use App\Http\Controllers\PediatricMeasurementController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InstallationSettingController;
use App\Http\Controllers\RoleController;
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

    // Clinics management — NOT inside clinic.required because the doctor
    // needs to be able to create their first clinic from a state of zero.
    Route::middleware('permission:clinics.manage')->group(function () {
        Route::resource('clinics', ClinicController::class)->except(['show']);
    });

    // ====================================================================
    // Operational routes — require at least one clinic to exist for the
    // logged-in doctor. The clinic.required middleware redirects to
    // /clinics/create if the doctor has no clinics yet.
    // ====================================================================
    Route::middleware('clinic.required')->group(function () {

        // Staff (secretaries, nurses) management — explicitly NOT for managing
        // doctors, which is exclusive to the super admin via /admin.
        Route::middleware('permission:staff.manage')->group(function () {
            Route::resource('secretaries', SecretaryController::class);
            Route::patch('/secretaries/{secretary}/toggle', [SecretaryController::class, 'toggle'])->name('secretaries.toggle');
        });

        // Roles & permissions management
        Route::middleware('permission:roles.manage')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::patch('/users/{user}/role', [RoleController::class, 'assignRole'])->name('users.assign-role');
        });

        // Patients (gated by patients.view; finer permissions enforced in controller)
        Route::middleware('permission:patients.view')->group(function () {
            Route::resource('patients', PatientController::class);
            Route::get('/patients/{patient}/history', [PatientController::class, 'history'])->name('patients.history');
            Route::post('/patients/{patient}/photo', [PatientController::class, 'updatePhoto'])->name('patients.photo');
            Route::delete('/patients/{patient}/photo', [PatientController::class, 'deletePhoto'])->name('patients.photo.delete');
            Route::get('/api/patients/{patient}/last-consultation', [PatientController::class, 'lastConsultation'])->name('patients.last-consultation');

            // Pediatria: mediciones + curvas de crecimiento
            Route::get('/patients/{patient}/growth', [PediatricMeasurementController::class, 'charts'])->name('patients.growth');
            Route::post('/patients/{patient}/measurements', [PediatricMeasurementController::class, 'store'])->name('patients.measurements.store');
            Route::delete('/patients/{patient}/measurements/{measurement}', [PediatricMeasurementController::class, 'destroy'])->name('patients.measurements.destroy');
            Route::post('/api/patients/{patient}/measurements/calculate', [PediatricMeasurementController::class, 'calculate'])->name('patients.measurements.calculate');
        });

        // Appointments (gated by appointments.view)
        Route::middleware('permission:appointments.view')->group(function () {
            Route::resource('appointments', AppointmentController::class);
            Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
        });

        // Installation branding + module toggles
        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('/settings', [InstallationSettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [InstallationSettingController::class, 'update'])->name('settings.update');
        });

        // Services catalog (income side) — each doctor manages their own.
        Route::middleware(['permission:services.manage', 'module:services'])->group(function () {
            Route::post('/services/quick', [ServiceController::class, 'quickStore'])->name('services.quick-store');
            Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
            Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');
        });

        // Expense categories — clinic-wide catalog
        Route::middleware('permission:expense-categories.manage')->group(function () {
            Route::post('/expense-categories/quick', [ExpenseCategoryController::class, 'quickStore'])->name('expense-categories.quick-store');
            Route::resource('expense-categories', ExpenseCategoryController::class)->only(['index', 'store', 'update']);
            Route::patch('/expense-categories/{expenseCategory}/toggle', [ExpenseCategoryController::class, 'toggle'])->name('expense-categories.toggle');
        });

        // Expenses
        Route::middleware(['module:expenses'])->group(function () {
            Route::middleware('permission:expenses.view')->group(function () {
                Route::resource('expenses', ExpenseController::class)->except(['show']);
            });
            Route::middleware('permission:expenses.view-summary')->group(function () {
                Route::get('/financial-summary', [ExpenseController::class, 'summary'])->name('expenses.summary');
            });
            Route::middleware('permission:expenses.view-my-summary')->group(function () {
                Route::get('/my-financial-summary', [ExpenseController::class, 'mySummary'])->name('expenses.my-summary');
            });
            Route::middleware('permission:expenses.view-shared-pool')->group(function () {
                Route::get('/shared-pool', [ExpenseController::class, 'sharedPool'])->name('expenses.shared-pool');
            });
        });

        // Payments — view and create are gated independently
        Route::get('/payments/create', [PaymentController::class, 'create'])
            ->middleware('permission:payments.create')->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])
            ->middleware('permission:payments.create')->name('payments.store');
        Route::get('/payments', [PaymentController::class, 'index'])
            ->middleware('permission:payments.view')->name('payments.index');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])
            ->middleware('permission:payments.view')->name('payments.show');

        // Cash register
        Route::middleware(['module:cash_register'])->group(function () {
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
        });

        // Consultations — read separated from write
        Route::middleware('permission:consultations.view')->group(function () {
            Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
            Route::get('/consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
        });
        Route::middleware('permission:consultations.create')->group(function () {
            Route::get('/consultations-create', [ConsultationController::class, 'create'])->name('consultations.create');
            Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
            Route::get('/consultations/{consultation}/edit', [ConsultationController::class, 'edit'])->name('consultations.edit');
            Route::put('/consultations/{consultation}', [ConsultationController::class, 'update'])->name('consultations.update');
            Route::post('/appointments/{appointment}/consultation', [ConsultationController::class, 'createFromAppointment'])->name('consultations.from-appointment');
        });

        // Prescriptions — read (view/print) separated from write (create/edit)
        Route::middleware(['module:prescriptions'])->group(function () {
            Route::middleware('permission:prescriptions.view')->group(function () {
                Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
                Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');
                Route::get('/prescriptions/{prescription}/pdf', [PrescriptionController::class, 'pdf'])->name('prescriptions.pdf');
            });
            Route::middleware('permission:prescriptions.create')->group(function () {
                Route::get('/prescriptions-create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
                Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
                Route::get('/prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
                Route::put('/prescriptions/{prescription}', [PrescriptionController::class, 'update'])->name('prescriptions.update');
                Route::delete('/prescriptions/{prescription}', [PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');
                Route::post('/consultations/{consultation}/prescription', [PrescriptionController::class, 'createFromConsultation'])->name('prescriptions.from-consultation');
            });
        });
    });
});
