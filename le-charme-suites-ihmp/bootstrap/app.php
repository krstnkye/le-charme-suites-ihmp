<?php

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\EnsureIntegrationApiKey;
use App\Http\Middleware\EnsureUserPermission;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        // Trust forwarded headers only from a proxy/tunnel on this machine (e.g. cloudflared),
        // so the app builds https links when served through a public tunnel.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('login'));

        $middleware->alias([
            'role' => EnsureUserRole::class,
            'permission' => EnsureUserPermission::class,
            'jwt.auth' => AuthenticateJwt::class,
            'integration.key' => EnsureIntegrationApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
