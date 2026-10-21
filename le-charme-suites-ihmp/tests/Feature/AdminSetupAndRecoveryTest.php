<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSetupAndRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_time_setup_creates_an_approved_administrator(): void
    {
        $response = $this->post(route('setup.store'), [
            'name' => 'Hotel Manager',
            'email' => 'Manager@LeCharmeHotel.com',
            'password' => 'first-admin-pass',
            'password_confirmation' => 'first-admin-pass',
        ]);

        $response->assertRedirect(route('admin.login'));
        $admin = User::where('email', 'manager@lecharmehotel.com')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertNotNull($admin->approved_at);
        $this->assertTrue(Hash::check('first-admin-pass', $admin->password));
    }

    public function test_setup_closes_once_an_administrator_exists(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->get(route('setup.create'))->assertNotFound();
        $this->post(route('setup.store'), [
            'name' => 'Intruder',
            'email' => 'intruder@lecharmehotel.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruder@lecharmehotel.com']);
    }

    public function test_login_page_links_to_setup_only_when_no_administrator_exists(): void
    {
        $this->get(route('login'))->assertOk()->assertSeeText('Set up the first administrator');
        $this->get(route('admin.login'))->assertOk()->assertSeeText('Set up the first administrator');

        User::factory()->create(['role' => 'admin']);

        $this->get(route('login'))->assertOk()->assertDontSeeText('Set up the first administrator');
        $this->get(route('admin.login'))->assertOk()->assertDontSeeText('Set up the first administrator');
    }

    public function test_setup_requires_a_work_email(): void
    {
        $response = $this->post(route('setup.store'), [
            'name' => 'Someone',
            'email' => 'someone@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_recovery_command_resets_an_administrator_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@lecharmehotel.com']);

        $this->artisan('ihmp:recover-admin', ['email' => 'admin@lecharmehotel.com'])
            ->expectsQuestion('New password (at least 8 characters)', 'recovered-pass-1')
            ->expectsQuestion('Confirm the new password', 'recovered-pass-1')
            ->expectsOutput('Password reset for administrator admin@lecharmehotel.com.')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('recovered-pass-1', $admin->fresh()->password));
    }

    public function test_recovery_command_creates_an_administrator_when_none_can_sign_in(): void
    {
        $this->artisan('ihmp:recover-admin', ['email' => 'it.support@lecharmehotel.com', '--name' => 'IT Support'])
            ->expectsQuestion('New password (at least 8 characters)', 'recovered-pass-2')
            ->expectsQuestion('Confirm the new password', 'recovered-pass-2')
            ->expectsOutput('Administrator it.support@lecharmehotel.com created.')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'it.support@lecharmehotel.com', 'role' => 'admin', 'name' => 'IT Support']);
    }

    public function test_recovery_command_rejects_mismatched_passwords(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@lecharmehotel.com']);
        $originalHash = $admin->password;

        $this->artisan('ihmp:recover-admin', ['email' => 'admin@lecharmehotel.com'])
            ->expectsQuestion('New password (at least 8 characters)', 'recovered-pass-1')
            ->expectsQuestion('Confirm the new password', 'something-else')
            ->assertFailed();

        $this->assertSame($originalHash, $admin->fresh()->password);
    }
}
