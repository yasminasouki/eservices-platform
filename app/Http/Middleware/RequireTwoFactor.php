<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Social login users are exempt from 2FA (brief: TOTP for email/password accounts)
        if ($user->social_provider) {
            return $next($request);
        }

        // Every email/password user must finish TOTP enrollment before protected areas (admin, office, citizen).
        if (!$user->two_factor_confirmed_at) {
            if (!$request->routeIs('2fa.setup', '2fa.setup.confirm', 'logout')) {
                return redirect()->route('2fa.setup');
            }

            return $next($request);
        }

        // After login, require an OTP check each session
        if (!session('2fa_verified')) {
            if (!$request->routeIs('2fa.verify', '2fa.verify.confirm', 'logout')) {
                return redirect()->route('2fa.verify');
            }
        }

        return $next($request);
    }
}
