<?php

namespace Tests\Feature;

use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.secret' => str_repeat('test-jwt-secret-', 4),
            'jwt.issuer' => 'http://ihmp.test',
            'jwt.audience' => 'ihmp-test-api',
            'jwt.access_ttl' => 15,
            'jwt.refresh_ttl' => 60,
        ]);
    }

    public function test_staff_can_receive_and_use_a_signed_access_token(): void
    {
        $user = User::factory()->create([
            'email' => 'frontdesk@lecharmehotel.com',
            'password' => 'secure-password',
            'role' => 'receptionist',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.role', 'receptionist');

        $accessToken = $login->json('access_token');

        $this->withToken($accessToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonFragment(['reservations.read']);
    }

    public function test_refresh_tokens_rotate_and_cannot_be_reused(): void
    {
        $user = User::factory()->create([
            'email' => 'cashier@lecharmehotel.com',
            'password' => 'secure-password',
            'role' => 'cashier',
        ]);

        $refreshToken = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->json('refresh_token');

        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $refreshToken])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'refresh_token']);

        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $refreshToken])
            ->assertUnauthorized();
    }

    public function test_logout_revokes_the_access_and_refresh_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'housekeeping@lecharmehotel.com',
            'password' => 'secure-password',
            'role' => 'housekeeping',
        ]);

        $tokens = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->json();

        $this->withToken($tokens['access_token'])
            ->postJson('/api/v1/auth/logout', ['refresh_token' => $tokens['refresh_token']])
            ->assertOk();

        $this->withToken($tokens['access_token'])->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $tokens['refresh_token']])->assertUnauthorized();
        $this->assertSame(2, JwtToken::whereNotNull('revoked_at')->count());
    }

    public function test_tampered_and_missing_tokens_are_rejected(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->withToken('not.a.valid-token')->getJson('/api/v1/auth/me')->assertUnauthorized();

        $user = User::factory()->create([
            'email' => 'tamper-test@lecharmehotel.com',
            'password' => 'secure-password',
        ]);
        $accessToken = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->json('access_token');

        $this->withToken($accessToken.'changed')->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_expired_access_tokens_are_rejected(): void
    {
        config(['jwt.access_ttl' => -1]);
        $user = User::factory()->create([
            'email' => 'expired-test@lecharmehotel.com',
            'password' => 'secure-password',
        ]);
        $accessToken = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->json('access_token');

        $this->withToken($accessToken)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_jwt_staff_routes_also_enforce_role_permissions(): void
    {
        $receptionist = User::factory()->create([
            'email' => 'room-reader@lecharmehotel.com',
            'password' => 'secure-password',
            'role' => 'receptionist',
        ]);
        $cashier = User::factory()->create([
            'email' => 'no-room-access@lecharmehotel.com',
            'password' => 'secure-password',
            'role' => 'cashier',
        ]);

        $receptionistToken = $this->postJson('/api/v1/auth/login', [
            'email' => $receptionist->email,
            'password' => 'secure-password',
        ])->json('access_token');
        $cashierToken = $this->postJson('/api/v1/auth/login', [
            'email' => $cashier->email,
            'password' => 'secure-password',
        ])->json('access_token');

        $this->withToken($receptionistToken)->getJson('/api/v1/staff/rooms')->assertOk();
        $this->withToken($cashierToken)->getJson('/api/v1/staff/rooms')->assertForbidden();
    }
}
