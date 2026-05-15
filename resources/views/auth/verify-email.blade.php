@extends('layouts.auth')
@section('title', 'Verify Email — E-Services Platform')
@section('auth_brand_icon', 'bi-person-fill')
@section('auth_brand_title', 'Citizen Portal')
@section('heading', 'Check your inbox')
@section('subtitle', 'Enter the 6-digit code we sent to your email address.')

@section('content')

    @if(session('success'))
        <div class="alert alert-success small"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    {{-- Code entry form --}}
    <form method="POST" action="{{ route('verification.code') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-600 small">Verification code</label>
            <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
                   class="form-control form-control-lg text-center fw-800 @error('code') is-invalid @enderror"
                   placeholder="000000" autocomplete="one-time-code" autofocus
                   style="font-size:1.8rem;letter-spacing:.3em;border-color:#ddd6fe;">
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-check-circle me-2"></i>Verify Email
        </button>
    </form>

    <div class="text-center text-muted small mb-3">Didn't receive a code?</div>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary w-100 mb-3">
            <i class="bi bi-send me-2"></i>Send new code
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-link text-muted small text-decoration-none p-0">
            <i class="bi bi-arrow-left me-1"></i>Use a different account
        </button>
    </form>

@endsection
