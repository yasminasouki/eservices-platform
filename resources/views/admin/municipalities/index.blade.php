@extends('layouts.admin')

@section('title', __('ui.admin_muni_title'))

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

    .muni-panel {
        background: #fff; border-radius: 16px;
        border: 1px solid #e4e4e7;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .muni-panel-header {
        padding: .9rem 1.5rem; border-bottom: 1px solid #f4f4f5;
        display: flex; align-items: center; justify-content: space-between;
    }
    .muni-panel-title { font-size: .78rem; font-weight: 700; color: #71717a; text-transform: uppercase; letter-spacing: .07em; }
    .muni-count-badge {
        font-size: .7rem; font-weight: 800; background: #f0f9ff;
        color: #0ea5e9; border: 1px solid #bae6fd;
        padding: .15rem .55rem; border-radius: 999px;
    }

    .muni-table { width: 100%; border-collapse: collapse; }
    .muni-table thead th {
        background: #fafafa; font-size: .67rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .08em; color: #a1a1aa;
        padding: .65rem 1rem; border-bottom: 1px solid #f4f4f5; white-space: nowrap;
    }
    .muni-table thead th:first-child { padding-left: 1.5rem; }
    .muni-table thead th:last-child  { padding-right: 1.5rem; text-align: right; }

    .muni-table tbody tr { border-bottom: 1px solid #fafafa; transition: background .1s; }
    .muni-table tbody tr:last-child { border-bottom: none; }
    .muni-table tbody tr:hover { background: #fafafa; }
    .muni-table tbody td { padding: 1rem 1rem; vertical-align: middle; font-size: .875rem; color: #3f3f46; }
    .muni-table tbody td:first-child { padding-left: 1.5rem; }
    .muni-table tbody td:last-child  { padding-right: 1.5rem; }

    .muni-name { font-weight: 700; color: #18181b; display: flex; align-items: center; gap: .65rem; }
    .muni-avatar {
        width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
        background: #f0f9ff; display: flex; align-items: center; justify-content: center;
        color: #0ea5e9; font-size: .9rem; font-weight: 800;
        border: 1px solid #bae6fd;
    }

    .region-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .77rem; font-weight: 600; color: #52525b;
        background: #f4f4f5; border: 1px solid #e4e4e7;
        padding: .22rem .65rem; border-radius: 999px;
    }

    .offices-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .77rem; font-weight: 700;
        background: #f0f9ff; color: #0284c7;
        border: 1px solid #bae6fd;
        padding: .22rem .65rem; border-radius: 999px;
    }
    .offices-chip.zero { background: #fafafa; color: #a1a1aa; border-color: #e4e4e7; }

    .action-group { display: flex; align-items: center; gap: .4rem; justify-content: flex-end; }
    .act-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 8px;
        font-size: .85rem; text-decoration: none; border: none; cursor: pointer;
        transition: background .12s, color .12s;
    }
    .act-edit   { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }
    .act-edit:hover { background: #e0f2fe; color: #0369a1; }
    .act-delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .act-delete:hover { background: #fee2e2; color: #b91c1c; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon {
        width: 60px; height: 60px; background: #f4f4f5; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #d4d4d8; font-size: 1.5rem; margin: 0 auto .9rem;
    }
    .empty-state p { font-size: .85rem; color: #a1a1aa; margin: 0; }
</style>
@endpush

@section('content')

    <div class="page-header">
        <div>
            <div class="page-title">{{ __('ui.admin_muni_title') }}</div>
            <p class="page-sub">{{ __('ui.admin_muni_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.municipalities.create') }}" class="btn-add">
            <i class="bi bi-plus-lg"></i> {{ __('ui.admin_muni_add') }}
        </a>
    </div>

    <div class="muni-panel">
        <div class="muni-panel-header">
            <span class="muni-panel-title">{{ __('ui.admin_muni_all') }}</span>
            <span class="muni-count-badge">{{ $municipalities->total() }} {{ __('ui.admin_muni_total') }}</span>
        </div>

        <table class="muni-table">
            <thead>
                <tr>
                    <th>{{ __('ui.admin_muni_col_name') }}</th>
                    <th>{{ __('ui.admin_muni_col_region') }}</th>
                    <th>{{ __('ui.admin_muni_col_offices') }}</th>
                    <th>{{ __('ui.admin_muni_col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($municipalities as $municipality)
                    <tr>
                        <td>
                            <div class="muni-name">
                                <div class="muni-avatar">
                                    {{ strtoupper(substr($municipality->name, 0, 1)) }}
                                </div>
                                {{ $municipality->name }}
                            </div>
                        </td>
                        <td>
                            @if($municipality->region)
                                <span class="region-chip">
                                    <i class="bi bi-geo-alt" style="font-size:.7rem;"></i>
                                    {{ $municipality->region }}
                                </span>
                            @else
                                <span style="color:#d4d4d8;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="offices-chip {{ $municipality->government_offices_count == 0 ? 'zero' : '' }}">
                                <i class="bi bi-building" style="font-size:.7rem;"></i>
                                {{ $municipality->government_offices_count }}
                                {{ Str::plural('office', $municipality->government_offices_count) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.municipalities.edit', $municipality) }}" class="act-btn act-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.municipalities.destroy', $municipality) }}" class="d-inline"
                                      onsubmit="return confirm('{{ __('ui.admin_muni_delete_confirm') }}');">
                                    @csrf @method('DELETE')
                                    <button class="act-btn act-delete" type="submit" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-geo-alt"></i></div>
                                <p>{{ __('ui.admin_muni_empty') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($municipalities->hasPages())
        <div class="mt-3">{{ $municipalities->links() }}</div>
    @endif

@endsection
