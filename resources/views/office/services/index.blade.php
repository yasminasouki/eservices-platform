@extends('layouts.office')

@section('title', 'Services')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Services</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('office.categories.index', $office) }}" class="btn btn-outline-secondary btn-sm">Categories</a>
            <a href="{{ route('office.services.create', $office) }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New service
            </a>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body p-0">
            @if($services->isEmpty())
                <p class="text-muted p-4 mb-0">No services yet. Add a category first, then create services here.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Service</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $svc)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $svc->name }}</div>
                                        @if($svc->description)
                                            <div class="text-muted text-truncate" style="max-width:20rem;">{{ $svc->description }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $svc->category?->name ?? '—' }}</td>
                                    <td>{{ number_format((float) $svc->price, 2) }}</td>
                                    <td>
                                        @if($svc->duration)
                                            {{ $svc->duration }} {{ $svc->duration_unit }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($svc->is_active)
                                            <span class="badge text-bg-success">Active</span>
                                        @else
                                            <span class="badge text-bg-secondary">Hidden</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end text-nowrap">
                                        <a href="{{ route('office.services.edit', [$office, $svc]) }}" class="btn btn-outline-secondary btn-sm py-0">Edit</a>
                                        <form action="{{ route('office.services.destroy', [$office, $svc]) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this service?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">{{ $services->links() }}</div>
            @endif
        </div>
    </div>
@endsection
