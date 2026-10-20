<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\IntegrationEvent;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOnlineReservation
{
    /** @param array<string, mixed> $data */
    public function handle(array $data, string $source, ?string $ipAddress = null): Reservation
    {
        return DB::transaction(function () use ($data, $source, $ipAddress): Reservation {
            $externalReference = filled($data['external_reference'] ?? null)
                ? trim((string) $data['external_reference'])
                : null;

            if ($externalReference) {
                $existingReservation = Reservation::query()
                    ->where('external_reference', $externalReference)
                    ->where('source', $source)
                    ->with(['guest', 'room'])
                    ->first();

                if ($existingReservation) {
                    return $existingReservation;
                }
            }

            $room = Room::query()
                ->where('room_type', $data['room_type'])
                ->whereNotIn('status', ['maintenance'])
                ->where('capacity', '>=', $data['number_of_guests'])
                ->whereDoesntHave('reservations', function ($query) use ($data) {
                    $query->whereIn('status', ['pending', 'reserved', 'checked_in'])
                        ->where('check_in_date', '<', $data['check_out_date'])
                        ->where('check_out_date', '>', $data['check_in_date']);
                })
                ->lockForUpdate()
                ->orderBy('room_number')
                ->first();

            if (! $room) {
                throw ValidationException::withMessages([
                    'room_type' => 'No room of this type is available for the selected dates and number of guests.',
                ]);
            }

            $guest = Guest::query()->firstOrCreate(
                ['email' => Str::lower($data['email'])],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'] ?? null,
                ],
            );

            $guest->fill(array_filter([
                'phone' => blank($guest->phone) ? $data['phone'] : null,
                'address' => blank($guest->address) ? ($data['address'] ?? null) : null,
            ]))->save();

            $reservation = Reservation::create([
                'booking_reference' => $this->reference(),
                'external_reference' => $externalReference,
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'number_of_guests' => $data['number_of_guests'],
                'status' => 'pending',
                'source' => $source,
                'notes' => $data['notes'] ?? null,
            ]);

            HotelTransaction::create([
                'guest_id' => $guest->id,
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
                'transaction_type' => 'online_booking_request',
                'status' => 'pending',
                'transaction_date' => now(),
                'description' => 'Online booking request received from '.$source.'.',
            ]);

            IntegrationEvent::create([
                'channel' => match ($source) {
                    'public_website' => 'website',
                    'official_website_simulation' => 'website_simulation',
                    default => 'api',
                },
                'direction' => 'inbound',
                'dataset' => 'reservations',
                'format' => 'json',
                'record_count' => 1,
                'status' => 'completed',
                'endpoint' => match ($source) {
                    'public_website' => '/book',
                    'official_website_simulation' => '/website-booking',
                    default => '/api/v1/bookings',
                },
                'metadata' => [
                    'ip_address' => $ipAddress,
                    'source' => $source,
                    'booking_reference' => $reservation->booking_reference,
                    'external_reference' => $externalReference,
                ],
                'occurred_at' => now(),
            ]);

            return $reservation->load(['guest', 'room']);
        });
    }

    private function reference(): string
    {
        do {
            $reference = 'LCS-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Reservation::where('booking_reference', $reference)->exists());

        return $reference;
    }
}
