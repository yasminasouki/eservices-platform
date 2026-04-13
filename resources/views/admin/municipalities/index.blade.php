@extends('layouts.admin')

@section('title', 'Municipalities')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Municipalities</h2>
            <p class="text-muted mb-0">Create and manage municipalities. Government offices can be assigned to them.</p>
        </div>
        <a href="{{ route('admin.municipalities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Add municipality
        </a>
    </div>

    <div class="card card-soft">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Region</th>
                        <th>Offices</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($municipalities as $municipality)
                        <tr>
                            <td class="fw-semibold">{{ $municipality->name }}</td>
                            <td>{{ $municipality->region ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $municipality->government_offices_count }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.municipalities.edit', $municipality) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.municipalities.destroy', $municipality) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this municipality? Offices will become unassigned from it.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No municipalities yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $municipalities->links() }}
    </div>
@endsection
