<?php

namespace App\Http\Middleware;

use App\Models\GovernmentOffice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficeStaffBelongsToOffice
{
    public function handle(Request $request, Closure $next): Response
    {
        $office = $request->route('office');

        if ($office instanceof GovernmentOffice) {
            $allowed = $request->user()->governmentOffices()->whereKey($office->getKey())->exists();

            if (! $allowed) {
                abort(403, 'You are not assigned to this office.');
            }
        }

        return $next($request);
    }
}
