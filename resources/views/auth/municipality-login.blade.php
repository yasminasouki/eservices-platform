@extends('layouts.auth')
@section('title', 'Municipality Portal — E-Services Platform')
@section('subtitle', 'Municipality Staff Access')

@section('content')
    <form method="POST" action="{{ route('municipality.login.attempt') }}" autocomplete="off">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="you@municipality.gov.lb" required autofocus
                       autocomplete="off" readonly onfocus="this.removeAttribute('readonly')">
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
                       placeholder="••••••••" required
                       autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')">
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
            <i class="bi bi-building me-2"></i>Municipality Sign In
        </button>

        <p class="text-center mb-0 small text-muted">
            Citizen portal?
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">Citizen Login</a>
        </p>
        <p class="text-center mt-2 mb-0 small text-muted">
            Admin access?
            <a href="{{ route('admin.login') }}" class="text-decoration-none fw-semibold text-primary">Admin Login</a>
        </p>
    </form>

    @push('scripts')
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const pwd  = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    </script>
    @endpush
@endsection
