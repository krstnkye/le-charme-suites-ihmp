<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Synchronize encoded official room types with the hotel's published starting rates.
     */
    public function up(): void
    {
        foreach ([
            'Standard Room' => 8764.00,
            'Deluxe King' => 13750.00,
            'Deluxe Twin' => 15840.00,
            'Charmé Suite' => 19500.00,
            'Family Room' => 23800.00,
        ] as $roomType => $rate) {
            DB::table('rooms')->where('room_type', $roomType)->update([
                'rate' => $rate,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Previous room rates are not restored because they may contain legitimate historical values.
    }
};
