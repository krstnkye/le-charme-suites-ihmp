<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $allowedRoles = array_filter(func_get_args(), fn ($argument) => is_string($argument));

        if (! $user) {
            abort(403);
        }

        if ($user->role !== 'admin' && ! in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Your account role does not have access to this module.');
        }

        return $next($request);
    }
}
