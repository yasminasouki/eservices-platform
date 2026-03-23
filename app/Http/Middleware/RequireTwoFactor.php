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

        // Social login users are exempt from 2FA
        if ($user->social_provider) {
            return $next($request);
        }

        // 2FA secret set but not yet confirmed → force setup completion
        if ($user->two_factor_secret && !$user->two_factor_confirmed_at) {
            if (!$request->routeIs('2fa.setup', '2fa.setup.confirm', 'logout')) {
                return redirect()->route('2fa.setup');
            }
        }

        // 2FA confirmed but not verified this session → force verification
        if ($user->two_factor_confirmed_at && !session('2fa_verified')) {
            if (!$request->routeIs('2fa.verify', '2fa.verify.confirm', 'logout')) {
                return redirect()->route('2fa.verify');
            }
        }

        return $next($request);
    }
}
