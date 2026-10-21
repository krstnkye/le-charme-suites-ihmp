<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\RoomStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomInspectionController extends Controller
{
    /**
     * Record a room inspection. A pass releases a cleaned room back to sale; a fail sends it
     * back to housekeeping with a high-priority re-clean task.
     */
    public function store(Request $request, Room $room): RedirectResponse
    {
        $data = $request->validate([
            'result' => ['required', 'in:pass,fail'],
            'notes' => ['nullable', 'required_if:result,fail', 'string', 'max:1000'],
        ], [
            'notes.required_if' => 'Explain what failed so housekeeping knows what to fix.',
        ]);

        if ($room->status !== 'cleaning') {
            throw ValidationException::withMessages([
                'result' => "Room {$room->room_number} is not waiting for inspection.",
            ]);
        }

        $message = DB::transaction(function () use ($request, $room, $data): string {
            $openTasks = HousekeepingTask::where('room_id', $room->id)->whereNotIn('status', ['completed', 'cancelled']);

            if ($data['result'] === 'pass') {
                if (CheckIn::where('room_id', $room->id)->where('status', 'active')->exists()) {
                    throw ValidationException::withMessages(['result' => "Room {$room->room_number} has an in-house guest."]);
                }

                $openTasks->update(['status' => 'completed', 'completed_at' => now(), 'completion_notes' => 'Closed by passed inspection.']);
                $room->update(['status' => 'available']);
                $this->log($room, 'available', 'Inspection passed by '.$request->user()->name.(filled($data['notes'] ?? null) ? ': '.$data['notes'] : '.'));

                return "Room {$room->room_number} passed inspection and is available for sale.";
            }

            HousekeepingTask::create([
                'room_id' => $room->id,
                'assigned_to' => (clone $openTasks)->latest()->value('assigned_to'),
                'created_by' => $request->user()->id,
                'task_type' => 'turnover',
                'title' => 'Re-clean after failed inspection',
                'instructions' => $data['notes'],
                'priority' => 'high',
                'status' => 'queued',
                'due_at' => now()->addHour(),
            ]);
            $this->log($room, 'cleaning', 'Inspection failed: '.$data['notes']);

            return "Room {$room->room_number} failed inspection; a re-clean task was created.";
        });

        return back()->with('status', $message);
    }

    private function log(Room $room, string $status, string $notes): void
    {
        RoomStatusLog::create(['room_id' => $room->id, 'status' => $status, 'notes' => $notes, 'changed_at' => now()]);
    }
}
