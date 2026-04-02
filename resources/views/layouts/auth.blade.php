<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Services Platform')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --auth-accent: #1d4ed8;
            --auth-accent-deep: #1e3a8a;
            --auth-accent-soft: rgba(29, 78, 216, 0.12);
            --auth-strip-bg: linear-gradient(135deg, #e8edf7 0%, #dbeafe 42%, #f0f9ff 100%);
            --auth-strip-border: rgba(30, 58, 138, 0.14);
        }

        .auth-variant-admin {
            --auth-accent: #6366f1;
            --auth-accent-deep: #312e81;
            --auth-accent-soft: rgba(99, 102, 241, 0.15);
            --auth-strip-bg: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 45%, #f5f3ff 100%);
            --auth-strip-border: rgba(49, 46, 129, 0.16);
        }

        .auth-variant-municipality {
            --auth-accent: #0d9488;
            --auth-accent-deep: #115e59;
            --auth-accent-soft: rgba(13, 148, 136, 0.14);
            --auth-strip-bg: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 48%, #f0fdfa 100%);
            --auth-strip-border: rgba(17, 94, 89, 0.14);
        }

        * { box-sizing: border-box; }

        body.auth-page {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem 2rem;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: #0f172a;
            background-color: #eef2f7;
            background-image: linear-gradient(
                165deg,
                #dfe7f2 0%,
                #f0f4f8 38%,
                #e9eff7 72%,
                #f3f6fa 100%
            );
        }

        .auth-single {
            width: 100%;
            max-width: 440px;
        }

        .auth-form-card {
            background: #fff;
            border-radius: 1.25rem;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.04),
                0 24px 48px -12px rgba(15, 23, 42, 0.12);
            padding: 0;
            overflow: hidden;
        }

        /* Slim top bar: lighter than the old full blue header, more character than a plain card */
        .auth-card-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.7rem 1.25rem;
            background: var(--auth-strip-bg);
            border-bottom: 1px solid var(--auth-strip-border);
        }
        .auth-card-strip i {
            font-size: 1.1rem;
            color: var(--auth-accent);
            line-height: 1;
        }
        .auth-card-strip .auth-strip-title {
            font-weight: 700;
            font-size: 0.9375rem;
            letter-spacing: -0.02em;
            color: var(--auth-accent-deep);
        }

        .auth-card-body {
            padding: 1.5rem 1.75rem 1.35rem;
        }
        @media (min-width: 576px) {
            .auth-card-body { padding: 1.65rem 2rem 1.5rem; }
        }

        .auth-form-header {
            margin-bottom: 1.5rem;
        }
        .auth-form-header .heading-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 0.75rem;
            margin-bottom: 0.35rem;
        }
        .auth-form-box h2 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            margin: 0;
        }
        .auth-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            background: var(--auth-accent-soft);
            color: var(--auth-accent-deep);
        }
        .auth-form-box .subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
            line-height: 1.5;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.8125rem;
            margin-bottom: 0.4rem;
        }
        .auth-page .input-group {
            border-radius: 0.65rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .auth-page .input-group-text {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-right: none;
            color: var(--auth-accent);
            padding-left: 0.9rem;
            padding-right: 0.75rem;
        }
        .auth-page .form-control {
            border: 1px solid #e2e8f0;
            border-left: none;
            background: #fff;
            font-size: 0.9375rem;
            padding: 0.65rem 0.85rem;
        }
        .auth-page .input-group:focus-within .input-group-text {
            border-color: var(--auth-accent);
            background: #fff;
        }
        .auth-page .input-group:focus-within .form-control {
            border-color: var(--auth-accent);
            box-shadow: none;
        }
        .auth-page .input-group .form-control:focus {
            box-shadow: none;
        }
        .auth-page .input-group .btn-outline-secondary {
            border-color: #e2e8f0;
            border-left: none;
            background: #fff;
            color: #64748b;
        }
        .auth-page .input-group .btn-outline-secondary:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #e2e8f0;
        }
        .auth-page .input-group:focus-within .btn-outline-secondary {
            border-color: var(--auth-accent);
        }

        .auth-page .form-check-input:checked {
            background-color: var(--auth-accent);
            border-color: var(--auth-accent);
        }
        .auth-page .form-check-label { color: #475569; }

        .auth-page .btn-primary {
            background: linear-gradient(135deg, var(--auth-accent-deep), var(--auth-accent));
            border: none;
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            font-size: 0.9375rem;
            letter-spacing: 0.01em;
            border-radius: 0.65rem;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.15);
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
        .auth-page .btn-primary:hover {
            filter: brightness(1.05);
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.18);
            transform: translateY(-1px);
        }
        .auth-page .btn-primary:active { transform: translateY(0); }

        @media (prefers-reduced-motion: reduce) {
            .auth-page .btn-primary {
                transition: none;
            }
            .auth-page .btn-primary:hover { transform: none; }
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.35rem 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0 20%, #e2e8f0 80%, transparent);
        }
        .divider span {
            padding: 0 1rem;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .btn-social {
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.62rem 1rem;
            border-radius: 0.65rem;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }
        .btn-social:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }
        .social-icon { width: 18px; height: 18px; margin-right: 0.5rem; vertical-align: middle; }

        .auth-page .alert {
            border: none;
            border-radius: 0.65rem;
            font-size: 0.875rem;
        }
        .auth-page .alert-success {
            background: #ecfdf5;
            color: #065f46;
        }
        .auth-page .alert-info {
            background: #eff6ff;
            color: #1e40af;
        }
        .auth-page .alert-danger {
            background: #fef2f2;
            color: #991b1b;
        }

        .auth-footer-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .auth-footer-note i { font-size: 0.85rem; color: #22c55e; }

        .auth-page a.text-primary,
        .auth-page a.fw-semibold.text-primary {
            color: var(--auth-accent) !important;
            font-weight: 600;
        }
        .auth-page a.text-primary:hover,
        .auth-page a.fw-semibold.text-primary:hover {
            color: var(--auth-accent-deep) !important;
        }

        .auth-links-stack p {
            line-height: 1.6;
        }
    </style>
    @stack('auth-styles')
</head>
<body class="auth-page @yield('auth_body_class')">

    <div class="auth-single">
        <div class="auth-form-card auth-form-box">
            <div class="auth-card-strip">
                <i class="bi @yield('auth_brand_icon', 'bi-building-fill-gear')" aria-hidden="true"></i>
                <span class="auth-strip-title">@yield('auth_brand_title', 'E-Services Platform')</span>
            </div>

            <div class="auth-card-body">
                <div class="auth-form-header">
                    <div class="heading-row">
                        <h2>@yield('heading', 'Welcome back')</h2>
                        @hasSection('heading_badge')
                            <span class="auth-badge">@yield('heading_badge')</span>
                        @endif
                    </div>
                    <p class="subtitle">@yield('subtitle', 'Sign in to continue')</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')

                <div class="auth-footer-note">
                    <i class="bi bi-lock-fill"></i>
                    <span>Encrypted connection. Never share your password.</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
