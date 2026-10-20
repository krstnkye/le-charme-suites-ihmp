<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\IntegrationEvent;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateOfficialWebsiteReservation
{
    /** @param array<string, mixed> $data */
    public function handle(Reservation $reservation, array $data, ?string $ipAddress = null): Reservation
    {
        if (! in_array($reservation->status, ['pending', 'reserved'], true)) {
            throw ValidationException::withMessages([
                'external_reference' => 'Only pending or confirmed reservations can be changed by the official website.',
            ]);
        }

        return DB::transaction(function () use ($reservation, $data, $ipAddress): Reservation {
            $reservation->loadMissing(['guest', 'room']);

            $roomType = $data['room_type'] ?? $reservation->room->room_type;
            $checkInDate = $data['check_in_date'] ?? $reservation->check_in_date->toDateString();
            $checkOutDate = $data['check_out_date'] ?? $reservation->check_out_date->toDateString();
            $guestCount = $data['number_of_guests'] ?? $reservation->number_of_guests;

            $room = Room::query()
                ->where('room_type', $roomType)
                ->whereNotIn('status', ['maintenance'])
                ->where('capacity', '>=', $guestCount)
                ->whereDoesntHave('reservations', function ($query) use ($reservation, $checkInDate, $checkOutDate) {
                    $query->whereKeyNot($reservation->id)
                        ->whereIn('status', ['pending', 'reserved', 'checked_in'])
                        ->where('check_in_date', '<', $checkOutDate)
                        ->where('check_out_date', '>', $checkInDate);
                })
                ->lockForUpdate()
                ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$reservation->room_id])
                ->orderBy('room_number')
                ->first();

            if (! $room) {
                throw ValidationException::withMessages([
                    'room_type' => 'No room of this type is available for the updated dates and number of guests.',
                ]);
            }

            $guest = $reservation->guest;
            if (isset($data['email'])) {
                $emailIsUsed = Guest::query()
                    ->where('email', $data['email'])
                    ->whereKeyNot($guest->id)
                    ->exists();

                if ($emailIsUsed) {
                    throw ValidationException::withMessages([
                        'email' => 'This email address belongs to another guest record.',
                    ]);
                }
            }

            $guest->update(array_filter([
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ], fn ($value) => $value !== null));

            $reservation->update(array_filter([
                'room_id' => $room->id,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'number_of_guests' => $guestCount,
                'adult_guests' => $data['adults'] ?? null,
                'child_guests' => $data['children'] ?? null,
                'official_booking_status' => $data['official_booking_status'] ?? null,
                'payment_status' => $data['payment_status'] ?? null,
                'currency' => $data['currency'] ?? null,
                'gross_amount' => $data['gross_amount'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? null,
                'total_amount' => $data['total_amount'] ?? null,
                'rate_plan' => $data['rate_plan'] ?? null,
                'notes' => $data['notes'] ?? null,
            ], fn ($value) => $value !== null));

            HotelTransaction::create([
                'guest_id' => $guest->id,
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
                'transaction_type' => 'online_booking_update',
                'amount' => $data['total_amount'] ?? 0,
                'status' => 'recorded',
                'transaction_date' => now(),
                'description' => 'Official website booking details updated.',
            ]);

            IntegrationEvent::create([
                'channel' => 'api',
                'direction' => 'inbound',
                'dataset' => 'reservations',
                'format' => 'json',
                'record_count' => 1,
                'status' => 'completed',
                'endpoint' => '/api/v1/bookings/{external_reference}',
                'metadata' => [
                    'action' => 'updated',
                    'ip_address' => $ipAddress,
                    'booking_reference' => $reservation->booking_reference,
                    'external_reference' => $reservation->external_reference,
                ],
                'occurred_at' => now(),
            ]);

            return $reservation->fresh(['guest', 'room']);
        });
    }
}
