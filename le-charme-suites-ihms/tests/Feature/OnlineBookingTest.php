<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unmatched_final_room_card_is_centered_on_wider_screens(): void
    {
        $this->get(route('booking.create'))
            ->assertOk()
            ->assertSee('sm:col-span-2 sm:w-full sm:max-w-[calc(50%-0.625rem)] sm:justify-self-center', false)
            ->assertSeeText('Bike-Friendly Destination:')
            ->assertSeeText('Direct booking request');
    }

    public function test_guest_can_submit_online_booking_that_appears_in_shared_database(): void
    {
        $room = Room::factory()->create([
            'room_number' => '101',
            'room_type' => 'Deluxe King',
            'capacity' => 3,
        ]);

        $response = $this->post(route('booking.store'), $this->bookingPayload());

        $reservation = Reservation::with('guest')->firstOrFail();
        $response->assertRedirect(route('booking.confirmation', $reservation->booking_reference));
        $this->assertSame('pending', $reservation->status);
        $this->assertSame('public_website', $reservation->source);
        $this->assertSame($room->id, $reservation->room_id);
        $this->assertSame('guest@example.com', $reservation->guest->email);
        $this->assertDatabaseHas('integration_events', [
            'channel' => 'website',
            'dataset' => 'reservations',
            'record_count' => 1,
        ]);
    }

    public function test_overlapping_online_booking_is_rejected_without_creating_another_record(): void
    {
        $room = Room::factory()->create([
            'room_number' => '102',
            'room_type' => 'Deluxe King',
            'capacity' => 3,
        ]);
        $this->post(route('booking.store'), $this->bookingPayload())->assertRedirect();

        $response = $this->from(route('booking.create'))->post(route('booking.store'), $this->bookingPayload([
            'email' => 'second@example.com',
        ]));

        $response->assertRedirect(route('booking.create'));
        $response->assertSessionHasErrors('room_type');
        $this->assertSame(1, Reservation::where('room_id', $room->id)->count());
    }

    public function test_hotel_website_api_requires_key_and_creates_booking(): void
    {
        config(['integrations.api_key' => 'booking-secret']);
        Room::factory()->create([
            'room_number' => '103',
            'room_type' => 'Deluxe King',
            'capacity' => 3,
        ]);

        $this->postJson('/api/v1/bookings', $this->bookingPayload())->assertUnauthorized();

        $response = $this->withToken('booking-secret')
            ->postJson('/api/v1/bookings', $this->bookingPayload());

        $response->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('room_type', 'Deluxe King');
        $this->assertDatabaseHas('reservations', ['source' => 'hotel_website_api', 'status' => 'pending']);
    }

    /** @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function bookingPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Online',
            'last_name' => 'Guest',
            'email' => 'guest@example.com',
            'phone' => '+639171234567',
            'room_type' => 'Deluxe King',
            'check_in_date' => now()->addDays(10)->toDateString(),
            'check_out_date' => now()->addDays(12)->toDateString(),
            'number_of_guests' => 2,
            'notes' => 'Late arrival requested.',
            'privacy_consent' => '1',
        ], $overrides);
    }
}
