<?php

namespace App\Services;

use App\Models\CheckOut;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FolioCalculator
{
    /**
     * @return array{nights: int, roomCharges: float, extraGuestCharges: float, additionalCharges: Collection<int, HotelTransaction>, additionalTotal: float, totalCharges: float}
     */
    public function forReservation(?Reservation $reservation, ?Room $room): array
    {
        $nights = $reservation?->check_in_date && $reservation?->check_out_date
            ? max(1, (int) $reservation->check_in_date->diffInDays($reservation->check_out_date))
            : 1;
        $roomRate = $this->toCentavos($reservation?->agreed_room_rate ?? $room?->rate ?? 0);
        $baseOccupancy = (int) ($reservation?->agreed_base_occupancy ?? $room?->base_occupancy ?? 1);
        $extraGuestRate = $this->toCentavos($reservation?->agreed_extra_guest_rate ?? $room?->extra_guest_rate ?? 0);
        $extraGuests = max(0, (int) ($reservation?->number_of_guests ?? 1) - $baseOccupancy);
        $roomCharges = $roomRate * $nights;
        $extraGuestCharges = $extraGuestRate * $extraGuests * $nights;
        $additionalCharges = HotelTransaction::query()
            ->where('reservation_id', $reservation?->id)
            ->whereNotIn('transaction_type', ['reservation', 'check_in', 'check_out', 'payment', 'online_booking_request'])
            ->where('status', '!=', 'void')
            ->where('amount', '>', 0)
            ->orderBy('transaction_date')
            ->get();
        $additionalTotal = $additionalCharges->sum(fn (HotelTransaction $transaction): int => $this->toCentavos($transaction->amount));
        $totalCharges = $roomCharges + $extraGuestCharges + $additionalTotal;

        return [
            'nights' => $nights,
            'roomCharges' => $this->toPesos($roomCharges),
            'extraGuestCharges' => $this->toPesos($extraGuestCharges),
            'additionalCharges' => $additionalCharges,
            'additionalTotal' => $this->toPesos($additionalTotal),
            'totalCharges' => $this->toPesos($totalCharges),
        ];
    }

    /**
     * @return array{nights: int, roomCharges: float, extraGuestCharges: float, additionalCharges: Collection<int, HotelTransaction>, additionalTotal: float, totalCharges: float}
     */
    public function forCheckOut(CheckOut $checkOut): array
    {
        $snapshot = $checkOut->folio_snapshot;

        if (is_array($snapshot)) {
            $transactions = collect($snapshot['additional_items'] ?? [])->map(function (array $item): object {
                $item['transaction_date'] = filled($item['transaction_date'] ?? null)
                    ? Carbon::parse($item['transaction_date'])
                    : null;

                return (object) $item;
            });

            return [
                'nights' => (int) $snapshot['nights'],
                'roomCharges' => (float) $snapshot['room_charges'],
                'extraGuestCharges' => (float) $snapshot['extra_guest_charges'],
                'additionalCharges' => $transactions,
                'additionalTotal' => (float) $snapshot['additional_total'],
                'totalCharges' => (float) $snapshot['total_charges'],
            ];
        }

        return $this->forReservation($checkOut->reservation, $checkOut->room);
    }

    /** @param array{nights: int, roomCharges: float, extraGuestCharges: float, additionalCharges: Collection<int, HotelTransaction>, additionalTotal: float, totalCharges: float} $folio */
    public function snapshot(array $folio): array
    {
        return [
            'nights' => $folio['nights'],
            'room_charges' => $folio['roomCharges'],
            'extra_guest_charges' => $folio['extraGuestCharges'],
            'additional_total' => $folio['additionalTotal'],
            'total_charges' => $folio['totalCharges'],
            'additional_items' => $folio['additionalCharges']->map(fn (HotelTransaction $transaction): array => [
                'id' => $transaction->id,
                'description' => $transaction->description,
                'transaction_type' => $transaction->transaction_type,
                'transaction_date' => $transaction->transaction_date?->toISOString(),
                'amount' => (float) $transaction->amount,
            ])->values()->all(),
        ];
    }

    private function toCentavos(string|int|float $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function toPesos(int $centavos): float
    {
        return $centavos / 100;
    }
}
