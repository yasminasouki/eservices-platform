@extends('layouts.citizen')

@section('title', 'My requests')

@php
    $statusBadge = fn (string $status) => match ($status) {
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
            <h2 class="fw-bold mb-1">My requests</h2>
            <p class="text-muted small mb-0">Track status and reference codes for your submissions.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('citizen.offices.index') }}" class="btn btn-primary btn-sm">Browse services</a>
            <a href="{{ route('citizen.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
        </div>
    </div>

    <div class="card card-soft">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Service</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="ps-4 font-monospace small">#{{ $req->id }}</td>
                            <td>{{ $req->service?->name ?? '—' }}</td>
                            <td>{{ $req->governmentOffice?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $statusBadge($req->status) }}">{{ $statusLabel($req->status) }}</span>
                            </td>
                            <td class="text-muted small">{{ $req->submitted_at?->format('M j, Y') ?? $req->created_at?->format('M j, Y') }}</td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('citizen.requests.show', $req) }}" class="btn btn-sm btn-outline-primary">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted p-4">You have not submitted any requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer bg-white border-0">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
