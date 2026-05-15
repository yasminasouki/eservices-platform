@extends('layouts.auth')
@section('title', 'Verify Email — E-Services Platform')
@section('auth_brand_icon', 'bi-person-fill')
@section('auth_brand_title', 'Citizen Portal')
@section('heading', 'Check your inbox')
@section('subtitle', 'Click the verification link we sent to your email.')

@section('content')
    <div class="alert alert-info small">
        <i class="bi bi-info-circle me-2"></i>
        Didn't receive the email? Check your spam folder or resend it below.
    </div>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-send me-2"></i>Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-link text-muted small text-decoration-none p-0">
            <i class="bi bi-arrow-left me-1"></i>Use a different account
        </button>
    </form>
@endsection
