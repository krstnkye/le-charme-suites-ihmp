<?php

namespace Database\Factories;

use App\Models\LostFoundItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LostFoundItem>
 */
class LostFoundItemFactory extends Factory
{
    /**
     * Define the model's default state: an item held in custody.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_name' => 'Phone charger',
            'category' => 'electronics',
            'room_id' => Room::factory(),
            'found_at' => now(),
            'found_by' => User::factory()->state(['role' => 'housekeeping']),
            'storage_location' => 'Housekeeping office cabinet',
            'status' => 'in_custody',
        ];
    }
}
