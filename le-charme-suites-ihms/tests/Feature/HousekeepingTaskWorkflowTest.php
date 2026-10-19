<?php

namespace Tests\Feature;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousekeepingTaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_housekeeper_can_create_take_and_complete_room_task(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'cleaning']);

        $response = $this->actingAs($housekeeper)->post(route('housekeeping-tasks.store'), [
            'room_id' => $room->id,
            'task_type' => 'turnover',
            'title' => 'Departure room turnover',
            'instructions' => 'Replace linen and complete final inspection.',
            'priority' => 'high',
            'due_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $task = HousekeepingTask::firstOrFail();
        $response->assertRedirect();
        $this->assertSame($housekeeper->id, $task->assigned_to);
        $this->assertSame('queued', $task->status);

        $response = $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'completed',
            'assigned_to' => $housekeeper->id,
            'completion_notes' => 'Room cleaned, restocked, and inspected.',
        ]);

        $response->assertRedirect();
        $this->assertSame('completed', $task->fresh()->status);
        $this->assertSame('available', $room->fresh()->status);
        $this->assertDatabaseHas('room_status_logs', [
            'room_id' => $room->id,
            'status' => 'available',
        ]);
    }

    public function test_housekeeper_cannot_update_task_assigned_to_another_housekeeper(): void
    {
        $assignedHousekeeper = User::factory()->create(['role' => 'housekeeping']);
        $otherHousekeeper = User::factory()->create(['role' => 'housekeeping']);
        $task = HousekeepingTask::factory()->create(['assigned_to' => $assignedHousekeeper->id]);

        $response = $this->actingAs($otherHousekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'in_progress',
        ]);

        $response->assertForbidden();
        $this->assertSame('queued', $task->fresh()->status);
    }
}
