@extends('layouts.admin')

@section('title', 'Citizen Accounts')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Citizen Accounts</h2>
            <p class="text-muted mb-0">Activate/deactivate citizen accounts and monitor verification status.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white border-0">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" value="{{ $search }}" placeholder="Search by name or email">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary">Search</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>ID Status</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($citizens as $citizen)
                        <tr>
                            <td>{{ $citizen->name }}</td>
                            <td>{{ $citizen->email }}</td>
                            <td>
                                @php
                                    $idStatus = $citizen->id_document_status ?? 'N/A';
                                    $idBadge = match($idStatus) {
                                        'verified' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'pending' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $idBadge }}">{{ $idStatus }}</span>
                            </td>
                            <td>
                                @if($citizen->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.citizens.show', $citizen) }}"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <form method="POST" action="{{ route('admin.citizens.toggle-active', $citizen) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $citizen->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $citizen->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No citizens found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $citizens->links() }}
    </div>
@endsection
