<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\CheckIn;
use App\Models\CheckOut;
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
            'availableRooms' => Room::where('status', 'available')->count(),
            'occupiedRooms' => Room::where('status', 'occupied')->count(),
            'reservedRooms' => Room::where('status', 'reserved')->count(),
            'maintenanceRooms' => Room::whereIn('status', ['maintenance', 'cleaning'])->count(),
            'activeReservations' => Reservation::whereIn('status', ['reserved', 'checked_in'])->count(),
            'currentGuests' => Guest::whereHas('reservations', fn ($query) => $query->where('status', 'checked_in'))->count(),
            'arrivalsToday' => Reservation::where('status', 'reserved')->whereDate('check_in_date', now(config('app.timezone'))->toDateString())->count(),
            'departuresToday' => Reservation::where('status', 'checked_in')->whereDate('check_out_date', now(config('app.timezone'))->toDateString())->count(),
            'pendingCheckIns' => Reservation::where('status', 'reserved')->count(),
            'recentTransactions' => HotelTransaction::with(['guest', 'room'])->latest('transaction_date')->take(5)->get(),
            'activeReservationsList' => Reservation::with(['guest', 'room'])->latest()->take(8)->get(),
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

            return view('dashboard.billing', [
                ...$data,
                'activeCheckIns' => $activeCheckIns,
                'completedToday' => $completedToday,
                'folioActivity' => $folioActivity,
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
            ]);
        }

        if (auth()->user()?->role === 'owner') {
            return view('owner.index', [
                ...$data,
                'page' => 'dashboard',
                'totalGuests' => Guest::count(),
                'settledToday' => CheckOut::whereDate('checked_out_at', now(config('app.timezone'))->toDateString())->count(),
                'collectedToday' => CheckOut::whereDate('checked_out_at', now(config('app.timezone'))->toDateString())->sum('amount_paid'),
                'recordedRevenue' => HotelTransaction::whereDate('transaction_date', now(config('app.timezone'))->toDateString())->sum('amount'),
                'guests' => Guest::withCount('reservations')->withSum('transactions', 'amount')->latest()->take(8)->get(),
                'guestSpendTotal' => HotelTransaction::sum('amount'),
                'identifiedGuests' => Guest::whereNotNull('identification_number')->count(),
                'rooms' => Room::orderBy('room_number')->take(12)->get(),
                'reservations' => Reservation::with(['guest', 'room'])->latest()->take(8)->get(),
                'checkIns' => CheckIn::with(['guest', 'room', 'reservation'])->latest('checked_in_at')->take(8)->get(),
                'checkOuts' => CheckOut::with(['guest', 'room'])->latest('checked_out_at')->take(8)->get(),
                'transactions' => HotelTransaction::with(['guest', 'room'])->latest('transaction_date')->take(8)->get(),
                'housekeepingLogs' => RoomStatusLog::with('room')->latest('changed_at')->take(8)->get(),
            ]);
        }

        return view('dashboard.index', $data);
    }
}
