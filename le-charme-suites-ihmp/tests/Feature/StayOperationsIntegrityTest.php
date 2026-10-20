<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StayOperationsIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_be_checked_in_before_the_arrival_date(): void
    {
        $this->travelTo('2026-10-20 10:00:00');
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = Reservation::factory()->create([
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-27',
        ]);

        $response = $this->actingAs($receptionist)->post(route('check-ins.store'), ['reservation_id' => $reservation->id]);

        $response->assertSessionHasErrors(['reservation_id' => 'This guest is scheduled to arrive on Oct 25, 2026 and cannot be checked in early.']);
        $this->assertSame('reserved', $reservation->fresh()->status);
        $this->assertDatabaseCount('check_ins', 0);
    }

    public function test_guest_cannot_be_checked_in_to_a_room_that_is_still_occupied(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create(['room_number' => '305', 'status' => 'occupied']);
        $currentStay = Reservation::factory()->checkedIn()->for($room)->create([
            'check_in_date' => today()->subDays(2),
            'check_out_date' => today(),
        ]);
        CheckIn::factory()->forReservation($currentStay)->create();
        $arrival = Reservation::factory()->for($room)->create();

        $response = $this->actingAs($receptionist)->post(route('check-ins.store'), ['reservation_id' => $arrival->id]);

        $response->assertSessionHasErrors(['reservation_id' => 'Room 305 is still occupied or under maintenance. Move the reservation to another room first.']);
        $this->assertSame('reserved', $arrival->fresh()->status);
        $this->assertSame(1, CheckIn::count());
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function inconsistentPayments(): array
    {
        return [
            'paid but short' => [1000.0, 'paid'],
            'partial with nothing collected' => [0.0, 'partial'],
            'partial that covers the bill' => [17528.0, 'partial'],
            'unpaid with money collected' => [500.0, 'unpaid'],
        ];
    }

    #[DataProvider('inconsistentPayments')]
    public function test_checkout_rejects_a_payment_status_that_does_not_match_the_amount(float $amountPaid, string $paymentStatus): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $room = Room::factory()->create(['rate' => 8764, 'status' => 'occupied']);
        $reservation = Reservation::factory()->checkedIn()->for($room)->create();
        $checkIn = CheckIn::factory()->forReservation($reservation)->create();

        $response = $this->actingAs($cashier)->post(route('check-outs.store'), [
            'check_in_id' => $checkIn->id,
            'amount_paid' => $amountPaid,
            'payment_status' => $paymentStatus,
        ]);

        $response->assertSessionHasErrors('amount_paid');
        $this->assertSame('active', $checkIn->fresh()->status);
        $this->assertDatabaseCount('check_outs', 0);
    }

    public function test_finishing_a_stayover_task_keeps_the_guest_room_occupied(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'occupied']);
        $reservation = Reservation::factory()->checkedIn()->for($room)->create();
        CheckIn::factory()->forReservation($reservation)->create();
        $task = HousekeepingTask::factory()->create([
            'room_id' => $room->id,
            'task_type' => 'stayover',
            'assigned_to' => $housekeeper->id,
        ]);

        $response = $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'completed',
            'assigned_to' => $housekeeper->id,
        ]);

        $response->assertRedirect();
        $this->assertSame('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
        $this->assertSame('occupied', $room->fresh()->status);
    }

    public function test_room_with_an_in_house_guest_cannot_be_marked_available(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create(['status' => 'occupied']);
        $reservation = Reservation::factory()->checkedIn()->for($room)->create();
        CheckIn::factory()->forReservation($reservation)->create();

        $response = $this->actingAs($receptionist)->put(route('rooms.update', $room), [
            'room_number' => $room->room_number,
            'room_type' => $room->room_type,
            'rate' => $room->rate,
            'capacity' => $room->capacity,
            'status' => 'available',
        ]);

        $response->assertSessionHasErrors(['status' => 'This room has an in-house guest. Check the guest out before changing the room status.']);
        $this->assertSame('occupied', $room->fresh()->status);
    }

    public function test_room_with_booking_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::factory()->create();
        Reservation::factory()->completed()->for($room)->create();

        $response = $this->actingAs($admin)->delete(route('rooms.destroy', $room));

        $response->assertSessionHasErrors('room');
        $this->assertModelExists($room);
    }

    public function test_guest_with_stay_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guest = Guest::factory()->create();
        $reservation = Reservation::factory()->completed()->for($guest)->create();

        $response = $this->actingAs($admin)->delete(route('guests.destroy', $guest));

        $response->assertSessionHasErrors('guest');
        $this->assertModelExists($guest);
        $this->assertModelExists($reservation);
    }

    public function test_public_booking_does_not_overwrite_an_existing_guest_profile(): void
    {
        Room::factory()->create(['room_type' => 'Deluxe King', 'capacity' => 3]);
        $guest = Guest::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.com',
            'phone' => '+639170000001',
        ]);

        $response = $this->post(route('booking.store'), [
            'first_name' => 'Someone',
            'last_name' => 'Else',
            'email' => 'maria@example.com',
            'phone' => '+639179999999',
            'room_type' => 'Deluxe King',
            'check_in_date' => today()->addDays(3)->toDateString(),
            'check_out_date' => today()->addDays(4)->toDateString(),
            'number_of_guests' => 2,
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect();
        $guest->refresh();
        $this->assertSame('Maria', $guest->first_name);
        $this->assertSame('Santos', $guest->last_name);
        $this->assertSame('+639170000001', $guest->phone);
    }

    public function test_changing_the_password_requires_the_current_password(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);
        $originalHash = $user->password;

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors(['current_password' => 'Your current password is incorrect.']);
        $this->assertSame($originalHash, $user->fresh()->password);
    }

    public function test_changing_the_email_requires_the_current_password(): void
    {
        $user = User::factory()->create(['role' => 'receptionist', 'email' => 'old.address@lecharmehotel.com']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'new.address@lecharmehotel.com',
        ]);

        $response->assertSessionHasErrors(['current_password' => 'Enter your current password to change your email or password.']);
        $this->assertSame('old.address@lecharmehotel.com', $user->fresh()->email);
    }
}
