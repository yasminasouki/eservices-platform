@extends('layouts.admin')

@section('title', __('ui.admin_ou_title'))

@push('styles')
<style>
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .page-title  { font-size: 1.3rem; font-weight: 800; color: #18181b; margin-bottom: .2rem; }
    .page-sub    { font-size: .84rem; color: #a1a1aa; margin: 0; }

    /* ── Create form panel ── */
    .form-panel {
        background: #fff; border-radius: 16px; border: 1px solid #e4e4e7;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05);
        margin-bottom: 1.5rem; overflow: hidden;
    }
    .form-panel-header {
        padding: .9rem 1.5rem; border-bottom: 1px solid #f4f4f5;
        display: flex; align-items: center; gap: .6rem;
    }
    .form-panel-icon { width: 28px; height: 28px; border-radius: 7px; background: #f0f9ff; display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: .82rem; }
    .form-panel-title { font-size: .875rem; font-weight: 800; color: #18181b; }
    .form-panel-body  { padding: 1.4rem 1.5rem; }

    .form-label { font-size: .78rem; font-weight: 700; color: #52525b; margin-bottom: .4rem; }
    .form-control, .form-select {
        border: 1px solid #e4e4e7; border-radius: 9px;
        font-size: .875rem; color: #18181b; padding: .55rem .85rem;
        transition: border-color .15s, box-shadow .15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); outline: none;
    }
    .btn-create {
        display: inline-flex; align-items: center; gap: .45rem;
        background: #18181b; color: #fff; font-weight: 700; font-size: .84rem;
        padding: .6rem 1.3rem; border-radius: 10px; border: none; cursor: pointer;
        transition: background .15s;
    }
    .btn-create:hover { background: #27272a; }

    /* ── Search ── */
    .search-bar { display: flex; gap: .6rem; margin-bottom: 1.25rem; }
    .search-input { flex: 1; max-width: 320px; padding: .55rem 1rem; border: 1px solid #e4e4e7; border-radius: 10px; font-size: .875rem; color: #18181b; outline: none; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
    .search-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1.1rem; background: #18181b; color: #fff; border: none; border-radius: 10px; font-size: .84rem; font-weight: 600; cursor: pointer; transition: background .15s; }
    .search-btn:hover { background: #27272a; }

    /* ── Table panel ── */
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
    .data-table tbody td { padding: .9rem 1rem; vertical-align: middle; font-size: .855rem; color: #3f3f46; }
    .data-table tbody td:first-child { padding-left: 1.5rem; }
    .data-table tbody td:last-child  { padding-right: 1.5rem; }

    .row-name   { display: flex; align-items: center; gap: .65rem; }
    .row-avatar { width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; background: #27272a; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .78rem; font-weight: 800; }
    .user-name  { font-weight: 700; color: #18181b; font-size: .875rem; }
    .user-email { font-size: .76rem; color: #a1a1aa; }

    .office-chip { display: inline-flex; align-items: center; gap: .28rem; font-size: .75rem; font-weight: 600; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: .2rem .6rem; border-radius: 999px; margin: .1rem; }
    .unassigned  { font-size: .78rem; color: #d4d4d8; }

    .status-active   { display: inline-flex; align-items: center; gap: .28rem; font-size: .71rem; font-weight: 700; padding: .26rem .68rem; border-radius: 999px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .status-inactive { display: inline-flex; align-items: center; gap: .28rem; font-size: .71rem; font-weight: 700; padding: .26rem .68rem; border-radius: 999px; background: #f4f4f5; color: #71717a; border: 1px solid #e4e4e7; }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

    .action-group { display: flex; align-items: center; gap: .4rem; justify-content: flex-end; }
    .act-edit     { display: inline-flex; align-items: center; gap: .3rem; height: 30px; padding: 0 .85rem; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 8px; font-size: .78rem; font-weight: 700; text-decoration: none; transition: background .12s; }
    .act-edit:hover { background: #e0f2fe; color: #0369a1; }
    .act-toggle-off { display: inline-flex; align-items: center; height: 30px; padding: 0 .85rem; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 8px; font-size: .78rem; font-weight: 700; cursor: pointer; transition: background .12s; }
    .act-toggle-off:hover { background: #fee2e2; }
    .act-toggle-on  { display: inline-flex; align-items: center; height: 30px; padding: 0 .85rem; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 8px; font-size: .78rem; font-weight: 700; cursor: pointer; transition: background .12s; }
    .act-toggle-on:hover { background: #dcfce7; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon  { width: 60px; height: 60px; background: #f4f4f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d4d4d8; font-size: 1.5rem; margin: 0 auto .9rem; }
    .empty-state p { font-size: .85rem; color: #a1a1aa; margin: 0; }
</style>
@endpush

@section('content')

    <div class="page-header">
        <div>
            <div class="page-title">{{ __('ui.admin_ou_title') }}</div>
            <p class="page-sub">{{ __('ui.admin_ou_subtitle') }}</p>
        </div>
    </div>

    {{-- Create form --}}
    <div class="form-panel">
        <div class="form-panel-header">
            <div class="form-panel-icon"><i class="bi bi-person-plus"></i></div>
            <div class="form-panel-title">{{ __('ui.admin_ou_form_title') }}</div>
        </div>
        <div class="form-panel-body">
            <form method="POST" action="{{ route('admin.office-users.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('ui.admin_ou_lbl_name') }} <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('ui.admin_ou_lbl_email') }} <span style="color:#ef4444;">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('ui.admin_ou_lbl_password') }} <span style="color:#ef4444;">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('ui.admin_ou_lbl_confirm_pw') }} <span style="color:#ef4444;">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('ui.admin_ou_lbl_phone') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('ui.admin_ou_lbl_assign_office') }}</label>
                        <select name="government_office_id" class="form-select">
                            <option value="">{{ __('ui.admin_ou_unassigned') }}</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}" @selected((string) old('government_office_id') === (string) $office->id)>
                                    {{ $office->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('ui.admin_ou_lbl_role') }}</label>
                        <input type="text" name="role_in_office" class="form-control" value="{{ old('role_in_office') }}" placeholder="{{ __('ui.admin_ou_role_ph') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-create">
                            <i class="bi bi-person-plus"></i> {{ __('ui.admin_ou_create_btn') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" class="search-bar">
        <input type="text" name="q" class="search-input" value="{{ $search }}" placeholder="{{ __('ui.admin_ou_search_ph') }}">
        <button type="submit" class="search-btn"><i class="bi bi-search"></i> {{ __('ui.admin_ou_search_btn') }}</button>
    </form>

    {{-- Users table --}}
    <div class="data-panel">
        <div class="data-panel-header">
            <span class="data-panel-title">{{ __('ui.admin_ou_all') }}</span>
            <span class="count-badge">{{ $officeUsers->total() }} {{ __('ui.admin_citizens_total') }}</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('ui.admin_ou_col_name') }}</th>
                    <th>{{ __('ui.admin_ou_col_email') }}</th>
                    <th>{{ __('ui.admin_ou_col_offices') }}</th>
                    <th>{{ __('ui.admin_ou_col_status') }}</th>
                    <th>{{ __('ui.admin_ou_col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($officeUsers as $user)
                    <tr>
                        <td>
                            <div class="row-name">
                                <div class="row-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                <span class="user-name">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td><span class="user-email">{{ $user->email }}</span></td>
                        <td>
                            @if($user->governmentOffices->isEmpty())
                                <span class="unassigned">{{ __('ui.admin_ou_status_unassigned') }}</span>
                            @else
                                @foreach($user->governmentOffices as $office)
                                    <span class="office-chip"><i class="bi bi-building" style="font-size:.65rem;"></i>{{ $office->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="status-active"><span class="status-dot" style="background:#22c55e;"></span>{{ __('ui.admin_ou_status_active') }}</span>
                            @else
                                <span class="status-inactive"><span class="status-dot" style="background:#a1a1aa;"></span>{{ __('ui.admin_ou_status_inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.office-users.edit', $user) }}" class="act-edit">
                                    <i class="bi bi-pencil"></i> {{ __('ui.admin_ou_btn_edit') }}
                                </a>
                                <form method="POST" action="{{ route('admin.office-users.toggle-active', $user) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    @if($user->is_active)
                                        <button type="submit" class="act-toggle-off">{{ __('ui.admin_ou_btn_deactivate') }}</button>
                                    @else
                                        <button type="submit" class="act-toggle-on">{{ __('ui.admin_ou_btn_activate') }}</button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="bi bi-person-gear"></i></div>
                            <p>{{ __('ui.admin_ou_empty') }}{{ $search ? ' matching "'.e($search).'"' : '' }}.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($officeUsers->hasPages())
        <div class="mt-3">{{ $officeUsers->links() }}</div>
    @endif

@endsection
