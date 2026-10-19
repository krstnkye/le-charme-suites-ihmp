<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialHotelProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_property_profile_is_available_to_the_system(): void
    {
        $this->assertSame('Le Charmé Suites', config('hotel.name'));
        $this->assertSame('+63 (047) 250-3333', config('hotel.phone'));
        $this->assertSame('frontoffice@lecharmehotels.com', config('hotel.email'));
        $this->assertSame('2:00 PM', config('hotel.check_in_time'));
        $this->assertSame('12:00 PM', config('hotel.check_out_time'));
        $this->assertSame(45, config('hotel.published_room_count'));
        $this->assertSame(5, config('hotel.published_room_type_count'));
        $this->assertCount(5, config('hotel.room_types'));

        foreach (config('hotel.room_types') as $roomType) {
            $this->assertFileExists(public_path($roomType['image']));
            $this->assertStringStartsWith('https://lecharmehotels.com/img/rooms/', $roomType['image_source']);
        }
    }

    public function test_shared_interaction_styles_are_available_offline(): void
    {
        $this->assertFileExists(public_path('css/interactions.css'));

        $this->get(route('booking.create'))
            ->assertOk()
            ->assertSee(asset('css/interactions.css'), false);
    }

    public function test_database_seeder_creates_the_official_room_catalog(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('rooms', 5);
        $this->assertDatabaseHas('rooms', [
            'room_number' => '102',
            'room_type' => 'Deluxe King',
            'rate' => 13750.00,
            'capacity' => 3,
            'base_occupancy' => 2,
            'room_size_sqm' => 45.00,
            'bed_configuration' => '1 King Bed',
            'extra_guest_rate' => 1165.00,
        ]);
        $this->assertDatabaseHas('rooms', [
            'room_number' => '301',
            'room_type' => 'Family Room',
            'rate' => 23800.00,
            'capacity' => 7,
            'bathrooms' => 2,
        ]);
    }

    public function test_official_room_type_populates_operational_specifications(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);

        $this->actingAs($administrator)
            ->post(route('rooms.store'), [
                'room_number' => '501',
                'room_type' => 'Deluxe Twin',
                'rate' => 15840,
                'capacity' => 5,
                'status' => 'available',
                'description' => 'Official catalog test room.',
            ])
            ->assertRedirect(route('admin.operations.index', ['module' => 'room-inventory']));

        $room = Room::where('room_number', '501')->firstOrFail();

        $this->assertSame('45.00', $room->room_size_sqm);
        $this->assertSame('2 Queen Beds', $room->bed_configuration);
        $this->assertSame(2, $room->base_occupancy);
        $this->assertSame(1, $room->bathrooms);
        $this->assertSame('1165.00', $room->extra_guest_rate);
        $this->assertContains('Bike Rack', $room->amenities);
        $this->assertTrue($room->non_smoking);
    }

    public function test_an_official_room_type_uses_its_published_rate_instead_of_a_manual_override(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);

        $this->actingAs($administrator)
            ->post(route('rooms.store'), [
                'room_number' => '502',
                'room_type' => 'Charmé Suite',
                'rate' => 1,
                'capacity' => 3,
                'status' => 'available',
                'description' => 'Published rate synchronization test.',
            ])
            ->assertRedirect(route('admin.operations.index', ['module' => 'room-inventory']));

        $this->assertDatabaseHas('rooms', [
            'room_number' => '502',
            'room_type' => 'Charmé Suite',
            'rate' => 19500.00,
        ]);
    }
}
