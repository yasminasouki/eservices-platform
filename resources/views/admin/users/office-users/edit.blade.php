@extends('layouts.admin')

@section('title', 'Edit municipality user')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="small mb-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.office-users.index') }}">Municipality users</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">Edit municipality user</h2>
            <p class="text-muted mb-0">Update profile, office assignments, or send a password reset email.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.office-users.password-reset', $user) }}" class="d-inline"
                  onsubmit="return confirm('Send a password reset link to {{ e($user->email) }}?');">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-envelope me-1"></i>Email password reset
                </button>
            </form>
            <a href="{{ route('admin.office-users.index') }}" class="btn btn-outline-primary">Back to list</a>
        </div>
    </div>

    @php
        $oldAssignments = old('assignments');
        $assignedIds = $oldAssignments !== null
            ? array_map('intval', (array) $oldAssignments)
            : $user->governmentOffices->pluck('id')->all();
    @endphp

    <div class="card card-soft mb-4">
        <div class="card-header bg-white border-0 fw-semibold">Account</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.office-users.update', $user) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" maxlength="50">
                </div>
                <div class="col-md-6">
                    <label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current">
                    <div class="form-text">If set, clears “must change password” on next login.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm new password</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password">
                </div>

                <div class="col-12">
                    <hr class="my-2">
                    <h6 class="fw-semibold mb-3">Office assignments</h6>
                    <p class="text-muted small">Staff can belong to multiple offices. Set an optional role label per office.</p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:3rem"></th>
                                    <th>Office</th>
                                    <th style="min-width:12rem">Role in office</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offices as $office)
                                    @php
                                        $pivotRole = $user->governmentOffices->firstWhere('id', $office->id)?->pivot?->role_in_office;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox" name="assignments[]"
                                                   value="{{ $office->id }}" id="asg-{{ $office->id }}"
                                                   @checked(in_array((int) $office->id, $assignedIds, true))>
                                        </td>
                                        <td>
                                            <label class="form-check-label mb-0" for="asg-{{ $office->id }}">{{ $office->name }}</label>
                                        </td>
                                        <td>
                                            <input type="text" name="role[{{ $office->id }}]" class="form-control form-control-sm"
                                                   value="{{ old('role.'.$office->id, $pivotRole) }}"
                                                   maxlength="100" placeholder="e.g. clerk">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-semibold me-2">Status:</span>
                @if($user->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.office-users.toggle-active', $user) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                    {{ $user->is_active ? 'Deactivate account' : 'Activate account' }}
                </button>
            </form>
        </div>
    </div>
@endsection
