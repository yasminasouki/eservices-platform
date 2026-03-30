@extends('layouts.office')

@section('title', 'Service categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Service categories</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.categories.create', $office) }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg me-1"></i>New category
        </a>
    </div>

    <div class="card card-soft">
        <div class="card-body p-0">
            @if($categories->isEmpty())
                <p class="text-muted p-4 mb-0">No categories yet. Create one before adding services.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Services</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $cat->name }}</div>
                                        @if($cat->description)
                                            <div class="text-muted text-truncate" style="max-width:28rem;">{{ $cat->description }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $cat->services_count }}</td>
                                    <td class="pe-4 text-end text-nowrap">
                                        <a href="{{ route('office.categories.edit', [$office, $cat]) }}" class="btn btn-outline-secondary btn-sm py-0">Edit</a>
                                        <form action="{{ route('office.categories.destroy', [$office, $cat]) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this category? All services in it will be deleted.');">
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
                <div class="p-3 border-top">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
@endsection
