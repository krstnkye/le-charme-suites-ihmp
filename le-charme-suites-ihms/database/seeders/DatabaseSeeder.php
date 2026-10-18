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
            ['101', 'Standard Room'],
            ['102', 'Deluxe King'],
            ['201', 'Deluxe Twin'],
            ['202', 'Charmé Suite'],
            ['301', 'Family Room'],
        ] as [$number, $type]) {
            $specification = config("hotel.room_types.{$type}");
            $room = Room::firstOrNew(['room_number' => $number]);

            $room->fill([
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
            ]);

            if (! $room->exists) {
                $room->status = 'available';
            }

            $room->save();
        }
    }
}
