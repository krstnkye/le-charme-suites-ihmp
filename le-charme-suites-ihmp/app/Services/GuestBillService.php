<?php

namespace App\Services;

use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Collection;

class GuestBillService
{
    /**
     * Transaction types that record activity or money received rather than a charge to the guest.
     */
    public const NON_CHARGE_TYPES = ['reservation', 'check_in', 'check_out', 'deposit', 'online_booking_request', 'booking_confirmation'];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'card' => 'Credit / Debit Card',
        'gcash' => 'GCash',
        'maya' => 'Maya',
        'bank_transfer' => 'Bank Transfer',
    ];

    /**
     * Room rates are tax-inclusive, so the bill is room charges for the booked nights plus
     * recorded extra charges, less any deposits or downpayments already received.
     *
     * @return array{nights: int, roomCharges: float, additionalCharges: Collection<int, HotelTransaction>, additionalTotal: float, totalCharges: float, deposits: Collection<int, HotelTransaction>, depositTotal: float, balanceDue: float}
     */
    public function forReservation(?Reservation $reservation, ?Room $room = null): array
    {
        $room ??= $reservation?->room;
        $nights = $reservation?->check_in_date && $reservation?->check_out_date
            ? max(1, (int) $reservation->check_in_date->diffInDays($reservation->check_out_date))
            : 1;
        $roomCharges = round((float) ($room?->rate ?? 0) * $nights, 2);

        $transactions = $reservation
            ? HotelTransaction::query()
                ->where('reservation_id', $reservation->id)
                ->where('status', '!=', 'void')
                ->where('amount', '>', 0)
                ->orderBy('transaction_date')
                ->get()
            : collect();

        $additionalCharges = $transactions->whereNotIn('transaction_type', self::NON_CHARGE_TYPES)->values();
        $deposits = $transactions->where('transaction_type', 'deposit')->values();
        $additionalTotal = round((float) $additionalCharges->sum('amount'), 2);
        $totalCharges = round($roomCharges + $additionalTotal, 2);
        $depositTotal = round((float) $deposits->sum('amount'), 2);

        return [
            'nights' => $nights,
            'roomCharges' => $roomCharges,
            'additionalCharges' => $additionalCharges,
            'additionalTotal' => $additionalTotal,
            'totalCharges' => $totalCharges,
            'deposits' => $deposits,
            'depositTotal' => $depositTotal,
            'balanceDue' => max(0, round($totalCharges - $depositTotal, 2)),
        ];
    }
}
