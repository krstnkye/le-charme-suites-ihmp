<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkEmailAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_and_registration_exclude_admin(): void
    {
        $expectedOrder = ['Receptionist', 'Billing', 'Housekeeping'];

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('data-value="admin"', false)
            ->assertSeeInOrder($expectedOrder)
            ->assertSee('id="email-required-error"', false)
            ->assertSeeText('Email address is required.');

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('value="admin"', false)
            ->assertSeeInOrder(['Receptionist', 'Billing', 'Housekeeping'])
            ->assertSee('id="password-match-status"', false)
            ->assertSeeText('Passwords match.')
            ->assertSeeText('Passwords do not match.');
    }

    public function test_public_registration_cannot_create_an_administrator(): void
    {
        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Unapproved Administrator',
            'email' => 'newadmin@lecharmehotel.com',
            'role' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'newadmin@lecharmehotel.com']);
    }

    public function test_registration_accepts_a_work_email(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Sample Employee',
            'email' => 'Sample@LeCharmeHotel.com',
            'role' => 'receptionist',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Account request submitted. A system administrator must approve it before you can sign in.');
        $response->assertSessionHasInput('email', 'sample@lecharmehotel.com');
        $response->assertSessionHasInput('role', 'receptionist');
        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'sample@lecharmehotel.com', 'approved_at' => null]);
        $this->assertDatabaseHas('auth_activity_logs', [
            'email' => 'sample@lecharmehotel.com',
            'event_type' => 'ACCOUNT_REGISTERED',
            'successful' => true,
        ]);
        $this->assertDatabaseMissing('auth_activity_logs', [
            'email' => 'sample@lecharmehotel.com',
            'event_type' => 'LOGIN_SUCCESS',
        ]);
    }

    public function test_registration_rejects_a_non_work_email(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Sample Employee',
            'email' => 'sample@gmail.com',
            'role' => 'receptionist',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'email' => 'Invalid email. Please use your work email ending in @lecharmehotel.com.',
        ]);
        $this->assertDatabaseMissing('users', ['email' => 'sample@gmail.com']);
    }

    public function test_registration_rejects_passwords_that_do_not_match(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Sample Employee',
            'email' => 'sample@lecharmehotel.com',
            'role' => 'receptionist',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'password' => 'Passwords do not match.',
        ]);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'sample@lecharmehotel.com']);
    }

    public function test_login_accepts_a_work_email_case_insensitively(): void
    {
        User::factory()->create([
            'email' => 'employee@lecharmehotel.com',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'Employee@LeCharmeHotel.com',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('auth_activity_logs', [
            'email' => 'employee@lecharmehotel.com',
            'event_type' => 'LOGIN_SUCCESS',
            'successful' => true,
        ]);
    }

    public function test_login_requires_an_email_when_a_password_is_entered_first(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => '',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Email address is required.',
        ]);
        $this->assertGuest();
    }

    public function test_login_rejects_a_non_work_email_before_authentication(): void
    {
        User::factory()->create([
            'email' => 'employee@gmail.com',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'employee@gmail.com',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Invalid email. Please use your work email ending in @lecharmehotel.com.',
        ]);
        $this->assertGuest();
        $this->assertDatabaseHas('auth_activity_logs', [
            'email' => 'employee@gmail.com',
            'event_type' => 'LOGIN_FAILED',
            'successful' => false,
        ]);
    }

    public function test_failed_password_and_logout_are_recorded(): void
    {
        $user = User::factory()->create([
            'email' => 'employee@lecharmehotel.com',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => 'receptionist',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('auth_activity_logs', [
            'email' => $user->email,
            'event_type' => 'LOGIN_FAILED',
            'successful' => false,
        ]);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertDatabaseHas('auth_activity_logs', [
            'user_id' => $user->id,
            'event_type' => 'LOGOUT',
            'successful' => true,
        ]);
    }

    public function test_login_rejects_a_department_that_does_not_match_the_account(): void
    {
        User::factory()->create([
            'email' => 'admin@lecharmehotel.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'admin@lecharmehotel.com',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
        $this->assertGuest();
    }

    public function test_admin_login_page_is_available_at_admin_url(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeText('Administrator Log In Console')
            ->assertDontSee('role-card', false);
    }

    public function test_admin_can_sign_in_from_the_admin_page_only(): void
    {
        User::factory()->create([
            'email' => 'admin@lecharmehotel.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@lecharmehotel.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->post(route('logout'))->assertRedirect(route('admin.login'));
    }

    public function test_staff_account_cannot_sign_in_from_the_admin_page(): void
    {
        User::factory()->create([
            'email' => 'employee@lecharmehotel.com',
            'password' => 'password',
            'role' => 'receptionist',
        ]);

        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'employee@lecharmehotel.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guests_opening_admin_pages_are_sent_to_the_admin_login(): void
    {
        $this->get(route('admin.accounts.create'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_approved_staff_accounts_but_not_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'boss@lecharmehotel.com']);

        $this->actingAs($admin)->get(route('admin.accounts.create'))
            ->assertOk()
            ->assertSeeText('Create Account')
            ->assertDontSee('value="admin"', false);

        $this->actingAs($admin)->post(route('admin.accounts.store'), [
            'name' => 'New Cashier',
            'email' => 'newcashier@lecharmehotel.com',
            'role' => 'cashier',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('admin.accounts.create'));

        $created = User::where('email', 'newcashier@lecharmehotel.com')->first();
        $this->assertSame('cashier', $created->role);
        $this->assertNotNull($created->approved_at);

        $this->actingAs($admin)->post(route('admin.accounts.store'), [
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@lecharmehotel.com',
            'role' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@lecharmehotel.com']);
    }

    public function test_non_admin_cannot_open_create_account(): void
    {
        $user = User::factory()->create(['role' => 'receptionist', 'email' => 'r@lecharmehotel.com']);

        $this->actingAs($user)->get(route('admin.accounts.create'))->assertForbidden();
    }

    public function test_every_registered_department_opens_its_own_dashboard(): void
    {
        $dashboards = [
            'receptionist' => 'Front Desk Dashboard',
            'cashier' => 'Cashier &amp; Billing Dashboard',
            'housekeeping' => 'Daily Rooms Ready Status & Room Preparation Control',
        ];

        foreach ($dashboards as $role => $signature) {
            $email = "{$role}@lecharmehotel.com";

            $this->post(route('register.store'), [
                'name' => ucfirst($role).' Test User',
                'email' => $email,
                'role' => $role,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])->assertRedirect(route('login'));

            $this->assertDatabaseHas('users', ['email' => $email, 'role' => $role]);
            $this->assertGuest();
            User::where('email', $email)->update(['approved_at' => now()]);

            $this->post(route('login.store'), [
                'email' => $email,
                'role' => $role,
                'password' => 'password',
            ])->assertRedirect(route('dashboard'));

            $this->get(route('dashboard'))->assertOk()->assertSee($signature, false);
            $this->post(route('logout'))->assertRedirect(route('login'));
        }
    }
}
