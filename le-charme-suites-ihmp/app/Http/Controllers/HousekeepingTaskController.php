<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HousekeepingTaskController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'housekeeping')),
            ],
            'task_type' => ['required', 'in:turnover,stayover,inspection,guest_request,deep_cleaning,maintenance_follow_up'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'due_at' => ['nullable', 'date'],
        ]);

        if ($request->user()->role === 'housekeeping' && empty($data['assigned_to'])) {
            $data['assigned_to'] = $request->user()->id;
        }

        HousekeepingTask::create($data + [
            'created_by' => $request->user()->id,
            'status' => 'queued',
        ]);

        return back()->with('status', 'Housekeeping task created and shared with the department.');
    }

    public function update(Request $request, HousekeepingTask $housekeepingTask): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:queued,in_progress,inspection,completed,cancelled'],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'housekeeping')),
            ],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! array_key_exists('assigned_to', $data)) {
            $data['assigned_to'] = $housekeepingTask->assigned_to;
        }

        $this->ensureStatusChangeIsAllowed($request->user(), $housekeepingTask, $data['status']);

        DB::transaction(function () use ($data, $housekeepingTask): void {
            if ($data['status'] === 'in_progress' && $housekeepingTask->started_at === null) {
                $data['started_at'] = now();
            }

            if ($data['status'] === 'completed' && $housekeepingTask->status !== 'completed') {
                $data['completed_at'] = now();
                $housekeepingTask->update($data);
                $this->releaseRoomIfReady($housekeepingTask);

                return;
            }

            $housekeepingTask->update($data);
        });

        return back()->with('status', 'Housekeeping task updated.');
    }

    /**
     * Enforce the task workflow and who may move a task through it.
     *
     * @throws ValidationException
     */
    private function ensureStatusChangeIsAllowed(User $user, HousekeepingTask $task, string $status): void
    {
        $message = match (true) {
            ! $task->canMoveTo($status) => in_array($task->status, ['completed', 'cancelled'], true)
                ? 'This task is already '.$task->status.' and cannot be changed. Create a new task instead.'
                : 'A '.str_replace('_', ' ', $task->status).' task cannot be moved to '.str_replace('_', ' ', $status).'.',
            $status !== $task->status
                && in_array($status, HousekeepingTask::HOUSEKEEPING_ONLY_STATUSES, true)
                && ! in_array($user->role, ['housekeeping', 'admin'], true) => 'Only housekeeping staff can start, inspect, or complete cleaning tasks.',
            $status === 'completed'
                && $task->status !== 'completed'
                && $task->requiresInspection()
                && $task->status !== 'inspection' => 'Send this task for inspection before marking it complete.',
            default => null,
        };

        if ($message) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    /**
     * Put a cleaned room back on sale once its last blocking task is finished.
     *
     * Only a completed turnover, inspection, or deep cleaning releases a room,
     * and only when the room is waiting for cleaning, has no guest, and has no
     * other open cleaning or maintenance task. Maintenance rooms stay blocked.
     */
    private function releaseRoomIfReady(HousekeepingTask $task): void
    {
        if (! in_array($task->task_type, HousekeepingTask::ROOM_RELEASING_TYPES, true)) {
            return;
        }

        $room = Room::whereKey($task->room_id)->lockForUpdate()->first();

        if ($room === null || $room->status !== 'cleaning') {
            return;
        }

        $roomHasGuest = CheckIn::query()
            ->where('room_id', $room->id)
            ->where('status', 'active')
            ->exists();

        $roomHasOtherOpenWork = HousekeepingTask::query()
            ->where('room_id', $room->id)
            ->whereKeyNot($task->id)
            ->whereIn('task_type', HousekeepingTask::ROOM_BLOCKING_TYPES)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($roomHasGuest || $roomHasOtherOpenWork) {
            return;
        }

        $room->update(['status' => 'available']);
        RoomStatusLog::create([
            'room_id' => $room->id,
            'status' => 'available',
            'notes' => 'Housekeeping task #'.$task->id.' completed: '.($task->completion_notes ?? $task->title),
            'changed_at' => now(),
        ]);
    }
}
