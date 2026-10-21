<?php

namespace Tests\Feature;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomInspectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_passing_inspection_releases_the_room_and_closes_its_tasks(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['room_number' => '210', 'status' => 'cleaning']);
        $task = HousekeepingTask::factory()->create(['room_id' => $room->id, 'status' => 'inspection']);

        $response = $this->actingAs($housekeeper)->post(route('rooms.inspection.store', $room), ['result' => 'pass']);

        $response->assertSessionHas('status', 'Room 210 passed inspection and is available for sale.');
        $this->assertSame('available', $room->fresh()->status);
        $this->assertSame('completed', $task->fresh()->status);
        $this->assertDatabaseHas('room_status_logs', ['room_id' => $room->id, 'status' => 'available']);
    }

    public function test_failing_inspection_creates_a_re_clean_task(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['room_number' => '211', 'status' => 'cleaning']);

        $response = $this->actingAs($housekeeper)->post(route('rooms.inspection.store', $room), [
            'result' => 'fail',
            'notes' => 'Bathroom mirror streaked.',
        ]);

        $response->assertSessionHas('status', 'Room 211 failed inspection; a re-clean task was created.');
        $this->assertSame('cleaning', $room->fresh()->status);
        $this->assertDatabaseHas('housekeeping_tasks', [
            'room_id' => $room->id,
            'title' => 'Re-clean after failed inspection',
            'instructions' => 'Bathroom mirror streaked.',
            'priority' => 'high',
        ]);
    }

    public function test_failing_needs_a_reason(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'cleaning']);

        $response = $this->actingAs($housekeeper)->post(route('rooms.inspection.store', $room), ['result' => 'fail']);

        $response->assertSessionHasErrors(['notes' => 'Explain what failed so housekeeping knows what to fix.']);
        $this->assertDatabaseCount('housekeeping_tasks', 0);
    }

    public function test_only_rooms_waiting_for_cleaning_can_be_inspected(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['room_number' => '212', 'status' => 'occupied']);

        $response = $this->actingAs($housekeeper)->post(route('rooms.inspection.store', $room), ['result' => 'pass']);

        $response->assertSessionHasErrors(['result' => 'Room 212 is not waiting for inspection.']);
        $this->assertSame('occupied', $room->fresh()->status);
    }
}
