@extends('layouts.admin')

@section('title', __('ui.admin_offices_title'))

@push('styles')
<style>
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .page-title  { font-size: 1.3rem; font-weight: 800; color: #18181b; margin-bottom: .2rem; }
    .page-sub    { font-size: .84rem; color: #a1a1aa; margin: 0; }
    .btn-add {
        display: inline-flex; align-items: center; gap: .45rem;
        background: #0ea5e9; color: #fff; font-weight: 700; font-size: .84rem;
        padding: .6rem 1.2rem; border-radius: 10px; text-decoration: none;
        border: none; box-shadow: 0 4px 14px rgba(14,165,233,.3);
        transition: background .15s; white-space: nowrap;
    }
    .btn-add:hover { background: #0284c7; color: #fff; }

    .data-panel { background: #fff; border-radius: 16px; border: 1px solid #e4e4e7; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05); overflow: hidden; }
    .data-panel-header { padding: .9rem 1.5rem; border-bottom: 1px solid #f4f4f5; display: flex; align-items: center; justify-content: space-between; }
    .data-panel-title  { font-size: .78rem; font-weight: 700; color: #71717a; text-transform: uppercase; letter-spacing: .07em; }
    .count-badge { font-size: .7rem; font-weight: 800; background: #f0f9ff; color: #0ea5e9; border: 1px solid #bae6fd; padding: .15rem .55rem; border-radius: 999px; }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead th { background: #fafafa; font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #a1a1aa; padding: .65rem 1rem; border-bottom: 1px solid #f4f4f5; white-space: nowrap; }
    .data-table thead th:first-child { padding-left: 1.5rem; }
    .data-table thead th:last-child  { padding-right: 1.5rem; text-align: right; }
    .data-table tbody tr { border-bottom: 1px solid #fafafa; transition: background .1s; }
    .data-table tbody tr:last-child { border-bottom: none; }
    .data-table tbody tr:hover { background: #fafafa; }
    .data-table tbody td { padding: .95rem 1rem; vertical-align: middle; font-size: .855rem; color: #3f3f46; }
    .data-table tbody td:first-child { padding-left: 1.5rem; }
    .data-table tbody td:last-child  { padding-right: 1.5rem; }

    .row-name    { font-weight: 700; color: #18181b; display: flex; align-items: center; gap: .65rem; }
    .row-avatar  { width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0; background: #f0f9ff; display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: .85rem; font-weight: 800; border: 1px solid #bae6fd; }
    .row-sub     { font-size: .75rem; color: #a1a1aa; margin-top: .1rem; }

    .muni-chip   { display: inline-flex; align-items: center; gap: .3rem; font-size: .77rem; font-weight: 600; color: #52525b; background: #f4f4f5; border: 1px solid #e4e4e7; padding: .22rem .65rem; border-radius: 999px; }
    .contact-email { font-size: .82rem; color: #3f3f46; font-weight: 500; }
    .contact-phone { font-size: .75rem; color: #a1a1aa; margin-top: .1rem; }

    .status-active   { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700; padding: .25rem .7rem; border-radius: 999px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .status-inactive { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700; padding: .25rem .7rem; border-radius: 999px; background: #f4f4f5; color: #71717a; border: 1px solid #e4e4e7; }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; }

    .action-group { display: flex; align-items: center; gap: .4rem; justify-content: flex-end; }
    .act-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; font-size: .85rem; text-decoration: none; border: none; cursor: pointer; transition: background .12s; }
    .act-edit   { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }
    .act-edit:hover { background: #e0f2fe; color: #0369a1; }
    .act-delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .act-delete:hover { background: #fee2e2; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon  { width: 60px; height: 60px; background: #f4f4f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d4d4d8; font-size: 1.5rem; margin: 0 auto .9rem; }
    .empty-state p { font-size: .85rem; color: #a1a1aa; margin: 0; }
</style>
@endpush

@section('content')

    <div class="page-header">
        <div>
            <div class="page-title">{{ __('ui.admin_offices_title') }}</div>
            <p class="page-sub">{{ __('ui.admin_offices_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.offices.create') }}" class="btn-add">
            <i class="bi bi-plus-lg"></i> {{ __('ui.admin_offices_add') }}
        </a>
    </div>

    <div class="data-panel">
        <div class="data-panel-header">
            <span class="data-panel-title">{{ __('ui.admin_offices_all') }}</span>
            <span class="count-badge">{{ $offices->total() }} {{ __('ui.admin_offices_total') }}</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('ui.admin_offices_col_name') }}</th>
                    <th>{{ __('ui.admin_offices_col_municipality') }}</th>
                    <th>{{ __('ui.admin_offices_col_contact') }}</th>
                    <th>{{ __('ui.admin_offices_col_status') }}</th>
                    <th>{{ __('ui.admin_offices_col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offices as $office)
                    <tr>
                        <td>
                            <div class="row-name">
                                <div class="row-avatar">{{ strtoupper(substr($office->name, 0, 1)) }}</div>
                                <div>
                                    {{ $office->name }}
                                    @if($office->address)
                                        <div class="row-sub">{{ $office->address }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($office->municipality)
                                <span class="muni-chip"><i class="bi bi-geo-alt" style="font-size:.7rem;"></i>{{ $office->municipality->name }}</span>
                            @else
                                <span style="color:#d4d4d8;font-size:.82rem;">{{ __('ui.admin_offices_status_unassigned') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="contact-email">{{ $office->email ?? '—' }}</div>
                            @if($office->phone)<div class="contact-phone">{{ $office->phone }}</div>@endif
                        </td>
                        <td>
                            @if($office->is_active)
                                <span class="status-active"><span class="status-dot" style="background:#22c55e;"></span>{{ __('ui.admin_offices_status_active') }}</span>
                            @else
                                <span class="status-inactive"><span class="status-dot" style="background:#a1a1aa;"></span>{{ __('ui.admin_offices_status_inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.offices.edit', $office) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.offices.destroy', $office) }}" class="d-inline"
                                      onsubmit="return confirm('{{ __('ui.admin_offices_delete_confirm') }}');">
                                    @csrf @method('DELETE')
                                    <button class="act-btn act-delete" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="bi bi-building"></i></div>
                            <p>{{ __('ui.admin_offices_empty') }}</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($offices->hasPages())
        <div class="mt-3">{{ $offices->links() }}</div>
    @endif

@endsection
