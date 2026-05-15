@extends('layouts.auth')
@section('auth_body_class', 'auth-variant-admin')
@section('title', 'Admin Login — E-Services Platform')
@section('auth_brand_icon', 'bi-shield-lock-fill')
@section('auth_brand_title', __('ui.admin_panel'))
@section('heading', __('ui.auth_admin_heading'))
@section('heading_badge', __('ui.auth_admin_badge'))
@section('subtitle', __('ui.auth_admin_subtitle'))

@section('content')
    <form method="POST" action="{{ route('admin.login.attempt') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('ui.auth_admin_email') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="{{ __('ui.auth_email_placeholder') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('ui.auth_password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('ui.auth_password_placeholder') }}" required>
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
                <label class="form-check-label small" for="remember">{{ __('ui.auth_remember_me') }}</label>
            </div>
            <a href="{{ route('password.request') }}" class="text-decoration-none small text-primary">{{ __('ui.auth_forgot_password') }}</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-shield-lock me-2"></i>{{ __('ui.auth_admin_sign_in') }}
        </button>

        <div class="auth-links-stack text-center small text-muted pt-1">
            <p class="mb-0">
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">{{ __('ui.auth_citizen_portal_link') }}</a>
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
