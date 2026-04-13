@extends('layouts.admin')

@section('title', 'Municipality Users')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Municipality Users</h2>
            <p class="text-muted mb-0">Create office users and activate/deactivate accounts.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="card card-soft mb-4">
        <div class="card-header bg-white border-0 fw-semibold">Create Municipality User</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.office-users.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assign Office</label>
                    <select name="government_office_id" class="form-select">
                        <option value="">-- Unassigned --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" @selected((string) old('government_office_id') === (string) $office->id)>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role in Office</label>
                    <input type="text" name="role_in_office" class="form-control" value="{{ old('role_in_office') }}" placeholder="manager / officer / clerk">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Create User
                    </button>
                </div>
            </form>
        </div>
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
                        <th>Assigned Offices</th>
                        <th>Status</th>
                        <th class="text-end" style="min-width:11rem">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($officeUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="small">
                                @if($user->governmentOffices->isEmpty())
                                    <span class="text-muted">Unassigned</span>
                                @else
                                    {{ $user->governmentOffices->pluck('name')->join(', ') }}
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.office-users.edit', $user) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('admin.office-users.toggle-active', $user) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No municipality users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $officeUsers->links() }}
    </div>
@endsection
