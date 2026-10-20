<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state: a confirmed front-desk booking arriving today.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guest_id' => Guest::factory(),
            'room_id' => Room::factory(),
            'booking_reference' => 'LCS-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'check_in_date' => today(),
            'check_out_date' => today()->addDays(2),
            'number_of_guests' => 2,
            'status' => 'reserved',
            'source' => 'front_desk',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending', 'source' => 'public_website']);
    }

    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'checked_in']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'cancelled']);
    }
}
