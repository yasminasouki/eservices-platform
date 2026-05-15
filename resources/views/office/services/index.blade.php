@extends('layouts.office')

@section('title', 'Services')

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* Action buttons header */
    .btn-categories {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #f0fdf4; border: 1px solid #d1fae5; color: #15803d;
        font-weight: 600; font-size: .84rem; padding: .44rem 1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-categories:hover { background: #dcfce7; }
    .btn-new-service {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .84rem; padding: .45rem 1.1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-new-service:hover { background: #15803d; color: #fff; }

    /* Main card */
    .services-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }

    /* Table */
    .svc-table { width: 100%; border-collapse: collapse; }
    .svc-table thead th {
        background: #f0fdf4; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #86efac;
        padding: .65rem 1rem; border-bottom: 1px solid #d1fae5; white-space: nowrap;
    }
    .svc-table thead th:first-child { padding-left: 1.4rem; }
    .svc-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }
    .svc-table tbody tr { border-bottom: 1px solid #f0fdf4; transition: background .12s; }
    .svc-table tbody tr:last-child { border-bottom: none; }
    .svc-table tbody tr:hover { background: #f0fdf4; }
    .svc-table tbody td {
        padding: .85rem 1rem; vertical-align: middle; font-size: .875rem; color: #374151;
    }
    .svc-table tbody td:first-child { padding-left: 1.4rem; }
    .svc-table tbody td:last-child  { padding-right: 1.4rem; }

    /* Service name */
    .svc-name { font-weight: 700; color: #0f2d13; margin-bottom: .1rem; }
    .svc-desc { font-size: .76rem; color: #52916b; }

    /* Status pills */
    .pill-active { background: #dcfce7; color: #14532d; }
    .pill-hidden { background: #f3f4f6; color: #6b7280; }
    .status-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 700;
        padding: .28rem .7rem; border-radius: 999px; white-space: nowrap;
    }

    /* Action buttons row */
    .act-group { display: flex; align-items: center; gap: .4rem; justify-content: flex-end; }
    .act-btn-edit {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .74rem; font-weight: 600; padding: .3rem .75rem;
        border-radius: 7px; border: 1px solid #d1fae5;
        background: #f0fdf4; color: #14532d; text-decoration: none; white-space: nowrap;
        transition: background .12s;
    }
    .act-btn-edit:hover { background: #dcfce7; }
    .act-btn-delete {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .74rem; font-weight: 600; padding: .3rem .75rem;
        border-radius: 7px; border: 1px solid #fecaca;
        background: #fef2f2; color: #991b1b; cursor: pointer; white-space: nowrap;
        transition: background .12s;
    }
    .act-btn-delete:hover { background: #fde8e8; }

    /* Empty state */
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon {
        width: 72px; height: 72px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #86efac; font-size: 1.9rem; margin: 0 auto 1.1rem;
    }
    .empty-state h6 { font-weight: 700; color: #0f2d13; margin-bottom: .3rem; }
    .empty-state p  { font-size: .84rem; color: #52916b; margin-bottom: 0; }

    /* Pagination */
    .svc-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f0fdf4; }
    .svc-pagination .pagination { margin: 0; }
    .svc-pagination .page-link {
        border-radius: 7px !important; border-color: #dcfce7;
        color: #15803d; font-size: .82rem; margin: 0 .1rem;
    }
    .svc-pagination .page-link:hover { background: #dcfce7; border-color: #86efac; }
    .svc-pagination .page-item.active .page-link { background: #16a34a; border-color: #16a34a; color: #fff; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="page-title">Services</div>
            <p class="page-sub">{{ $office->name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('office.categories.index', $office) }}" class="btn-categories">
                <i class="bi bi-tags"></i>Categories
            </a>
            <a href="{{ route('office.services.create', $office) }}" class="btn-new-service">
                <i class="bi bi-plus-lg"></i>New service
            </a>
        </div>
    </div>

    <div class="services-card">
        @if($services->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-grid"></i></div>
                <h6>No services yet</h6>
                <p>Add a category first, then create services here.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="svc-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $svc)
                            <tr>
                                <td>
                                    <div class="svc-name">{{ $svc->name }}</div>
                                    @if($svc->description)
                                        <div class="svc-desc" style="max-width:22rem;">{{ \Illuminate\Support\Str::limit($svc->description, 80) }}</div>
                                    @endif
                                </td>
                                <td style="font-size:.84rem;">{{ $svc->category?->name ?? '—' }}</td>
                                <td style="font-weight:600;color:#0f2d13;">${{ number_format((float) $svc->price, 2) }}</td>
                                <td style="font-size:.84rem;">
                                    @if($svc->duration)
                                        {{ $svc->duration }} {{ $svc->duration_unit }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($svc->is_active)
                                        <span class="status-pill pill-active">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="status-pill pill-hidden">
                                            <i class="bi bi-eye-slash"></i> Hidden
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="act-group">
                                        <a href="{{ route('office.services.edit', [$office, $svc]) }}" class="act-btn-edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('office.services.destroy', [$office, $svc]) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this service?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act-btn-delete">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($services->hasPages())
                <div class="svc-pagination">{{ $services->links() }}</div>
            @endif
        @endif
    </div>

@endsection
