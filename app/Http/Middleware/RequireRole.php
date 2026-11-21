<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:admin|pharmacist'). Uses the user resolved by auth:api.
 */
class RequireRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole(explode('|', $roles))) {
            abort(403, 'You do not have the required role.');
        }

        return $next($request);
    }
}
