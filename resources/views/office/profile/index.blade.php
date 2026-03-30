@extends('layouts.office')

@section('title', 'Office profile')

@section('content')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Office profile</h2>
            <p class="text-muted mb-0">Update public details, location, hours, and extra contact options.</p>
        </div>
        <a href="{{ route('office.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    @if($offices->isEmpty())
        <div class="alert alert-warning rounded-3">
            No office is assigned to your account. Ask an administrator to link you to a government office.
        </div>
    @else
        <div class="row g-3">
            @foreach($offices as $o)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-soft h-100">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6 fw-bold">{{ $o->name }}</h3>
                            <p class="text-muted small flex-grow-1 mb-3">{{ \Illuminate\Support\Str::limit($o->address, 80) }}</p>
                            <a href="{{ route('office.profile.edit', $o) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-pencil me-1"></i>Edit profile
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
