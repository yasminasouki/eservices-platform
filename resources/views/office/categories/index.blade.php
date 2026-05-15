@extends('layouts.office')

@section('title', __('ui.office_cat_title'))

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    .btn-new-cat {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .84rem; padding: .45rem 1.1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-new-cat:hover { background: #15803d; color: #fff; }

    /* Main card */
    .cat-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }

    /* Table */
    .cat-table { width: 100%; border-collapse: collapse; }
    .cat-table thead th {
        background: #f0fdf4; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #86efac;
        padding: .65rem 1rem; border-bottom: 1px solid #d1fae5; white-space: nowrap;
    }
    .cat-table thead th:first-child { padding-left: 1.4rem; }
    .cat-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }
    .cat-table tbody tr { border-bottom: 1px solid #f0fdf4; transition: background .12s; }
    .cat-table tbody tr:last-child { border-bottom: none; }
    .cat-table tbody tr:hover { background: #f0fdf4; }
    .cat-table tbody td {
        padding: .85rem 1rem; vertical-align: middle; font-size: .875rem; color: #374151;
    }
    .cat-table tbody td:first-child { padding-left: 1.4rem; }
    .cat-table tbody td:last-child  { padding-right: 1.4rem; }

    /* Category name */
    .cat-name { font-weight: 700; color: #0f2d13; margin-bottom: .1rem; }
    .cat-desc { font-size: .76rem; color: #52916b; }

    /* Services count chip */
    .svc-count-chip {
        display: inline-flex; align-items: center;
        background: #dcfce7; color: #14532d;
        font-size: .72rem; font-weight: 700;
        padding: .22rem .6rem; border-radius: 999px;
    }

    /* Action buttons */
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
    .cat-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f0fdf4; }
    .cat-pagination .pagination { margin: 0; }
    .cat-pagination .page-link {
        border-radius: 7px !important; border-color: #dcfce7;
        color: #15803d; font-size: .82rem; margin: 0 .1rem;
    }
    .cat-pagination .page-link:hover { background: #dcfce7; border-color: #86efac; }
    .cat-pagination .page-item.active .page-link { background: #16a34a; border-color: #16a34a; color: #fff; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="page-title">{{ __('ui.office_cat_title') }}</div>
            <p class="page-sub">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.categories.create', $office) }}" class="btn-new-cat">
            <i class="bi bi-plus-lg"></i>{{ __('ui.office_cat_new') }}
        </a>
    </div>

    <div class="cat-card">
        @if($categories->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-tags"></i></div>
                <h6>{{ __('ui.office_cat_empty') }}</h6>
                <p>{{ __('ui.office_cat_empty_desc') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="cat-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.office_cat_col_name') }}</th>
                            <th>{{ __('ui.office_cat_col_services') }}</th>
                            <th>{{ __('ui.office_cat_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td>
                                    <div class="cat-name">{{ $cat->name }}</div>
                                    @if($cat->description)
                                        <div class="cat-desc" style="max-width:30rem;">{{ \Illuminate\Support\Str::limit($cat->description, 100) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="svc-count-chip">
                                        <i class="bi bi-grid me-1"></i>{{ $cat->services_count }}
                                    </span>
                                </td>
                                <td>
                                    <div class="act-group">
                                        <a href="{{ route('office.categories.edit', [$office, $cat]) }}" class="act-btn-edit">
                                            <i class="bi bi-pencil"></i> {{ __('ui.office_cat_edit') }}
                                        </a>
                                        <form action="{{ route('office.categories.destroy', [$office, $cat]) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('{{ __('ui.office_cat_delete') }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act-btn-delete">
                                                <i class="bi bi-trash"></i> {{ __('ui.office_cat_delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="cat-pagination">{{ $categories->links() }}</div>
            @endif
        @endif
    </div>

@endsection
