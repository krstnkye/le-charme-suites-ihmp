<?php

namespace App\Http\Controllers;

use App\Models\AuthActivityLog;
use App\Models\JwtToken;
use App\Models\User;
use App\Rules\WorkEmail;
use App\Services\JwtService;
use App\Services\PermissionService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    public function login(Request $request, JwtService $jwt): JsonResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'email' => ['required', 'email', new WorkEmail],
            'password' => ['required', 'string'],
        ]);
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->record($request, null, 'JWT_LOGIN_FAILED', false);

            return response()->json(['message' => 'The email or password is incorrect.'], 401);
        }

        $this->record($request, $user, 'JWT_LOGIN_SUCCESS', true);

        return response()->json([
            ...$jwt->issuePair($user),
            'user' => $this->userData($user),
        ]);
    }

    public function refresh(Request $request, JwtService $jwt): JsonResponse
    {
        $data = $request->validate(['refresh_token' => ['required', 'string']]);

        try {
            $refreshToken = $jwt->authenticate($data['refresh_token'], 'refresh');
        } catch (AuthenticationException) {
            return response()->json(['message' => 'The refresh token is invalid, expired, or revoked.'], 401);
        }

        $jwt->revoke($refreshToken);

        return response()->json([
            ...$jwt->issuePair($refreshToken->user),
            'user' => $this->userData($refreshToken->user),
        ]);
    }

    public function me(Request $request, PermissionService $permissions): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $this->userData($user),
            'permissions' => collect(config('rbac.capabilities'))
                ->keys()
                ->flatMap(fn (string $capability) => collect(config('rbac.actions'))
                    ->map(fn (string $action) => "{$capability}.{$action}"))
                ->filter(fn (string $permission) => $permissions->allows($user, $permission))
                ->values(),
        ]);
    }

    public function logout(Request $request, JwtService $jwt): JsonResponse
    {
        /** @var JwtToken $accessToken */
        $accessToken = $request->attributes->get('jwt_token');
        $jwt->revoke($accessToken);

        if ($request->filled('refresh_token')) {
            try {
                $refreshToken = $jwt->authenticate((string) $request->input('refresh_token'), 'refresh');

                if ($refreshToken->user_id === $request->user()->id) {
                    $jwt->revoke($refreshToken);
                }
            } catch (AuthenticationException) {
                // Access-token logout still succeeds when the optional refresh token is stale.
            }
        }

        $this->record($request, $request->user(), 'JWT_LOGOUT', true);

        return response()->json(['message' => 'Signed out successfully.']);
    }

    private function userData(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'role']);
    }

    private function record(Request $request, ?User $user, string $event, bool $successful): void
    {
        AuthActivityLog::create([
            'user_id' => $user?->id,
            'email' => $user?->email ?? $request->string('email')->toString(),
            'event_type' => $event,
            'successful' => $successful,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
            'details' => 'IHMP API authentication event.',
            'occurred_at' => now(),
        ]);
    }
}
