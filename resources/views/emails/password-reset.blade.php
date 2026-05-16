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
        .btn { display: inline-block; background: #4c1d95; color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 10px; font-weight: 700; font-size: 1rem; margin: 8px 0 24px; }
        .expires { font-size: .8rem; color: #b4a0d4; margin-top: 4px; }
        .footer { font-size: .8rem; color: #b4a0d4; margin-top: 28px; border-top: 1px solid #f0ecff; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">E-Services Platform</div>
        <h2>Reset your password</h2>
        <p>Hello {{ $userName }}, click the button below to set a new password for your account.</p>

        <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
        <div class="expires">This link expires in 60 minutes.</div>

        <p style="margin-top: 24px;">If you did not request a password reset, you can ignore this email — your password will remain unchanged.</p>

        <div class="footer">E-Services Platform &mdash; Do not reply to this email.</div>
    </div>
</body>
</html>
