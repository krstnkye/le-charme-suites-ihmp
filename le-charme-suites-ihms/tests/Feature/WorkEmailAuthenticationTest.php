<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkEmailAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_show_roles_in_the_same_order(): void
    {
        $expectedOrder = ['Admin', 'Receptionist', 'Billing', 'Housekeeping'];

        $this->get(route('login'))
            ->assertOk()
            ->assertSeeInOrder($expectedOrder)
            ->assertSee('id="email-required-error"', false)
            ->assertSeeText('Email address is required.');

        $this->get(route('register'))
            ->assertOk()
            ->assertSeeInOrder($expectedOrder)
            ->assertSee('id="password-match-status"', false)
            ->assertSeeText('Passwords match.')
            ->assertSeeText('Passwords do not match.');
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
        $response->assertSessionHas('status', 'Account created successfully. Please sign in with your new work account to continue.');
        $response->assertSessionHasInput('email', 'sample@lecharmehotel.com');
        $response->assertSessionHasInput('role', 'receptionist');
        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'sample@lecharmehotel.com']);
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
            'email' => 'The selected department does not match this account. Choose Admin and try again.',
        ]);
        $this->assertGuest();
    }

    public function test_every_registered_department_opens_its_own_dashboard(): void
    {
        $dashboards = [
            'admin' => 'Property Systems Integrity & Control',
            'receptionist' => 'Front Desk Dashboard',
            'cashier' => 'Cashier &amp; Billing Dashboard',
            'housekeeping' => 'Daily Rooms Readiness & Turnover Control',
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
