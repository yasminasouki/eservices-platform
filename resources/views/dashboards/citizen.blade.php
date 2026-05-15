@extends('layouts.citizen')

@section('title', __('ui.dashboard'))

@push('styles')
<style>
    /* Hero */
    .dashboard-hero {
        background: linear-gradient(135deg, #ede9fe 0%, #e0d9ff 60%, #ddd6fe 100%);
        border: 1px solid #d8d0fb;
        border-radius: 16px;
        padding: 2rem 2.25rem;
        color: #3b0764;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.75rem;
    }
    .dashboard-hero::after {
        content: '';
        position: absolute;
        right: -40px; top: -40px;
        width: 220px; height: 220px;
        background: rgba(167, 139, 250, 0.15);
        border-radius: 50%;
    }
    .dashboard-hero::before {
        content: '';
        position: absolute;
        right: 60px; bottom: -60px;
        width: 160px; height: 160px;
        background: rgba(167, 139, 250, 0.1);
        border-radius: 50%;
    }
    .dashboard-hero .greeting { font-size: 1.55rem; font-weight: 700; margin-bottom: .2rem; color: #3b0764; }
    .dashboard-hero .sub { font-size: .92rem; color: #7c5cbf; margin-bottom: 0; }
    .hero-actions { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.25rem; position: relative; z-index: 1; }
    .hero-btn {
        display: inline-flex; align-items: center; gap: .45rem;
        background: rgba(255,255,255,0.6);
        border: 1px solid #c4b5fd;
        color: #4c1d95;
        padding: .45rem 1rem;
        border-radius: 9px;
        font-size: .85rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s;
    }
    .hero-btn:hover { background: rgba(255,255,255,0.85); color: #3b0764; }
    .hero-btn.primary {
        background: #c4b5fd;
        color: #3b0764;
        border-color: #a78bfa;
    }
    .hero-btn.primary:hover { background: #a78bfa; color: #3b0764; }

    /* Stat cards */
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.75rem; }
    @media (max-width: 991px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 479px) { .stat-grid { grid-template-columns: 1fr 1fr; } }

    .stat-card-v2 {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0,0,0,.06);
        padding: 1.25rem 1.25rem 1.1rem;
        display: flex;
        flex-direction: column;
        gap: .3rem;
        transition: transform .2s, box-shadow .2s;
        border-top: 4px solid transparent;
        text-decoration: none;
        cursor: pointer;
    }
    .stat-card-v2:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,.1); }

    .stat-card-v2.c-purple  { border-top-color: #c4b5fd; }
    .stat-card-v2.c-green   { border-top-color: #a7f3d0; }
    .stat-card-v2.c-blue    { border-top-color: #bfdbfe; }
    .stat-card-v2.c-amber   { border-top-color: #fde68a; }

    .stat-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        margin-bottom: .5rem;
    }
    .stat-icon.purple { background: #ede9fe; color: #7c3aed; }
    .stat-icon.green  { background: #d1fae5; color: #059669; }
    .stat-icon.blue   { background: #dbeafe; color: #3b82f6; }
    .stat-icon.amber  { background: #fef3c7; color: #d97706; }

    .stat-label { font-size: .78rem; color: #9ca3af; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
    .stat-value { font-size: 1.9rem; font-weight: 800; color: #111827; line-height: 1; }

    /* Bottom cards */
    .section-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .section-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem .85rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .section-card-title {
        font-weight: 700;
        font-size: .95rem;
        color: #111827;
        display: flex; align-items: center; gap: .5rem;
    }
    .section-card-title i { color: #a78bfa; }
    .section-card-link { font-size: .82rem; color: #8b5cf6; text-decoration: none; font-weight: 500; }
    .section-card-link:hover { text-decoration: underline; }

    /* Request list */
    .req-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1.25rem;
        border-bottom: 1px solid #f9fafb;
        transition: background .12s;
    }
    .req-item:last-child { border-bottom: none; }
    .req-item:hover { background: #fafafa; }
    .req-service { font-weight: 600; font-size: .88rem; color: #111827; }
    .req-office  { font-size: .8rem; color: #9ca3af; margin-top: .1rem; }

    /* Browse services card */
    .browse-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0,0,0,.06);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .browse-card-banner {
        background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
        padding: 1.4rem 1.4rem 1rem;
        border-bottom: 1px solid #e8e2fb;
        position: relative;
        overflow: hidden;
    }
    .browse-card-banner::after {
        content: '\F52F';
        font-family: 'bootstrap-icons';
        position: absolute;
        right: -10px; top: -10px;
        font-size: 5.5rem;
        color: rgba(167,139,250,0.15);
        line-height: 1;
        pointer-events: none;
    }
    .browse-banner-icon {
        width: 42px; height: 42px;
        background: #ddd6fe;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #7c3aed;
        font-size: 1.2rem;
        margin-bottom: .7rem;
    }
    .browse-banner-title {
        font-weight: 800;
        font-size: 1rem;
        color: #1f1235;
        margin-bottom: .2rem;
    }
    .browse-banner-sub {
        font-size: .8rem;
        color: #9d7ecf;
        line-height: 1.45;
    }
    .browse-features-body { padding: 1.1rem 1.4rem 1.4rem; flex: 1; display: flex; flex-direction: column; }
    .browse-features { display: flex; flex-direction: column; gap: .55rem; margin-bottom: 1.3rem; flex: 1; }
    .browse-feature {
        display: flex; align-items: center; gap: .7rem;
        font-size: .84rem; color: #374151;
        font-weight: 500;
    }
    .browse-feature-icon {
        width: 28px; height: 28px;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem;
        flex-shrink: 0;
    }
    .browse-feature-icon.map   { background: #dbeafe; color: #2563eb; }
    .browse-feature-icon.cal   { background: #d1fae5; color: #059669; }
    .browse-feature-icon.track { background: #fef3c7; color: #d97706; }
    .browse-feature-icon.chat  { background: #fce7f3; color: #db2777; }
    .browse-cta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ede9fe;
        border: 1px solid #ddd6fe;
        border-radius: 11px;
        padding: .75rem 1rem;
        text-decoration: none;
        transition: background .15s, border-color .15s;
        gap: .5rem;
    }
    .browse-cta:hover { background: #ddd6fe; border-color: #c4b5fd; }
    .browse-cta-label { font-weight: 700; font-size: .88rem; color: #3b0764; }
    .browse-cta-arrow {
        width: 30px; height: 30px;
        background: #c4b5fd;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #3b0764;
        font-size: .9rem;
        flex-shrink: 0;
        transition: background .15s;
    }
    .browse-cta:hover .browse-cta-arrow { background: #a78bfa; }

    /* Pending alert */
    .id-pending-alert {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: .9rem 1.1rem;
        display: flex; align-items: flex-start; gap: .75rem;
        margin-bottom: 1.5rem;
    }
    .id-pending-alert i { color: #d97706; font-size: 1.2rem; margin-top: .05rem; flex-shrink: 0; }
    .id-pending-alert strong { color: #92400e; font-size: .88rem; }
    .id-pending-alert span { font-size: .82rem; color: #78350f; }
</style>
@endpush

@section('content')

    {{-- ID pending/rejected alert --}}
    @if(auth()->user()->id_document_status === 'pending')
        <div class="id-pending-alert">
            <i class="bi bi-clock-history"></i>
            <div>
                <strong>{{ __('ui.id_pending_title') }}</strong><br>
                <span>{{ __('ui.id_pending_desc') }}</span>
            </div>
        </div>
    @elseif(auth()->user()->id_document_status === 'rejected')
        <div class="id-pending-alert" style="background:#fef2f2;border-color:#fecaca;">
            <i class="bi bi-exclamation-circle" style="color:#dc2626;"></i>
            <div>
                <strong style="color:#991b1b;">{{ __('ui.id_rejected_title') }}</strong><br>
                <span style="color:#7f1d1d;">{{ __('ui.id_rejected_desc') }}</span>
            </div>
        </div>
    @endif

    {{-- Hero --}}
    <div class="dashboard-hero">
        <div class="greeting">{{ __('ui.welcome_back', ['name' => auth()->user()->name]) }}</div>
        <p class="sub">{{ __('ui.hero_sub') }}</p>
        <div class="hero-actions">
            <a href="{{ route('citizen.offices.index') }}" class="hero-btn primary">
                <i class="bi bi-grid-fill"></i> {{ __('ui.browse_services') }}
            </a>
            <a href="{{ route('citizen.requests.index') }}" class="hero-btn">
                <i class="bi bi-folder2-open"></i> {{ __('ui.my_requests') }}
            </a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="stat-grid">
        <a href="{{ route('citizen.requests.index') }}" class="stat-card-v2 c-purple">
            <div class="stat-icon purple"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-label">{{ __('ui.active_requests') }}</div>
            <div class="stat-value">{{ $activeRequests }}</div>
        </a>
        <a href="{{ route('citizen.requests.index', ['tab' => 'completed']) }}" class="stat-card-v2 c-green">
            <div class="stat-icon green"><i class="bi bi-check2-all"></i></div>
            <div class="stat-label">{{ __('ui.completed') }}</div>
            <div class="stat-value">{{ $completedRequests }}</div>
        </a>
        <a href="{{ route('citizen.appointments.index') }}" class="stat-card-v2 c-blue">
            <div class="stat-icon blue"><i class="bi bi-calendar-event"></i></div>
            <div class="stat-label">{{ __('ui.upcoming_appts') }}</div>
            <div class="stat-value">{{ $upcomingAppointments > 0 ? $upcomingAppointments : '—' }}</div>
        </a>
        <div class="stat-card-v2 c-amber">
            <div class="stat-icon amber"><i class="bi bi-wallet2"></i></div>
            <div class="stat-label">{{ __('ui.total_paid') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ number_format($totalPaid, 2) }}</div>
        </div>
    </div>

    {{-- Bottom row --}}
    <div class="row g-4">

        {{-- Browse services --}}
        <div class="col-md-5">
            <div class="browse-card">
                <div class="browse-card-banner">
                    <div class="browse-banner-icon"><i class="bi bi-building"></i></div>
                    <div class="browse-banner-title">{{ __('ui.browse_services') }}</div>
                    <div class="browse-banner-sub">{{ __('ui.browse_services_subtitle') }}</div>
                </div>
                <div class="browse-features-body">
                    <div class="browse-features">
                        <div class="browse-feature">
                            <div class="browse-feature-icon map"><i class="bi bi-geo-alt-fill"></i></div>
                            {{ __('ui.view_nearby_offices') }}
                        </div>
                        <div class="browse-feature">
                            <div class="browse-feature-icon cal"><i class="bi bi-calendar-check"></i></div>
                            {{ __('ui.book_appointments_online') }}
                        </div>
                        <div class="browse-feature">
                            <div class="browse-feature-icon track"><i class="bi bi-arrow-repeat"></i></div>
                            {{ __('ui.track_requests_realtime') }}
                        </div>
                        <div class="browse-feature">
                            <div class="browse-feature-icon chat"><i class="bi bi-chat-dots-fill"></i></div>
                            {{ __('ui.chat_with_office') }}
                        </div>
                    </div>
                    <a href="{{ route('citizen.offices.index') }}" class="browse-cta">
                        <span class="browse-cta-label">{{ __('ui.view_all_offices') }}</span>
                        <span class="browse-cta-arrow">
                            <i class="bi {{ app()->getLocale() === 'ar' ? 'bi-arrow-left' : 'bi-arrow-right' }}"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent requests --}}
        <div class="col-md-7">
            <div class="section-card h-100">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="bi bi-clock-history"></i> {{ __('ui.recent_requests') }}
                    </div>
                    <a href="{{ route('citizen.requests.index') }}" class="section-card-link">
                        {{ __('ui.view_all') }} <i class="bi {{ app()->getLocale() === 'ar' ? 'bi-arrow-left' : 'bi-arrow-right' }}"></i>
                    </a>
                </div>

                @if($recentRequests->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3" style="font-size:2.5rem;opacity:.25;">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <div class="text-muted small fw-semibold">{{ __('ui.no_requests_yet') }}</div>
                        <div class="text-muted" style="font-size:.8rem;">
                            {{ __('ui.no_requests_desc') }}
                        </div>
                        <a href="{{ route('citizen.offices.index') }}" class="btn btn-sm btn-primary mt-3">
                            {{ __('ui.get_started') }}
                        </a>
                    </div>
                @else
                    @foreach($recentRequests as $req)
                        <div class="req-item">
                            <div>
                                <div class="req-service">
                                    {{ $req->service?->name ?? __('ui.request_hash', ['id' => $req->id]) }}
                                </div>
                                <div class="req-office">
                                    <i class="bi bi-building me-1"></i>{{ $req->governmentOffice?->name }}
                                </div>
                            </div>
                            <a href="{{ route('citizen.requests.show', $req) }}"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                {{ __('ui.open') }}
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

    </div>

@endsection
