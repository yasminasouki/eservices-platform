@extends('layouts.auth')
@section('title', 'Forgot Password — E-Services Platform')
@section('auth_brand_icon', 'bi-person-fill')
@section('auth_brand_title', __('ui.auth_citizen_portal'))
@section('heading', __('ui.auth_forgot_heading'))
@section('subtitle', __('ui.auth_forgot_subtitle'))

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">{{ __('ui.auth_email') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="{{ __('ui.auth_forgot_email_ph') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-send me-2"></i>{{ __('ui.auth_send_reset_link') }}
        </button>

        <p class="text-center mb-0 small text-muted">
            {{ __('ui.auth_remembered_password') }}
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">{{ __('ui.auth_sign_in_link') }}</a>
        </p>
    </form>
@endsection
