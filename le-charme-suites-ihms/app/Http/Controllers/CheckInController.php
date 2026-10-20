<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function index(): View
    {
        $today = now(config('app.timezone'))->toDateString();

        return view('check_ins.index', [
            'checkIns' => CheckIn::with(['guest', 'room', 'reservation'])->latest('checked_in_at')->paginate(10),
            'todayArrivals' => Reservation::with(['guest', 'room'])
                ->where('status', 'reserved')
                ->whereDate('check_in_date', $today)
                ->orderBy('check_in_date')
                ->get(),
            'arrivalCount' => Reservation::where('status', 'reserved')->whereDate('check_in_date', $today)->count(),
            'queueCount' => Reservation::where('status', 'reserved')->count(),
            'activeCount' => CheckIn::where('status', 'active')->count(),
            'completedToday' => CheckIn::whereDate('checked_in_at', $today)->count(),
            'availableRooms' => Room::where('status', 'available')->count(),
        ]);
    }

    public function create(): View
    {
        return view('check_ins.create', [
            'reservations' => Reservation::with(['guest', 'room'])
                ->where('status', 'reserved')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $reservation = Reservation::with('room')->lockForUpdate()->findOrFail($data['reservation_id']);

            abort_if($reservation->status !== 'reserved', 422, 'Only reserved reservations can be checked in.');

            if ($reservation->check_in_date->isAfter(today())) {
                throw ValidationException::withMessages([
                    'reservation_id' => 'This guest is scheduled to arrive on '.$reservation->check_in_date->format('M j, Y').' and cannot be checked in early.',
                ]);
            }

            $roomIsTaken = CheckIn::query()
                ->where('room_id', $reservation->room_id)
                ->where('status', 'active')
                ->exists();

            if ($roomIsTaken || in_array($reservation->room->status, ['occupied', 'maintenance'], true)) {
                throw ValidationException::withMessages([
                    'reservation_id' => 'Room '.$reservation->room->room_number.' is still occupied or under maintenance. Move the reservation to another room first.',
                ]);
            }

            $checkIn = CheckIn::create([
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id,
                'checked_in_at' => now(),
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            $reservation->update(['status' => 'checked_in']);
            $reservation->room->update(['status' => 'occupied']);
            RoomStatusLog::create([
                'room_id' => $reservation->room_id,
                'status' => 'occupied',
                'notes' => 'Guest checked in through check-in #'.$checkIn->id,
                'changed_at' => now(),
            ]);
            HotelTransaction::create([
                'guest_id' => $reservation->guest_id,
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'transaction_type' => 'check_in',
                'status' => 'recorded',
                'transaction_date' => now(),
                'description' => 'Guest checked in.',
            ]);
        });

        return redirect()->route('check-ins.index')->with('status', 'Guest checked in.');
    }
}
