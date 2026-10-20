<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Collection;

class RoomAvailabilityService
{
    /**
     * Place every room in exactly one bucket so the counts always add up to the room total.
     *
     * - occupied: a guest is checked in
     * - out_of_service: being cleaned or under maintenance
     * - reserved: a confirmed booking is due to arrive today (or is overdue), or staff marked it reserved
     * - available: ready to sell right now
     *
     * @return array{property_total: int, encoded: int, available: int, reserved: int, occupied: int, out_of_service: int, by_type: Collection<string, array{total: int, available: int, reserved: int, occupied: int, out_of_service: int}>}
     */
    public function summary(): array
    {
        $rooms = Room::query()->get(['id', 'room_type', 'status']);
        $roomsWithGuests = CheckIn::where('status', 'active')->pluck('room_id')->flip();
        $roomsAwaitingArrival = Reservation::query()
            ->where('status', 'reserved')
            ->whereDate('check_in_date', '<=', today())
            ->pluck('room_id')
            ->flip();

        $bucketed = $rooms->map(fn (Room $room): array => [
            'room_type' => $room->room_type,
            'bucket' => match (true) {
                $room->status === 'occupied' || $roomsWithGuests->has($room->id) => 'occupied',
                in_array($room->status, ['cleaning', 'maintenance'], true) => 'out_of_service',
                $room->status === 'reserved' || $roomsAwaitingArrival->has($room->id) => 'reserved',
                default => 'available',
            },
        ]);

        $count = fn (Collection $rooms, string $bucket): int => $rooms->where('bucket', $bucket)->count();

        return [
            'property_total' => (int) config('hotel.published_room_count', $rooms->count()),
            'encoded' => $rooms->count(),
            'available' => $count($bucketed, 'available'),
            'reserved' => $count($bucketed, 'reserved'),
            'occupied' => $count($bucketed, 'occupied'),
            'out_of_service' => $count($bucketed, 'out_of_service'),
            'by_type' => $bucketed->groupBy('room_type')->map(fn (Collection $rooms): array => [
                'total' => $rooms->count(),
                'available' => $count($rooms, 'available'),
                'reserved' => $count($rooms, 'reserved'),
                'occupied' => $count($rooms, 'occupied'),
                'out_of_service' => $count($rooms, 'out_of_service'),
            ]),
        ];
    }
}
