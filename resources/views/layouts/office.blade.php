<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Office Portal') — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --accent:       #14532d;
            --accent-mid:   #15803d;
            --accent-light: #22c55e;
            --accent-subtle: rgba(21, 128, 61, 0.1);
        }
        body { background: #f0f4f8; }
        .navbar-brand { font-weight: 700; letter-spacing: .4px; }
        .navbar { background: linear-gradient(135deg, var(--accent), var(--accent-mid)) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .card-soft { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
        .office-subnav .nav-pills .nav-link { border-radius: 8px; font-size: 0.875rem; color: #374151; transition: background .15s, color .15s; }
        .office-subnav .nav-pills .nav-link:hover:not(.active) { background: var(--accent-subtle); color: var(--accent); }
        .office-subnav .nav-pills .nav-link.active { background: linear-gradient(135deg, var(--accent), var(--accent-mid)); color: #fff; }
        .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent-mid)); border: none; font-weight: 600; }
        .btn-primary:hover, .btn-primary:focus { background: linear-gradient(135deg, #052e16, #166534); border: none; }
        .form-control:focus, .form-select:focus { border-color: var(--accent-mid); box-shadow: 0 0 0 0.2rem var(--accent-subtle); }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark px-3" style="background: linear-gradient(135deg, #1b5e20, #2e7d32);">
        <a class="navbar-brand text-decoration-none text-white" href="{{ route('office.dashboard') }}">
            <i class="bi bi-building-fill-gear me-2"></i>E-Services — Office
        </a>
        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
            @if(isset($officeNavOffices) && $officeNavOffices->count() > 1)
                <form method="POST" action="{{ route('office.context') }}" class="d-flex align-items-center gap-2">
                    @csrf
                    <label class="text-white-50 small mb-0 d-none d-md-inline">Office</label>
                    <select name="office_id" class="form-select form-select-sm" style="width:auto;min-width:10rem;" onchange="this.form.submit()">
                        @foreach($officeNavOffices as $o)
                            <option value="{{ $o->id }}" @selected($officeContext && (int) $officeContext->id === (int) $o->id)>
                                {{ $o->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            <div class="d-none d-sm-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25 text-white fw-bold"
                     style="width:34px;height:34px;font-size:0.85rem;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="lh-sm">
                    <div class="text-white fw-semibold" style="font-size:0.85rem;">{{ auth()->user()->name }}</div>
                    <div class="text-white-50" style="font-size:0.7rem;">Office Staff</div>
                </div>
            </div>
            @include('partials.notification-bell')
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <button type="button" class="btn btn-sm d-flex align-items-center gap-1 text-white border-0"
                        style="background:rgba(255,255,255,0.15);border-radius:8px;padding:6px 12px;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.15)'"
                        data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-md-inline">Logout</span>
                </button>
            </form>
        </div>
    </nav>

    @include('partials.office-subnav')

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
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

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center px-4 pt-4 pb-3">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width:56px;height:56px;">
                            <i class="bi bi-box-arrow-right text-danger fs-4"></i>
                        </span>
                    </div>
                    <h6 class="fw-bold mb-1">Log out?</h6>
                    <p class="text-muted small mb-4">Are you sure you want to log out of your account?</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit()">Yes, log me out</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
