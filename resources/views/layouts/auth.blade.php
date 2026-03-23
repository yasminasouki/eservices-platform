<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Services Platform')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 0;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #1a237e, #1565c0);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .auth-header .logo-icon { font-size: 3rem; margin-bottom: 0.5rem; }
        .auth-body { padding: 2rem; }
        .btn-primary {
            background: linear-gradient(135deg, #1a237e, #1565c0);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        .btn-primary:hover { background: linear-gradient(135deg, #0d1b6e, #0d47a1); }
        .form-control:focus, .form-select:focus {
            border-color: #1565c0;
            box-shadow: 0 0 0 0.2rem rgba(21,101,192,0.25);
        }
        .form-label { font-weight: 500; color: #333; }
        .divider { display: flex; align-items: center; margin: 1.5rem 0; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #dee2e6; }
        .divider span { padding: 0 1rem; color: #6c757d; font-size: 0.875rem; }
        .btn-social {
            border: 1px solid #dee2e6;
            background: white;
            color: #333;
            font-weight: 500;
            padding: 0.6rem 1rem;
            transition: all 0.2s;
        }
        .btn-social:hover { background: #f8f9fa; border-color: #adb5bd; }
        .social-icon { width: 20px; height: 20px; margin-right: 0.5rem; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo-icon"><i class="bi bi-building-fill-gear"></i></div>
            <h4 class="mb-1 fw-bold">E-Services Platform</h4>
            <p class="mb-0 opacity-75 small">@yield('subtitle', 'Secure Government Services')</p>
        </div>
        <div class="auth-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
