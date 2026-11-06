<?php

namespace Tests\Feature;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HousekeepingTaskRulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every status pair for a turnover task changed by a housekeeper.
     *
     * @return array<string, array{string, string, bool}>
     */
    public static function turnoverStatusChanges(): array
    {
        $allowed = [
            'queued' => ['queued', 'in_progress', 'cancelled'],
            'in_progress' => ['queued', 'in_progress', 'inspection', 'cancelled'],
            'inspection' => ['in_progress', 'inspection', 'completed', 'cancelled'],
            'completed' => ['completed'],
            'cancelled' => ['cancelled'],
        ];
        $statuses = array_keys($allowed);
        $cases = [];

        foreach ($statuses as $from) {
            foreach ($statuses as $to) {
                $cases["{$from} to {$to}"] = [$from, $to, in_array($to, $allowed[$from], true)];
            }
        }

        return $cases;
    }

    #[DataProvider('turnoverStatusChanges')]
    public function test_turnover_task_follows_the_status_workflow(string $from, string $to, bool $isAllowed): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $task = HousekeepingTask::factory()->create([
            'room_id' => Room::factory()->create(['status' => 'cleaning'])->id,
            'assigned_to' => $housekeeper->id,
            'status' => $from,
        ]);

        $response = $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => $to,
            'assigned_to' => $housekeeper->id,
        ]);

        if ($isAllowed) {
            $response->assertSessionHasNoErrors();
            $this->assertSame($to, $task->fresh()->status);
        } else {
            $response->assertSessionHasErrors('status');
            $this->assertSame($from, $task->fresh()->status);
        }
    }

    public function test_quick_tasks_can_be_completed_straight_from_the_queue(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $task = HousekeepingTask::factory()->create([
            'task_type' => 'guest_request',
            'assigned_to' => $housekeeper->id,
        ]);

        $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'completed',
            'assigned_to' => $housekeeper->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_completing_a_task_never_releases_a_room_under_maintenance(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'maintenance']);
        $guestRequest = HousekeepingTask::factory()->create(['room_id' => $room->id, 'task_type' => 'guest_request', 'assigned_to' => $housekeeper->id]);
        $turnover = HousekeepingTask::factory()->create(['room_id' => $room->id, 'status' => 'inspection', 'assigned_to' => $housekeeper->id]);

        foreach ([$guestRequest, $turnover] as $task) {
            $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $task), [
                'status' => 'completed',
                'assigned_to' => $housekeeper->id,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame('maintenance', $room->fresh()->status);
        $this->assertDatabaseMissing('room_status_logs', ['room_id' => $room->id, 'status' => 'available']);
    }

    public function test_completing_a_guest_request_does_not_release_a_room_waiting_for_cleaning(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'cleaning']);
        $task = HousekeepingTask::factory()->create(['room_id' => $room->id, 'task_type' => 'guest_request', 'assigned_to' => $housekeeper->id]);

        $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'completed',
            'assigned_to' => $housekeeper->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame('cleaning', $room->fresh()->status);
    }

    public function test_room_is_released_only_after_its_last_open_cleaning_task_is_completed(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'cleaning']);
        $turnover = HousekeepingTask::factory()->create(['room_id' => $room->id, 'status' => 'inspection', 'assigned_to' => $housekeeper->id]);
        $maintenanceFollowUp = HousekeepingTask::factory()->create(['room_id' => $room->id, 'task_type' => 'maintenance_follow_up', 'status' => 'in_progress', 'assigned_to' => $housekeeper->id]);

        $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $turnover), [
            'status' => 'completed',
            'assigned_to' => $housekeeper->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('cleaning', $room->fresh()->status);

        $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $maintenanceFollowUp), [
            'status' => 'completed',
            'assigned_to' => $housekeeper->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('cleaning', $room->fresh()->status);

        $inspection = HousekeepingTask::factory()->create(['room_id' => $room->id, 'task_type' => 'inspection', 'status' => 'in_progress', 'assigned_to' => $housekeeper->id]);
        $this->actingAs($housekeeper)->patch(route('housekeeping-tasks.update', $inspection), [
            'status' => 'completed',
            'assigned_to' => $housekeeper->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame('available', $room->fresh()->status);
        $this->assertDatabaseHas('room_status_logs', ['room_id' => $room->id, 'status' => 'available']);
    }

    public function test_receptionist_cannot_inspect_or_complete_a_cleaning_task(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $room = Room::factory()->create(['status' => 'cleaning']);
        $task = HousekeepingTask::factory()->create(['room_id' => $room->id, 'status' => 'in_progress']);

        foreach (['inspection', 'completed'] as $status) {
            $this->actingAs($receptionist)->patch(route('housekeeping-tasks.update', $task), [
                'status' => $status,
                'assigned_to' => $task->assigned_to,
            ])->assertSessionHasErrors(['status' => 'Only housekeeping staff can start, inspect, or complete cleaning tasks.']);
        }

        $this->assertSame('in_progress', $task->fresh()->status);
        $this->assertSame('cleaning', $room->fresh()->status);
    }

    public function test_receptionist_can_reassign_and_cancel_a_task(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $otherHousekeeper = User::factory()->create(['role' => 'housekeeping']);
        $task = HousekeepingTask::factory()->create(['status' => 'in_progress']);

        $this->actingAs($receptionist)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'in_progress',
            'assigned_to' => $otherHousekeeper->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($otherHousekeeper->id, $task->fresh()->assigned_to);

        $this->actingAs($receptionist)->patch(route('housekeeping-tasks.update', $task), [
            'status' => 'cancelled',
            'assigned_to' => $otherHousekeeper->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $task->fresh()->status);
    }
}
