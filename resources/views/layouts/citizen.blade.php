<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Citizen') — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --accent:       #4c1d95;
            --accent-mid:   #6d28d9;
            --accent-light: #a78bfa;
            --accent-subtle: rgba(109, 40, 217, 0.1);
        }
        body { background: #f0f4f8; }
        .navbar-brand { font-weight: 700; letter-spacing: .4px; }
        .navbar { background: linear-gradient(135deg, var(--accent), var(--accent-mid)) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .card-soft { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.07); transition: transform .2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent-mid)); border: none; font-weight: 600; }
        .btn-primary:hover, .btn-primary:focus { background: linear-gradient(135deg, #2e1065, #5b21b6); border: none; }
        .form-control:focus, .form-select:focus { border-color: var(--accent-mid); box-shadow: 0 0 0 0.2rem var(--accent-subtle); }
    </style>
    @stack('styles')
    @stack('head')
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark px-3" style="background: linear-gradient(135deg, #4a148c, #6a1b9a);">
        <a class="navbar-brand text-decoration-none text-white" href="{{ route('citizen.dashboard') }}">
            <i class="bi bi-building-fill-gear me-2"></i>E-Services Platform
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a href="{{ route('citizen.offices.index') }}"
               class="d-none d-sm-flex align-items-center gap-1 text-white text-decoration-none px-3 py-1 rounded-pill"
               style="background:rgba(255,255,255,0.15);font-size:0.85rem;"
               onmouseover="this.style.background='rgba(255,255,255,0.25)'"
               onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="bi bi-grid"></i> Browse services
            </a>
            <a href="{{ route('citizen.requests.index') }}"
               class="d-none d-sm-flex align-items-center gap-1 text-white text-decoration-none px-3 py-1 rounded-pill"
               style="background:rgba(255,255,255,0.15);font-size:0.85rem;"
               onmouseover="this.style.background='rgba(255,255,255,0.25)'"
               onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="bi bi-folder2-open"></i> My requests
            </a>
            <a href="{{ route('citizen.offices.index') }}"
               class="d-none d-md-flex align-items-center gap-1 text-white text-decoration-none px-3 py-1 rounded-pill"
               style="background:rgba(255,255,255,0.15);font-size:0.85rem;"
               title="Pick an office, then open Live chat"
               onmouseover="this.style.background='rgba(255,255,255,0.25)'"
               onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="bi bi-chat-dots"></i> Message an office
            </a>
            <div class="dropdown d-none d-sm-flex">
                <button class="btn btn-sm d-flex align-items-center gap-2 text-white border-0 dropdown-toggle"
                        style="background:rgba(255,255,255,0.15);border-radius:8px;padding:6px 12px;"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25 text-white fw-bold"
                         style="width:28px;height:28px;font-size:0.8rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span style="font-size:0.85rem;">{{ auth()->user()->name }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow p-0" style="min-width:280px;">
                    <div class="px-3 py-3 border-bottom bg-light rounded-top">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <div class="small text-muted">{{ auth()->user()->email }}</div>
                        @php
                            $idStatus = auth()->user()->id_document_status ?? 'not uploaded';
                            $idBadge = match($idStatus) {
                                'verified' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'pending'  => 'bg-warning text-dark',
                                default    => 'bg-secondary',
                            };
                        @endphp
                        <div class="mt-1">
                            <span class="badge {{ $idBadge }} small">
                                ID: {{ ucfirst($idStatus) }}
                            </span>
                        </div>
                    </div>
                    @php
                        $verification = \App\Models\IdVerificationRequest::where('user_id', auth()->id())
                            ->latest()->first();
                    @endphp
                    @if($verification)
                        <div class="px-3 py-2 small border-bottom">
                            <div class="text-muted mb-1 fw-semibold">ID Information</div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Name</span>
                                <span>{{ $verification->extracted_name ?? '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">DOB</span>
                                <span>{{ $verification->extracted_dob?->format('Y-m-d') ?? '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">ID Number</span>
                                <span>{{ $verification->extracted_id_number ?? '—' }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="px-3 py-2">
                        <a href="{{ route('citizen.id.verify') }}" class="dropdown-item rounded px-2 py-1 small">
                            <i class="bi bi-person-vcard me-2"></i>
                            {{ $verification ? 'View ID verification' : 'Upload your ID' }}
                        </a>
                    </div>
                </div>
            </div>
            @include('partials.notification-bell', [
                'notificationIndexUrl' => route('citizen.notifications.index'),
                'notificationReadAllUrl' => route('citizen.notifications.read-all'),
                'notificationReadOneBaseUrl' => url('/citizen/notifications'),
            ])
            <form method="POST" action="{{ route('logout') }}" class="mb-0" id="logout-form">
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

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
