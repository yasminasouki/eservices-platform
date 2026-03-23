@extends('layouts.auth')
@section('title', 'Two-Factor Verification — E-Services Platform')
@section('subtitle', 'Verify your identity')

@section('content')
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3"
             style="width:64px;height:64px;">
            <i class="bi bi-shield-fill-check text-primary fs-3"></i>
        </div>
        <h5 class="fw-bold mb-1">Two-Factor Authentication</h5>
        <p class="text-muted small mb-0">
            Open your authenticator app and enter the 6-digit code,<br>
            or enter one of your recovery codes.
        </p>
    </div>

    <form method="POST" action="{{ route('2fa.verify.confirm') }}">
        @csrf
        <div class="mb-4">
            <label for="code" class="form-label fw-semibold">Authentication Code</label>
            <input type="text" id="code" name="code"
                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                   placeholder="000000" maxlength="20" inputmode="numeric"
                   autocomplete="one-time-code" required autofocus>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">You can also enter a recovery code (format: XXXXX-XXXXX).</div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-unlock me-2"></i>Verify & Continue
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-link text-muted small text-decoration-none p-0">
            <i class="bi bi-arrow-left me-1"></i>Use a different account
        </button>
    </form>
@endsection
