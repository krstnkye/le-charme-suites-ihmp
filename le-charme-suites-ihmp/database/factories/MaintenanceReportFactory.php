<?php

namespace Database\Factories;

use App\Models\MaintenanceReport;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceReport>
 */
class MaintenanceReportFactory extends Factory
{
    /**
     * Define the model's default state: an open report for a room.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'category' => 'plumbing',
            'severity' => 'normal',
            'description' => 'Bathroom sink drains slowly.',
            'status' => 'open',
            'reported_by' => User::factory()->state(['role' => 'housekeeping']),
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => 'Drain cleared.',
        ]);
    }
}
