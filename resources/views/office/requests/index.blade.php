@extends('layouts.office')

@section('title', __('ui.office_req_title'))

@php
    $statusConfig = fn (string $status) => match ($status) {
        'pending'           => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'clock'],
        'in_review'         => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'eye'],
        'missing_documents' => ['bg' => '#ffedd5', 'color' => '#c2410c', 'icon' => 'paperclip'],
        'approved'          => ['bg' => '#dcfce7', 'color' => '#14532d', 'icon' => 'check-circle'],
        'rejected'          => ['bg' => '#fde8e8', 'color' => '#991b1b', 'icon' => 'x-circle'],
        'completed'         => ['bg' => '#ede9fe', 'color' => '#4c1d95', 'icon' => 'patch-check'],
        default             => ['bg' => '#f3f4f6', 'color' => '#374151', 'icon' => 'circle'],
    };
    $statusLabel = fn (string $status) => str($status)->replace('_', ' ')->title()->toString();

    $allCollection = $requests->getCollection();
    $counts = [
        'all'               => $requests->total(),
        'pending'           => $allCollection->where('status', 'pending')->count(),
        'in_review'         => $allCollection->where('status', 'in_review')->count(),
        'missing_documents' => $allCollection->where('status', 'missing_documents')->count(),
        'approved'          => $allCollection->where('status', 'approved')->count(),
        'completed'         => $allCollection->where('status', 'completed')->count(),
        'rejected'          => $allCollection->where('status', 'rejected')->count(),
    ];
@endphp

