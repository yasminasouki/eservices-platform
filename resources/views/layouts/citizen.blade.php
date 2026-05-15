@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.dashboard')) — {{ __('ui.brand_name') }}</title>
    @if($isRtl)
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --accent:         #7c3aed;
            --accent-mid:     #8b5cf6;
            --accent-light:   #c4b5fd;
            --accent-subtle:  rgba(139, 92, 246, 0.1);
            --sidebar-width:  240px;
            --topbar-height:  60px;
            --sidebar-bg:     #f3f0ff;
            --sidebar-border: #e0d9ff;
            --active-bg:      #ddd6fe;
            --active-color:   #4c1d95;
            --link-color:     #6d5b8e;
            --link-hover-bg:  #ede9fe;
        }

        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f8f7ff;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        /* ══════════════════════════════
           SIDEBAR
        ══════════════════════════════ */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform .25s ease;
        }

        /* Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: 1.1rem 1.25rem 1rem;
            border-bottom: 1px solid var(--sidebar-border);
            text-decoration: none;
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background: #ddd6fe;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            font-size: 1.05rem;
            flex-shrink: 0;
        }
        .sidebar-brand-text {
            font-weight: 800;
            font-size: .95rem;
            color: #3b0764;
            letter-spacing: .2px;
            line-height: 1.2;
        }
        .sidebar-brand-sub {
            font-size: .68rem;
            color: #9d7ecf;
            font-weight: 500;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        /* Nav section label */
        .sidebar-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #b4a0d4;
            padding: 1.2rem 1.25rem .4rem;
        }

        /* Nav links */
        .sidebar-nav { flex: 1; overflow-y: auto; padding: .5rem .75rem; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .6rem .85rem;
            border-radius: 10px;
            color: var(--link-color);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: .15rem;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }
        .sidebar-link:hover {
            background: var(--link-hover-bg);
            color: var(--accent);
        }
        .sidebar-link.active {
            background: var(--active-bg);
            color: var(--active-color);
            font-weight: 700;
        }
        .sidebar-link .link-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
            background: transparent;
            transition: background .15s;
        }
        .sidebar-link:hover .link-icon { background: #e9d5ff; }
        .sidebar-link.active .link-icon { background: #c4b5fd; }

        /* Divider */
        .sidebar-divider {
            height: 1px;
            background: var(--sidebar-border);
            margin: .5rem .75rem;
        }

        /* User card at bottom */
        .sidebar-user {
            border-top: 1px solid var(--sidebar-border);
            padding: .85rem 1rem;
        }
        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: .65rem;
            border-radius: 10px;
            padding: .5rem .6rem;
            cursor: pointer;
            transition: background .15s;
            position: relative;
        }
        .sidebar-user-card:hover { background: var(--link-hover-bg); }
        .user-avatar {
            width: 34px; height: 34px;
            background: #c4b5fd;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            font-size: .85rem;
            color: #4c1d95;
            flex-shrink: 0;
        }
        .user-info { min-width: 0; flex: 1; }
        .user-name  { font-size: .82rem; font-weight: 700; color: #3b0764; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: .71rem; color: #9d7ecf; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* User popup (opens upward) */
        .user-popup {
            display: none;
            position: absolute;
            bottom: calc(100% + 6px);
            left: 0; right: 0;
            background: #fff;
            border: 1px solid #ede9fe;
            border-radius: 12px;
            box-shadow: 0 8px 28px rgba(124, 58, 237, 0.14);
            overflow: hidden;
            z-index: 200;
        }
        .user-popup.open { display: block; }
        .user-popup-header {
            background: linear-gradient(135deg, #ede9fe, #f3f0ff);
            border-bottom: 1px solid #e0d9ff;
            padding: .85rem 1rem;
        }
        .user-popup-header .p-name  { font-weight: 700; font-size: .88rem; color: #3b0764; }
        .user-popup-header .p-email { font-size: .76rem; color: #9d7ecf; }
        .id-info-block {
            background: #f8f5ff;
            border: 1px solid #ede9fe;
            border-radius: 8px;
            padding: .55rem .75rem;
            margin: .5rem .6rem;
            font-size: .78rem;
        }
        .id-info-label { color: #b4a0d4; font-weight: 700; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .3rem; }
        .id-info-row { display: flex; justify-content: space-between; color: #374151; margin-bottom: .12rem; }
        .id-info-row:last-child { margin-bottom: 0; }
        .id-info-row .lbl { color: #b4a0d4; }
        .user-popup-actions { padding: .4rem .6rem .6rem; display: flex; flex-direction: column; gap: .15rem; }
        .popup-action {
            display: flex; align-items: center; gap: .55rem;
            padding: .45rem .65rem;
            border-radius: 8px;
            font-size: .82rem;
            color: #374151;
            text-decoration: none;
            transition: background .12s;
            border: none; background: transparent; width: 100%; text-align: left; cursor: pointer;
        }
        .popup-action:hover { background: #f3f0ff; color: var(--accent); }
        .popup-action i { color: var(--accent-mid); font-size: .95rem; }
        .popup-action.danger { color: #dc2626; }
        .popup-action.danger i { color: #dc2626; }
        .popup-action.danger:hover { background: #fef2f2; }

        /* ══════════════════════════════
           TOPBAR
        ══════════════════════════════ */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #ede9fe;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            z-index: 1030;
            box-shadow: 0 1px 8px rgba(124, 58, 237, 0.05);
        }
        .topbar-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1f1235;
            flex: 1;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        /* Mobile hamburger */
        .sidebar-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 34px; height: 34px;
            background: #ede9fe;
            border: 1px solid #ddd6fe;
            border-radius: 9px;
            color: var(--accent);
            font-size: 1.05rem;
            cursor: pointer;
            margin-right: .5rem;
        }

        /* Sidebar overlay (mobile) */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1039;
        }
        .sidebar-overlay.open { display: block; }

        @media (max-width: 767px) {
            .sidebar { transform: translateX(calc(-1 * var(--sidebar-width))); }
            .sidebar.open { transform: translateX(0); }
            .topbar { left: 0; }
            .main-wrap { margin-left: 0 !important; }
            .sidebar-toggle { display: flex; }
        }

        /* ══════════════════════════════
           MAIN CONTENT
        ══════════════════════════════ */
        .main-wrap {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
        }
        .main-content { padding: 1.75rem 1.5rem; }

        /* ══════════════════════════════
           SHARED COMPONENT STYLES
        ══════════════════════════════ */
        .card-soft {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 14px rgba(0,0,0,.06);
            background: #fff;
        }
        .stat-card {
            border: none; border-radius: 14px;
            box-shadow: 0 2px 14px rgba(0,0,0,.06);
            background: #fff;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,.1); }

        /* Buttons */
        .btn-primary {
            background: #c4b5fd;
            border: none;
            color: #3b0764;
            font-weight: 700;
            border-radius: 8px;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #a78bfa;
            border: none;
            color: #3b0764;
        }
        .btn-outline-primary {
            border-color: #c4b5fd;
            color: var(--accent);
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-outline-primary:hover {
            background: #ede9fe;
            border-color: #c4b5fd;
            color: var(--accent);
        }

        /* Forms */
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-light);
            box-shadow: 0 0 0 0.2rem var(--accent-subtle);
        }

        /* Alerts */
        .alert { border-radius: 12px; border: none; }
        .alert-success { background: #f0fdf4; color: #166534; }
        .alert-info    { background: #f0f7ff; color: #1e3a8a; }
        .alert-warning { background: #fffbeb; color: #92400e; }
        .alert-danger  { background: #fef2f2; color: #991b1b; }

        /* Notification bell override */
        #notif-dropdown .btn-outline-light {
            background: #ede9fe;
            border: 1px solid #ddd6fe;
            border-radius: 9px;
            color: var(--accent);
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            padding: 0;
            font-size: 1rem;
        }
        #notif-dropdown .btn-outline-light:hover,
        #notif-dropdown .btn-outline-light:focus {
            background: #ddd6fe;
            border-color: #c4b5fd;
            color: #3b0764;
            box-shadow: none;
        }

        /* ══════════════════════════════
           RTL OVERRIDES
        ══════════════════════════════ */
        [dir="rtl"] body {
            font-family: 'Cairo', 'Segoe UI', system-ui, sans-serif;
        }
        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
            border-right: none;
            border-left: 1px solid var(--sidebar-border);
        }
        [dir="rtl"] .topbar {
            left: 0;
            right: var(--sidebar-width);
        }
        [dir="rtl"] .main-wrap {
            margin-left: 0;
            margin-right: var(--sidebar-width);
        }
        [dir="rtl"] .sidebar-toggle {
            margin-right: 0;
            margin-left: .5rem;
        }
        [dir="rtl"] .user-popup {
            left: 0;
            right: 0;
        }
        [dir="rtl"] .popup-action {
            text-align: right;
        }
        @media (max-width: 767px) {
            [dir="rtl"] .sidebar {
                transform: translateX(var(--sidebar-width));
            }
            [dir="rtl"] .sidebar.open {
                transform: translateX(0);
            }
            [dir="rtl"] .topbar {
                right: 0;
            }
            [dir="rtl"] .main-wrap {
                margin-right: 0 !important;
            }
        }

        /* Language toggle button */
        .lang-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .65rem;
            border-radius: 8px;
            border: 1px solid #ddd6fe;
            background: #ede9fe;
            color: var(--accent);
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            transition: background .15s, border-color .15s;
            white-space: nowrap;
        }
        .lang-btn:hover {
            background: #ddd6fe;
            border-color: #c4b5fd;
            color: #4c1d95;
        }
    </style>
    @stack('styles')
    @stack('head')
</head>
<body>

    {{-- ════════════ SIDEBAR ════════════ --}}
    <aside class="sidebar" id="sidebar">

        {{-- Brand --}}
        <a class="sidebar-brand" href="{{ route('citizen.dashboard') }}">
            <div class="sidebar-brand-icon"><i class="bi bi-building-fill-gear"></i></div>
            <div>
                <div class="sidebar-brand-text">{{ __('ui.brand_name') }}</div>
                <div class="sidebar-brand-sub">{{ __('ui.brand_sub') }}</div>
            </div>
        </a>

        {{-- Nav --}}
        <nav class="sidebar-nav">
            <div class="sidebar-label">{{ __('ui.menu') }}</div>

            <a href="{{ route('citizen.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('citizen.dashboard') ? 'active' : '' }}">
                <span class="link-icon"><i class="bi bi-house"></i></span>
                {{ __('ui.dashboard') }}
            </a>

            <a href="{{ route('citizen.offices.index') }}"
               class="sidebar-link {{ request()->routeIs('citizen.offices.*') ? 'active' : '' }}">
                <span class="link-icon"><i class="bi bi-building"></i></span>
                {{ __('ui.available_offices') }}
            </a>

            <a href="{{ route('citizen.requests.index') }}"
               class="sidebar-link {{ request()->routeIs('citizen.requests.*') ? 'active' : '' }}">
                <span class="link-icon"><i class="bi bi-folder2-open"></i></span>
                {{ __('ui.my_requests') }}
            </a>

            <a href="{{ route('citizen.appointments.index') }}"
               class="sidebar-link {{ request()->routeIs('citizen.appointments.*') ? 'active' : '' }}">
                <span class="link-icon"><i class="bi bi-calendar-check"></i></span>
                {{ __('ui.my_appointments') }}
            </a>

            <div class="sidebar-divider"></div>
            <div class="sidebar-label">{{ __('ui.account') }}</div>

            <a href="{{ route('citizen.id.verify') }}"
               class="sidebar-link {{ request()->routeIs('citizen.id.*') ? 'active' : '' }}">
                <span class="link-icon"><i class="bi bi-person-vcard"></i></span>
                {{ __('ui.id_verification') }}
                @php $idStatus = auth()->user()->id_document_status ?? 'not uploaded'; @endphp
                @if($idStatus === 'pending')
                    <span class="badge bg-warning text-dark ms-auto" style="font-size:.62rem;">{{ __('ui.pending') }}</span>
                @elseif($idStatus === 'verified')
                    <span class="badge ms-auto" style="font-size:.62rem; background:#d1fae5; color:#065f46;">{{ __('ui.verified') }}</span>
                @elseif($idStatus === 'rejected')
                    <span class="badge bg-danger ms-auto" style="font-size:.62rem;">{{ __('ui.rejected') }}</span>
                @endif
            </a>
        </nav>

        {{-- User card --}}
        @php
            $idBadge = match($idStatus) {
                'verified' => 'bg-success',
                'rejected' => 'bg-danger',
                'pending'  => 'bg-warning text-dark',
                default    => 'bg-secondary',
            };
            $verification = \App\Models\IdVerificationRequest::where('user_id', auth()->id())
                ->latest()->first();
        @endphp
        <div class="sidebar-user">
            <div class="sidebar-user-card" id="userCardToggle">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-email">{{ auth()->user()->email }}</div>
                </div>
                <i class="bi bi-chevron-up" style="font-size:.75rem; color:#b4a0d4;" id="userChevron"></i>

                {{-- Popup --}}
                <div class="user-popup" id="userPopup">
                    <div class="user-popup-header">
                        <div class="p-name">{{ auth()->user()->name }}</div>
                        <div class="p-email">{{ auth()->user()->email }}</div>
                        @php $idStatusKey = str_replace(' ', '_', $idStatus); @endphp
                        <span class="badge {{ $idBadge }} mt-1" style="font-size:.65rem;">
                            ID {{ __('ui.'.$idStatusKey, [], app()->getLocale()) ?: ucfirst($idStatus) }}
                        </span>
                    </div>

                    @if($verification)
                        <div class="id-info-block">
                            <div class="id-info-label">{{ __('ui.id_information') }}</div>
                            <div class="id-info-row">
                                <span class="lbl">{{ __('ui.name') }}</span>
                                <span>{{ $verification->extracted_name ?? '—' }}</span>
                            </div>
                            <div class="id-info-row">
                                <span class="lbl">{{ __('ui.date_of_birth') }}</span>
                                <span>{{ $verification->extracted_dob?->format('Y-m-d') ?? '—' }}</span>
                            </div>
                            <div class="id-info-row">
                                <span class="lbl">{{ __('ui.id_number') }}</span>
                                <span>{{ $verification->extracted_id_number ?? '—' }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="user-popup-actions">
                        <a href="{{ route('citizen.id.verify') }}" class="popup-action">
                            <i class="bi bi-person-vcard"></i>
                            {{ $verification ? __('ui.view_id_verification') : __('ui.upload_your_id') }}
                        </a>
                        <div style="height:1px;background:#f3f0ff;margin:.1rem 0;"></div>
                        <button type="button" class="popup-action danger"
                                data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="bi bi-box-arrow-right"></i> {{ __('ui.log_out') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ════════════ TOPBAR ════════════ --}}
    <header class="topbar">
        <button class="sidebar-toggle border-0" id="sidebarToggle" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title">@yield('title', 'Dashboard')</div>
        <div class="topbar-right">
            @if($isRtl)
                <a href="{{ route('language.switch', 'en') }}" class="lang-btn">
                    <i class="bi bi-translate"></i> English
                </a>
            @else
                <a href="{{ route('language.switch', 'ar') }}" class="lang-btn">
                    <i class="bi bi-translate"></i> عربي
                </a>
            @endif
            @include('partials.notification-bell', [
                'notificationIndexUrl'       => route('citizen.notifications.index'),
                'notificationReadAllUrl'     => route('citizen.notifications.read-all'),
                'notificationReadOneBaseUrl' => url('/citizen/notifications'),
            ])
        </div>
    </header>

    {{-- ════════════ MAIN CONTENT ════════════ --}}
    <div class="main-wrap">
        <div class="main-content">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <span>{{ session('info') }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    {{-- ════════════ LOGOUT MODAL ════════════ --}}
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content border-0" style="border-radius:20px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.18);">
                <div class="modal-body text-center p-0">
                    <div style="padding:2rem 2rem 1.5rem;">
                        <div class="mx-auto mb-4 d-flex align-items-center justify-content-center"
                             style="width:64px;height:64px;border-radius:18px;background:rgba(239,68,68,.1);">
                            <i class="bi bi-box-arrow-right" style="font-size:1.6rem;color:#ef4444;"></i>
                        </div>
                        <h5 class="fw-800 mb-2" style="font-size:1.15rem;letter-spacing:-.02em;color:#0f172a;">{{ __('ui.log_out_title') }}</h5>
                        <p style="font-size:.875rem;color:#64748b;line-height:1.6;margin:0;">
                            {{ __('ui.log_out_confirm') }}
                        </p>
                    </div>
                    <div style="padding:0 1.5rem 1.75rem;display:grid;gap:.625rem;">
                        <button type="button"
                                onclick="document.getElementById('logout-form').submit()"
                                style="width:100%;padding:.75rem 1.25rem;background:linear-gradient(135deg,#dc2626,#ef4444);border:none;border-radius:12px;color:#fff;font-weight:700;font-size:.9375rem;letter-spacing:.01em;cursor:pointer;box-shadow:0 4px 14px rgba(239,68,68,.3);transition:filter .15s,transform .15s;"
                                onmouseover="this.style.filter='brightness(1.08)';this.style.transform='translateY(-1px)'"
                                onmouseout="this.style.filter='';this.style.transform=''">
                            <i class="bi bi-box-arrow-right me-2"></i>{{ __('ui.yes_sign_out') }}
                        </button>
                        <button type="button" data-bs-dismiss="modal"
                                style="width:100%;padding:.72rem 1.25rem;background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;color:#475569;font-weight:600;font-size:.9375rem;cursor:pointer;transition:background .15s,border-color .15s;"
                                onmouseover="this.style.background='#f8fafc';this.style.borderColor='#cbd5e1'"
                                onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0'">
                            {{ __('ui.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">@csrf</form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    <script>
        // ── Sidebar mobile toggle ──
        const sidebar  = document.getElementById('sidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        const toggler  = document.getElementById('sidebarToggle');

        function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('open'); }
        function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

        toggler.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
        overlay.addEventListener('click', closeSidebar);

        // ── User card popup ──
        const userCard    = document.getElementById('userCardToggle');
        const userPopup   = document.getElementById('userPopup');
        const userChevron = document.getElementById('userChevron');

        userCard.addEventListener('click', e => {
            const open = userPopup.classList.toggle('open');
            userChevron.className = open ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
            userChevron.style.fontSize = '.75rem';
            userChevron.style.color = '#b4a0d4';
            e.stopPropagation();
        });

        document.addEventListener('click', e => {
            if (!userCard.contains(e.target)) {
                userPopup.classList.remove('open');
                userChevron.className = 'bi bi-chevron-up';
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
