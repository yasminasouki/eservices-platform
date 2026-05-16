@extends('layouts.auth')
@section('title', 'Reset Password — E-Services Platform')
@section('auth_body_class', 'auth-variant-municipality')
@section('auth_brand_icon', 'bi-building')
@section('auth_brand_title', __('ui.office_portal'))
@section('heading', __('ui.auth_reset_heading'))
@section('subtitle', __('ui.auth_reset_subtitle'))

@section('content')
    <form method="POST" action="{{ route('municipality.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('ui.auth_email') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $email) }}" placeholder="{{ __('ui.auth_forgot_email_ph') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('ui.auth_new_password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('ui.auth_new_password_ph') }}"
                       autocomplete="new-password" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-text">{{ __('ui.auth_password_strength') }}</div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">{{ __('ui.auth_confirm_new_password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" placeholder="{{ __('ui.auth_confirm_new_ph') }}"
                       autocomplete="new-password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ __('ui.auth_reset_btn') }}
        </button>

        <p class="text-center mb-0 small text-muted">
            <a href="{{ route('municipality.login') }}" class="text-decoration-none fw-semibold text-primary">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('ui.auth_back_to_login') }}
            </a>
        </p>
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
