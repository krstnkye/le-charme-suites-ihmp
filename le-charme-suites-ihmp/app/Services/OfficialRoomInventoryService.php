<?php

namespace App\Services;

use App\Models\Room;

class OfficialRoomInventoryService
{
    public function seedMissingRooms(): void
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
