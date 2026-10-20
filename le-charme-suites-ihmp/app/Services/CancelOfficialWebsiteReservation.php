<?php

namespace App\Services;

use App\Models\HotelTransaction;
use App\Models\IntegrationEvent;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelOfficialWebsiteReservation
{
    public function handle(Reservation $reservation, ?string $reason = null, ?string $ipAddress = null): Reservation
    {
        if ($reservation->status === 'cancelled') {
            return $reservation->loadMissing(['guest', 'room']);
        }

        if (! in_array($reservation->status, ['pending', 'reserved'], true)) {
            throw ValidationException::withMessages([
                'external_reference' => 'A guest who is already checked in or checked out cannot be cancelled through the website.',
            ]);
        }

        return DB::transaction(function () use ($reservation, $reason, $ipAddress): Reservation {
            $reservation->loadMissing(['guest', 'room']);
            $reservation->update([
                'status' => 'cancelled',
                'official_booking_status' => 'cancelled',
                'notes' => $this->appendCancellationReason($reservation->notes, $reason),
            ]);

            if ($reservation->room->status === 'reserved') {
                $reservation->room->update(['status' => 'available']);
            }

            HotelTransaction::create([
                'guest_id' => $reservation->guest_id,
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'transaction_type' => 'online_booking_cancellation',
                'status' => 'recorded',
                'transaction_date' => now(),
                'description' => $reason ? 'Official website cancellation: '.$reason : 'Official website booking cancelled.',
            ]);

            IntegrationEvent::create([
                'channel' => 'api',
                'direction' => 'inbound',
                'dataset' => 'reservations',
                'format' => 'json',
                'record_count' => 1,
                'status' => 'completed',
                'endpoint' => '/api/v1/bookings/{external_reference}/cancel',
                'metadata' => [
                    'action' => 'cancelled',
                    'ip_address' => $ipAddress,
                    'booking_reference' => $reservation->booking_reference,
                    'external_reference' => $reservation->external_reference,
                    'reason' => $reason,
                ],
                'occurred_at' => now(),
            ]);

            return $reservation->fresh(['guest', 'room']);
        });
    }

    private function appendCancellationReason(?string $notes, ?string $reason): ?string
    {
        if (! $reason) {
            return $notes;
        }

        return trim(implode("\n", array_filter([$notes, 'Cancellation reason: '.$reason])));
    }
}
