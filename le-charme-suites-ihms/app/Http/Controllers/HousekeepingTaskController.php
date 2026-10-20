<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\RoomStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        DB::transaction(function () use ($data, $housekeepingTask): void {
            if ($data['status'] === 'in_progress' && $housekeepingTask->started_at === null) {
                $data['started_at'] = now();
            }

            if ($data['status'] === 'completed') {
                $data['completed_at'] = now();
                $housekeepingTask->room()->update(['status' => 'available']);
                RoomStatusLog::create([
                    'room_id' => $housekeepingTask->room_id,
                    'status' => 'available',
                    'notes' => 'Housekeeping task #'.$housekeepingTask->id.' completed: '.($data['completion_notes'] ?? $housekeepingTask->title),
                    'changed_at' => now(),
                ]);
            }

            $housekeepingTask->update($data);
        });

        return back()->with('status', 'Housekeeping task updated.');
    }
}
