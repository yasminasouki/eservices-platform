@extends('layouts.admin')

@section('title', 'Service operations')

@php
    $statusBadgeClass = fn (string $status) => match ($status) {
        'pending' => 'bg-warning text-dark',
        'in_review' => 'bg-info text-dark',
        'missing_documents' => 'bg-warning',
        'approved' => 'bg-primary',
        'rejected' => 'bg-danger',
        'completed' => 'bg-success',
        default => 'bg-secondary',
    };
    $statusLabel = fn (string $status) => str($status)->replace('_', ' ')->title()->toString();
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Service operations</h2>
            <p class="text-muted mb-0">Monitor service requests across offices with filters and status breakdown. Date range applies to submission time when recorded, otherwise to when the request was created.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
    </div>

    <div class="card card-soft mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.service-requests.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label for="government_office_id" class="form-label small text-muted mb-1">Office</label>
                    <select name="government_office_id" id="government_office_id" class="form-select">
                        <option value="">All offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" @selected((string) ($filters['government_office_id'] ?? '') === (string) $office->id)>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="status" class="form-label small text-muted mb-1">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach(\App\Models\ServiceRequest::STATUSES as $s)
                            <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>
                                {{ $statusLabel($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="date_from" class="form-label small text-muted mb-1">From (submitted)</label>
                    <input type="date" name="date_from" id="date_from" class="form-control"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="date_to" class="form-label small text-muted mb-1">To (submitted)</label>
                    <input type="date" name="date_to" id="date_to" class="form-control"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-8 col-lg-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <a href="{{ route('admin.service-requests.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <h6 class="fw-semibold mb-3">Counts by status <span class="text-muted fw-normal small">(respects office &amp; date filters)</span></h6>
    <div class="row g-3 mb-4">
        @foreach($statusCounts as $status => $count)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card card-soft p-3 h-100">
                    <div class="small text-muted mb-1">{{ $statusLabel($status) }}</div>
                    <div class="fs-4 fw-bold">{{ $count }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Requests</span>
            <span class="text-muted small fw-normal">{{ $requests->total() }} in this list</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Citizen</th>
                        <th>Service</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="fw-semibold">#{{ $req->id }}</td>
                            <td>
                                <div>{{ $req->citizen?->name ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $req->citizen?->email ?? '' }}</div>
                            </td>
                            <td>{{ $req->service?->name ?? 'N/A' }}</td>
                            <td>{{ $req->governmentOffice?->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $statusBadgeClass($req->status) }}">
                                    {{ $statusLabel($req->status) }}
                                </span>
                            </td>
                            <td>{{ $req->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $req->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No requests match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $requests->links() }}
    </div>
@endsection
