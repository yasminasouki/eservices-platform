@extends('layouts.auth')
@section('title', 'Two-Factor Verification — E-Services Platform')
@section('auth_brand_icon', 'bi-shield-lock-fill')
@section('auth_brand_title', 'Two-Factor Auth')
@section('heading', 'Verify your identity')
@section('subtitle', 'Enter the code from your authenticator app.')

@section('content')
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
