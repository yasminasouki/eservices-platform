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
