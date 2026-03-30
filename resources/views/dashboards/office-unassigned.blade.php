@extends('layouts.office')

@section('title', 'No office assignment')

@section('content')
    <div class="alert alert-warning rounded-3">
        <h2 class="h5 fw-bold mb-2">
            <i class="bi bi-building-exclamation me-2"></i>No office assigned
        </h2>
        <p class="mb-0">
            Your account is not linked to any government office yet. Ask a platform administrator
            to assign you in <strong>Admin → Municipality users</strong> with an office selected.
        </p>
    </div>
@endsection
