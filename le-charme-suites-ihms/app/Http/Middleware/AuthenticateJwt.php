<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateJwt
{
    public function __construct(private readonly JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $encoded = (string) $request->bearerToken();

        if ($encoded === '') {
            return new JsonResponse(['message' => 'A bearer access token is required.'], 401);
        }

        try {
            $token = $this->jwt->authenticate($encoded);
        } catch (AuthenticationException) {
            return new JsonResponse(['message' => 'The access token is invalid, expired, or revoked.'], 401);
        }

        $token->update(['last_used_at' => now()]);
        Auth::setUser($token->user);
        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('jwt_token', $token);

        return $next($request);
    }
}
