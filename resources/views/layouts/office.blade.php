@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.office_portal')) — {{ __('ui.brand_name') }}</title>
    @if($isRtl)
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width:   240px;
            --topbar-height:   60px;
            --sidebar-bg:      #f0fdf4;
            --sidebar-border:  #d1fae5;
            --accent:          #16a34a;
            --accent-mid:      #15803d;
            --accent-light:    #86efac;
            --accent-pale:     #dcfce7;
            --accent-dark:     #14532d;
            --body-bg:         #f5fdf7;
        }

        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; background: var(--body-bg); font-family: system-ui, -apple-system, sans-serif; }

        /* ── Sidebar ── */
        .office-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex; flex-direction: column;
            z-index: 1040; transition: transform .25s ease;
        }

        /* Brand */
        .sidebar-brand {
            height: var(--topbar-height);
            display: flex; align-items: center; gap: .65rem;
            padding: 0 1.2rem;
            border-bottom: 1px solid var(--sidebar-border);
            text-decoration: none; color: var(--accent-dark);
            font-weight: 800; font-size: 1rem; letter-spacing: -.2px; flex-shrink: 0;
        }
        .sidebar-brand .brand-icon {
            width: 34px; height: 34px; border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), var(--accent-mid));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem; flex-shrink: 0;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1; overflow-y: auto; padding: 1rem .75rem;
            display: flex; flex-direction: column; gap: .15rem;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--accent-light); border-radius: 4px; }

        .sidebar-section-label {
            font-size: .62rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .09em; color: var(--accent-light);
            padding: .7rem .65rem .2rem; margin-top: .3rem;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: .7rem;
            padding: .52rem .75rem; border-radius: 8px;
            color: #374151; font-size: .875rem; font-weight: 500;
            text-decoration: none; transition: background .12s, color .12s;
        }
        .sidebar-link i { font-size: .95rem; width: 20px; text-align: center; flex-shrink: 0; }
        .sidebar-link:hover { background: var(--accent-pale); color: var(--accent-dark); }
        .sidebar-link.active {
            background: linear-gradient(135deg, var(--accent), var(--accent-mid));
            color: #fff; font-weight: 700;
            box-shadow: 0 4px 12px rgba(22,163,74,.22);
        }
        .sidebar-link.disabled { color: #9ca3af; cursor: default; pointer-events: none; }

        /* Office context switcher */
        .office-switcher {
            padding: .6rem .75rem;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }
        .office-switcher label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--accent-light); display: block; margin-bottom: .25rem; }
        .office-switcher .form-select {
            font-size: .8rem; border-color: var(--sidebar-border);
            border-radius: 8px; padding: .3rem .6rem;
            background-color: #fff; color: var(--accent-dark); font-weight: 600;
        }
        .office-switcher .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 .15rem rgba(22,163,74,.15); }

        /* User card */
        .sidebar-user-wrap {
            padding: .85rem; border-top: 1px solid var(--sidebar-border);
            position: relative; flex-shrink: 0;
        }
        .sidebar-user-btn {
            display: flex; align-items: center; gap: .65rem;
            padding: .55rem .65rem; border-radius: 8px;
            cursor: pointer; transition: background .12s;
            border: none; background: transparent; width: 100%; text-align: left;
        }
        .sidebar-user-btn:hover { background: var(--accent-pale); }
        .sidebar-avatar {
            width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--accent), var(--accent-mid));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: .85rem;
        }
        .sidebar-user-name { font-size: .83rem; font-weight: 700; color: var(--accent-dark); line-height: 1.2; }
        .sidebar-user-role { font-size: .7rem; color: var(--accent-light); }
        .sidebar-user-caret { margin-left: auto; color: var(--accent-light); font-size: .8rem; }

        /* User popup */
        .user-popup {
            position: absolute; bottom: calc(100% + 4px); left: .85rem; right: .85rem;
            background: #fff; border: 1px solid var(--sidebar-border);
            border-radius: 12px; box-shadow: 0 8px 24px rgba(22,163,74,.14);
            padding: .4rem; display: none; z-index: 10;
        }
        .user-popup.open { display: block; }
        .user-popup-item {
            display: flex; align-items: center; gap: .6rem;
            padding: .5rem .75rem; border-radius: 8px;
            font-size: .83rem; color: #374151; text-decoration: none;
            transition: background .12s; cursor: pointer; border: none; background: transparent; width: 100%;
        }
        .user-popup-item:hover { background: var(--accent-pale); color: var(--accent-dark); }
        .user-popup-item.danger { color: #dc2626; }
        .user-popup-item.danger:hover { background: #fef2f2; }

        /* ── Topbar ── */
        .office-topbar {
            position: fixed; top: 0; left: var(--sidebar-width); right: 0;
            height: var(--topbar-height);
            background: #fff; border-bottom: 1px solid var(--sidebar-border);
            display: flex; align-items: center; padding: 0 1.5rem;
            z-index: 1030; gap: 1rem;
        }
        .topbar-hamburger {
            display: none; background: none; border: none;
            width: 36px; height: 36px; border-radius: 8px;
            align-items: center; justify-content: center;
            font-size: 1.2rem; color: #64748b; cursor: pointer;
        }
        .topbar-hamburger:hover { background: var(--accent-pale); color: var(--accent-dark); }
        .topbar-title { font-weight: 700; font-size: 1rem; color: var(--accent-dark); flex: 1; }
        .topbar-actions { display: flex; align-items: center; gap: .65rem; }

        /* ── Main ── */
        .office-main { margin-left: var(--sidebar-width); padding-top: var(--topbar-height); min-height: 100vh; }
        .office-content { padding: 1.75rem; }

        /* ── Mobile overlay ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(20,83,45,.35); z-index: 1039; backdrop-filter: blur(2px);
        }
        .sidebar-overlay.open { display: block; }

        @media (max-width: 991px) {
            .office-sidebar { transform: translateX(-100%); }
            .office-sidebar.open { transform: translateX(0); }
            .office-main { margin-left: 0; }
            .office-topbar { left: 0; }
            .topbar-hamburger { display: flex; }
        }

        /* ── Breadcrumbs ── */
        .breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem; padding: 0; margin: 0; list-style: none; background: none; }
        .breadcrumb-item { display: flex; align-items: center; }
        .breadcrumb-item + .breadcrumb-item::before { content: '›'; color: var(--accent-light); font-size: .85rem; margin-right: .35rem; padding: 0; }
        .breadcrumb-item a, .breadcrumb-link { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .65rem; background: var(--accent-pale); border: 1.5px solid var(--accent-light); border-radius: 20px; font-size: .78rem; font-weight: 600; color: var(--accent); text-decoration: none; transition: background .15s, border-color .15s; }
        .breadcrumb-item a:hover, .breadcrumb-link:hover { background: #bbf7d0; border-color: var(--accent); color: var(--accent-mid); }
        .breadcrumb-item.active, .breadcrumb-item span, .breadcrumb-current { display: inline-flex; align-items: center; padding: .2rem .65rem; font-size: .78rem; font-weight: 600; color: #6b7280; }
        .breadcrumb-sep { color: var(--accent-light); font-size: .85rem; margin: 0 .1rem; }

        /* ── Global overrides ── */
        .card-soft { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(22,163,74,.07); }
        .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent-mid)); border: none; color: #fff; font-weight: 600; }
        .btn-primary:hover, .btn-primary:focus { background: linear-gradient(135deg, var(--accent-mid), #166534); border: none; color: #fff; }
        .btn-success { background: linear-gradient(135deg, var(--accent), var(--accent-mid)); border: none; font-weight: 600; }
        .btn-success:hover, .btn-success:focus { background: linear-gradient(135deg, var(--accent-mid), #166534); border: none; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 .2rem rgba(22,163,74,.15); }
        .alert { border-radius: 10px; }

        /* Notification bell override */
        #notif-dropdown .notif-bell-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--accent-pale); border: 1px solid var(--accent-light);
            display: flex; align-items: center; justify-content: center;
            color: var(--accent); font-size: 1.05rem;
            cursor: pointer; position: relative; transition: background .15s;
        }
        #notif-dropdown .notif-bell-btn:hover { background: #bbf7d0; }

        /* ── RTL overrides ── */
        [dir="rtl"] body { font-family: 'Cairo', system-ui, sans-serif; }
        [dir="rtl"] .office-sidebar { left: auto; right: 0; border-right: none; border-left: 1px solid var(--sidebar-border); }
        [dir="rtl"] .office-topbar { left: 0; right: var(--sidebar-width); }
        [dir="rtl"] .office-main { margin-left: 0; margin-right: var(--sidebar-width); }
        [dir="rtl"] .sidebar-user-caret { margin-left: 0; margin-right: auto; }
        [dir="rtl"] .user-popup-item { text-align: right; }
        @media (max-width: 991px) {
            [dir="rtl"] .office-sidebar { transform: translateX(var(--sidebar-width)); }
            [dir="rtl"] .office-sidebar.open { transform: translateX(0); }
            [dir="rtl"] .office-main { margin-right: 0; }
            [dir="rtl"] .office-topbar { right: 0; }
        }

        /* Language toggle */
        .lang-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .3rem .65rem; border-radius: 8px;
            border: 1px solid var(--accent-light);
            background: var(--accent-pale); color: var(--accent-dark);
            font-size: .78rem; font-weight: 700; text-decoration: none;
            transition: background .15s; white-space: nowrap;
        }
        .lang-btn:hover { background: #bbf7d0; color: var(--accent-dark); }
    </style>
    @stack('styles')
    @stack('head')
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ── Sidebar ── --}}
    <aside class="office-sidebar" id="officeSidebar">

        <a class="sidebar-brand" href="{{ route('office.dashboard') }}">
            <span class="brand-icon"><i class="bi bi-building-fill-gear"></i></span>
            {{ __('ui.brand_name') }}
        </a>

        {{-- Office context switcher --}}
        @if(isset($officeNavOffices) && $officeNavOffices->count() > 1)
            <div class="office-switcher">
                <label>{{ __('ui.offices') }}</label>
                <form method="POST" action="{{ route('office.context') }}">
                    @csrf
                    <select name="office_id" class="form-select" onchange="this.form.submit()">
                        @foreach($officeNavOffices as $o)
                            <option value="{{ $o->id }}" @selected($officeContext && (int) $officeContext->id === (int) $o->id)>
                                {{ $o->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        <nav class="sidebar-nav">
            @php $ctx = $officeContext ?? null; @endphp

            <span class="sidebar-section-label">{{ __('ui.menu') }}</span>

            <a href="{{ route('office.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('office.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> {{ __('ui.dashboard') }}
            </a>
            <a href="{{ route('office.profile.index') }}"
               class="sidebar-link {{ request()->routeIs('office.profile.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> {{ __('ui.offices') }}
            </a>

            <span class="sidebar-section-label">{{ __('ui.services') }}</span>

            @if($ctx)
                <a href="{{ route('office.categories.index', $ctx) }}"
                   class="sidebar-link {{ request()->routeIs('office.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> {{ __('ui.categories') }}
                </a>
                <a href="{{ route('office.services.index', $ctx) }}"
                   class="sidebar-link {{ request()->routeIs('office.services.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> {{ __('ui.services') }}
                </a>
            @else
                <span class="sidebar-link disabled"><i class="bi bi-grid"></i> {{ __('ui.categories') }}</span>
                <span class="sidebar-link disabled"><i class="bi bi-box-seam"></i> {{ __('ui.services') }}</span>
            @endif

            <span class="sidebar-section-label">{{ __('ui.management') }}</span>

            @if($ctx)
                <a href="{{ route('office.requests.index', $ctx) }}"
                   class="sidebar-link {{ request()->routeIs('office.requests.*') ? 'active' : '' }}">
                    <i class="bi bi-inbox"></i> {{ __('ui.service_requests') }}
                </a>
                <a href="{{ route('office.chat.index', $ctx) }}"
                   class="sidebar-link {{ request()->routeIs('office.chat.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots"></i> {{ __('ui.chat') }}
                </a>
                <a href="{{ route('office.feedback.index', $ctx) }}"
                   class="sidebar-link {{ request()->routeIs('office.feedback.*') ? 'active' : '' }}">
                    <i class="bi bi-star"></i> {{ __('ui.feedback') }}
                </a>
                <a href="{{ route('office.slots.index', $ctx) }}"
                   class="sidebar-link {{ request()->routeIs('office.slots.*') ? 'active' : '' }}">
                    <i class="bi bi-clock"></i> Time Slots
                </a>
                <a href="{{ route('office.appointments.index', $ctx) }}"
                   class="sidebar-link {{ request()->routeIs('office.appointments.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> {{ __('ui.appointments') }}
                </a>
            @else
                <span class="sidebar-link disabled"><i class="bi bi-inbox"></i> {{ __('ui.service_requests') }}</span>
                <span class="sidebar-link disabled"><i class="bi bi-chat-dots"></i> {{ __('ui.chat') }}</span>
                <span class="sidebar-link disabled"><i class="bi bi-star"></i> {{ __('ui.feedback') }}</span>
                <span class="sidebar-link disabled"><i class="bi bi-clock"></i> Time Slots</span>
                <span class="sidebar-link disabled"><i class="bi bi-calendar-check"></i> {{ __('ui.appointments') }}</span>
            @endif

        </nav>

        {{-- User card --}}
        <div class="sidebar-user-wrap">
            <div class="user-popup" id="officeUserPopup">
                <a href="{{ route('office.password.change') }}" class="user-popup-item">
                    <i class="bi bi-key"></i> {{ __('ui.change_password') }}
                </a>
                <button type="button" class="user-popup-item danger"
                        data-bs-toggle="modal" data-bs-target="#officeLogoutModal">
                    <i class="bi bi-box-arrow-right"></i> {{ __('ui.log_out') }}
                </button>
            </div>
            <button class="sidebar-user-btn" onclick="toggleUserPopup(event)">
                <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">{{ __('ui.office_portal') }}</div>
                </div>
                <i class="bi bi-chevron-up sidebar-user-caret"></i>
            </button>
        </div>

    </aside>

    {{-- ── Topbar ── --}}
    <header class="office-topbar">
        <button class="topbar-hamburger" onclick="openSidebar()" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title">@yield('title', __('ui.office_portal'))</div>
        <div class="topbar-actions">
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
                'notificationIndexUrl'       => route('office.notifications.index'),
                'notificationFeedUrl'        => route('office.notifications.feed'),
                'notificationReadAllUrl'     => route('office.notifications.read-all'),
                'notificationReadOneBaseUrl' => url('/office/notifications'),
            ])
        </div>
    </header>

    {{-- ── Main ── --}}
    <main class="office-main">
        <div class="office-content">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')

        </div>
    </main>

    {{-- ════════════ LOGOUT MODAL ════════════ --}}
    <div class="modal fade" id="officeLogoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content border-0" style="border-radius:20px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.18);">
                <div class="modal-body text-center p-0">
                    <div style="padding:2rem 2rem 1.5rem;">
                        <div class="mx-auto mb-4 d-flex align-items-center justify-content-center"
                             style="width:64px;height:64px;border-radius:18px;background:rgba(239,68,68,.1);">
                            <i class="bi bi-box-arrow-right" style="font-size:1.6rem;color:#ef4444;"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="font-size:1.15rem;letter-spacing:-.02em;color:#0f172a;">{{ __('ui.log_out_title') }}</h5>
                        <p style="font-size:.875rem;color:#64748b;line-height:1.6;margin:0;">
                            {{ __('ui.log_out_confirm') }}
                        </p>
                    </div>
                    <div style="padding:0 1.5rem 1.75rem;display:grid;gap:.625rem;">
                        <button type="button"
                                onclick="document.getElementById('office-logout-form').submit()"
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
    <form method="POST" action="{{ route('logout') }}" class="d-none" id="office-logout-form">@csrf</form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openSidebar() {
            document.getElementById('officeSidebar').classList.add('open');
            document.getElementById('sidebarOverlay').classList.add('open');
        }
        function closeSidebar() {
            document.getElementById('officeSidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
        }
        function toggleUserPopup(e) {
            e.stopPropagation();
            document.getElementById('officeUserPopup').classList.toggle('open');
        }
        document.addEventListener('click', function () {
            document.getElementById('officeUserPopup').classList.remove('open');
        });
    </script>
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
