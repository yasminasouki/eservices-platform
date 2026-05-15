@extends('layouts.auth')
@section('title', '2FA Setup — E-Services Platform')
@section('auth_brand_icon', 'bi-shield-lock-fill')
@section('auth_brand_title', 'Security Setup')
@section('heading', 'Set up 2FA')
@section('subtitle', 'Scan the QR code with your authenticator app.')

@section('content')

    {{-- Step 1: QR Code --}}
    <div class="bg-light rounded-3 p-3 text-center mb-3">
        <img src="{{ $qrCode }}" alt="2FA QR Code" class="img-fluid mb-2" style="max-width:180px;">
        <p class="small text-muted mb-1">Can't scan the QR code? Enter this key manually:</p>
        <code class="fs-6 fw-bold text-dark user-select-all">{{ $secret }}</code>
    </div>

    {{-- Step 2: Recovery Codes --}}
    <div class="alert alert-warning mb-3">
        <p class="fw-semibold mb-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Save your recovery codes now
        </p>
        <p class="small mb-2">
            If you lose access to your authenticator app, use these one-time codes to sign in.
            Store them somewhere safe — they won't be shown again.
        </p>
        <div class="row row-cols-2 g-1">
            @foreach($recoveryCodes as $code)
                <div class="col">
                    <code class="small bg-white d-block text-center py-1 rounded border user-select-all">{{ $code }}</code>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Step 3: Confirm --}}
    <form method="POST" action="{{ route('2fa.setup.confirm') }}">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label fw-semibold">
                Enter the 6-digit code from your authenticator app
            </label>
            <input type="text" id="code" name="code"
                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                   placeholder="000000" maxlength="6" inputmode="numeric"
                   autocomplete="one-time-code" required autofocus>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-shield-check me-2"></i>Confirm & Enable 2FA
        </button>
    </form>
@endsection
