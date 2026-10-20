<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRealDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_desk_dashboard_only_lists_active_reservation_records(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create(['room_number' => '110', 'status' => 'reserved']);
        $activeGuest = Guest::create(['first_name' => 'Active', 'last_name' => 'Guest']);
        $cancelledGuest = Guest::create(['first_name' => 'Cancelled', 'last_name' => 'Guest']);

        Reservation::create([
            'guest_id' => $activeGuest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'status' => 'reserved',
        ]);
        Reservation::create([
            'guest_id' => $cancelledGuest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'status' => 'cancelled',
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Active Guest')
            ->assertDontSee('Cancelled Guest')
            ->assertSee('name="search"', false);
    }

    public function test_housekeeping_dashboard_separates_property_total_from_encoded_room_records(): void
    {
        $user = User::factory()->create(['role' => 'housekeeping']);
        Room::factory()->create(['room_number' => '120', 'status' => 'available']);
        Room::factory()->create(['room_number' => '121', 'status' => 'cleaning']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Property Rooms')
            ->assertSee('45')
            ->assertSee('Operational records')
            ->assertDontSee('Linen &amp; Supplies', false);
    }

    public function test_billing_dashboard_calculates_guest_bill_from_saved_room_and_charge_records(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);
        $room = Room::factory()->create(['room_number' => '220', 'rate' => 1000, 'status' => 'occupied']);
        $guest = Guest::create(['first_name' => 'Maria', 'last_name' => 'Santos']);
        $reservation = Reservation::create([
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->subDay()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'status' => 'checked_in',
        ]);
        CheckIn::create([
            'reservation_id' => $reservation->id,
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'checked_in_at' => now()->subDay(),
            'status' => 'active',
        ]);
        HotelTransaction::create([
            'guest_id' => $guest->id,
            'reservation_id' => $reservation->id,
            'room_id' => $room->id,
            'transaction_type' => 'additional_charge',
            'amount' => 250,
            'status' => 'recorded',
            'transaction_date' => now(),
            'description' => 'Recorded guest service charge',
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('PHP 2,000.00')
            ->assertSee('PHP 250.00')
            ->assertSee('PHP 2,250.00')
            ->assertSee('Payment Record Summary')
            ->assertDontSee('Starting cash')
            ->assertDontSee('Subic Bay Tax Records');
    }
}
