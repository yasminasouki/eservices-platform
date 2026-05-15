@extends('layouts.admin')

@section('title', __('ui.admin_sr_title'))

@php
    $statusLabel = fn (string $s) => str($s)->replace('_', ' ')->title()->toString();
    $statusConfig = fn (string $s) => match ($s) {
        'pending'           => ['pill' => 's-pending',   'dot' => '#f59e0b', 'icon' => 'clock'],
        'in_review'         => ['pill' => 's-inreview',  'dot' => '#0ea5e9', 'icon' => 'eye'],
        'missing_documents' => ['pill' => 's-missing',   'dot' => '#f97316', 'icon' => 'paperclip'],
        'approved'          => ['pill' => 's-approved',  'dot' => '#22c55e', 'icon' => 'check-circle'],
        'rejected'          => ['pill' => 's-rejected',  'dot' => '#ef4444', 'icon' => 'x-circle'],
        'completed'         => ['pill' => 's-completed', 'dot' => '#8b5cf6', 'icon' => 'patch-check'],
        default             => ['pill' => 's-default',   'dot' => '#a1a1aa', 'icon' => 'circle'],
    };
    $statDots = ['pending'=>'#f59e0b','in_review'=>'#0ea5e9','missing_documents'=>'#f97316','approved'=>'#22c55e','rejected'=>'#ef4444','completed'=>'#8b5cf6'];
@endphp

