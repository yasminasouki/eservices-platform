<?php

use App\Http\Middleware\EnsureOfficeStaffBelongsToOffice;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RequireTwoFactor;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Authenticated users hitting guest routes (e.g. /login) must not redirect to "/".
         * Laravel's default looks for a route named "dashboard" or "home"; this app uses
         * role-specific names only, so the fallback was "/" — same as the root redirect
         * to login — causing ERR_TOO_MANY_REDIRECTS until cookies were cleared.
         */
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if (! $user) {
                return route('login');
            }

            return match ($user->role) {
                'admin' => route('admin.dashboard'),
                'office_user' => route('office.dashboard'),
                'citizen' => route('citizen.dashboard'),
                default => route('login'),
            };
        });

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'active' => EnsureUserIsActive::class,
            '2fa' => RequireTwoFactor::class,
            'office.access' => EnsureOfficeStaffBelongsToOffice::class,
        ]);

        $middleware->validateCsrfTokens([
            'webhooks/stripe',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
