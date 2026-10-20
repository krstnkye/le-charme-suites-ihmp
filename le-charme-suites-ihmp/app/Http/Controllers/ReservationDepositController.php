<?php

namespace App\Http\Controllers;

use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Services\GuestBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationDepositController extends Controller
{
    /**
     * Record a deposit or downpayment so it is deducted from the guest's balance due.
     */
    public function store(Request $request, Reservation $reservation, GuestBillService $bills): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(array_keys(GuestBillService::PAYMENT_METHODS))],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ], [
            'amount.min' => 'Enter a deposit amount of at least PHP 1.00.',
        ]);

        if (! in_array($reservation->status, ['pending', 'reserved', 'checked_in'], true)) {
            throw ValidationException::withMessages([
                'amount' => 'Deposits can only be recorded for upcoming or in-house stays.',
            ]);
        }

        $balanceDue = $bills->forReservation($reservation)['balanceDue'];
        $amount = round((float) $data['amount'], 2);

        if ($amount > $balanceDue) {
            throw ValidationException::withMessages([
                'amount' => 'The deposit cannot be more than the PHP '.number_format($balanceDue, 2).' balance due.',
            ]);
        }

        $method = GuestBillService::PAYMENT_METHODS[$data['payment_method']];

        HotelTransaction::create([
            'guest_id' => $reservation->guest_id,
            'reservation_id' => $reservation->id,
            'room_id' => $reservation->room_id,
            'transaction_type' => 'deposit',
            'amount' => $amount,
            'status' => 'paid',
            'transaction_date' => now(),
            'description' => trim("Deposit / downpayment received via {$method}. ".(filled($data['payment_reference'] ?? null) ? "Ref: {$data['payment_reference']}" : '')),
        ]);

        return back()->with('status', 'Deposit of PHP '.number_format($amount, 2).' recorded and deducted from the balance due.');
    }
}
