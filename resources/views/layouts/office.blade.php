<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Office Portal') — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: 700; letter-spacing: .4px; }
        .card-soft { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, .08); }
        .office-subnav .nav-pills .nav-link { border-radius: 8px; }
        .office-subnav .nav-pills .nav-link.active { background: linear-gradient(135deg, #1b5e20, #2e7d32); }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark px-3" style="background: linear-gradient(135deg, #1b5e20, #2e7d32);">
        <a class="navbar-brand text-decoration-none text-white" href="{{ route('office.dashboard') }}">
            <i class="bi bi-building-fill-gear me-2"></i>E-Services — Office
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white small d-none d-sm-inline">{{ auth()->user()->name }}</span>
            <span class="badge bg-light text-dark">Office Staff</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
