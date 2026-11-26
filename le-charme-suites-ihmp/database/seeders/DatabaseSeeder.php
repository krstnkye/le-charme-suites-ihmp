<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $bootstrapAccounts = [
            ['Hotel Administrator', 'admin@lecharmehotel.com', 'admin'],
        ];

        if (config('hotel.seed_demo_users')) {
            $bootstrapAccounts = [
                ...$bootstrapAccounts,
                ['Front Desk Receptionist', 'receptionist@lecharmehotel.com', 'receptionist'],
                ['Billing Staff', 'billing@lecharmehotel.com', 'cashier'],
                ['Housekeeping Staff', 'housekeeping@lecharmehotel.com', 'housekeeping'],
            ];
        }

        foreach ($bootstrapAccounts as [$name, $email, $role]) {
            $temporaryPassword = Str::password(20);
            $user = User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => $name,
                'role' => $role,
                'password' => $temporaryPassword,
                'approved_at' => now(),
            ]);

            $rotatedKnownPassword = ! $user->wasRecentlyCreated && Hash::check('password', $user->password);

            if ($rotatedKnownPassword) {
                $user->forceFill([
                    'password' => $temporaryPassword,
                    'remember_token' => Str::random(60),
                ])->save();
            }

            if ($user->wasRecentlyCreated || $rotatedKnownPassword) {
                $this->command?->warn("Temporary credentials created for {$email}: {$temporaryPassword}");
                $this->command?->warn('Store this password securely and change it immediately after sign-in. It will not be shown again.');
            }
        }

        // Clean up installations seeded by older versions without recreating
        // optional demo accounts when demo seeding is disabled.
        foreach (['receptionist@lecharmehotel.com', 'billing@lecharmehotel.com', 'housekeeping@lecharmehotel.com', 'owner@lecharmehotel.com'] as $email) {
            $user = User::where('email', $email)->first();

            if ($user && Hash::check('password', $user->password)) {
                $temporaryPassword = Str::password(20);
                $user->forceFill([
                    'password' => $temporaryPassword,
                    'remember_token' => Str::random(60),
                ])->save();
                $this->command?->warn("Legacy demo password rotated for {$email}: {$temporaryPassword}");
                $this->command?->warn('Store this password securely and change it immediately after sign-in. It will not be shown again.');
            }
        }

        foreach (config('hotel.sample_room_layout') as $number => $type) {
            $number = (string) $number;
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
