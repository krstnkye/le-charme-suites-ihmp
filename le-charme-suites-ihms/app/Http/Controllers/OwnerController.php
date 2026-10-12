<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomStatusLog;
use Illuminate\View\View;

class OwnerController extends Controller
{
    public function __invoke(string $page = 'dashboard'): View
    {
        $allowedPages = ['dashboard', 'guests', 'rooms', 'room-inventory', 'room-availability', 'room-status', 'reservations', 'checkins', 'checkin-records', 'checkout-records', 'billing', 'billing-records', 'housekeeping', 'housekeeping-requests', 'reports', 'operational-reports', 'transaction-reports'];
        abort_unless(in_array($page, $allowedPages, true), 404);

        $today = now(config('app.timezone'))->toDateString();

        return view('owner.index', [
            'page' => $page,
            'totalRooms' => Room::count(),
            'availableRooms' => Room::where('status', 'available')->count(),
            'occupiedRooms' => Room::where('status', 'occupied')->count(),
            'reservedRooms' => Room::where('status', 'reserved')->count(),
            'maintenanceRooms' => Room::whereIn('status', ['maintenance', 'cleaning'])->count(),
            'totalGuests' => Guest::count(),
            'currentGuests' => Guest::whereHas('reservations', fn ($query) => $query->where('status', 'checked_in'))->count(),
            'arrivalsToday' => Reservation::where('status', 'reserved')->whereDate('check_in_date', $today)->count(),
            'departuresToday' => Reservation::where('status', 'checked_in')->whereDate('check_out_date', $today)->count(),
            'activeReservations' => Reservation::whereIn('status', ['reserved', 'checked_in'])->count(),
            'settledToday' => CheckOut::whereDate('checked_out_at', $today)->count(),
            'collectedToday' => CheckOut::whereDate('checked_out_at', $today)->sum('amount_paid'),
            'recordedRevenue' => HotelTransaction::whereDate('transaction_date', $today)->sum('amount'),
            'guests' => Guest::withCount('reservations')->withSum('transactions', 'amount')->latest()->take(8)->get(),
            'guestSpendTotal' => HotelTransaction::sum('amount'),
            'identifiedGuests' => Guest::whereNotNull('identification_number')->count(),
            'rooms' => Room::orderBy('room_number')->get(),
            'reservations' => Reservation::with(['guest', 'room'])->latest()->take(8)->get(),
            'checkIns' => CheckIn::with(['guest', 'room', 'reservation'])->latest('checked_in_at')->take(50)->get(),
            'checkOuts' => CheckOut::with(['guest', 'room', 'checkIn.reservation'])->latest('checked_out_at')->take(50)->get(),
            'transactions' => HotelTransaction::with(['guest', 'room', 'reservation'])->latest('transaction_date')->take(50)->get(),
            'housekeepingLogs' => RoomStatusLog::with('room')->latest('changed_at')->take(8)->get(),
        ]);
    }
}
