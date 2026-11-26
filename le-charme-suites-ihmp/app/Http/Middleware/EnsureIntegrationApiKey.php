<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIntegrationApiKey
{
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $configuredKey = (string) config("integrations.credentials.{$scope}");

        if ($configuredKey === '') {
            return new JsonResponse([
                'message' => "The IHMP {$scope} integration is disabled until its scoped credential is configured.",
            ], 503);
        }

        $providedKey = (string) ($request->bearerToken() ?: $request->header('X-IHMP-Key'));

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return new JsonResponse(['message' => 'Invalid integration credentials for this operation.'], 401);
        }

        $request->attributes->set('integration_scope', $scope);

        return $next($request);
    }
}
