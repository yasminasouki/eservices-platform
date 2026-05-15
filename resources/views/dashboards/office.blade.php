@extends('layouts.office')

@section('title', 'Dashboard')

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
@endphp

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* ── Stat cards ── */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #d1fae5;
        box-shadow: 0 2px 12px rgba(22,163,74,.06);
        padding: 1.25rem 1.25rem 1rem;
        display: flex; flex-direction: column; gap: .75rem;
        transition: transform .18s, box-shadow .18s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(22,163,74,.1); cursor: pointer; }
    .stat-icon-wrap {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; flex-shrink: 0;
    }
    .stat-label { font-size: .75rem; font-weight: 600; color: #52916b; margin-bottom: .1rem; }
    .stat-value { font-size: 1.85rem; font-weight: 800; color: #0f2d13; line-height: 1; }
    .stat-sub   { font-size: .72rem; color: #86efac; font-weight: 500; margin-top: .15rem; }

    /* ── Password alert ── */
    .pwd-alert {
        background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
        padding: .9rem 1.2rem; margin-bottom: 1.5rem;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
    }
    .pwd-alert-text { font-size: .875rem; color: #78350f; font-weight: 500; }
    .pwd-alert-btn {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #fde68a; border: none; color: #78350f;
        font-weight: 700; font-size: .8rem; padding: .4rem .9rem;
        border-radius: 8px; text-decoration: none; white-space: nowrap;
        transition: background .12s;
    }
    .pwd-alert-btn:hover { background: #fcd34d; color: #78350f; }

    /* ── Recent requests card ── */
    .requests-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(22,163,74,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }
    .card-header-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.4rem; border-bottom: 1px solid #f0fdf4; flex-wrap: wrap; gap: .5rem;
    }
    .card-header-title { font-size: .95rem; font-weight: 700; color: #0f2d13; display: flex; align-items: center; gap: .5rem; }
    .card-header-icon {
        width: 32px; height: 32px; border-radius: 9px; background: #dcfce7;
        display: flex; align-items: center; justify-content: center;
        color: #16a34a; font-size: .9rem;
    }
    .view-all-link {
        font-size: .8rem; font-weight: 600; color: #16a34a;
        text-decoration: none; display: flex; align-items: center; gap: .25rem;
    }
    .view-all-link:hover { color: #15803d; }

    /* ── Table ── */
    .req-table { width: 100%; border-collapse: collapse; }
    .req-table thead th {
        background: #f0fdf4; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #86efac;
        padding: .6rem 1rem; border-bottom: 1px solid #d1fae5; white-space: nowrap;
    }
    .req-table thead th:first-child { padding-left: 1.4rem; }
    .req-table thead th:last-child  { padding-right: 1.4rem; }
    .req-table tbody tr {
        border-bottom: 1px solid #f0fdf4; transition: background .12s; cursor: pointer;
    }
    .req-table tbody tr:last-child { border-bottom: none; }
    .req-table tbody tr:hover { background: #f0fdf4; }
    .req-table tbody td { padding: .85rem 1rem; vertical-align: middle; font-size: .875rem; color: #374151; }
    .req-table tbody td:first-child { padding-left: 1.4rem; }
    .req-table tbody td:last-child  { padding-right: 1.4rem; }

    .req-id {
        display: inline-flex; align-items: center; justify-content: center;
        font-family: monospace; font-size: .78rem; font-weight: 800;
        background: #f0fdf4; color: #15803d;
        padding: .28rem .55rem; border-radius: 7px; white-space: nowrap;
    }
    .citizen-name  { font-weight: 600; color: #0f2d13; font-size: .875rem; }
    .req-date      { font-size: .79rem; color: #86efac; white-space: nowrap; font-weight: 500; }
    .status-pill {
        display: inline-flex; align-items: center; gap: .32rem;
        font-size: .74rem; font-weight: 700;
        padding: .28rem .72rem; border-radius: 999px; white-space: nowrap;
    }
    .status-pill i { font-size: .7rem; }

    .empty-state { text-align: center; padding: 3.5rem 2rem; }
    .empty-icon {
        width: 64px; height: 64px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #86efac; font-size: 1.7rem; margin: 0 auto 1rem;
    }
    .empty-state p { font-size: .84rem; color: #52916b; margin: 0; }
</style>
@endpush

@section('content')

    {{-- Password change alert --}}
    @if(auth()->user()->must_change_password)
        <div class="pwd-alert">
            <div class="pwd-alert-text">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Security:</strong> You must change your password before continuing.
            </div>
            <a href="{{ route('office.password.change') }}" class="pwd-alert-btn">
                <i class="bi bi-key"></i> Change Password
            </a>
        </div>
    @endif

    {{-- Header --}}
    <div style="margin-bottom:1.5rem;">
        <div class="page-title">Dashboard</div>
        <p class="page-sub">
            @if($offices->count() === 1)
                {{ $offices->first()->name }}
            @else
                {{ $offices->count() }} offices: {{ $offices->pluck('name')->join(', ') }}
            @endif
        </p>
    </div>

    {{-- Stat cards --}}
    <div class="stat-grid">
        @isset($officeContext)
        <a href="{{ route('office.requests.index', $officeContext) }}" class="stat-card" style="text-decoration:none;">
        @else
        <div class="stat-card">
        @endisset
            <div class="stat-icon-wrap" style="background:#fef3c7;">
                <i class="bi bi-hourglass-split" style="color:#92400e;"></i>
            </div>
            <div>
                <div class="stat-label">Pending requests</div>
                <div class="stat-value">{{ $pending }}</div>
                <div class="stat-sub">Awaiting action</div>
            </div>
        @isset($officeContext)
        </a>
        @else
        </div>
        @endisset

        @isset($officeContext)
        <a href="{{ route('office.requests.index', $officeContext) }}?status=completed" class="stat-card" style="text-decoration:none;">
        @else
        <div class="stat-card">
        @endisset
            <div class="stat-icon-wrap" style="background:#dcfce7;">
                <i class="bi bi-check-circle" style="color:#14532d;"></i>
            </div>
            <div>
                <div class="stat-label">Completed today</div>
                <div class="stat-value">{{ $completedToday }}</div>
                <div class="stat-sub">Processed today</div>
            </div>
        @isset($officeContext)
        </a>
        @else
        </div>
        @endisset

        @isset($officeContext)
        <a href="{{ route('office.appointments.index', $officeContext) }}" class="stat-card" style="text-decoration:none;">
        @else
        <div class="stat-card">
        @endisset
            <div class="stat-icon-wrap" style="background:#dbeafe;">
                <i class="bi bi-calendar-check" style="color:#1e40af;"></i>
            </div>
            <div>
                <div class="stat-label">Appointments today</div>
                <div class="stat-value">{{ $appointmentsToday }}</div>
                <div class="stat-sub">Scheduled for today</div>
            </div>
        @isset($officeContext)
        </a>
        @else
        </div>
        @endisset

        @isset($officeContext)
        <a href="{{ route('office.feedback.index', $officeContext) }}" class="stat-card" style="text-decoration:none;">
        @else
        <div class="stat-card">
        @endisset
            <div class="stat-icon-wrap" style="background:#f0fdf4;">
                <i class="bi bi-star-fill" style="color:#16a34a;"></i>
            </div>
            <div>
                <div class="stat-label">Average rating</div>
                <div class="stat-value">{{ $averageRating !== null ? $averageRating : '—' }}</div>
                <div class="stat-sub">{{ $averageRating !== null ? 'out of 5 stars' : 'No ratings yet' }}</div>
            </div>
        @isset($officeContext)
        </a>
        @else
        </div>
        @endisset
    </div>

    {{-- Recent requests --}}
    <div class="requests-card">
        <div class="card-header-row">
            <div class="card-header-title">
                <div class="card-header-icon"><i class="bi bi-inbox"></i></div>
                Recent Requests
            </div>
            @isset($officeContext)
                <a href="{{ route('office.requests.index', $officeContext) }}" class="view-all-link">
                    View all <i class="bi bi-arrow-right"></i>
                </a>
            @endisset
        </div>

        @if($latestRequests->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <p>No service requests for your office yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="req-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Service</th>
                            <th>Citizen</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestRequests as $req)
                            @php $cfg = $statusConfig($req->status); @endphp
                            <tr onclick="window.location='{{ route('office.requests.show', [$req->government_office_id, $req->id]) }}'">
                                <td><span class="req-id">#{{ $req->id }}</span></td>
                                <td>{{ $req->service?->name ?? '—' }}</td>
                                <td><span class="citizen-name">{{ $req->citizen?->name ?? '—' }}</span></td>
                                <td>
                                    <span class="status-pill" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
                                        <i class="bi bi-{{ $cfg['icon'] }}"></i>
                                        {{ $statusLabel($req->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="req-date">
                                        {{ optional($req->submitted_at ?? $req->created_at)->format('M j, Y g:i a') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