@push('styles')
<style>
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .page-title  { font-size: 1.3rem; font-weight: 800; color: #18181b; margin-bottom: .2rem; }
    .page-sub    { font-size: .82rem; color: #a1a1aa; margin: 0; max-width: 680px; line-height: 1.5; }

    /* ── Filter panel ── */
    .filter-panel {
        background: #fff; border-radius: 14px; border: 1px solid #e4e4e7;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05);
        padding: 1.25rem 1.5rem; margin-bottom: 1.25rem;
    }
    .filter-row  { display: flex; align-items: flex-end; gap: .75rem; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: .3rem; min-width: 0; }
    .filter-group.wide { flex: 2; min-width: 180px; }
    .filter-group.mid  { flex: 1.2; min-width: 130px; }
    .filter-group.sm   { flex: 1; min-width: 120px; }
    .filter-label { font-size: .7rem; font-weight: 700; color: #71717a; text-transform: uppercase; letter-spacing: .06em; }
    .filter-control {
        padding: .5rem .85rem; border: 1px solid #e4e4e7; border-radius: 9px;
        font-size: .855rem; color: #18181b; width: 100%;
        transition: border-color .15s, box-shadow .15s; outline: none;
        background: #fff;
    }
    .filter-control:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
    .filter-actions { display: flex; gap: .5rem; align-items: flex-end; padding-bottom: 1px; }
    .btn-apply {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #0ea5e9; color: #fff; font-weight: 700; font-size: .84rem;
        padding: .52rem 1.15rem; border-radius: 9px; border: none; cursor: pointer;
        box-shadow: 0 3px 10px rgba(14,165,233,.28); transition: background .15s; white-space: nowrap;
    }
    .btn-apply:hover { background: #0284c7; }
    .btn-reset {
        display: inline-flex; align-items: center;
        padding: .52rem 1rem; background: #f4f4f5; color: #52525b;
        border: 1px solid #e4e4e7; border-radius: 9px; font-size: .84rem;
        font-weight: 600; text-decoration: none; transition: background .15s; white-space: nowrap;
    }
    .btn-reset:hover { background: #e4e4e7; color: #18181b; }

    /* ── Status stat cards ── */
    .stat-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: .75rem; margin-bottom: 1.25rem; }
    @media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(3,1fr); } }
    @media (max-width: 600px)  { .stat-grid { grid-template-columns: repeat(2,1fr); } }

    .stat-card {
        background: #fff; border-radius: 12px; border: 1px solid #e4e4e7;
        box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: .9rem 1.1rem;
        transition: transform .15s, box-shadow .15s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .stat-card-label { font-size: .68rem; font-weight: 700; color: #a1a1aa; text-transform: uppercase; letter-spacing: .06em; margin-bottom: .4rem; display: flex; align-items: center; gap: .35rem; }
    .stat-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .stat-value { font-size: 1.7rem; font-weight: 800; color: #18181b; line-height: 1; }

    /* ── Requests panel ── */
    .data-panel { background: #fff; border-radius: 16px; border: 1px solid #e4e4e7; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05); overflow: hidden; }
    .data-panel-header { padding: .9rem 1.5rem; border-bottom: 1px solid #f4f4f5; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .data-panel-title  { font-size: .875rem; font-weight: 800; color: #18181b; }
    .count-badge { font-size: .7rem; font-weight: 800; background: #f0f9ff; color: #0ea5e9; border: 1px solid #bae6fd; padding: .15rem .55rem; border-radius: 999px; }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead th { background: #fafafa; font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #a1a1aa; padding: .65rem 1rem; border-bottom: 1px solid #f4f4f5; white-space: nowrap; }
    .data-table thead th:first-child { padding-left: 1.5rem; }
    .data-table thead th:last-child  { padding-right: 1.5rem; }
    .data-table tbody tr { border-bottom: 1px solid #fafafa; transition: background .1s; }
    .data-table tbody tr:last-child { border-bottom: none; }
    .data-table tbody tr:hover { background: #fafafa; }
    .data-table tbody td { padding: .85rem 1rem; vertical-align: middle; font-size: .845rem; color: #3f3f46; }
    .data-table tbody td:first-child { padding-left: 1.5rem; }
    .data-table tbody td:last-child  { padding-right: 1.5rem; }

    .req-id { font-family: monospace; font-size: .82rem; font-weight: 800; background: #f0f9ff; color: #0284c7; padding: .28rem .58rem; border-radius: 7px; text-decoration: none; display: inline-block; transition: background .12s; }
    .req-id:hover { background: #e0f2fe; }
    .citizen-name  { font-weight: 700; color: #18181b; font-size: .855rem; }
    .citizen-email { font-size: .75rem; color: #a1a1aa; }
    .service-name  { font-weight: 500; color: #3f3f46; }
    .office-name   { font-size: .82rem; color: #71717a; }
    .date-cell     { font-size: .78rem; color: #a1a1aa; white-space: nowrap; }

    /* status pills */
    .s-pill { display: inline-flex; align-items: center; gap: .3rem; font-size: .71rem; font-weight: 700; padding: .28rem .72rem; border-radius: 999px; white-space: nowrap; }
    .s-pending   { background: #fffbeb; color: #92400e; }
    .s-inreview  { background: #f0f9ff; color: #0369a1; }
    .s-missing   { background: #fff7ed; color: #c2410c; }
    .s-approved  { background: #f0fdf4; color: #166534; }
    .s-rejected  { background: #fef2f2; color: #991b1b; }
    .s-completed { background: #f5f3ff; color: #5b21b6; }
    .s-default   { background: #f4f4f5; color: #52525b; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon  { width: 60px; height: 60px; background: #f4f4f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d4d4d8; font-size: 1.5rem; margin: 0 auto .9rem; }
    .empty-state p { font-size: .85rem; color: #a1a1aa; margin: 0; }
</style>
@endpush

@section('content')

    <div class="page-header">
        <div>
            <div class="page-title">{{ __('ui.admin_sr_title') }}</div>
            <p class="page-sub">{{ __('ui.admin_sr_subtitle') }}</p>
        </div>
    </div>

    {{-- Filter panel --}}
    <div class="filter-panel">
        <form method="GET" action="{{ route('admin.service-requests.index') }}">
            <div class="filter-row">
                <div class="filter-group wide">
                    <label class="filter-label">{{ __('ui.admin_sr_filter_office') }}</label>
                    <select name="government_office_id" class="filter-control">
                        <option value="">{{ __('ui.admin_sr_filter_all_offices') }}</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" @selected((string)($filters['government_office_id'] ?? '') === (string)$office->id)>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group mid">
                    <label class="filter-label">{{ __('ui.admin_sr_filter_status') }}</label>
                    <select name="status" class="filter-control">
                        <option value="">{{ __('ui.admin_sr_filter_all_statuses') }}</option>
                        @foreach(\App\Models\ServiceRequest::STATUSES as $s)
                            <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ $statusLabel($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group sm">
                    <label class="filter-label">{{ __('ui.admin_sr_filter_from') }}</label>
                    <input type="date" name="date_from" class="filter-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="filter-group sm">
                    <label class="filter-label">{{ __('ui.admin_sr_filter_to') }}</label>
                    <input type="date" name="date_to" class="filter-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-apply"><i class="bi bi-funnel"></i> {{ __('ui.admin_sr_filter_apply') }}</button>
                    <a href="{{ route('admin.service-requests.index') }}" class="btn-reset">{{ __('ui.admin_sr_filter_reset') }}</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Status counts --}}
    <div class="stat-grid">
        @foreach($statusCounts as $status => $count)
            @php $cfg = $statusConfig($status); @endphp
            <div class="stat-card">
                <div class="stat-card-label">
                    <span class="stat-dot" style="background:{{ $statDots[$status] ?? '#a1a1aa' }};"></span>
                    {{ $statusLabel($status) }}
                </div>
                <div class="stat-value">{{ $count }}</div>
            </div>
        @endforeach
    </div>

    {{-- Requests table --}}
    <div class="data-panel">
        <div class="data-panel-header">
            <div class="data-panel-title">{{ __('ui.admin_sr_requests') }}</div>
            <span class="count-badge">{{ $requests->total() }} {{ __('ui.admin_sr_total') }}</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('ui.admin_sr_col_ref') }}</th>
                    <th>{{ __('ui.admin_sr_col_citizen') }}</th>
                    <th>{{ __('ui.admin_sr_col_service') }}</th>
                    <th>{{ __('ui.admin_sr_col_office') }}</th>
                    <th>{{ __('ui.admin_sr_col_status') }}</th>
                    <th>{{ __('ui.admin_sr_col_submitted') }}</th>
                    <th>{{ __('ui.admin_sr_col_updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    @php $cfg = $statusConfig($req->status); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.service-requests.show', $req) }}" class="req-id">#{{ $req->id }}</a>
                        </td>
                        <td>
                            <div class="citizen-name">{{ $req->citizen?->name ?? 'N/A' }}</div>
                            <div class="citizen-email">{{ $req->citizen?->email ?? '' }}</div>
                        </td>
                        <td><div class="service-name">{{ $req->service?->name ?? 'N/A' }}</div></td>
                        <td><div class="office-name">{{ $req->governmentOffice?->name ?? 'N/A' }}</div></td>
                        <td>
                            <span class="s-pill {{ $cfg['pill'] }}">
                                <i class="bi bi-{{ $cfg['icon'] }}" style="font-size:.64rem;"></i>
                                {{ $statusLabel($req->status) }}
                            </span>
                        </td>
                        <td class="date-cell">{{ $req->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td class="date-cell">{{ $req->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                            <p>{{ __('ui.admin_sr_empty') }}</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif

@endsection
