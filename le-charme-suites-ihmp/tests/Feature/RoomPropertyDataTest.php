<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomPropertyDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_status_separates_property_total_from_encoded_inventory(): void
    {
        $user = User::factory()->create([
            'role' => 'receptionist',
            'email' => 'frontdesk@lecharmehotel.com',
        ]);
        Room::factory()->create(['status' => 'available']);

        $this->actingAs($user)
            ->get(route('rooms.index'))
            ->assertOk()
            ->assertSeeText('Property Rooms')
            ->assertSeeText('1 of 45')
            ->assertSeeText('Hotel total')
            ->assertSeeText('Rooms added');
    }
}
