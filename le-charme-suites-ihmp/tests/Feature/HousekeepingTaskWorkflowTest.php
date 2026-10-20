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

    public function test_housekeeper_can_reassign_task_to_another_housekeeper(): void
    {
        $assignedHousekeeper = User::factory()->create(['role' => 'housekeeping']);
        $otherHousekeeper = User::factory()->create(['role' => 'housekeeping']);
        $task = HousekeepingTask::factory()->create(['assigned_to' => $assignedHousekeeper->id]);

        $response = $this->actingAs($assignedHousekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'in_progress',
            'assigned_to' => $otherHousekeeper->id,
        ]);

        $response->assertRedirect();
        $this->assertSame('in_progress', $task->fresh()->status);
        $this->assertSame($otherHousekeeper->id, $task->fresh()->assigned_to);
    }

    public function test_housekeeping_room_page_uses_real_task_assignee(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping', 'name' => 'Test Housekeeper']);
        $room = Room::factory()->create(['status' => 'cleaning']);
        HousekeepingTask::factory()->create([
            'room_id' => $room->id,
            'assigned_to' => $housekeeper->id,
            'status' => 'queued',
        ]);

        $this->actingAs($housekeeper)
            ->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('Test Housekeeper')
            ->assertSee('Update Assignment');
    }

    public function test_housekeeping_dashboard_shows_real_task_assignment_controls(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping', 'name' => 'Assigned Staff']);
        $room = Room::factory()->create(['status' => 'cleaning', 'room_number' => '305']);
        HousekeepingTask::factory()->create([
            'room_id' => $room->id,
            'assigned_to' => $housekeeper->id,
            'title' => 'Prepare Room 305',
            'status' => 'queued',
        ]);

        $this->actingAs($housekeeper)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Housekeeping Task Assignments')
            ->assertSee('Prepare Room 305')
            ->assertSee('Assigned Staff')
            ->assertSee('Assign Task');
    }

    public function test_receptionist_can_assign_a_cleaning_task_to_themselves(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create(['status' => 'cleaning']);

        $response = $this->actingAs($receptionist)->post(route('housekeeping-tasks.store'), [
            'room_id' => $room->id,
            'assigned_to' => $receptionist->id,
            'task_type' => 'turnover',
            'title' => 'Room cleaning after check-out',
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('housekeeping_tasks', [
            'room_id' => $room->id,
            'assigned_to' => $receptionist->id,
            'created_by' => $receptionist->id,
            'status' => 'queued',
        ]);
    }

    public function test_receptionist_can_assign_a_cleaning_task_to_a_housekeeper(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'cleaning']);

        $response = $this->actingAs($receptionist)->post(route('housekeeping-tasks.store'), [
            'room_id' => $room->id,
            'assigned_to' => $housekeeper->id,
            'task_type' => 'turnover',
            'title' => 'Room cleaning after check-out',
            'priority' => 'normal',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('housekeeping_tasks', ['room_id' => $room->id, 'assigned_to' => $housekeeper->id]);
    }

    public function test_tasks_cannot_be_assigned_to_other_front_desk_or_billing_staff(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $otherReceptionist = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create(['status' => 'cleaning']);

        $response = $this->actingAs($receptionist)->post(route('housekeeping-tasks.store'), [
            'room_id' => $room->id,
            'assigned_to' => $otherReceptionist->id,
            'task_type' => 'turnover',
            'title' => 'Room cleaning after check-out',
            'priority' => 'normal',
        ]);

        $response->assertSessionHasErrors('assigned_to');
        $this->assertDatabaseCount('housekeeping_tasks', 0);
    }

    public function test_housekeeper_can_update_a_task_that_front_desk_kept_for_themselves(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $task = HousekeepingTask::factory()->create(['assigned_to' => $receptionist->id]);

        $response = $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'inspection',
            'assigned_to' => $receptionist->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('inspection', $task->fresh()->status);
        $this->assertSame($receptionist->id, $task->fresh()->assigned_to);
    }

    public function test_front_desk_dashboard_offers_self_assignment(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist', 'name' => 'Callista Bree']);
        $housekeeper = User::factory()->create(['role' => 'housekeeping', 'name' => 'Cyrene Lagman']);

        $response = $this->actingAs($receptionist)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('Cleaning Task Assignments');
        $response->assertSee('<option value="'.$receptionist->id.'" >Myself (Callista Bree)</option>', false);
        $response->assertSeeText('Cyrene Lagman');
    }
}
