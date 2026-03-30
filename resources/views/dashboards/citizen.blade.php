@extends('layouts.citizen')

@section('title', 'My dashboard')

@section('content')
    @if(auth()->user()->id_document_status === 'pending')
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-clock-history fs-5 me-3"></i>
            <div>
                <strong>Identity Verification Pending</strong><br>
                <span class="small">Your national ID is being reviewed. Some features may be limited until verification is complete.</span>
            </div>
        </div>
    @endif

    <h2 class="fw-bold mb-1">Welcome, {{ auth()->user()->name }}</h2>
    <p class="text-muted mb-4">Browse government services and track your requests.</p>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-4 text-center h-100">
                <i class="bi bi-file-earmark-plus fs-1 text-primary mb-2"></i>
                <div class="text-muted small">Active requests</div>
                <div class="fs-3 fw-bold">{{ $activeRequests }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-4 text-center h-100">
                <i class="bi bi-check2-all fs-1 text-success mb-2"></i>
                <div class="text-muted small">Completed</div>
                <div class="fs-3 fw-bold">{{ $completedRequests }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-4 text-center h-100">
                <i class="bi bi-calendar-event fs-1 text-info mb-2"></i>
                <div class="text-muted small">Upcoming appointments</div>
                <div class="fs-3 fw-bold">{{ $upcomingAppointments > 0 ? $upcomingAppointments : '—' }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-4 text-center h-100">
                <i class="bi bi-wallet2 fs-1 text-warning mb-2"></i>
                <div class="text-muted small">Total paid</div>
                <div class="fs-3 fw-bold">{{ number_format($totalPaid, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card card-soft">
                <div class="card-header bg-white fw-semibold border-0 pt-3">
                    <i class="bi bi-search me-2 text-primary"></i>Browse services
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Find a government office and request a service online.</p>
                    <a href="{{ route('citizen.offices.index') }}" class="btn btn-primary btn-sm">View offices</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-soft">
                <div class="card-header bg-white fw-semibold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-2 text-secondary"></i>Recent requests</span>
                    <a href="{{ route('citizen.requests.index') }}" class="small">View all</a>
                </div>
                <div class="card-body p-0">
                    @if($recentRequests->isEmpty())
                        <p class="text-muted small mb-0 p-3">No requests yet.</p>
                    @else
                        <ul class="list-group list-group-flush small">
                            @foreach($recentRequests as $req)
                                <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $req->service?->name ?? 'Request #'.$req->id }}</div>
                                        <div class="text-muted">{{ $req->governmentOffice?->name }}</div>
                                    </div>
                                    <a href="{{ route('citizen.requests.show', $req) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
