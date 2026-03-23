@extends('layouts.auth')
@section('title', 'Forgot Password — E-Services Platform')
@section('subtitle', 'Reset your password')

@section('content')
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3"
             style="width:64px;height:64px;">
            <i class="bi bi-key-fill text-primary fs-3"></i>
        </div>
        <h5 class="fw-bold mb-1">Forgot Your Password?</h5>
        <p class="text-muted small mb-0">
            Enter your email address and we'll send you a link to reset your password.
        </p>
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-send me-2"></i>Send Reset Link
        </button>

        <p class="text-center mb-0 small text-muted">
            Remembered your password?
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">Sign in</a>
        </p>
    </form>
@endsection
