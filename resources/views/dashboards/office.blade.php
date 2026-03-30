@extends('layouts.office')

@section('title', 'Office Dashboard')

@php
    $__statusBadge = fn (string $status) => match ($status) {
        'pending' => 'bg-warning text-dark',
        'in_review' => 'bg-info text-dark',
        'missing_documents' => 'bg-warning',
        'approved' => 'bg-primary',
        'rejected' => 'bg-danger',
        'completed' => 'bg-success',
        default => 'bg-secondary',
    };
    $__statusLabel = fn (string $status) => str($status)->replace('_', ' ')->title()->toString();
@endphp

@section('content')
    @if(auth()->user()->must_change_password)
        <div class="alert alert-warning rounded-3 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
            <div>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Security:</strong> Change your password before continuing.
            </div>
            <a href="{{ route('office.password.change') }}" class="btn btn-warning btn-sm fw-semibold text-nowrap">
                Change Password
            </a>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted mb-0">
                @if($offices->count() === 1)
                    {{ $offices->first()->name }}
                @else
                    {{ $offices->count() }} offices:
                    {{ $offices->pluck('name')->join(', ') }}
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @isset($officeContext)
                <a href="{{ route('office.requests.index', $officeContext) }}" class="btn btn-outline-success btn-sm">Requests</a>
                <a href="{{ route('office.profile.edit', $officeContext) }}" class="btn btn-outline-success btn-sm">Office profile</a>
                <a href="{{ route('office.categories.index', $officeContext) }}" class="btn btn-outline-success btn-sm">Categories</a>
                <a href="{{ route('office.services.index', $officeContext) }}" class="btn btn-outline-success btn-sm">Services</a>
            @endisset
            <a href="{{ route('office.password.change') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-key me-1"></i>Password
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-4 text-center h-100">
                <i class="bi bi-hourglass-split fs-1 text-warning mb-2"></i>
                <div class="text-muted small">Pending requests</div>
                <div class="fs-3 fw-bold">{{ $pending }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-4 text-center h-100">
                <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                <div class="text-muted small">Completed today</div>
                <div class="fs-3 fw-bold">{{ $completedToday }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-4 text-center h-100">
                <i class="bi bi-calendar-check fs-1 text-primary mb-2"></i>
                <div class="text-muted small">Appointments (today)</div>
                <div class="fs-3 fw-bold">{{ $appointmentsToday }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-4 text-center h-100">
                <i class="bi bi-star fs-1 text-info mb-2"></i>
                <div class="text-muted small">Average rating</div>
                <div class="fs-3 fw-bold">
                    {{ $averageRating !== null ? $averageRating . ' / 5' : '—' }}
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white fw-semibold border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="bi bi-inbox me-2 text-warning"></i>Recent requests</span>
            @isset($officeContext)
                <a href="{{ route('office.requests.index', $officeContext) }}" class="text-success small fw-semibold text-decoration-none">
                    View all requests →
                </a>
            @endisset
        </div>
        <div class="card-body p-0">
            @if($latestRequests->isEmpty())
                <p class="text-muted small mb-0 p-4">No requests yet for your office(s).</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Service</th>
                                <th>Citizen</th>
                                <th>Status</th>
                                <th class="pe-4">Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestRequests as $req)
                                <tr style="cursor: pointer;"
                                    onclick="window.location='{{ route('office.requests.show', [$req->government_office_id, $req->id]) }}'">
                                    <td class="ps-4 font-monospace">#{{ $req->id }}</td>
                                    <td>{{ $req->service?->name ?? '—' }}</td>
                                    <td>{{ $req->citizen?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $__statusBadge($req->status) }}">
                                            {{ $__statusLabel($req->status) }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-muted">
                                        {{ optional($req->submitted_at ?? $req->created_at)->format('M j, Y g:i a') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
