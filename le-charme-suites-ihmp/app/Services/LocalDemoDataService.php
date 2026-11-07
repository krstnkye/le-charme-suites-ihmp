<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LocalDemoDataService
{
    public function __construct(
        private readonly LocalDemoDatabaseGuard $guard,
        private readonly OfficialRoomInventoryService $rooms,
    ) {}

    /**
     * @return array<string, int>
     */
    public function prepare(string $password): array
    {
        $this->guard->assertSafe();

        if (mb_strlen($password) < 12) {
            throw new RuntimeException('The local demo password must contain at least 12 characters.');
        }

        return DB::transaction(function () use ($password): array {
            $this->rooms->seedMissingRooms();
            $users = $this->prepareUsers($password);

            Room::query()->update(['status' => 'available']);
            $this->prepareHotelRecords($users);

            return [
                'users' => User::count(),
                'rooms' => Room::count(),
                'guests' => Guest::count(),
                'reservations' => Reservation::count(),
                'check_ins' => CheckIn::count(),
                'check_outs' => CheckOut::count(),
                'housekeeping_tasks' => HousekeepingTask::count(),
            ];
        });
    }

    /**
     * @return array<string, User>
     */
    private function prepareUsers(string $password): array
    {
        $users = [];

        foreach ([
            'admin' => ['Hotel Demo Administrator', 'admin@lecharmehotel.com', 'admin'],
            'receptionist' => ['Front Desk Demo Staff', 'receptionist@lecharmehotel.com', 'receptionist'],
            'billing' => ['Billing Demo Staff', 'billing@lecharmehotel.com', 'cashier'],
            'housekeeping' => ['Housekeeping Demo Staff', 'housekeeping@lecharmehotel.com', 'housekeeping'],
        ] as $key => [$name, $email, $role]) {
            $users[$key] = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'role' => $role,
                'password' => $password,
                'approved_at' => now(),
            ]);
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $users
     */
    private function prepareHotelRecords(array $users): void
    {
        $records = [
            [
                'key' => 'pending',
                'guest' => ['Paolo', 'Santos', 'paolo.santos@example.test', '09170000001'],
                'room' => '104',
                'reference' => 'DEMO-PENDING-001',
                'dates' => [today()->addDays(5), today()->addDays(7)],
                'status' => 'pending',
                'source' => 'website',
            ],
            [
                'key' => 'arrival',
                'guest' => ['Ana', 'Reyes', 'ana.reyes@example.test', '09170000002'],
                'room' => '101',
                'reference' => 'DEMO-ARRIVAL-001',
                'dates' => [today(), today()->addDays(2)],
                'status' => 'reserved',
                'source' => 'front_desk',
            ],
            [
                'key' => 'stay',
                'guest' => ['Miguel', 'Cruz', 'miguel.cruz@example.test', '09170000003'],
                'room' => '102',
                'reference' => 'DEMO-STAY-001',
                'dates' => [today()->subDay(), today()->addDay()],
                'status' => 'checked_in',
                'source' => 'front_desk',
            ],
            [
                'key' => 'checkout',
                'guest' => ['Liza', 'Garcia', 'liza.garcia@example.test', '09170000004'],
                'room' => '201',
                'reference' => 'DEMO-CHECKOUT-001',
                'dates' => [today()->subDays(3), today()->subDay()],
                'status' => 'completed',
                'source' => 'front_desk',
            ],
        ];

        foreach ($records as $record) {
            $guest = Guest::updateOrCreate(['email' => $record['guest'][2]], [
                'first_name' => $record['guest'][0],
                'last_name' => $record['guest'][1],
                'phone' => $record['guest'][3],
                'address' => 'Synthetic address for classroom demonstration only',
                'identification_type' => 'Demo ID',
                'identification_number' => 'FICTIONAL-'.strtoupper($record['key']),
                'notes' => 'FICTIONAL DEMO RECORD — not a real hotel guest.',
            ]);
            $room = Room::where('room_number', $record['room'])->firstOrFail();
            $reservation = Reservation::updateOrCreate(['booking_reference' => $record['reference']], [
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'check_in_date' => $record['dates'][0],
                'check_out_date' => $record['dates'][1],
                'number_of_guests' => 2,
                'status' => $record['status'],
                'source' => $record['source'],
                'created_by' => $users['receptionist']->id,
                'confirmed_by' => $record['status'] === 'pending' ? null : $users['receptionist']->id,
                'confirmed_at' => $record['status'] === 'pending' ? null : now()->subDay(),
                'notes' => 'FICTIONAL DEMO RESERVATION.',
            ]);

            if ($record['key'] === 'arrival') {
                $room->update(['status' => 'reserved']);
            }

            if ($record['key'] === 'stay') {
                $room->update(['status' => 'occupied']);
                $checkIn = CheckIn::updateOrCreate(['reservation_id' => $reservation->id], [
                    'guest_id' => $guest->id,
                    'room_id' => $room->id,
                    'checked_in_at' => now()->subDay(),
                    'status' => 'active',
                    'notes' => 'FICTIONAL DEMO CHECK-IN.',
                ]);
                CheckOut::where('check_in_id', $checkIn->id)->delete();
                $this->prepareRoomCharge($guest, $reservation, $room, 'recorded');
            }

            if ($record['key'] === 'checkout') {
                $room->update(['status' => 'cleaning']);
                $checkIn = CheckIn::updateOrCreate(['reservation_id' => $reservation->id], [
                    'guest_id' => $guest->id,
                    'room_id' => $room->id,
                    'checked_in_at' => now()->subDays(3),
                    'status' => 'completed',
                    'notes' => 'FICTIONAL DEMO COMPLETED STAY.',
                ]);
                CheckOut::updateOrCreate(['check_in_id' => $checkIn->id], [
                    'reservation_id' => $reservation->id,
                    'guest_id' => $guest->id,
                    'room_id' => $room->id,
                    'checked_out_at' => now()->subHours(2),
                    'amount_paid' => (float) $room->rate * 2,
                    'payment_status' => 'paid',
                    'notes' => 'FICTIONAL DEMO RECEIPT.',
                ]);
                $this->prepareRoomCharge($guest, $reservation, $room, 'paid');
                HousekeepingTask::updateOrCreate([
                    'room_id' => $room->id,
                    'title' => 'Demo checkout cleaning — Room '.$room->room_number,
                ], [
                    'assigned_to' => $users['housekeeping']->id,
                    'created_by' => $users['receptionist']->id,
                    'task_type' => 'turnover',
                    'instructions' => 'FICTIONAL DEMO TASK. Clean and submit for inspection.',
                    'priority' => 'high',
                    'status' => 'queued',
                    'due_at' => now()->addHours(2),
                    'started_at' => null,
                    'completed_at' => null,
                    'completion_notes' => null,
                ]);
            }
        }
    }

    private function prepareRoomCharge(Guest $guest, Reservation $reservation, Room $room, string $status): void
    {
        HotelTransaction::updateOrCreate([
            'reservation_id' => $reservation->id,
            'transaction_type' => 'room_charge',
        ], [
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'amount' => (float) $room->rate * 2,
            'status' => $status,
            'transaction_date' => now(),
            'description' => 'FICTIONAL DEMO ROOM CHARGE.',
        ]);
    }
}
