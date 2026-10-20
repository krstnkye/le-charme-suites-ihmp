<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_account_cannot_sign_in_to_the_portal(): void
    {
        $pendingUser = User::factory()->pendingApproval()->create(['role' => 'receptionist']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $pendingUser->email,
            'role' => 'receptionist',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email' => 'Your account is still awaiting approval from a system administrator.']);
        $this->assertGuest();
    }

    public function test_pending_account_cannot_receive_an_api_token(): void
    {
        config(['jwt.secret' => str_repeat('test-jwt-secret-', 4)]);
        $pendingUser = User::factory()->pendingApproval()->create(['role' => 'cashier']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $pendingUser->email,
            'password' => 'password',
        ]);

        $response->assertForbidden();
        $response->assertJsonMissingPath('access_token');
        $this->assertDatabaseCount('jwt_tokens', 0);
    }

    public function test_admin_approval_lets_the_account_sign_in(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingUser = User::factory()->pendingApproval()->create(['role' => 'housekeeping']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeText('Pending Account Approvals')
            ->assertSeeText($pendingUser->email);
        $this->actingAs($admin)
            ->post(route('admin.users.approve', $pendingUser))
            ->assertRedirect(route('admin.users.index'));
        $this->post(route('logout'));

        $this->assertNotNull($pendingUser->fresh()->approved_at);
        $this->post(route('login.store'), [
            'email' => $pendingUser->email,
            'role' => 'housekeeping',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($pendingUser);
    }

    public function test_only_admins_can_approve_accounts(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $pendingUser = User::factory()->pendingApproval()->create(['role' => 'receptionist']);

        $response = $this->actingAs($receptionist)->post(route('admin.users.approve', $pendingUser));

        $response->assertForbidden();
        $this->assertNull($pendingUser->fresh()->approved_at);
    }

    public function test_accounts_created_by_an_admin_are_approved_immediately(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Cashier',
            'email' => 'new.cashier@lecharmehotel.com',
            'role' => 'cashier',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertNotNull(User::where('email', 'new.cashier@lecharmehotel.com')->value('approved_at'));
    }
}
