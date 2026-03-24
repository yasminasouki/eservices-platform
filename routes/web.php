<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\GovernmentOfficeController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminServiceOperationsController;
use App\Http\Controllers\Admin\AdminUserManagementController;
use Illuminate\Support\Facades\Route;

// ── Root ──────────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Guest-only routes ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.attempt');

    // Social Login
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

});

// ── Password Reset (no auth required) ────────────────────────────────────────
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// ── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');

    // 2FA routes (no 2fa_verified required — these build that state)
    Route::get('/2fa/setup', [AuthController::class, 'show2faSetup'])->name('2fa.setup');
    Route::post('/2fa/setup', [AuthController::class, 'confirm2faSetup'])->name('2fa.setup.confirm');
    Route::get('/2fa/verify', [AuthController::class, 'show2faVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [AuthController::class, 'verify2fa'])->name('2fa.verify.confirm');

    // ── Routes requiring verified email + 2FA ────────────────────────────────
    Route::middleware(['verified', '2fa'])->group(function () {

        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::resource('/admin/offices', GovernmentOfficeController::class)
                ->except(['show'])
                ->parameters(['offices' => 'office'])
                ->names('admin.offices');

            Route::get('/admin/office-users', [AdminUserManagementController::class, 'officeUsersIndex'])
                ->name('admin.office-users.index');
            Route::post('/admin/office-users', [AdminUserManagementController::class, 'officeUsersStore'])
                ->name('admin.office-users.store');
            Route::patch('/admin/office-users/{user}/toggle-active', [AdminUserManagementController::class, 'officeUsersToggleActive'])
                ->name('admin.office-users.toggle-active');

            Route::get('/admin/citizens', [AdminUserManagementController::class, 'citizensIndex'])
                ->name('admin.citizens.index');
            Route::patch('/admin/citizens/{user}/toggle-active', [AdminUserManagementController::class, 'citizensToggleActive'])
                ->name('admin.citizens.toggle-active');

            Route::get('/admin/service-requests', [AdminServiceOperationsController::class, 'index'])
                ->name('admin.service-requests.index');

            Route::get('/admin/reports', [AdminReportsController::class, 'index'])
                ->name('admin.reports.index');
        });

        Route::middleware('role:office_user')->group(function () {
            Route::get('/office/dashboard', fn() => view('dashboards.office'))->name('office.dashboard');
        });

        Route::middleware('role:citizen')->group(function () {
            Route::get('/citizen/dashboard', fn() => view('dashboards.citizen'))->name('citizen.dashboard');
        });
    });
});