@push('styles')
<style>
    /* ── Header ── */
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* ── Stat cards ── */
    .stat-card {
        background: #fff; border-radius: 12px;
        border: 1px solid #d1fae5;
        box-shadow: 0 1px 6px rgba(21,128,61,.06);
        padding: .9rem 1rem;
    }
    .stat-card .stat-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #52916b; margin-bottom: .2rem; }
    .stat-card .stat-value { font-size: 1.5rem; font-weight: 800; color: #14532d; }

    /* ── Filter card ── */
    .filter-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #d1fae5;
        box-shadow: 0 1px 8px rgba(21,128,61,.05);
        padding: 1.1rem 1.3rem; margin-bottom: 1.25rem;
    }
    .filter-card .form-label { font-size: .75rem; font-weight: 600; color: #52916b; margin-bottom: .25rem; }
    .filter-card .form-control,
    .filter-card .form-select { font-size: .85rem; border-color: #d1fae5; }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
    .btn-apply {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .84rem; padding: .45rem 1.1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-apply:hover { background: #15803d; color: #fff; }
    .btn-reset {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #f0fdf4; border: 1px solid #d1fae5; color: #15803d;
        font-weight: 600; font-size: .84rem; padding: .44rem 1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-reset:hover { background: #dcfce7; }

    /* ── Main card ── */
    .requests-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }

    /* ── Filter tabs ── */
    .filter-bar {
        display: flex; align-items: center; gap: .3rem; flex-wrap: wrap;
        padding: .9rem 1.4rem; border-bottom: 1px solid #f0fdf4; overflow-x: auto;
    }
    .filter-tab {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .78rem; font-weight: 600; color: #52916b;
        background: transparent; border: 1px solid transparent;
        border-radius: 8px; padding: .32rem .75rem;
        cursor: pointer; transition: all .15s; white-space: nowrap;
    }
    .filter-tab:hover { background: #f0fdf4; color: #15803d; }
    .filter-tab.active {
        background: #dcfce7; border-color: #bbf7d0;
        color: #14532d; font-weight: 700;
    }
    .filter-count {
        background: rgba(21,128,61,.1); color: #15803d;
        font-size: .65rem; font-weight: 800;
        padding: .1rem .4rem; border-radius: 999px;
    }
    .filter-tab.active .filter-count { background: #86efac; color: #14532d; }

    /* ── Table ── */
    .req-table { width: 100%; border-collapse: collapse; }
    .req-table thead th {
        background: #f0fdf4; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #86efac;
        padding: .65rem 1rem; border-bottom: 1px solid #d1fae5; white-space: nowrap;
    }
    .req-table thead th:first-child { padding-left: 1.4rem; }
    .req-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }

    .req-table tbody tr {
        border-bottom: 1px solid #f0fdf4; transition: background .12s;
    }
    .req-table tbody tr:last-child { border-bottom: none; }
    .req-table tbody tr:hover { background: #f0fdf4; }
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
        background: #dcfce7; color: #14532d;
        padding: .3rem .6rem; border-radius: 8px;
        min-width: 36px; white-space: nowrap;
    }

    /* ── Status pill ── */
    .status-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .76rem; font-weight: 700;
        padding: .32rem .8rem; border-radius: 999px; white-space: nowrap;
    }
    .status-pill i { font-size: .72rem; }

    /* ── Citizen info ── */
    .citizen-name { font-weight: 700; color: #0f2d13; margin-bottom: .1rem; }
    .citizen-email { font-size: .76rem; color: #52916b; }

    /* ── Action button ── */
    .act-btn-open {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 600; padding: .32rem .8rem;
        border-radius: 7px; text-decoration: none; white-space: nowrap;
        transition: background .12s; border: 1px solid #bbf7d0;
        background: #dcfce7; color: #14532d;
    }
    .act-btn-open:hover { background: #bbf7d0; color: #0f2d13; }

    /* ── Empty ── */
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon {
        width: 72px; height: 72px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #86efac; font-size: 1.9rem; margin: 0 auto 1.1rem;
    }
    .empty-state h6 { font-weight: 700; color: #0f2d13; margin-bottom: .3rem; }
    .empty-state p  { font-size: .84rem; color: #52916b; margin-bottom: 0; }

    /* ── No-filter message ── */
    .no-filter-results {
        display: none; text-align: center;
        padding: 3rem 2rem; color: #86efac; font-size: .85rem;
    }
    .no-filter-results i { font-size: 1.8rem; display: block; margin-bottom: .5rem; }

    /* ── Pagination ── */
    .req-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f0fdf4; }
    .req-pagination .pagination { margin: 0; }
    .req-pagination .page-link {
        border-radius: 7px !important; border-color: #dcfce7;
        color: #15803d; font-size: .82rem; margin: 0 .1rem;
    }
    .req-pagination .page-link:hover { background: #dcfce7; border-color: #86efac; }
    .req-pagination .page-item.active .page-link { background: #16a34a; border-color: #16a34a; color: #fff; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="d-flex align-items-flex-start justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="page-title">{{ __('ui.office_req_title') }}</div>
            <p class="page-sub">{{ $office->name }} — {{ __('ui.office_req_subtitle') }}</p>
        </div>
    </div>

    {{-- Filter card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('office.requests.index', $office) }}" class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label for="q" class="form-label">{{ __('ui.office_req_col_citizen') }}</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="{{ __('ui.office_req_search_ph') }}"
                       value="{{ $filters['q'] ?? '' }}" maxlength="100">
            </div>
            <div class="col-md-4 col-lg-2">
                <label for="status" class="form-label">{{ __('ui.office_req_filter_status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ __('ui.office_req_filter_all') }}</option>
                    @foreach(\App\Models\ServiceRequest::STATUSES as $s)
                        <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>
                            {{ $statusLabel($s) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label for="date_from" class="form-label">{{ __('ui.office_req_filter_from') }}</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-4 col-lg-2">
                <label for="date_to" class="form-label">{{ __('ui.office_req_filter_to') }}</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-8 col-lg-3 d-flex flex-wrap gap-2">
                <button type="submit" class="btn-apply">
                    <i class="bi bi-funnel"></i>{{ __('ui.office_req_filter_apply') }}
                </button>
                <a href="{{ route('office.requests.index', $office) }}" class="btn-reset">{{ __('ui.office_req_filter_reset') }}</a>
            </div>
        </form>
    </div>

    {{-- Status count cards --}}
    <div class="row g-3 mb-4">
        @foreach($statusCounts as $status => $count)
            @php $cfg = $statusConfig($status); @endphp
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card">
                    <div class="stat-label">{{ $statusLabel($status) }}</div>
                    <div class="stat-value" style="color:{{ $cfg['color'] }}">{{ $count }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Main requests card --}}
    <div class="requests-card">

        {{-- Filter tabs --}}
        @if(!$requests->isEmpty())
        <div class="filter-bar">
            <button class="filter-tab active" onclick="filterBy('all', this)">
                {{ __('ui.office_req_tab_all') }} <span class="filter-count">{{ $counts['all'] }}</span>
            </button>
            @if($counts['pending'] > 0)
            <button class="filter-tab" onclick="filterBy('pending', this)">
                <i class="bi bi-clock" style="font-size:.72rem;"></i> {{ __('ui.office_req_tab_pending') }}
                <span class="filter-count">{{ $counts['pending'] }}</span>
            </button>
            @endif
            @if($counts['in_review'] > 0)
            <button class="filter-tab" onclick="filterBy('in_review', this)">
                <i class="bi bi-eye" style="font-size:.72rem;"></i> {{ __('ui.office_req_tab_review') }}
                <span class="filter-count">{{ $counts['in_review'] }}</span>
            </button>
            @endif
            @if($counts['missing_documents'] > 0)
            <button class="filter-tab" onclick="filterBy('missing_documents', this)">
                <i class="bi bi-paperclip" style="font-size:.72rem;"></i> {{ __('ui.office_req_tab_missing') }}
                <span class="filter-count">{{ $counts['missing_documents'] }}</span>
            </button>
            @endif
            @if($counts['approved'] > 0)
            <button class="filter-tab" onclick="filterBy('approved', this)">
                <i class="bi bi-check-circle" style="font-size:.72rem;"></i> {{ __('ui.office_req_tab_approved') }}
                <span class="filter-count">{{ $counts['approved'] }}</span>
            </button>
            @endif
            @if($counts['completed'] > 0)
            <button class="filter-tab" onclick="filterBy('completed', this)">
                <i class="bi bi-patch-check" style="font-size:.72rem;"></i> {{ __('ui.office_req_tab_completed') }}
                <span class="filter-count">{{ $counts['completed'] }}</span>
            </button>
            @endif
            @if($counts['rejected'] > 0)
            <button class="filter-tab" onclick="filterBy('rejected', this)">
                <i class="bi bi-x-circle" style="font-size:.72rem;"></i> {{ __('ui.office_req_tab_rejected') }}
                <span class="filter-count">{{ $counts['rejected'] }}</span>
            </button>
            @endif
        </div>
        @endif

        @if($requests->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <h6>{{ __('ui.office_req_empty') }}</h6>
                <p>{{ __('ui.office_req_empty_filter') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="req-table" id="reqTable">
                    <thead>
                        <tr>
                            <th>{{ __('ui.office_req_col_ref') }}</th>
                            <th>{{ __('ui.office_req_col_citizen') }}</th>
                            <th>{{ __('ui.office_req_col_service') }}</th>
                            <th>{{ __('ui.office_req_col_status') }}</th>
                            <th>{{ __('ui.office_req_col_submitted') }}</th>
                            <th>{{ __('ui.office_req_col_updated') }}</th>
                            <th>{{ __('ui.office_req_col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            @php $cfg = $statusConfig($req->status); @endphp
                            <tr data-status="{{ $req->status }}">
                                <td><span class="req-id">#{{ $req->id }}</span></td>
                                <td>
                                    <div class="citizen-name">{{ $req->citizen?->name ?? 'N/A' }}</div>
                                    <div class="citizen-email">{{ $req->citizen?->email ?? '' }}</div>
                                </td>
                                <td>{{ $req->service?->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="status-pill" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
                                        <i class="bi bi-{{ $cfg['icon'] }}"></i>
                                        {{ $statusLabel($req->status) }}
                                    </span>
                                </td>
                                <td style="font-size:.79rem;color:#52916b;">{{ $req->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td style="font-size:.79rem;color:#52916b;">{{ $req->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('office.requests.show', [$office, $req]) }}" class="act-btn-open">
                                        <i class="bi bi-arrow-right-circle"></i> {{ __('ui.office_req_btn_open') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="no-filter-results" id="noFilterResults">
                <i class="bi bi-funnel"></i>
                {{ __('ui.office_req_empty_tab') }}
            </div>

            @if($requests->hasPages())
                <div class="req-pagination">{{ $requests->withQueryString()->links() }}</div>
            @endif
        @endif
    </div>

@endsection

@push('scripts')
<script>
function filterBy(status, btn) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');

    const rows = document.querySelectorAll('#reqTable tbody tr');
    let visible = 0;

    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.classList.remove('hidden-row');
            visible++;
        } else {
            row.classList.add('hidden-row');
        }
    });

    const noResults = document.getElementById('noFilterResults');
    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
}
</script>
@endpush
