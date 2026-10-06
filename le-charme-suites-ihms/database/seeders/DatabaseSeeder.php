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
     */
    public function run(): void
    {
        foreach ([
            ['Hotel Administrator', 'admin@example.com', 'admin'],
            ['Front Desk Receptionist', 'receptionist@lecharme.test', 'receptionist'],
            ['Cashier Staff', 'cashier@lecharme.test', 'cashier'],
            ['Housekeeping Staff', 'housekeeping@lecharme.test', 'housekeeping'],
            ['Hotel Owner', 'owner@lecharme.test', 'owner'],
        ] as [$name, $email, $role]) {
            User::updateOrCreate([
                'email' => $email,
            ], [
                'name' => $name,
                'role' => $role,
                'password' => Hash::make('password'),
            ]);
        }

        foreach ([
            ['101', 'Standard', 1800, 2],
            ['102', 'Standard', 1800, 2],
            ['201', 'Deluxe', 2800, 3],
            ['202', 'Deluxe', 2800, 3],
            ['301', 'Suite', 4500, 4],
        ] as [$number, $type, $rate, $capacity]) {
            Room::updateOrCreate([
                'room_number' => $number,
            ], [
                'room_type' => $type,
                'rate' => $rate,
                'capacity' => $capacity,
                'status' => 'available',
            ]);
        }
    }
}
