<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkEmailAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_accepts_a_work_email(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Sample Employee',
            'email' => 'Sample@LeCharmeHotel.com',
            'role' => 'receptionist',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'sample@lecharmehotel.com']);
        $this->assertDatabaseHas('auth_activity_logs', [
            'email' => 'sample@lecharmehotel.com',
            'event_type' => 'ACCOUNT_REGISTERED',
            'successful' => true,
        ]);
        $this->assertDatabaseHas('auth_activity_logs', [
            'email' => 'sample@lecharmehotel.com',
            'event_type' => 'LOGIN_SUCCESS',
            'successful' => true,
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

    public function test_login_accepts_a_work_email_case_insensitively(): void
    {
        User::factory()->create([
            'email' => 'employee@lecharmehotel.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'Employee@LeCharmeHotel.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('auth_activity_logs', [
            'email' => 'employee@lecharmehotel.com',
            'event_type' => 'LOGIN_SUCCESS',
            'successful' => true,
        ]);
    }

    public function test_login_rejects_a_non_work_email_before_authentication(): void
    {
        User::factory()->create([
            'email' => 'employee@gmail.com',
            'password' => 'password',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'employee@gmail.com',
            'password' => 'password',
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
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
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
}
