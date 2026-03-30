@extends('layouts.citizen')

@section('title', 'Government offices')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Browse offices</h2>
            <p class="text-muted small mb-0">Choose an office to see available public services.</p>
        </div>
        <a href="{{ route('citizen.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
    </div>

    <div class="card card-soft">
        <div class="card-body p-0">
            @if($offices->isEmpty())
                <p class="text-muted mb-0 p-4">No active offices are available yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Office</th>
                                <th>Municipality</th>
                                <th>Services</th>
                                <th class="pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offices as $o)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $o->name }}</td>
                                    <td class="text-muted small">{{ $o->municipality?->name ?? '—' }}</td>
                                    <td>{{ $o->services_count }}</td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('citizen.offices.show', $o) }}" class="btn btn-sm btn-primary">View services</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($offices->hasPages())
                    <div class="card-footer bg-white border-0">{{ $offices->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection
