<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f7ff; margin: 0; padding: 40px 20px; }
        .card { background: #fff; border-radius: 16px; max-width: 480px; margin: 0 auto; padding: 40px 36px; box-shadow: 0 4px 24px rgba(124,58,237,.08); }
        .logo { font-size: 1.1rem; font-weight: 800; color: #4c1d95; margin-bottom: 28px; }
        h2 { font-size: 1.3rem; color: #1f1235; margin: 0 0 8px; }
        p { color: #6d5b8e; font-size: .95rem; line-height: 1.6; margin: 0 0 24px; }
        .code-box { background: #f3f0ff; border: 2px dashed #c4b5fd; border-radius: 12px; text-align: center; padding: 20px; margin: 24px 0; }
        .code { font-size: 2.8rem; font-weight: 900; letter-spacing: .3em; color: #4c1d95; font-family: monospace; }
        .expires { font-size: .8rem; color: #b4a0d4; margin-top: 8px; }
        .footer { font-size: .8rem; color: #b4a0d4; margin-top: 28px; border-top: 1px solid #f0ecff; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">E-Services Platform</div>
        <h2>Verify your email address</h2>
        <p>Hello {{ $userName }}, enter the code below on the verification page to activate your account.</p>

        <div class="code-box">
            <div class="code">{{ $code }}</div>
            <div class="expires">Valid for 24 hours</div>
        </div>

        <p>Open the app on your computer and enter this code when prompted. If you did not create an account, you can ignore this email.</p>

        <div class="footer">E-Services Platform &mdash; Do not reply to this email.</div>
    </div>
</body>
</html>
