@extends('layouts.office')

@section('title', 'Service requests')

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
            <h2 class="fw-bold mb-1">Service requests</h2>
            <p class="text-muted small mb-0">
                {{ $office->name }} — inbox scoped to this office. Dates use submission time when recorded, otherwise creation time.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('office.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <div class="card card-soft mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('office.requests.index', $office) }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label for="q" class="form-label small text-muted mb-1">Search citizen</label>
                    <input type="text" name="q" id="q" class="form-control" placeholder="Name or email"
                           value="{{ $filters['q'] ?? '' }}" maxlength="100">
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
                    <label for="date_from" class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="date_to" class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-8 col-lg-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <a href="{{ route('office.requests.index', $office) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <h6 class="fw-semibold mb-3">Counts by status <span class="text-muted fw-normal small">(respects filters below)</span></h6>
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
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Updated</th>
                        <th></th>
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
                            <td>
                                <span class="badge {{ $statusBadgeClass($req->status) }}">
                                    {{ $statusLabel($req->status) }}
                                </span>
                            </td>
                            <td>{{ $req->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $req->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('office.requests.show', [$office, $req]) }}" class="btn btn-sm btn-outline-success">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted p-4">No requests match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer bg-white border-0 pt-0">
                {{ $requests->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
