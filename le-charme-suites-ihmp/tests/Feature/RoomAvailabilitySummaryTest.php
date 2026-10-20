<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAvailabilitySummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_room_is_counted_in_exactly_one_bucket(): void
    {
        $occupiedRoom = Room::factory()->create(['status' => 'occupied']);
        CheckIn::factory()->forReservation(Reservation::factory()->checkedIn()->for($occupiedRoom)->create())->create();
        Reservation::factory()->for(Room::factory()->create())->create(['check_in_date' => today()]);
        Room::factory()->create(['status' => 'cleaning']);
        Room::factory()->create(['status' => 'maintenance']);
        Reservation::factory()->for(Room::factory()->create())->create([
            'check_in_date' => today()->addDays(5),
            'check_out_date' => today()->addDays(7),
        ]);
        Room::factory()->create();

        $summary = app(RoomAvailabilityService::class)->summary();

        $this->assertSame(45, $summary['property_total']);
        $this->assertSame(6, $summary['encoded']);
        $this->assertSame(1, $summary['occupied']);
        $this->assertSame(1, $summary['reserved']);
        $this->assertSame(2, $summary['out_of_service']);
        $this->assertSame(2, $summary['available']);
    }

    public function test_room_types_page_shows_availability_against_the_hotel_total(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Room::factory()->create(['room_type' => 'Deluxe King']);
        Room::factory()->create(['room_type' => 'Deluxe King', 'status' => 'occupied']);

        $response = $this->actingAs($admin)->get(route('admin.operations.index', ['module' => 'room-types']));

        $response->assertOk();
        $response->assertSeeText('1 of 45 rooms available');
        $response->assertSeeText('Total hotel rooms');
        $response->assertSee('data-room-availability-summary', false);
    }

    public function test_front_desk_dashboard_shows_the_availability_summary(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        Room::factory()->count(2)->create();

        $response = $this->actingAs($receptionist)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('2 of 45 rooms available');
    }
}
