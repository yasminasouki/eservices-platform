@extends('layouts.office')

@section('title', 'Change Password')

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* Password card */
    .pw-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #d1fae5;
        box-shadow: 0 2px 20px rgba(21,128,61,.07);
        padding: 2rem 2.25rem;
        max-width: 440px; margin: 0 auto;
    }

    .pw-icon-circle {
        width: 56px; height: 56px; background: #dcfce7; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #16a34a; font-size: 1.4rem; margin: 0 auto 1.1rem;
    }

    .pw-card h4 {
        font-size: 1.1rem; font-weight: 800; color: #0f2d13;
        text-align: center; margin-bottom: .25rem;
    }
    .pw-card .pw-sub {
        font-size: .83rem; color: #52916b; text-align: center; margin-bottom: 1.5rem;
    }

    .form-label { font-size: .82rem; font-weight: 600; color: #14532d; }
    .form-control { border-color: #d1fae5; font-size: .875rem; }
    .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }

    .btn-update-pw {
        display: flex; align-items: center; justify-content: center; gap: .4rem;
        width: 100%; background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .9rem; padding: .6rem 1.25rem;
        border-radius: 9px; transition: background .15s; cursor: pointer;
    }
    .btn-update-pw:hover { background: #15803d; }

    .btn-back-link {
        display: flex; align-items: center; justify-content: center; gap: .3rem;
        color: #52916b; text-decoration: none; font-size: .82rem; margin-top: .85rem;
        transition: color .12s;
    }
    .btn-back-link:hover { color: #14532d; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="mb-4 text-center">
        <div class="page-title">Change Password</div>
        <p class="page-sub">Update your account security credentials.</p>
    </div>

    <div class="pw-card">

        <div class="pw-icon-circle">
            <i class="bi bi-key-fill"></i>
        </div>

        <h4>Change Password</h4>
        <p class="pw-sub">Update your account password below.</p>

        <form method="POST" action="{{ route('office.password.update') }}">
            @csrf

            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" id="current_password" name="current_password"
                       class="form-control @error('current_password') is-invalid @enderror"
                       required>
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" required>
            </div>

            <button type="submit" class="btn-update-pw">
                <i class="bi bi-check-lg"></i>Update Password
            </button>
        </form>

        <a href="{{ route('office.dashboard') }}" class="btn-back-link">
            <i class="bi bi-arrow-left"></i>Back to Dashboard
        </a>

    </div>

@endsection
