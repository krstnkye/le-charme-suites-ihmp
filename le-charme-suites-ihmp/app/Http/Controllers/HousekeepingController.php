<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\User;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HousekeepingController extends Controller
{
    private const PAGES = [
        'assigned-rooms' => [
            'active' => 'assigned-rooms',
            'title' => 'My Assigned Rooms',
            'subtitle' => 'Rooms needing cleaning, inspection, occupied-room service, or maintenance follow-up.',
            'icon' => 'assignment_ind',
            'group' => 'Rooms',
        ],
        'room-inspection' => [
            'active' => 'room-inspection',
            'title' => 'Room Inspection',
            'subtitle' => 'Rooms that must be checked before returning to available inventory.',
            'icon' => 'fact_check',
            'group' => 'Rooms',
        ],
        'tasks' => [
            'active' => 'tasks',
            'title' => 'Housekeeping Tasks',
            'subtitle' => 'Daily cleaning, priority, and completed turnover work.',
            'icon' => 'task_alt',
            'group' => 'Housekeeping Tasks',
        ],
        'cleaning-tasks' => [
            'active' => 'cleaning-tasks',
            'title' => 'Cleaning Tasks',
            'subtitle' => 'Rooms currently marked for cleaning and turnover.',
            'icon' => 'mop',
            'group' => 'Housekeeping Tasks',
        ],
        'priority-rooms' => [
            'active' => 'priority-rooms',
            'title' => 'Priority Rooms',
            'subtitle' => 'Maintenance and cleaning rooms that need first attention.',
            'icon' => 'priority_high',
            'group' => 'Housekeeping Tasks',
        ],
        'completed-tasks' => [
            'active' => 'completed-tasks',
            'title' => 'Completed Tasks',
            'subtitle' => 'Recent room status work completed by the housekeeping workflow.',
            'icon' => 'done_all',
            'group' => 'Housekeeping Tasks',
        ],
        'service-requests' => [
            'active' => 'service-requests',
            'title' => 'Service Requests',
            'subtitle' => 'Guest service and special cleaning items handled by housekeeping.',
            'icon' => 'room_service',
            'group' => 'Service Requests',
        ],
        'guest-requests' => [
            'active' => 'guest-requests',
            'title' => 'Guest Requests',
            'subtitle' => 'Occupied rooms available for amenity, linen, or service follow-up.',
            'icon' => 'support_agent',
            'group' => 'Service Requests',
        ],
        'special-cleaning' => [
            'active' => 'special-cleaning',
            'title' => 'Special Cleaning',
            'subtitle' => 'Rooms requiring extra care because of cleaning or maintenance status.',
            'icon' => 'cleaning_services',
            'group' => 'Service Requests',
        ],
        'maintenance-requests' => [
            'active' => 'maintenance',
            'title' => 'Maintenance Requests',
            'subtitle' => 'Rooms reported for maintenance and operational follow-up.',
            'icon' => 'construction',
            'group' => 'Maintenance Requests',
        ],
        'report-issue' => [
            'active' => 'report-issue',
            'title' => 'Report Issue',
            'subtitle' => 'Open a room and mark it for maintenance with notes for the next action.',
            'icon' => 'report',
            'group' => 'Maintenance Requests',
        ],
        'reported-issues' => [
            'active' => 'reported-issues',
            'title' => 'Reported Issues',
            'subtitle' => 'Maintenance rooms currently waiting for review or resolution.',
            'icon' => 'plumbing',
            'group' => 'Maintenance Requests',
        ],
        'lost-found' => [
            'active' => 'lost-found',
            'title' => 'Lost & Found',
            'subtitle' => 'Recovered-item tracking for housekeeping discoveries.',
            'icon' => 'travel_explore',
            'group' => 'Records',
        ],
        'reports' => [
            'active' => 'reports',
            'title' => 'Housekeeping Reports',
            'subtitle' => 'Readiness, turnover, and maintenance summaries for housekeeping.',
            'icon' => 'monitoring',
            'group' => 'Reports',
        ],
        'notifications' => [
            'active' => 'notifications',
            'title' => 'Notifications',
            'subtitle' => 'Recent room updates and housekeeping status changes.',
            'icon' => 'notifications',
            'group' => 'Reports',
        ],
    ];

    public function __invoke(Request $request, string $page): View
    {
        abort_unless(array_key_exists($page, self::PAGES), 404);

        return view('housekeeping.page', [
            ...$this->data(),
            'pageKey' => $page,
            'page' => self::PAGES[$page],
        ]);
    }

    private function data(): array
    {
        $allRooms = Room::orderBy('room_number')->get();
        $availability = app(RoomAvailabilityService::class)->summary();
        $cleaningRooms = $allRooms->where('status', 'cleaning')->values();
        $maintenanceQueue = $allRooms->where('status', 'maintenance')->values();
        $priorityRooms = $allRooms
            ->whereIn('status', ['maintenance', 'cleaning'])
            ->sortBy(fn (Room $room) => ($room->status === 'maintenance' ? '0' : '1').$room->room_number)
            ->values();

        return [
            'allRooms' => $allRooms,
            'totalRooms' => $allRooms->count(),
            'publishedRoomCount' => (int) config('hotel.published_room_count', $allRooms->count()),
            'roomAvailability' => $availability,
            'availableRooms' => $availability['available'],
            'reservedRooms' => $availability['reserved'],
            'occupiedRooms' => $availability['occupied'],
            'cleaningRooms' => $cleaningRooms,
            'maintenanceQueue' => $maintenanceQueue,
            'assignedRooms' => $allRooms
                ->whereIn('status', ['cleaning', 'maintenance', 'occupied'])
                ->sortBy(fn (Room $room) => match ($room->status) {
                    'cleaning' => '0'.$room->room_number,
                    'maintenance' => '1'.$room->room_number,
                    default => '2'.$room->room_number,
                })
                ->values(),
            'inspectionRooms' => $priorityRooms,
            'priorityRooms' => $priorityRooms,
            'guestRequestRooms' => $allRooms->where('status', 'occupied')->values(),
            'completedTasks' => RoomStatusLog::with('room')
                ->where('status', 'available')
                ->whereDate('changed_at', now(config('app.timezone'))->toDateString())
                ->latest('changed_at')
                ->get(),
            'recentRoomLogs' => RoomStatusLog::with('room')
                ->latest('changed_at')
                ->take(20)
                ->get(),
            'housekeepingTasks' => HousekeepingTask::with(['room', 'assignee', 'creator'])
                ->latest()
                ->get(),
            'housekeepingStaff' => User::where('role', 'housekeeping')->orderBy('name')->get(),
        ];
    }
}
