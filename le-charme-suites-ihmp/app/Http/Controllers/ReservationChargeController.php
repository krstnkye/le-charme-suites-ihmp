<?php

namespace App\Http\Controllers;

use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Services\GuestBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationChargeController extends Controller
{
    /**
     * Post an extra charge (minibar, laundry, food, damages, ...) to an in-house guest's bill.
     */
    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate([
            'charge_category' => ['required', Rule::in(array_keys(GuestBillService::CHARGE_CATEGORIES))],
            'charge_amount' => ['required', 'numeric', 'min:1', 'max:500000'],
            'charge_description' => ['nullable', 'string', 'max:255'],
        ], [
            'charge_amount.min' => 'Enter a charge of at least PHP 1.00.',
        ]);

        if ($reservation->status !== 'checked_in') {
            throw ValidationException::withMessages([
                'charge_amount' => 'Extra charges can only be posted to guests who are currently checked in.',
            ]);
        }

        $category = GuestBillService::CHARGE_CATEGORIES[$data['charge_category']];
        $amount = round((float) $data['charge_amount'], 2);

        HotelTransaction::create([
            'guest_id' => $reservation->guest_id,
            'reservation_id' => $reservation->id,
            'room_id' => $reservation->room_id,
            'transaction_type' => $data['charge_category'],
            'amount' => $amount,
            'status' => 'recorded',
            'transaction_date' => now(),
            'description' => filled($data['charge_description'] ?? null) ? "{$category}: {$data['charge_description']}" : $category,
        ]);

        return back()->with('status', "{$category} charge of PHP ".number_format($amount, 2).' added to the guest bill.');
    }
}
