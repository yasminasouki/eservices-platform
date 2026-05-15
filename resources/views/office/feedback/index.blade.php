@extends('layouts.office')

@section('title', 'Citizen Feedback')

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* Filter bar */
    .filter-bar {
        display: flex; align-items: center; gap: .3rem; flex-wrap: wrap;
        padding: .85rem 1.4rem; border-bottom: 1px solid #f0fdf4; overflow-x: auto;
    }
    .filter-tab {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .78rem; font-weight: 600; color: #52916b;
        background: transparent; border: 1px solid transparent;
        border-radius: 8px; padding: .32rem .75rem;
        text-decoration: none; transition: all .15s; white-space: nowrap;
    }
    .filter-tab:hover { background: #f0fdf4; color: #15803d; }
    .filter-tab.active {
        background: #dcfce7; border-color: #bbf7d0;
        color: #14532d; font-weight: 700;
    }

    /* Main card */
    .feedback-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }

    /* Table */
    .fb-table { width: 100%; border-collapse: collapse; }
    .fb-table thead th {
        background: #f0fdf4; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #86efac;
        padding: .65rem 1rem; border-bottom: 1px solid #d1fae5; white-space: nowrap;
    }
    .fb-table thead th:first-child { padding-left: 1.4rem; }
    .fb-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }
    .fb-table tbody tr { border-bottom: 1px solid #f0fdf4; transition: background .12s; }
    .fb-table tbody tr:last-child { border-bottom: none; }
    .fb-table tbody tr:hover { background: #f0fdf4; }
    .fb-table tbody td {
        padding: .85rem 1rem; vertical-align: middle;
        font-size: .875rem; color: #374151;
    }
    .fb-table tbody td:first-child { padding-left: 1.4rem; }
    .fb-table tbody td:last-child  { padding-right: 1.4rem; }

    /* Citizen name */
    .citizen-name { font-weight: 700; color: #0f2d13; }
    .citizen-email { font-size: .76rem; color: #52916b; }

    /* Stars */
    .star-row { color: #f59e0b; font-size: .9rem; }

    /* Status pill */
    .status-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 700;
        padding: .28rem .7rem; border-radius: 999px; white-space: nowrap;
    }
    .pill-replied { background: #dcfce7; color: #14532d; }
    .pill-open    { background: #f3f4f6; color: #374151; }

    /* Reply button */
    .act-btn-reply {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 600; padding: .32rem .8rem;
        border-radius: 7px; text-decoration: none; white-space: nowrap;
        transition: background .12s; border: 1px solid #bbf7d0;
        background: #dcfce7; color: #14532d;
    }
    .act-btn-reply:hover { background: #bbf7d0; color: #0f2d13; }

    /* Request link */
    .req-link { color: #16a34a; text-decoration: none; font-weight: 600; font-size: .82rem; }
    .req-link:hover { color: #14532d; text-decoration: underline; }

    /* Empty state */
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon {
        width: 72px; height: 72px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #86efac; font-size: 1.9rem; margin: 0 auto 1.1rem;
    }
    .empty-state h6 { font-weight: 700; color: #0f2d13; margin-bottom: .3rem; }
    .empty-state p  { font-size: .84rem; color: #52916b; }

    /* Pagination */
    .fb-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f0fdf4; }
    .fb-pagination .pagination { margin: 0; }
    .fb-pagination .page-link {
        border-radius: 7px !important; border-color: #dcfce7;
        color: #15803d; font-size: .82rem; margin: 0 .1rem;
    }
    .fb-pagination .page-link:hover { background: #dcfce7; border-color: #86efac; }
    .fb-pagination .page-item.active .page-link { background: #16a34a; border-color: #16a34a; color: #fff; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <div class="page-title">Citizen Feedback</div>
            <p class="page-sub">Ratings and comments from citizens who used your services.</p>
        </div>
    </div>

    {{-- Main card --}}
    <div class="feedback-card">

        {{-- Filter tabs (form-driven, GET) --}}
        <div class="filter-bar">
            <a href="{{ route('office.feedback.index', [$office, 'replied' => 'all']) }}"
               class="filter-tab {{ $repliedFilter === 'all' ? 'active' : '' }}">All</a>
            <a href="{{ route('office.feedback.index', [$office, 'replied' => 'no']) }}"
               class="filter-tab {{ $repliedFilter === 'no' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split" style="font-size:.72rem;"></i> Awaiting reply
            </a>
            <a href="{{ route('office.feedback.index', [$office, 'replied' => 'yes']) }}"
               class="filter-tab {{ $repliedFilter === 'yes' ? 'active' : '' }}">
                <i class="bi bi-check-circle" style="font-size:.72rem;"></i> Replied
            </a>
        </div>

        @if($entries->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-chat-square-text"></i></div>
                <h6>No feedback yet</h6>
                <p>No feedback for this office matching the current filter.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="fb-table">
                    <thead>
                        <tr>
                            <th>Citizen</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Linked</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $fb)
                            <tr>
                                <td>
                                    <div class="citizen-name">{{ $fb->citizen?->name ?? '—' }}</div>
                                    <div class="citizen-email">{{ $fb->citizen?->email }}</div>
                                </td>
                                <td>
                                    <span class="star-row" aria-label="{{ $fb->rating }} of 5 stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $fb->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </span>
                                </td>
                                <td class="text-break" style="max-width: 14rem; font-size:.83rem;">
                                    {{ \Illuminate\Support\Str::limit($fb->comment ?? '—', 80) }}
                                </td>
                                <td>
                                    @if($fb->serviceRequest)
                                        <a href="{{ route('office.requests.show', [$office, $fb->serviceRequest]) }}" class="req-link">
                                            <i class="bi bi-file-earmark-text me-1"></i>#{{ $fb->serviceRequest->id }}
                                        </a>
                                    @else
                                        <span style="font-size:.82rem;color:#52916b;">General</span>
                                    @endif
                                </td>
                                <td>
                                    @if($fb->replied_at)
                                        <span class="status-pill pill-replied">
                                            <i class="bi bi-check-circle"></i> Replied
                                        </span>
                                    @else
                                        <span class="status-pill pill-open">
                                            <i class="bi bi-circle"></i> Open
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('office.feedback.edit', [$office, $fb]) }}" class="act-btn-reply">
                                        <i class="bi bi-reply"></i> Reply
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($entries->hasPages())
                <div class="fb-pagination">{{ $entries->links() }}</div>
            @endif
        @endif
    </div>

@endsection
