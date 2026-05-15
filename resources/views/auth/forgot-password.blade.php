@extends('layouts.auth')
@section('title', 'Forgot Password — E-Services Platform')
@section('auth_brand_icon', 'bi-person-fill')
@section('auth_brand_title', 'Citizen Portal')
@section('heading', 'Reset your password')
@section('subtitle', 'Enter your email and we\'ll send you a reset link.')

@section('content')
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
