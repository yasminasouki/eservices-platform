@extends('layouts.auth')
@section('auth_body_class', 'auth-variant-admin')
@section('title', 'Admin Login — E-Services Platform')
@section('auth_brand_icon', 'bi-shield-lock-fill')
@section('heading', 'Admin sign-in')
@section('heading_badge', 'Admin')
@section('subtitle', 'Use your administrator credentials. This area is not for public accounts.')

@section('content')
    <form method="POST" action="{{ route('admin.login.attempt') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Admin Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Enter your password" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" class="text-decoration-none small text-primary">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-shield-lock me-2"></i>Admin Sign In
        </button>

        <div class="auth-links-stack text-center small text-muted pt-1">
            <p class="mb-0">
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">← Citizen portal</a>
            </p>
        </div>
    </form>

    @push('scripts')
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    </script>
    @endpush
@endsection
