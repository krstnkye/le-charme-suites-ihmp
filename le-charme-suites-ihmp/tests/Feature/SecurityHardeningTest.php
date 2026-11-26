<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_confirmation_requires_a_temporary_signature(): void
    {
        $reservation = Reservation::factory()->pending()->create();

        $this->get(route('booking.confirmation', $reservation->booking_reference))->assertForbidden();
    }

    public function test_security_headers_are_added_to_responses(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_scoped_integration_credentials_cannot_cross_capabilities(): void
    {
        config([
            'integrations.credentials.export' => 'export-only',
            'integrations.credentials.booking' => 'booking-only',
        ]);

        $this->withToken('booking-only')->getJson('/api/v1/status')->assertUnauthorized();
        $this->withToken('export-only')->postJson('/api/v1/bookings', [])->assertUnauthorized();
    }

    public function test_seeding_does_not_reset_an_existing_secure_password(): void
    {
        config(['hotel.seed_demo_users' => false]);
        $user = User::factory()->create([
            'email' => 'admin@lecharmehotel.com',
            'password' => 'A-secure-existing-password',
        ]);
        $hash = $user->password;

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_seeding_rotates_the_legacy_documented_password(): void
    {
        config(['hotel.seed_demo_users' => false]);
        $user = User::factory()->create([
            'email' => 'admin@lecharmehotel.com',
            'password' => 'password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertFalse(Hash::check('password', $user->fresh()->password));
    }

    public function test_government_identification_is_encrypted_at_rest_and_maskable(): void
    {
        $guest = Guest::factory()->create(['identification_number' => 'P123456789']);
        $stored = DB::table('guests')->where('id', $guest->id)->value('identification_number');

        $this->assertNotSame('P123456789', $stored);
        $this->assertSame('••••••6789', $guest->masked_identification_number);
    }
}
