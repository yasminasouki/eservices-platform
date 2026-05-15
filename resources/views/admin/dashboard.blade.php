@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ── Hero ── */
    .dash-hero {
        background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 60%, #e0f7ff 100%);
        border-radius: 18px; padding: 1.75rem 2rem;
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;
        position: relative; overflow: hidden;
        border: 1px solid #bae6fd;
    }
    .dash-hero::before {
        content: ''; position: absolute; top: -60px; right: 40px;
        width: 280px; height: 280px; border-radius: 50%;
        background: radial-gradient(circle, rgba(14,165,233,.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .dash-hero-eyebrow {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .1em; color: #0284c7; margin-bottom: .5rem;
        display: flex; align-items: center; gap: .4rem;
    }
    .dash-hero-eyebrow::before { content: ''; display: inline-block; width: 18px; height: 2px; background: #0284c7; border-radius: 2px; }
    .dash-hero-title { font-size: 1.5rem; font-weight: 800; color: #0c4a6e; margin-bottom: .25rem; }
    .dash-hero-sub   { font-size: .84rem; color: #64748b; margin: 0; }
    .btn-reports {
        display: inline-flex; align-items: center; gap: .5rem;
        background: #0284c7; color: #fff;
        font-weight: 700; font-size: .84rem; padding: .65rem 1.35rem;
        border-radius: 10px; text-decoration: none; border: none;
        box-shadow: 0 4px 16px rgba(2,132,199,.28);
        transition: background .15s; white-space: nowrap; position: relative; z-index: 1;
    }
    .btn-reports:hover { background: #0369a1; color: #fff; }

    /* ── Stat cards ── */
    .stat-cards-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width:1100px) { .stat-cards-row { grid-template-columns: repeat(2,1fr); } }
    @media (max-width:576px)  { .stat-cards-row { grid-template-columns: 1fr; } }

    .stat-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e4e4e7;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05);
        padding: 1.25rem 1.4rem;
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        transition: box-shadow .2s, transform .2s;
    }
    .stat-card:hover { box-shadow: 0 6px 24px rgba(14,165,233,.12); transform: translateY(-3px); }
    .stat-label { font-size: .7rem; color: #a1a1aa; font-weight: 600; margin-bottom: .3rem; text-transform: uppercase; letter-spacing: .07em; }
    .stat-value { font-size: 2rem; font-weight: 800; color: #0c4a6e; line-height: 1; }
    .stat-icon-wrap {
        width: 50px; height: 50px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
    }

    /* ── Mid row ── */
    .mid-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width:1100px) { .mid-row { grid-template-columns: 1fr 1fr; } }
    @media (max-width:700px)  { .mid-row { grid-template-columns: 1fr; } }

    .panel {
        background: #fff; border-radius: 14px;
        border: 1px solid #e4e4e7;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05);
        padding: 1.35rem;
    }
    .panel-label {
        font-size: .68rem; font-weight: 700; color: #a1a1aa;
        text-transform: uppercase; letter-spacing: .09em; margin-bottom: .85rem;
        display: flex; align-items: center; justify-content: space-between;
    }
    .panel-badge {
        font-size: .62rem; font-weight: 800;
        background: #f0f9ff; color: #0ea5e9; border: 1px solid #bae6fd;
        padding: .12rem .5rem; border-radius: 999px; text-transform: none; letter-spacing: 0;
    }

    .revenue-amount {
        font-size: 2.5rem; font-weight: 800; color: #0c4a6e; line-height: 1; margin-bottom: .4rem;
    }
    .revenue-highlight { color: #0ea5e9; }
    .revenue-sub { font-size: .78rem; color: #a1a1aa; line-height: 1.5; }
    .revenue-sub code { color: #0284c7; background: #f0f9ff; padding: .1rem .3rem; border-radius: 4px; font-size: .72rem; }

    /* ── Requests table ── */
    .requests-panel {
        background: #fff; border-radius: 14px;
        border: 1px solid #e4e4e7;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .requests-panel-header {
        padding: 1rem 1.5rem; border-bottom: 1px solid #f4f4f5;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    }
    .requests-panel-title {
        font-size: .875rem; font-weight: 800; color: #0c4a6e;
        display: flex; align-items: center; gap: .55rem;
    }
    .requests-panel-title .t-icon {
        width: 28px; height: 28px; border-radius: 7px;
        background: #f0f9ff; display: flex; align-items: center; justify-content: center;
        color: #0ea5e9; font-size: .82rem;
    }
    .panel-view-all { font-size: .78rem; color: #0ea5e9; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: .3rem; }
    .panel-view-all:hover { color: #0284c7; }

    .req-table { width: 100%; border-collapse: collapse; }
    .req-table thead th {
        background: #fafafa; font-size: .66rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .09em; color: #a1a1aa;
        padding: .65rem 1rem; border-bottom: 1px solid #f4f4f5; white-space: nowrap;
    }
    .req-table thead th:first-child { padding-left: 1.5rem; }
    .req-table thead th:last-child  { padding-right: 1.5rem; }
    .req-table tbody tr { border-bottom: 1px solid #fafafa; transition: background .1s; }
    .req-table tbody tr:last-child { border-bottom: none; }
    .req-table tbody tr:hover { background: #fafafa; }
    .req-table tbody td { padding: .9rem 1rem; vertical-align: middle; font-size: .845rem; }
    .req-table tbody td:first-child { padding-left: 1.5rem; }
    .req-table tbody td:last-child  { padding-right: 1.5rem; }

    .req-id { font-family: monospace; font-size: .8rem; font-weight: 800; background: #f0f9ff; color: #0284c7; padding: .28rem .6rem; border-radius: 7px; white-space: nowrap; }
    .citizen-name { font-weight: 700; color: #0c4a6e; font-size: .84rem; }
    .office-name  { font-size: .8rem; color: #71717a; }
    .req-date     { font-size: .78rem; color: #a1a1aa; white-space: nowrap; }

    .s-pill { display: inline-flex; align-items: center; gap: .3rem; font-size: .71rem; font-weight: 700; padding: .28rem .72rem; border-radius: 999px; white-space: nowrap; }
    .s-pending           { background: #fef9c3; color: #854d0e; }
    .s-in_review         { background: #f0f9ff; color: #0369a1; }
    .s-missing_documents { background: #fff7ed; color: #c2410c; }
    .s-approved          { background: #f0fdf4; color: #166534; }
    .s-completed         { background: #ecfeff; color: #164e63; }
    .s-rejected          { background: #fef2f2; color: #991b1b; }
    .s-default           { background: #f4f4f5; color: #52525b; }

    .empty-row td { text-align: center; padding: 3rem; color: #a1a1aa; font-size: .85rem; }
</style>
@endpush

@section('content')

    {{-- Hero --}}
    <div class="dash-hero">
        <div style="position:relative;z-index:1;">
            <div class="dash-hero-eyebrow">Admin Panel</div>
            <div class="dash-hero-title">Dashboard</div>
            <p class="dash-hero-sub">System overview, live request activity, and top offices by volume and revenue.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn-reports">
            <i class="bi bi-graph-up-arrow"></i> Reports &amp; analytics
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="stat-cards-row">
        <div class="stat-card">
            <div>
                <div class="stat-label">Gov. Offices</div>
                <div class="stat-value">{{ $stats['offices'] }}</div>
            </div>
            <div class="stat-icon-wrap" style="background:#f0f9ff;">
                <i class="bi bi-building" style="color:#0ea5e9;"></i>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Mun. Users</div>
                <div class="stat-value">{{ $stats['municipality_users'] }}</div>
            </div>
            <div class="stat-icon-wrap" style="background:#e0f2fe;">
                <i class="bi bi-person-gear" style="color:#0369a1;"></i>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Citizens</div>
                <div class="stat-value">{{ $stats['citizens'] }}</div>
            </div>
            <div class="stat-icon-wrap" style="background:#f0f9ff;">
                <i class="bi bi-people" style="color:#38bdf8;"></i>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Requests</div>
                <div class="stat-value">{{ $stats['requests'] }}</div>
            </div>
            <div class="stat-icon-wrap" style="background:#bae6fd;">
                <i class="bi bi-file-earmark-text" style="color:#0284c7;"></i>
            </div>
        </div>
    </div>

    {{-- Mid row --}}
    <div class="mid-row">
        <div class="panel">
            <div class="panel-label">Total Revenue</div>
            <div class="revenue-amount">$<span class="revenue-highlight">{{ number_format($stats['revenue'], 2) }}</span></div>
            <p class="revenue-sub mt-2 mb-0">All-time sum where <code>payments.status</code> is completed.</p>
        </div>
        <div class="panel">
            <div class="panel-label">Top offices by requests <span class="panel-badge">Top 10</span></div>
            @if(!empty($volumeLabels) && count($volumeLabels) > 0)
                <div style="height:200px;"><canvas id="chartVolume"></canvas></div>
            @else
                <div style="height:200px;display:flex;align-items:center;justify-content:center;color:#a1a1aa;font-size:.83rem;">No data yet.</div>
            @endif
        </div>
        <div class="panel">
            <div class="panel-label">Top offices by revenue <span class="panel-badge">Top 10</span></div>
            @if(!empty($revenueLabels) && count($revenueLabels) > 0)
                <div style="height:200px;"><canvas id="chartRevenue"></canvas></div>
            @else
                <div style="height:200px;display:flex;align-items:center;justify-content:center;color:#a1a1aa;font-size:.83rem;">No data yet.</div>
            @endif
        </div>
    </div>

    {{-- Latest requests --}}
    <div class="requests-panel">
        <div class="requests-panel-header">
            <div class="requests-panel-title">
                <div class="t-icon"><i class="bi bi-clock-history"></i></div>
                Latest Incoming Requests
            </div>
            <a href="{{ route('admin.service-requests.index') }}" class="panel-view-all">
                View all <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <table class="req-table">
            <thead>
                <tr><th>Ref</th><th>Citizen</th><th>Office</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($latestRequests as $req)
                    @php
                        $s = $req->status;
                        $pillClass = match($s) {
                            'pending'           => 's-pending',
                            'in_review'         => 's-in_review',
                            'missing_documents' => 's-missing_documents',
                            'approved'          => 's-approved',
                            'completed'         => 's-completed',
                            'rejected'          => 's-rejected',
                            default             => 's-default',
                        };
                        $icon = match($s) {
                            'pending'           => 'clock',
                            'in_review'         => 'eye',
                            'missing_documents' => 'paperclip',
                            'approved'          => 'check-circle',
                            'completed'         => 'patch-check',
                            'rejected'          => 'x-circle',
                            default             => 'circle',
                        };
                    @endphp
                    <tr>
                        <td><span class="req-id">#{{ $req->id }}</span></td>
                        <td><div class="citizen-name">{{ $req->citizen?->name ?? 'N/A' }}</div></td>
                        <td><div class="office-name">{{ $req->governmentOffice?->name ?? 'N/A' }}</div></td>
                        <td>
                            <span class="s-pill {{ $pillClass }}">
                                <i class="bi bi-{{ $icon }}" style="font-size:.64rem;"></i>
                                {{ str_replace('_', ' ', ucfirst($s)) }}
                            </span>
                        </td>
                        <td class="req-date">{{ $req->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="5">
                            <i class="bi bi-inbox" style="font-size:1.6rem;display:block;margin-bottom:.5rem;color:#d4d4d8;"></i>
                            No requests yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection

@push('scripts')
@if((!empty($volumeLabels) && count($volumeLabels) > 0) || (!empty($revenueLabels) && count($revenueLabels) > 0))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function makeGradient(ctx, c1, c2) {
        const g = ctx.createLinearGradient(0, 0, 260, 0);
        g.addColorStop(0, c1); g.addColorStop(1, c2); return g;
    }

    /* Light navy palette */
    const palette = [
        ['#6b8cba','#8aaad0'], ['#5c7dab','#7a9bc8'], ['#7896c4','#96b4d8'],
        ['#4e6f9e','#6b8cba'], ['#8aaad0','#a4c0e0'], ['#6480b2','#8098c8'],
        ['#7a9bc8','#98b6dc'], ['#5c7dab','#7898c4'], ['#8eb2d6','#a8cce4'], ['#6b8cba','#88a8d0'],
    ];

    const sharedOpts = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { color: '#a1a1aa', font: { size: 10 } }, grid: { color: '#f4f4f5' }, border: { color: '#e4e4e7' } },
            y: { ticks: { color: '#71717a', font: { size: 10 } }, grid: { display: false }, border: { display: false } },
        }
    };

    const volLabels = @json($volumeLabels ?? []);
    const volData   = @json($volumeCounts ?? []);
    const revLabels = @json($revenueLabels ?? []);
    const revData   = @json($revenueAmounts ?? []);

    if (volLabels.length > 0) {
        const ctx = document.getElementById('chartVolume').getContext('2d');
        new Chart(ctx, {
            type: 'bar', options: { ...sharedOpts, indexAxis: 'y' },
            data: { labels: volLabels, datasets: [{ data: volData, backgroundColor: palette.slice(0, volLabels.length).map(([c1,c2]) => makeGradient(ctx,c1,c2)), borderRadius: 6, borderSkipped: false }] }
        });
    }
    if (revLabels.length > 0) {
        const ctx = document.getElementById('chartRevenue').getContext('2d');
        const opts = JSON.parse(JSON.stringify(sharedOpts));
        opts.scales.x.ticks.callback = v => '$' + v;
        new Chart(ctx, {
            type: 'bar', options: { ...opts, indexAxis: 'y' },
            data: { labels: revLabels, datasets: [{ data: revData, backgroundColor: palette.slice(0, revLabels.length).map(([c1,c2]) => makeGradient(ctx,c1,c2)), borderRadius: 6, borderSkipped: false }] }
        });
    }
</script>
@endif
@endpush
