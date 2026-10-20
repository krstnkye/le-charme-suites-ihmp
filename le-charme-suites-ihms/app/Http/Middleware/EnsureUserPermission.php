<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserPermission
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string ...$required): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Authentication is required.');
        }

        foreach ($required as $permission) {
            if ($this->permissions->allows($user, $permission)) {
                return $next($request);
            }
        }

        abort(403, 'Your role does not have permission to perform this action.');
    }
}
