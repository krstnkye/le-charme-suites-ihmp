<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DataExchangeService
{
    /**
     * Return a deliberately limited, portable dataset for reports and integrations.
     * Sensitive identification numbers and passwords are never exported.
     */
    public function export(string $dataset, ?int $requestedLimit = null): Collection
    {
        $limit = min(
            max($requestedLimit ?? 100, 1),
            max((int) config('integrations.max_export_records', 200), 1),
        );

        return match ($dataset) {
            'guests' => Guest::query()->latest()->limit($limit)->get()->map(fn (Guest $guest) => [
                'id' => $guest->id,
                'name' => $guest->full_name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'created_at' => $guest->created_at?->toIso8601String(),
            ]),
            'rooms' => Room::query()->orderBy('room_number')->limit($limit)->get()->map(fn (Room $room) => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type,
                'status' => $room->status,
                'capacity' => $room->capacity,
                'rate' => (float) $room->rate,
                'updated_at' => $room->updated_at?->toIso8601String(),
            ]),
            'reservations' => Reservation::query()->with(['guest', 'room'])->latest()->limit($limit)->get()->map(fn (Reservation $reservation) => [
                'id' => $reservation->id,
                'guest' => $reservation->guest?->full_name,
                'room' => $reservation->room?->room_number,
                'check_in_date' => $reservation->check_in_date?->toDateString(),
                'check_out_date' => $reservation->check_out_date?->toDateString(),
                'number_of_guests' => $reservation->number_of_guests,
                'status' => $reservation->status,
                'updated_at' => $reservation->updated_at?->toIso8601String(),
            ]),
            'transactions' => HotelTransaction::query()->with(['guest', 'room'])->latest('transaction_date')->limit($limit)->get()->map(fn (HotelTransaction $transaction) => [
                'id' => $transaction->id,
                'guest' => $transaction->guest?->full_name,
                'room' => $transaction->room?->room_number,
                'transaction_type' => $transaction->transaction_type,
                'amount' => (float) $transaction->amount,
                'status' => $transaction->status,
                'transaction_date' => $transaction->transaction_date?->toIso8601String(),
                'description' => $transaction->description,
            ]),
            default => throw new InvalidArgumentException('Unsupported IHMS dataset.'),
        };
    }

    public function supportedDatasets(): array
    {
        return ['guests', 'rooms', 'reservations', 'transactions'];
    }
}
