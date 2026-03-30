<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Citizen') — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: 700; letter-spacing: .4px; }
        .card-soft { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, .08); }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, .08); transition: transform .2s; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark px-3" style="background: linear-gradient(135deg, #4a148c, #6a1b9a);">
        <a class="navbar-brand text-decoration-none text-white" href="{{ route('citizen.dashboard') }}">
            <i class="bi bi-building-fill-gear me-2"></i>E-Services Platform
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a href="{{ route('citizen.offices.index') }}" class="btn btn-outline-light btn-sm d-none d-sm-inline-block">Browse services</a>
            <a href="{{ route('citizen.requests.index') }}" class="btn btn-outline-light btn-sm d-none d-sm-inline-block">My requests</a>
            <span class="text-white small d-none d-md-inline">{{ auth()->user()->name }}</span>
            <span class="badge bg-light text-dark">Citizen</span>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
