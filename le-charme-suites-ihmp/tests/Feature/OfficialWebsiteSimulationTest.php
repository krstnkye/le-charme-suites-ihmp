<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialWebsiteSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulator_page_is_clearly_labelled_as_an_academic_simulation(): void
    {
        $response = $this->get(route('website-simulation.create'));

        $response->assertOk();
        $response->assertSeeText('Official website integration simulation');
        $response->assertSeeText('Academic mini thesis prototype.');
        $response->assertSee('action="'.route('website-simulation.store').'"', false);
    }

    public function test_simulated_website_booking_reaches_the_front_desk_queue(): void
    {
        Room::factory()->create(['room_type' => 'Deluxe King', 'capacity' => 3]);

        $response = $this->post(route('website-simulation.store'), $this->bookingPayload());

        $reservation = Reservation::firstOrFail();
        $response->assertRedirect(route('booking.confirmation', $reservation->booking_reference));
        $this->assertSame('pending', $reservation->status);
        $this->assertSame('official_website_simulation', $reservation->source);
        $this->assertDatabaseHas('integration_events', [
            'channel' => 'website_simulation',
            'direction' => 'inbound',
            'endpoint' => '/website-booking',
        ]);
    }

    public function test_front_desk_feed_lists_new_website_bookings(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = Reservation::factory()->pending()->create(['source' => 'official_website_simulation']);
        Reservation::factory()->create();

        $response = $this->actingAs($receptionist)->getJson(route('online-booking-requests.feed'));

        $response->assertOk();
        $response->assertJsonPath('pending_count', 1);
        $response->assertJsonPath('latest_id', $reservation->id);
        $response->assertJsonPath('latest.0.source', 'Official Website (Simulated)');
        $response->assertJsonPath('latest.0.guest', $reservation->guest->full_name);
    }

    public function test_departments_without_reservation_access_cannot_read_the_feed(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);

        $response = $this->actingAs($housekeeper)->getJson(route('online-booking-requests.feed'));

        $response->assertForbidden();
    }

    public function test_front_desk_pages_include_the_live_booking_alerts(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $response = $this->actingAs($receptionist)->get(route('reservations.index'));

        $response->assertOk();
        $response->assertSee('id="online-booking-alerts"', false);
        $response->assertSee(json_encode(route('online-booking-requests.feed')), false);
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPayload(): array
    {
        return [
            'first_name' => 'Demo',
            'last_name' => 'Professor',
            'email' => 'demo.professor@example.com',
            'phone' => '+639171234567',
            'room_type' => 'Deluxe King',
            'check_in_date' => today()->addDays(5)->toDateString(),
            'check_out_date' => today()->addDays(6)->toDateString(),
            'number_of_guests' => 2,
            'privacy_consent' => '1',
        ];
    }
}
