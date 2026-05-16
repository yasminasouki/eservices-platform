<?php

use App\Http\Controllers\Auth\MunicipalityAuthController;
use Illuminate\Support\Facades\Route;

// ── Municipality Staff Portal ─────────────────────────────────────────────────

Route::middleware('guest')->group(function () {

    Route::get('/municipality/login', [MunicipalityAuthController::class, 'showLoginForm'])
        ->name('municipality.login');

    Route::post('/municipality/login', [MunicipalityAuthController::class, 'login'])
        ->name('municipality.login.attempt');

});

// Password reset — no auth required
Route::get('/municipality/forgot-password', [MunicipalityAuthController::class, 'showForgotForm'])
    ->name('municipality.password.request');
Route::post('/municipality/forgot-password', [MunicipalityAuthController::class, 'sendResetLink'])
    ->name('municipality.password.email');
Route::get('/municipality/reset-password/{token}', [MunicipalityAuthController::class, 'showResetForm'])
    ->name('municipality.password.reset');
Route::post('/municipality/reset-password', [MunicipalityAuthController::class, 'resetPassword'])
    ->name('municipality.password.update');
