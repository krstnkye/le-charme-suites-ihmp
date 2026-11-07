<?php

namespace Database\Seeders;

use App\Services\OfficialRoomInventoryService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        app(OfficialRoomInventoryService::class)->seedMissingRooms();
    }
}
