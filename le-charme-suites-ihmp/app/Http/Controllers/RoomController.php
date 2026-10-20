<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->string('status');
        $type = (string) $request->string('type');
        $search = (string) $request->string('search');

        $rooms = Room::query()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('room_type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('room_number', 'like', '%'.$search.'%')
                        ->orWhere('room_type', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('room_number')
            ->paginate(12)
            ->withQueryString();

        $allRooms = Room::orderBy('room_number')->get();

        $view = $request->user()?->role === 'housekeeping'
            ? 'rooms.housekeeping-index'
            : 'rooms.index';

        return view($view, [
            'rooms' => $rooms,
            'status' => $status,
            'type' => $type,
            'search' => $search,
            'roomTypes' => Room::select('room_type')->distinct()->orderBy('room_type')->pluck('room_type'),
            'statusCounts' => Room::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'totalRooms' => $allRooms->count(),
            'publishedRoomCount' => (int) config('hotel.published_room_count', $allRooms->count()),
            'officialRoomTypeCount' => count(config('hotel.room_types', [])),
            'availableRooms' => $allRooms->where('status', 'available')->count(),
            'reservedRooms' => $allRooms->where('status', 'reserved')->count(),
            'occupiedRooms' => $allRooms->where('status', 'occupied')->count(),
            'maintenanceRooms' => $allRooms->whereIn('status', ['maintenance', 'cleaning'])->count(),
            'availableRecommendations' => $allRooms->where('status', 'available')->take(3),
            'roomMatrix' => $allRooms->groupBy(fn (Room $room) => substr($room->room_number, 0, 1)),
        ]);
    }

    public function create(): View
    {
        $officialRoomTypes = collect(config('hotel.room_types', []));

        return view('rooms.create', [
            'roomTypes' => collect($officialRoomTypes->keys())
                ->merge(Room::select('room_type')->distinct()->orderBy('room_type')->pluck('room_type'))
                ->unique()
                ->values(),
            'officialRoomTypes' => $officialRoomTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $room = Room::create($this->validatedData($request));
        $this->recordStatus($room, $room->status, 'Initial room status');

        $redirectTo = (string) $request->string('redirect_to');

        if ($redirectTo === 'admin.room-types') {
            return redirect()
                ->route('admin.operations.index', ['module' => 'room-types'])
                ->with('status', "Room {$room->room_number} was created under the {$room->room_type} type.");
        }

        if ($redirectTo === 'admin.room-rates') {
            return redirect()
                ->route('admin.operations.index', ['module' => 'room-rates'])
                ->with('status', "Room {$room->room_number} was created and is now reflected in room rates.");
        }

        if ($request->user()?->role === 'admin') {
            return redirect()
                ->route('admin.operations.index', ['module' => 'room-inventory'])
                ->with('status', "Room {$room->room_number} was created.");
        }

        return redirect()->route('rooms.index')->with('status', 'Room created.');
    }

    public function show(Room $room): View
    {
        $room->load([
            'reservations.guest',
            'statusLogs' => fn ($query) => $query->latest('changed_at'),
            'housekeepingTasks' => fn ($query) => $query
                ->with('assignee')
                ->latest(),
        ]);

        if (request()->user()?->role === 'housekeeping') {
            return view('rooms.housekeeping-show', [
                'room' => $room,
                'housekeepingStaff' => User::where('role', 'housekeeping')->orderBy('name')->get(),
            ]);
        }

        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room): View
    {
        if (request()->user()?->role === 'housekeeping') {
            return view('rooms.housekeeping-edit', compact('room'));
        }

        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $data = $this->validatedData($request, $room);
        $oldStatus = $room->status;

        $hasActiveStay = $room->checkIns()->where('status', 'active')->exists();

        if ($hasActiveStay && $data['status'] !== 'occupied') {
            return back()->withInput()->withErrors([
                'status' => 'This room has an in-house guest. Check the guest out before changing the room status.',
            ]);
        }

        if (! $hasActiveStay && $data['status'] === 'occupied') {
            return back()->withInput()->withErrors([
                'status' => 'A room becomes occupied only through guest check-in.',
            ]);
        }

        $room->update($data);

        if ($oldStatus !== $room->status) {
            $this->recordStatus($room, $room->status, 'Manual room status update');
        }

        return redirect()->route('rooms.index')->with('status', 'Room updated.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->reservations()->exists() || $room->checkIns()->exists()) {
            return back()->withErrors([
                'room' => "Room {$room->room_number} has booking history and cannot be deleted. Set it to maintenance instead.",
            ]);
        }

        $room->delete();

        return redirect()->route('rooms.index')->with('status', 'Room deleted.');
    }

    private function validatedData(Request $request, ?Room $room = null): array
    {
        $roomId = $room?->id ?? 'NULL';

        $data = $request->validate([
            'room_number' => ['required', 'string', 'max:50', 'unique:rooms,room_number,'.$roomId],
            'room_type' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:available,reserved,occupied,cleaning,maintenance'],
            'description' => ['nullable', 'string'],
        ]);

        $officialSpecification = config('hotel.room_types.'.$data['room_type']);

        if (is_array($officialSpecification)) {
            $data = [
                ...$data,
                'rate' => $officialSpecification['rate'],
                'base_occupancy' => $officialSpecification['base_occupancy'],
                'room_size_sqm' => $officialSpecification['size_sqm'],
                'bed_configuration' => $officialSpecification['bed_configuration'],
                'bathrooms' => $officialSpecification['bathrooms'],
                'extra_guest_rate' => config('hotel.extra_guest_rate'),
                'amenities' => $officialSpecification['amenities'],
                'non_smoking' => true,
            ];
        }

        return $data;
    }

    private function recordStatus(Room $room, string $status, string $notes): void
    {
        RoomStatusLog::create([
            'room_id' => $room->id,
            'status' => $status,
            'notes' => $notes,
            'changed_at' => now(),
        ]);
    }
}
