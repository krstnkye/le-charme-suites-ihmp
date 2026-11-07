<?php

namespace Tests\Feature;

use App\Models\Room;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_seeder_does_not_create_accounts_with_shared_credentials(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('rooms', 45);
    }

    public function test_reseeding_keeps_rooms_edited_by_an_administrator(): void
    {
        $this->seed(DatabaseSeeder::class);

        Room::where('room_number', '102')->update([
            'rate' => 15000,
            'description' => 'Renovated in 2026.',
            'status' => 'maintenance',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('rooms', 45);
        $this->assertDatabaseHas('rooms', [
            'room_number' => '102',
            'rate' => 15000,
            'description' => 'Renovated in 2026.',
            'status' => 'maintenance',
        ]);
    }

    public function test_seeding_adds_missing_sample_rooms_without_touching_existing_ones(): void
    {
        $this->seed(DatabaseSeeder::class);
        Room::where('room_number', '103')->delete();

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('rooms', 45);
        $this->assertDatabaseHas('rooms', ['room_number' => '103', 'status' => 'available']);
    }

    public function test_seeding_in_production_creates_no_demo_accounts(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('rooms', 45);
    }
}
