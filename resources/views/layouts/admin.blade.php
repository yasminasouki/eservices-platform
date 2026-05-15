<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') — E-Services Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* ── Variables ── */
        :root {
            --sidebar-width:   240px;
            --topbar-height:   60px;
            --sidebar-bg:      #f0f9ff;
            --sidebar-border:  #e0f2fe;
            --accent:          #0284c7;
            --accent-mid:      #0ea5e9;
            --accent-light:    #bae6fd;
            --accent-pale:     #e0f2fe;
            --accent-dark:     #0c4a6e;
            --body-bg:         #f5f9ff;
        }

        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; background: var(--body-bg); font-family: system-ui, -apple-system, sans-serif; }

        /* ── Sidebar (light) ── */
        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex; flex-direction: column;
            z-index: 1040; transition: transform .25s ease;
        }

        /* brand */
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

        /* nav */
        .sidebar-nav {
            flex: 1; overflow-y: auto; padding: 1rem .75rem;
            display: flex; flex-direction: column; gap: .15rem;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--accent-light); border-radius: 4px; }

        .sidebar-section-label {
            font-size: .62rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .09em; color: #93c5fd;
            padding: .7rem .65rem .2rem; margin-top: .3rem;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: .7rem;
            padding: .52rem .75rem; border-radius: 8px;
            color: #64748b; font-size: .875rem; font-weight: 500;
            text-decoration: none; transition: background .12s, color .12s;
        }
        .sidebar-link i { font-size: .95rem; width: 20px; text-align: center; flex-shrink: 0; }
        .sidebar-link:hover { background: var(--accent-pale); color: var(--accent-dark); }
        .sidebar-link.active {
            background: linear-gradient(135deg, var(--accent), var(--accent-mid));
            color: #fff; font-weight: 700;
            box-shadow: 0 4px 12px rgba(2,132,199,.22);
        }
        .sidebar-link .link-badge {
            margin-left: auto; font-size: .62rem; font-weight: 800;
            background: var(--accent-light); color: var(--accent-dark);
            padding: .1rem .45rem; border-radius: 999px;
        }

        /* user card */
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
        .sidebar-user-name  { font-size: .83rem; font-weight: 700; color: var(--accent-dark); line-height: 1.2; }
        .sidebar-user-role  { font-size: .7rem; color: #7dd3fc; }
        .sidebar-user-caret { margin-left: auto; color: #93c5fd; font-size: .8rem; }

        /* user popup — light themed */
        .user-popup {
            position: absolute; bottom: calc(100% + 4px); left: .85rem; right: .85rem;
            background: #fff; border: 1px solid var(--sidebar-border);
            border-radius: 12px; box-shadow: 0 8px 24px rgba(2,132,199,.14);
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
        .user-popup hr { margin: .3rem 0; border-color: var(--sidebar-border); }

        /* ── Topbar ── */
        .admin-topbar {
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
        .admin-main { margin-left: var(--sidebar-width); padding-top: var(--topbar-height); min-height: 100vh; }
        .admin-content { padding: 1.75rem; }

        /* ── Mobile overlay ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(12,74,110,.35); z-index: 1039; backdrop-filter: blur(2px);
        }
        .sidebar-overlay.open { display: block; }

        @media (max-width: 991px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .admin-topbar { left: 0; }
            .topbar-hamburger { display: flex; }
        }

        /* ── Notification bell ── */
        #notif-dropdown .notif-bell-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--accent-pale); border: 1px solid var(--accent-light);
            display: flex; align-items: center; justify-content: center;
            color: var(--accent); font-size: 1.05rem;
            cursor: pointer; position: relative; transition: background .15s;
        }
        #notif-dropdown .notif-bell-btn:hover { background: #bae6fd; }

        /* ── Global overrides ── */
        .card-soft { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(2,132,199,.07); }
        .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent-mid)); border: none; color: #fff; font-weight: 600; }
        .btn-primary:hover, .btn-primary:focus { background: linear-gradient(135deg, #0369a1, var(--accent)); border: none; color: #fff; }
        .form-control:focus, .form-select:focus { border-color: var(--accent-mid); box-shadow: 0 0 0 .2rem rgba(14,165,233,.18); }
        .alert { border-radius: 10px; }
    </style>
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ── Sidebar ── --}}
    <aside class="admin-sidebar" id="adminSidebar">

        <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
            <span class="brand-icon"><i class="bi bi-building-fill-gear"></i></span>
            E-Services
        </a>

        <nav class="sidebar-nav">

            <span class="sidebar-section-label">Overview</span>

            @php
                $navItems = [
                    ['route' => 'admin.dashboard',            'label' => 'Dashboard',          'icon' => 'speedometer2',  'match' => 'admin.dashboard'],
                    ['route' => 'admin.reports.index',        'label' => 'Reports',             'icon' => 'graph-up-arrow','match' => 'admin.reports.*'],
                ];
                $manageItems = [
                    ['route' => 'admin.municipalities.index', 'label' => 'Municipalities',      'icon' => 'geo-alt',       'match' => 'admin.municipalities.*'],
                    ['route' => 'admin.offices.index',        'label' => 'Offices',             'icon' => 'building',      'match' => 'admin.offices.*'],
                    ['route' => 'admin.office-users.index',   'label' => 'Municipality users',  'icon' => 'person-gear',   'match' => 'admin.office-users.*'],
                    ['route' => 'admin.citizens.index',       'label' => 'Citizens',            'icon' => 'people',        'match' => 'admin.citizens.*'],
                    ['route' => 'admin.service-requests.index','label'=> 'Service operations',  'icon' => 'clipboard-data','match' => 'admin.service-requests.*'],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="sidebar-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                    <i class="bi bi-{{ $item['icon'] }}"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <span class="sidebar-section-label">Manage</span>

            @foreach($manageItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="sidebar-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                    <i class="bi bi-{{ $item['icon'] }}"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach

        </nav>

        {{-- User card --}}
        <div class="sidebar-user-wrap">
            <div class="user-popup" id="adminUserPopup">
                <form method="POST" action="{{ route('logout') }}" id="admin-logout-form">@csrf</form>
                <button type="button" class="user-popup-item danger"
                        onclick="document.getElementById('admin-logout-form').submit()">
                    <i class="bi bi-box-arrow-right"></i> Log out
                </button>
            </div>
            <button class="sidebar-user-btn" onclick="toggleUserPopup(event)">
                <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">Administrator</div>
                </div>
                <i class="bi bi-chevron-up sidebar-user-caret"></i>
            </button>
        </div>
    </aside>

    {{-- ── Topbar ── --}}
    <header class="admin-topbar">
        <button class="topbar-hamburger" onclick="openSidebar()" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title">@yield('title', 'Admin Panel')</div>
        <div class="topbar-actions">
            @include('partials.notification-bell', [
                'notificationIndexUrl'      => route('admin.notifications.index'),
                'notificationReadAllUrl'    => route('admin.notifications.read-all'),
                'notificationReadOneBaseUrl'=> url('/admin/notifications'),
            ])
        </div>
    </header>

    {{-- ── Main ── --}}
    <main class="admin-main">
        <div class="admin-content">

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
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openSidebar() {
            document.getElementById('adminSidebar').classList.add('open');
            document.getElementById('sidebarOverlay').classList.add('open');
        }
        function closeSidebar() {
            document.getElementById('adminSidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
        }
        function toggleUserPopup(e) {
            e.stopPropagation();
            document.getElementById('adminUserPopup').classList.toggle('open');
        }
        document.addEventListener('click', () => {
            document.getElementById('adminUserPopup')?.classList.remove('open');
        });
    </script>
    @stack('scripts')
</body>
</html>
