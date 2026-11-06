<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Seeding only adds records that are missing. Existing accounts and rooms
     * are never overwritten, so re-running setup cannot reset passwords or
     * undo an administrator's room changes on a shared database.
     */
    public function run(): void
    {
        if (! app()->environment('production')) {
            $this->seedDemoAccounts();
        }

        $this->seedMissingSampleRooms();
    }

    /**
     * Create the demonstration staff accounts that do not exist yet.
     */
    private function seedDemoAccounts(): void
    {
        foreach ([
            ['Hotel Administrator', 'admin@lecharmehotel.com', 'admin'],
            ['Front Desk Receptionist', 'receptionist@lecharmehotel.com', 'receptionist'],
            ['Billing Staff', 'billing@lecharmehotel.com', 'cashier'],
            ['Housekeeping Staff', 'housekeeping@lecharmehotel.com', 'housekeeping'],
        ] as [$name, $email, $role]) {
            User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => $name,
                'role' => $role,
                'password' => Hash::make('password'),
                'approved_at' => now(),
            ]);
        }
    }

    /**
     * Create the official sample rooms that do not exist yet.
     */
    private function seedMissingSampleRooms(): void
    {
        foreach (config('hotel.sample_room_layout') as $number => $type) {
            $specification = config("hotel.room_types.{$type}");

            Room::firstOrCreate([
                'room_number' => (string) $number,
            ], [
                'room_type' => $type,
                'rate' => $specification['rate'],
                'capacity' => $specification['capacity'],
                'base_occupancy' => $specification['base_occupancy'],
                'room_size_sqm' => $specification['size_sqm'],
                'bed_configuration' => $specification['bed_configuration'],
                'bathrooms' => $specification['bathrooms'],
                'extra_guest_rate' => config('hotel.extra_guest_rate'),
                'amenities' => $specification['amenities'],
                'non_smoking' => true,
                'description' => $specification['description'],
                'status' => 'available',
            ]);
        }
    }
}
