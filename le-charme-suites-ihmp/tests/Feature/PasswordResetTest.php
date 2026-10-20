<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_available_from_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSeeText('Forgot your password?');
    }

    public function test_registered_work_email_receives_reset_instructions(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'employee@lecharmehotel.com']);

        $this->post(route('password.email'), ['email' => 'Employee@LeCharmeHotel.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('auth_activity_logs', [
            'user_id' => $user->id,
            'event_type' => 'PASSWORD_RESET_REQUESTED',
            'successful' => true,
        ]);
    }

    public function test_password_reset_requires_a_work_email(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'employee@gmail.com'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }

    public function test_staff_member_can_reset_their_password_with_a_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'employee@lecharmehotel.com',
            'password' => 'old-password',
        ]);

        $this->post(route('password.email'), ['email' => $user->email]);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
        $this->assertDatabaseHas('auth_activity_logs', [
            'user_id' => $user->id,
            'event_type' => 'PASSWORD_RESET_COMPLETED',
            'successful' => true,
        ]);
    }

    public function test_unknown_work_email_receives_the_same_generic_response(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => 'unknown@lecharmehotel.com']);

        $response->assertSessionHas(
            'status',
            'If that work email belongs to an account, password reset instructions have been sent.'
        );
        Notification::assertNothingSent();
    }
}
