<?php

namespace Database\Factories;

use App\Models\CheckIn;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckIn>
 */
class CheckInFactory extends Factory
{
    /**
     * Define the model's default state: an active in-house stay whose guest and room
     * come from its reservation.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory()->checkedIn(),
            'guest_id' => fn (array $attributes) => Reservation::find($attributes['reservation_id'])->guest_id,
            'room_id' => fn (array $attributes) => Reservation::find($attributes['reservation_id'])->room_id,
            'checked_in_at' => now(),
            'status' => 'active',
        ];
    }

    public function forReservation(Reservation $reservation): static
    {
        return $this->state(fn (array $attributes) => [
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $reservation->room_id,
        ]);
    }
}
