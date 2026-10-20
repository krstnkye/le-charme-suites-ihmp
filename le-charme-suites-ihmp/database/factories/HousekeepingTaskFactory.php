<?php

namespace Database\Factories;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HousekeepingTask>
 */
class HousekeepingTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'assigned_to' => User::factory()->state(['role' => 'housekeeping']),
            'task_type' => 'turnover',
            'title' => 'Prepare room for next arrival',
            'instructions' => fake()->sentence(),
            'priority' => 'normal',
            'status' => 'queued',
            'due_at' => now()->addHours(2),
        ];
    }
}
