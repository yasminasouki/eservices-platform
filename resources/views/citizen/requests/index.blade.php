@extends('layouts.citizen')

@section('title', __('ui.citizen_requests_title'))

@php
    $statusConfig = fn (string $status) => match ($status) {
        'pending'           => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'clock'],
        'in_review'         => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'eye'],
        'missing_documents' => ['bg' => '#ffedd5', 'color' => '#c2410c', 'icon' => 'paperclip'],
        'approved'          => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => 'check-circle'],
        'rejected'          => ['bg' => '#fde8e8', 'color' => '#991b1b', 'icon' => 'x-circle'],
        'completed'         => ['bg' => '#ede9fe', 'color' => '#4c1d95', 'icon' => 'patch-check'],
        default             => ['bg' => '#f3f4f6', 'color' => '#374151', 'icon' => 'circle'],
    };
    $statusLabel = fn (string $status) => str($status)->replace('_', ' ')->title()->toString();

    $totalAll = array_sum($statusCounts);
@endphp

@push('styles')
<style>
    /* ── Header ── */
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .page-title  { font-size: 1.25rem; font-weight: 800; color: #1f1235; margin-bottom: .15rem; }
    .page-sub    { font-size: .83rem; color: #9d7ecf; margin: 0; }

    .btn-new-request {
        display: inline-flex; align-items: center; gap: .45rem;
        background: #c4b5fd; border: none; color: #3b0764;
        font-weight: 700; font-size: .85rem; padding: .5rem 1.1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
        white-space: nowrap;
    }
    .btn-new-request:hover { background: #a78bfa; color: #3b0764; }

    /* ── Main card ── */
    .requests-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(124,58,237,.06);
        border: 1px solid #f0ecff; overflow: hidden;
    }

    /* ── Filter tabs ── */
    .filter-bar {
        display: flex; align-items: center; gap: .3rem; flex-wrap: wrap;
        padding: .9rem 1.4rem; border-bottom: 1px solid #f3f0ff;
        overflow-x: auto;
    }
    .filter-tab {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .78rem; font-weight: 600; color: #9d7ecf;
        background: transparent; border: 1px solid transparent;
        border-radius: 8px; padding: .32rem .75rem;
        cursor: pointer; transition: all .15s; white-space: nowrap;
    }
    .filter-tab:hover { background: #f3f0ff; color: #6d28d9; }
    .filter-tab.active {
        background: #ede9fe; border-color: #ddd6fe;
        color: #4c1d95; font-weight: 700;
    }
    .filter-count {
        background: rgba(109,40,217,0.1); color: #6d28d9;
        font-size: .65rem; font-weight: 800;
        padding: .1rem .4rem; border-radius: 999px;
    }
    .filter-tab.active .filter-count { background: #c4b5fd; color: #3b0764; }

    /* ── Table ── */
    .req-table { width: 100%; border-collapse: collapse; }
    .req-table thead th {
        background: #faf8ff; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #c4b5fd;
        padding: .65rem 1rem; border-bottom: 1px solid #f0ecff; white-space: nowrap;
    }
    .req-table thead th:first-child { padding-left: 1.4rem; }
    .req-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }

    .req-table tbody tr {
        border-bottom: 1px solid #faf9ff; transition: background .12s;
    }
    .req-table tbody tr:last-child { border-bottom: none; }
    .req-table tbody tr:hover { background: #faf8ff; }
    .req-table tbody tr.hidden-row { display: none; }

    .req-table tbody td {
        padding: .9rem 1rem; vertical-align: middle;
        font-size: .875rem; color: #374151;
    }
    .req-table tbody td:first-child { padding-left: 1.4rem; }
    .req-table tbody td:last-child  { padding-right: 1.4rem; }

    /* ── Ref chip ── */
    .req-id {
        display: inline-flex; align-items: center; justify-content: center;
        font-family: monospace; font-size: .78rem; font-weight: 800;
        background: #f3f0ff; color: #6d28d9;
        padding: .3rem .6rem; border-radius: 8px;
        min-width: 36px; white-space: nowrap;
    }

    /* ── Service/office ── */
    .req-service { font-weight: 700; color: #1f1235; margin-bottom: .15rem; font-size: .875rem; }
    .req-office  { font-size: .76rem; color: #c4b5fd; display: flex; align-items: center; gap: .25rem; }

    /* ── Status pill ── */
    .status-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .76rem; font-weight: 700;
        padding: .32rem .8rem; border-radius: 999px; white-space: nowrap;
    }
    .status-pill i { font-size: .72rem; }

    /* ── Payment due ── */
    .payment-due {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .7rem; font-weight: 700; color: #c2410c;
        background: #ffedd5; padding: .18rem .55rem;
        border-radius: 999px; margin-top: .3rem;
    }

    /* ── Date ── */
    .req-date { font-size: .79rem; color: #c4b5fd; white-space: nowrap; font-weight: 500; }

    /* ── Actions ── */
    .action-group { display: flex; align-items: center; gap: .35rem; justify-content: flex-end; flex-wrap: wrap; }
    .act-btn {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 600; padding: .32rem .72rem;
        border-radius: 7px; text-decoration: none; white-space: nowrap;
        transition: background .12s; border: 1px solid transparent;
    }
    .act-btn-details { background: #ede9fe; color: #4c1d95; border-color: #ddd6fe; }
    .act-btn-details:hover { background: #ddd6fe; color: #3b0764; }
    .act-btn-pay     { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .act-btn-pay:hover { background: #fde68a; color: #78350f; }
    .act-btn-qr      { background: #f3f4f6; color: #6b7280; border-color: #e5e7eb; }
    .act-btn-qr:hover { background: #e5e7eb; color: #374151; }
    .act-btn-rate    { background: #d1fae5; color: #065f46; border-color: #a7f3d0; }
    .act-btn-rate:hover { background: #a7f3d0; }

    /* ── Empty ── */
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon {
        width: 72px; height: 72px; background: #f3f0ff; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #c4b5fd; font-size: 1.9rem; margin: 0 auto 1.1rem;
    }
    .empty-state h6 { font-weight: 700; color: #1f1235; margin-bottom: .3rem; }
    .empty-state p  { font-size: .84rem; color: #b4a0d4; margin-bottom: 1.2rem; }

    /* ── No results for filter ── */
    .no-filter-results {
        display: none; text-align: center;
        padding: 3rem 2rem; color: #c4b5fd; font-size: .85rem;
    }
    .no-filter-results i { font-size: 1.8rem; display: block; margin-bottom: .5rem; }

    /* ── Pagination ── */
    .req-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f3f0ff; }
    .req-pagination .pagination { margin: 0; }
    .req-pagination .page-link {
        border-radius: 7px !important; border-color: #ede9fe;
        color: #7c3aed; font-size: .82rem; margin: 0 .1rem;
    }
    .req-pagination .page-link:hover { background: #ede9fe; border-color: #c4b5fd; }
    .req-pagination .page-item.active .page-link { background: #c4b5fd; border-color: #c4b5fd; color: #3b0764; }
</style>
@endpush

@section('content')

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="page-title">{{ __('ui.citizen_requests_title') }}</div>
            <p class="page-sub">{{ __('ui.citizen_requests_subtitle') }}</p>
        </div>
        <a href="{{ route('citizen.offices.index') }}" class="btn-new-request">
            <i class="bi bi-plus-lg"></i> {{ __('ui.citizen_requests_new') }}
        </a>
    </div>

    {{-- Main card --}}
    <div class="requests-card">

        {{-- Filter tabs --}}
        <div class="filter-bar">
            <a href="{{ route('citizen.requests.index') }}"
               class="filter-tab {{ $activeTab === 'all' ? 'active' : '' }}" style="text-decoration:none;">
                {{ __('ui.citizen_requests_tab_all') }} <span class="filter-count">{{ $totalAll }}</span>
            </a>
            @foreach([
                'pending'           => ['clock',        __('ui.citizen_requests_tab_pending')],
                'in_review'         => ['eye',          __('ui.citizen_requests_tab_review')],
                'missing_documents' => ['paperclip',    __('ui.citizen_requests_tab_missing')],
                'approved'          => ['check-circle', __('ui.citizen_requests_tab_approved')],
                'completed'         => ['patch-check',  __('ui.citizen_requests_tab_completed')],
                'rejected'          => ['x-circle',     __('ui.citizen_requests_tab_rejected')],
            ] as $status => [$icon, $label])
                @if(($statusCounts[$status] ?? 0) > 0 || $activeTab === $status)
                <a href="{{ route('citizen.requests.index', ['tab' => $status]) }}"
                   class="filter-tab {{ $activeTab === $status ? 'active' : '' }}" style="text-decoration:none;">
                    <i class="bi bi-{{ $icon }}" style="font-size:.72rem;"></i> {{ $label }}
                    <span class="filter-count">{{ $statusCounts[$status] ?? 0 }}</span>
                </a>
                @endif
            @endforeach
        </div>

        @if($requests->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-folder2-open"></i></div>
                @if($activeTab !== 'all')
                    <h6>{{ __('ui.citizen_requests_none_found') }}</h6>
                    <p>{{ __('ui.no_requests_desc') }}</p>
                    <a href="{{ route('citizen.requests.index') }}" class="btn-new-request" style="text-decoration:none;">
                        <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ __('ui.view_all') }}
                    </a>
                @else
                    <h6>{{ __('ui.citizen_requests_empty') }}</h6>
                    <p>{{ __('ui.no_requests_desc') }}</p>
                    <a href="{{ route('citizen.offices.index') }}" class="btn-new-request" style="text-decoration:none;">
                        <i class="bi bi-grid"></i> {{ __('ui.browse_services') }}
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="req-table" id="reqTable">
                    <thead>
                        <tr>
                            <th>{{ __('ui.citizen_requests_col_ref') }}</th>
                            <th>{{ __('ui.citizen_requests_col_service') }}</th>
                            <th>{{ __('ui.citizen_requests_col_status') }}</th>
                            <th>{{ __('ui.citizen_requests_col_submitted') }}</th>
                            <th>{{ __('ui.citizen_requests_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            @php $cfg = $statusConfig($req->status); @endphp
                            <tr data-status="{{ $req->status }}">
                                <td><span class="req-id">#{{ $req->id }}</span></td>

                                <td>
                                    <div class="req-service">{{ $req->service?->name ?? '—' }}</div>
                                    <div class="req-office">
                                        <i class="bi bi-building"></i>
                                        {{ $req->governmentOffice?->name ?? '—' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill"
                                          style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
                                        <i class="bi bi-{{ $cfg['icon'] }}"></i>
                                        {{ $statusLabel($req->status) }}
                                    </span>
                                    @if((float)($req->service?->price ?? 0) > 0 && $req->submitted_at === null)
                                        <div>
                                            <span class="payment-due">
                                                <i class="bi bi-cash-coin"></i> {{ __('ui.citizen_requests_payment_due') }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="req-date">
                                        {{ ($req->submitted_at ?? $req->created_at)?->format('M j, Y') ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="action-group">
                                        @if((float)($req->service?->price ?? 0) > 0 && $req->submitted_at === null)
                                            <a href="{{ route('citizen.requests.pay', $req) }}" class="act-btn act-btn-pay">
                                                <i class="bi bi-credit-card"></i> {{ __('ui.citizen_requests_btn_pay') }}
                                            </a>
                                        @endif
                                        <a href="{{ route('citizen.requests.show', $req) }}" class="act-btn act-btn-details">
                                            <i class="bi bi-eye"></i> {{ __('ui.citizen_requests_btn_details') }}
                                        </a>
                                        <a href="{{ route('requests.track', ['token' => $req->qr_code]) }}"
                                           class="act-btn act-btn-qr" target="_blank" rel="noopener">
                                            <i class="bi bi-qr-code"></i> {{ __('ui.citizen_requests_btn_qr') }}
                                        </a>
                                        @if($req->status === 'completed' && !$req->feedback)
                                            <a href="{{ route('citizen.feedback.request.create', $req) }}" class="act-btn act-btn-rate">
                                                <i class="bi bi-star"></i> {{ __('ui.citizen_requests_btn_rate') }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="req-pagination">{{ $requests->links() }}</div>
            @endif
        @endif
    </div>

@endsection
