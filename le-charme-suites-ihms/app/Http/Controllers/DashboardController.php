<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $data = [
            'totalRooms' => Room::count(),
            'publishedRoomCount' => (int) config('hotel.published_room_count', Room::count()),
            'officialRoomTypeCount' => count(config('hotel.room_types', [])),
            'availableRooms' => Room::where('status', 'available')->count(),
            'occupiedRooms' => Room::where('status', 'occupied')->count(),
            'reservedRooms' => Room::where('status', 'reserved')->count(),
            'maintenanceRooms' => Room::whereIn('status', ['maintenance', 'cleaning'])->count(),
            'activeReservations' => Reservation::whereIn('status', ['reserved', 'checked_in'])->count(),
            'currentGuests' => Guest::whereHas('reservations', fn ($query) => $query->where('status', 'checked_in'))->count(),
            'arrivalsToday' => Reservation::where('status', 'reserved')->whereDate('check_in_date', now(config('app.timezone'))->toDateString())->count(),
            'departuresToday' => Reservation::where('status', 'checked_in')->whereDate('check_out_date', now(config('app.timezone'))->toDateString())->count(),
            'pendingCheckIns' => Reservation::whereIn('status', ['pending', 'reserved'])->count(),
            'recentTransactions' => HotelTransaction::with(['guest', 'room'])->latest('transaction_date')->take(5)->get(),
            'activeReservationsList' => Reservation::with(['guest', 'room'])
                ->whereIn('status', ['pending', 'reserved', 'checked_in'])
                ->latest()
                ->take(8)
                ->get(),
            'roomMatrix' => Room::orderBy('room_number')->get()->groupBy(fn (Room $room) => substr($room->room_number, 0, 1)),
            'totalUsers' => User::count(),
            'roleCounts' => User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ];

        if (auth()->user()?->role === 'admin') {
            return view('dashboard.admin', $data);
        }

        if (auth()->user()?->role === 'receptionist') {
            return view('dashboard.front-desk', $data);
        }

        if (auth()->user()?->role === 'housekeeping') {
            return view('dashboard.housekeeping', [
                ...$data,
                'cleaningRooms' => Room::where('status', 'cleaning')->orderBy('room_number')->get(),
                'maintenanceQueue' => Room::where('status', 'maintenance')->orderBy('room_number')->get(),
                'assignedRooms' => Room::whereIn('status', ['cleaning', 'maintenance', 'occupied'])
                    ->orderByRaw("CASE status WHEN 'cleaning' THEN 1 WHEN 'maintenance' THEN 2 WHEN 'occupied' THEN 3 ELSE 4 END")
                    ->orderBy('room_number')
                    ->take(12)
                    ->get(),
                'inspectionRooms' => Room::whereIn('status', ['cleaning', 'maintenance'])
                    ->orderBy('room_number')
                    ->take(8)
                    ->get(),
                'priorityRooms' => Room::whereIn('status', ['maintenance', 'cleaning'])
                    ->orderByRaw("CASE status WHEN 'maintenance' THEN 1 WHEN 'cleaning' THEN 2 ELSE 3 END")
                    ->orderBy('room_number')
                    ->take(8)
                    ->get(),
                'completedTasks' => RoomStatusLog::with('room')
                    ->where('status', 'available')
                    ->whereDate('changed_at', now(config('app.timezone'))->toDateString())
                    ->latest('changed_at')
                    ->take(8)
                    ->get(),
                'guestRequestRooms' => Room::where('status', 'occupied')
                    ->orderBy('room_number')
                    ->take(8)
                    ->get(),
                'recentRoomLogs' => RoomStatusLog::with('room')
                    ->latest('changed_at')
                    ->take(10)
                    ->get(),
            ]);
        }

        if (auth()->user()?->role === 'cashier') {
            $today = now(config('app.timezone'))->toDateString();
            $activeCheckIns = CheckIn::with(['guest', 'room', 'reservation'])
                ->where('status', 'active')
                ->latest('checked_in_at')
                ->get();
            $completedToday = CheckOut::with(['guest', 'room'])
                ->whereDate('checked_out_at', $today)
                ->latest('checked_out_at')
                ->get();
            $folioActivity = HotelTransaction::with(['guest', 'room', 'reservation'])
                ->latest('transaction_date')
                ->take(8)
                ->get();
            $additionalCharges = HotelTransaction::query()
                ->whereIn('reservation_id', $activeCheckIns->pluck('reservation_id'))
                ->whereNotIn('transaction_type', ['reservation', 'booking_confirmation', 'check_in', 'check_out'])
                ->where('status', '!=', 'void')
                ->where('amount', '>', 0)
                ->selectRaw('reservation_id, SUM(amount) as total')
                ->groupBy('reservation_id')
                ->pluck('total', 'reservation_id');
            $activeBillAmounts = $activeCheckIns->mapWithKeys(function (CheckIn $checkIn) use ($additionalCharges) {
                $reservation = $checkIn->reservation;
                $nights = $reservation?->check_in_date && $reservation?->check_out_date
                    ? max(1, (int) $reservation->check_in_date->diffInDays($reservation->check_out_date))
                    : 1;
                $roomCharges = round((float) ($checkIn->room?->rate ?? 0) * $nights, 2);
                $additional = round((float) ($additionalCharges[$checkIn->reservation_id] ?? 0), 2);

                return [$checkIn->id => [
                    'nights' => $nights,
                    'room_charges' => $roomCharges,
                    'additional_charges' => $additional,
                    'total' => round($roomCharges + $additional, 2),
                ]];
            });

            return view('dashboard.billing', [
                ...$data,
                'activeCheckIns' => $activeCheckIns,
                'completedToday' => $completedToday,
                'folioActivity' => $folioActivity,
                'activeBillAmounts' => $activeBillAmounts,
                'settlementQueue' => $activeCheckIns->take(6),
                'pendingDepartures' => $activeCheckIns->count(),
                'departuresToday' => $activeCheckIns
                    ->filter(fn (CheckIn $checkIn) => $checkIn->reservation?->check_out_date?->toDateString() === $today)
                    ->count(),
                'settledToday' => $completedToday->count(),
                'collectedToday' => $completedToday->sum('amount_paid'),
                'recordedRevenue' => HotelTransaction::whereDate('transaction_date', $today)->sum('amount'),
                'unpaidFolios' => CheckOut::where('payment_status', 'unpaid')->count(),
                'partialFolios' => CheckOut::where('payment_status', 'partial')->count(),
                'paidToday' => $completedToday->where('payment_status', 'paid')->count(),
                'partialToday' => $completedToday->where('payment_status', 'partial')->count(),
                'unpaidToday' => $completedToday->where('payment_status', 'unpaid')->count(),
            ]);
        }

        abort(403, 'This account does not have a recognized department dashboard. Contact the system administrator.');
    }
}
