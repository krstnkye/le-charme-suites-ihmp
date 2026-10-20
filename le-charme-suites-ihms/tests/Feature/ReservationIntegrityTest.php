<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_cannot_exceed_the_room_capacity(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create(['room_number' => '204', 'capacity' => 2]);

        $response = $this->actingAs($receptionist)->post(route('reservations.store'), [
            ...$this->bookingFields($room, Guest::factory()->create()),
            'number_of_guests' => 3,
        ]);

        $response->assertSessionHasErrors(['number_of_guests' => 'Room 204 sleeps at most 2 guests.']);
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_new_booking_cannot_start_in_the_past(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create();

        $response = $this->actingAs($receptionist)->post(route('reservations.store'), [
            ...$this->bookingFields($room, Guest::factory()->create()),
            'check_in_date' => today()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('check_in_date');
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_edit_form_cannot_skip_the_check_in_workflow(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($receptionist)->put(route('reservations.update', $reservation), [
            ...$this->bookingFields($reservation->room, $reservation->guest),
            'status' => 'checked_in',
        ]);

        $response->assertSessionHasErrors(['status' => 'Use the check-in and check-out screens to move a stay into or out of the hotel.']);
        $this->assertSame('reserved', $reservation->fresh()->status);
        $this->assertDatabaseCount('check_ins', 0);
    }

    public function test_edit_form_offers_pending_status_for_online_bookings(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = Reservation::factory()->pending()->create();

        $response = $this->actingAs($receptionist)->get(route('reservations.edit', $reservation));

        $response->assertOk();
        $response->assertSee('<option value="pending" selected', false);
        $response->assertDontSee('<option value="completed"', false);
    }

    public function test_completed_stay_cannot_be_edited(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = Reservation::factory()->completed()->create();
        $originalCheckOut = $reservation->check_out_date->toDateString();

        $response = $this->actingAs($receptionist)->put(route('reservations.update', $reservation), [
            ...$this->bookingFields($reservation->room, $reservation->guest),
            'check_out_date' => today()->addDays(9)->toDateString(),
            'status' => 'completed',
        ]);

        $response->assertRedirect(route('reservations.show', $reservation));
        $response->assertSessionHasErrors('status');
        $this->assertSame($originalCheckOut, $reservation->fresh()->check_out_date->toDateString());
    }

    public function test_moving_an_in_house_guest_updates_the_active_stay(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $oldRoom = Room::factory()->create(['status' => 'occupied']);
        $newRoom = Room::factory()->create(['status' => 'available']);
        $reservation = Reservation::factory()->checkedIn()->for($oldRoom)->create();
        $checkIn = CheckIn::factory()->forReservation($reservation)->create();

        $response = $this->actingAs($receptionist)->put(route('reservations.update', $reservation), [
            ...$this->bookingFields($newRoom, $reservation->guest),
            'status' => 'checked_in',
        ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertSame($newRoom->id, $checkIn->fresh()->room_id);
        $this->assertSame('occupied', $newRoom->fresh()->status);
        $this->assertSame('cleaning', $oldRoom->fresh()->status);
    }

    public function test_reservation_with_a_receipt_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reservation = Reservation::factory()->completed()->create();

        $response = $this->actingAs($admin)->delete(route('reservations.destroy', $reservation));

        $response->assertSessionHasErrors('reservation');
        $this->assertModelExists($reservation);
    }

    public function test_deleting_a_cancelled_booking_leaves_an_occupied_room_occupied(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::factory()->create(['status' => 'occupied']);
        $reservation = Reservation::factory()->cancelled()->for($room)->create();

        $response = $this->actingAs($admin)->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect(route('reservations.index'));
        $this->assertModelMissing($reservation);
        $this->assertSame('occupied', $room->fresh()->status);
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingFields(Room $room, Guest $guest): array
    {
        return [
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => today()->toDateString(),
            'check_out_date' => today()->addDays(2)->toDateString(),
            'number_of_guests' => 2,
        ];
    }
}
