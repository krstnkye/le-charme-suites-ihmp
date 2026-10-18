<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIntegrationApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('integrations.api_key');

        if ($configuredKey === '') {
            return new JsonResponse([
                'message' => 'The IHMS integration API is disabled until IHMS_API_KEY is configured.',
            ], 503);
        }

        $providedKey = (string) ($request->bearerToken() ?: $request->header('X-IHMS-Key'));

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return new JsonResponse(['message' => 'Invalid integration credentials.'], 401);
        }

        return $next($request);
    }
}
