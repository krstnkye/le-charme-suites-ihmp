<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\MaintenanceReport;
use App\Models\Room;
use App\Models\RoomStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MaintenanceReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'room_id' => ['nullable', 'exists:rooms,id'],
            'location' => ['nullable', 'required_without:room_id', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(MaintenanceReport::CATEGORIES))],
            'severity' => ['required', Rule::in(array_keys(MaintenanceReport::SEVERITIES))],
            'description' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'take_out_of_service' => ['nullable', 'boolean'],
        ], [
            'location.required_without' => 'Choose a room or describe where the problem is.',
        ]);

        $message = DB::transaction(function () use ($request, $data): string {
            $room = isset($data['room_id']) ? Room::lockForUpdate()->find($data['room_id']) : null;
            $roomHasGuest = $room && CheckIn::where('room_id', $room->id)->where('status', 'active')->exists();
            $takeOutOfService = $room && $request->boolean('take_out_of_service') && ! $roomHasGuest;

            MaintenanceReport::create([
                'room_id' => $room?->id,
                'location' => $room ? null : $data['location'],
                'category' => $data['category'],
                'severity' => $data['severity'],
                'description' => $data['description'],
                'photo_path' => $request->file('photo')?->store('maintenance-photos', 'local'),
                'status' => 'open',
                'room_taken_out_of_service' => $takeOutOfService,
                'reported_by' => $request->user()->id,
            ]);

            if ($takeOutOfService) {
                $room->update(['status' => 'maintenance']);
                RoomStatusLog::create([
                    'room_id' => $room->id,
                    'status' => 'maintenance',
                    'notes' => 'Taken out of service: '.MaintenanceReport::CATEGORIES[$data['category']].' issue reported.',
                    'changed_at' => now(),
                ]);

                return "Issue reported. Room {$room->room_number} is now out of service until it is resolved.";
            }

            if ($request->boolean('take_out_of_service') && $roomHasGuest) {
                return "Issue reported. Room {$room->room_number} stays occupied because a guest is checked in.";
            }

            return 'Issue reported and added to the maintenance list.';
        });

        return to_route('housekeeping.page', 'reported-issues')->with('status', $message);
    }

    public function update(Request $request, MaintenanceReport $maintenanceReport): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(MaintenanceReport::STATUSES))],
            'resolution_notes' => ['nullable', 'required_if:status,resolved', 'string', 'max:2000'],
        ], [
            'resolution_notes.required_if' => 'Describe how the issue was fixed before marking it resolved.',
        ]);

        DB::transaction(function () use ($request, $data, $maintenanceReport): void {
            $wasOpen = $maintenanceReport->isOpen();
            $isResolving = $data['status'] === 'resolved' && $wasOpen;

            $maintenanceReport->update([
                'status' => $data['status'],
                'resolution_notes' => $data['resolution_notes'] ?? $maintenanceReport->resolution_notes,
                'resolved_by' => $data['status'] === 'resolved' ? ($maintenanceReport->resolved_by ?? $request->user()->id) : null,
                'resolved_at' => $data['status'] === 'resolved' ? ($maintenanceReport->resolved_at ?? now()) : null,
            ]);

            $room = $maintenanceReport->room;
            $otherReportsHoldRoom = $room && MaintenanceReport::where('room_id', $room->id)
                ->whereKeyNot($maintenanceReport->id)
                ->where('status', '!=', 'resolved')
                ->where('room_taken_out_of_service', true)
                ->exists();

            if ($isResolving && $maintenanceReport->room_taken_out_of_service && $room?->status === 'maintenance' && ! $otherReportsHoldRoom) {
                $room->update(['status' => 'cleaning']);
                RoomStatusLog::create([
                    'room_id' => $room->id,
                    'status' => 'cleaning',
                    'notes' => 'Maintenance resolved; room needs cleaning before it is sold again.',
                    'changed_at' => now(),
                ]);
            }
        });

        return back()->with('status', 'Maintenance report updated.');
    }

    public function photo(MaintenanceReport $maintenanceReport): BinaryFileResponse
    {
        $path = $maintenanceReport->photo_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
