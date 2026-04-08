<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        @page { margin: 48px 56px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.45;
        }
        .header {
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 17px;
            margin: 0 0 4px 0;
            color: #1e3a5f;
        }
        .meta { font-size: 10px; color: #444; }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 18px 0 8px 0;
            color: #1e3a5f;
        }
        table.facts { width: 100%; border-collapse: collapse; }
        table.facts td { padding: 6px 8px; border: 1px solid #ddd; vertical-align: top; }
        table.facts td.label { width: 32%; background: #f5f7fa; font-weight: bold; color: #333; }
        .footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #ccc;
            font-size: 9px;
            color: #666;
        }
        .seal {
            margin-top: 24px;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.name') }}</h1>
    <div class="meta">Government e-services · Official document · Issued {{ $issuedAt->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</div>
</div>

@yield('content')

<div class="footer">
    This document was generated electronically. Reference: {{ $request->qr_code ?? '—' }} · Request #{{ $request->id ?? '—' }}<br>
    For verification or support, contact the issuing office with your reference code.
</div>
</body>
</html>
