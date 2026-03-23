<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); transition: transform .2s; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark px-3" style="background: linear-gradient(135deg, #1a237e, #1565c0);">
        <span class="navbar-brand">
            <i class="bi bi-building-fill-gear me-2"></i>E-Services Platform
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white small">{{ auth()->user()->name }}</span>
            <span class="badge bg-light text-dark">Admin</span>
            <form method="POST" action="{{ route('logout') }}">
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

        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
        <p class="text-muted mb-4">Welcome back, {{ auth()->user()->name }}. Here's your system overview.</p>

        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-building fs-1 text-primary mb-2"></i>
                    <div class="text-muted small">Government Offices</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-people fs-1 text-success mb-2"></i>
                    <div class="text-muted small">Registered Citizens</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-file-earmark-text fs-1 text-warning mb-2"></i>
                    <div class="text-muted small">Total Requests</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-cash-stack fs-1 text-info mb-2"></i>
                    <div class="text-muted small">Total Revenue</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white fw-semibold border-0 pt-3">
                        <i class="bi bi-building me-2 text-primary"></i>Manage Offices
                    </div>
                    <div class="card-body text-muted small">Office management module coming soon.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white fw-semibold border-0 pt-3">
                        <i class="bi bi-person-gear me-2 text-success"></i>Manage Users
                    </div>
                    <div class="card-body text-muted small">User management module coming soon.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
