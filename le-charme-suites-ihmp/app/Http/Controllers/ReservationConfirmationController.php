<?php

namespace App\Http\Controllers;

use App\Models\HotelTransaction;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationConfirmationController extends Controller
{
    public function __invoke(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->status === 'pending', 422, 'Only pending online reservations can be confirmed.');

        DB::transaction(function () use ($request, $reservation): void {
            $hasConflict = Reservation::query()
                ->where('room_id', $reservation->room_id)
                ->whereKeyNot($reservation->id)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->where('check_in_date', '<', $reservation->check_out_date)
                ->where('check_out_date', '>', $reservation->check_in_date)
                ->lockForUpdate()
                ->exists();

            abort_if($hasConflict, 422, 'This room is no longer available for the requested dates.');

            $reservation->update([
                'status' => 'reserved',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);

            HotelTransaction::create([
                'guest_id' => $reservation->guest_id,
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'transaction_type' => 'booking_confirmation',
                'status' => 'recorded',
                'transaction_date' => now(),
                'description' => 'Online booking confirmed by front desk.',
            ]);
        });

        return back()->with('status', 'Online booking confirmed and added to the arrival list.');
    }
}
