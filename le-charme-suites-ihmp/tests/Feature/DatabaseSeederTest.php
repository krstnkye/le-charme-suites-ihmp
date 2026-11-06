<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_keeps_an_existing_account_password_role_and_approval(): void
    {
        $this->seed(DatabaseSeeder::class);

        $administrator = User::where('email', 'admin@lecharmehotel.com')->firstOrFail();
        $approvedAt = now()->subMonth()->startOfSecond();
        $administrator->update([
            'password' => 'a-changed-secret-123',
            'approved_at' => $approvedAt,
        ]);
        User::where('email', 'receptionist@lecharmehotel.com')->update(['role' => 'cashier']);

        $this->seed(DatabaseSeeder::class);

        $administrator->refresh();
        $this->assertTrue(Hash::check('a-changed-secret-123', $administrator->password));
        $this->assertFalse(Hash::check('password', $administrator->password));
        $this->assertTrue($approvedAt->equalTo($administrator->approved_at));
        $this->assertSame('cashier', User::where('email', 'receptionist@lecharmehotel.com')->value('role'));
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
