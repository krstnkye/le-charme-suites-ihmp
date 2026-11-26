<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\HousekeepingTask;
use App\Models\HousekeepingTaskStatusLog;
use App\Models\RoomStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HousekeepingTaskController extends Controller
{
    /** @var array<string, list<string>> */
    private const STATUS_TRANSITIONS = [
        'queued' => ['in_progress', 'cancelled'],
        'in_progress' => ['inspection', 'cancelled'],
        'inspection' => ['in_progress', 'completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

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
            'status' => ['required', Rule::in(self::STATUS_TRANSITIONS[$housekeepingTask->status] ?? [])],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'housekeeping')),
            ],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! array_key_exists('assigned_to', $data)) {
            $data['assigned_to'] = $housekeepingTask->assigned_to;
        }

        DB::transaction(function () use ($data, $housekeepingTask, $request): void {
            $task = HousekeepingTask::with('room')->lockForUpdate()->findOrFail($housekeepingTask->id);
            $fromStatus = $task->status;

            if ($data['status'] === 'in_progress' && $task->started_at === null) {
                $data['started_at'] = now();
            }

            $roomHasGuest = CheckIn::query()
                ->where('room_id', $task->room_id)
                ->where('status', 'active')
                ->exists();

            $hasBlockingTasks = HousekeepingTask::query()
                ->where('room_id', $task->room_id)
                ->whereKeyNot($task->id)
                ->whereIn('status', ['queued', 'in_progress', 'inspection'])
                ->whereIn('task_type', ['turnover', 'inspection', 'deep_cleaning', 'maintenance_follow_up'])
                ->exists();
            $canReleaseRoom = in_array($task->task_type, ['turnover', 'inspection', 'deep_cleaning'], true)
                && ! $roomHasGuest
                && ! $hasBlockingTasks
                && $task->room->status !== 'maintenance';

            if ($data['status'] === 'completed' && $canReleaseRoom) {
                $data['completed_at'] = now();
                $task->room()->update(['status' => 'available']);
                RoomStatusLog::create([
                    'room_id' => $task->room_id,
                    'status' => 'available',
                    'notes' => 'Housekeeping task #'.$task->id.' completed: '.($data['completion_notes'] ?? $task->title),
                    'changed_at' => now(),
                ]);
            } elseif ($data['status'] === 'completed' && $task->completed_at === null) {
                $data['completed_at'] = now();
            }

            $task->update($data);
            HousekeepingTaskStatusLog::create([
                'housekeeping_task_id' => $task->id,
                'changed_by' => $request->user()->id,
                'from_status' => $fromStatus,
                'to_status' => $data['status'],
                'notes' => $data['completion_notes'] ?? null,
                'changed_at' => now(),
            ]);
        });

        return back()->with('status', 'Housekeeping task updated.');
    }
}
