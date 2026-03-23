@extends('layouts.auth')
@section('title', 'Verify Email — E-Services Platform')
@section('subtitle', 'One more step')

@section('content')
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle mb-3"
             style="width:64px;height:64px;">
            <i class="bi bi-envelope-check-fill text-warning fs-3"></i>
        </div>
        <h5 class="fw-bold mb-1">Verify Your Email Address</h5>
        <p class="text-muted small mb-0">
            We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
            Click the link in that email to activate your account.
        </p>
    </div>

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
