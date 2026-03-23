<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); transition: transform .2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .id-badge { font-size: .75rem; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark px-3" style="background: linear-gradient(135deg, #4a148c, #6a1b9a);">
        <span class="navbar-brand fw-bold">
            <i class="bi bi-building-fill-gear me-2"></i>E-Services Platform
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white small">{{ auth()->user()->name }}</span>
            <span class="badge bg-light text-dark">Citizen</span>
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

        {{-- ID verification banner --}}
        @if(auth()->user()->id_document_status === 'pending')
            <div class="alert alert-warning d-flex align-items-center mb-4">
                <i class="bi bi-clock-history fs-5 me-3"></i>
                <div>
                    <strong>Identity Verification Pending</strong><br>
                    <span class="small">Your national ID is being reviewed. Some features may be limited until verification is complete.</span>
                </div>
            </div>
        @endif

        <h2 class="fw-bold mb-1">Welcome, {{ auth()->user()->name }}</h2>
        <p class="text-muted mb-4">Browse government services and track your requests.</p>

        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-file-earmark-plus fs-1 text-primary mb-2"></i>
                    <div class="text-muted small">Active Requests</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-check2-all fs-1 text-success mb-2"></i>
                    <div class="text-muted small">Completed</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-calendar-event fs-1 text-info mb-2"></i>
                    <div class="text-muted small">Upcoming Appointments</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card p-4 text-center">
                    <i class="bi bi-wallet2 fs-1 text-warning mb-2"></i>
                    <div class="text-muted small">Total Paid</div>
                    <div class="fs-3 fw-bold">—</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white fw-semibold border-0 pt-3">
                        <i class="bi bi-search me-2 text-primary"></i>Browse Services
                    </div>
                    <div class="card-body text-muted small">Service browsing module coming soon.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white fw-semibold border-0 pt-3">
                        <i class="bi bi-clock-history me-2 text-secondary"></i>My Request History
                    </div>
                    <div class="card-body text-muted small">Request history module coming soon.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
