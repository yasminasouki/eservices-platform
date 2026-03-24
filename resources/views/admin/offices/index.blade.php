@extends('layouts.admin')

@section('title', 'Government Offices')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Government Offices</h2>
            <p class="text-muted mb-0">Create, edit, delete offices and assign municipalities.</p>
        </div>
        <a href="{{ route('admin.offices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Add Office
        </a>
    </div>

    <div class="card card-soft">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Municipality</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offices as $office)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $office->name }}</div>
                                <div class="text-muted small">{{ $office->address }}</div>
                            </td>
                            <td>{{ $office->municipality?->name ?? 'Unassigned' }}</td>
                            <td>
                                <div class="small">{{ $office->email ?? 'No email' }}</div>
                                <div class="text-muted small">{{ $office->phone ?? 'No phone' }}</div>
                            </td>
                            <td>
                                @if($office->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.offices.edit', $office) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.offices.destroy', $office) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this office?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No offices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $offices->links() }}
    </div>
@endsection
