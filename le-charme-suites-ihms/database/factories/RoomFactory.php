<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_number' => (string) fake()->unique()->numberBetween(100, 999),
            'room_type' => 'Standard Room',
            'rate' => 8764,
            'capacity' => 2,
            'status' => 'available',
        ];
    }
}
