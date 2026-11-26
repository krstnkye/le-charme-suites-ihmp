<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table): void {
            $table->text('identification_number')->nullable()->change();
        });

        DB::table('guests')
            ->whereNotNull('identification_number')
            ->orderBy('id')
            ->each(function (object $guest): void {
                DB::table('guests')->where('id', $guest->id)->update([
                    'identification_number' => Crypt::encryptString($guest->identification_number),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('guests')
            ->whereNotNull('identification_number')
            ->orderBy('id')
            ->each(function (object $guest): void {
                DB::table('guests')->where('id', $guest->id)->update([
                    'identification_number' => Crypt::decryptString($guest->identification_number),
                ]);
            });

        Schema::table('guests', function (Blueprint $table): void {
            $table->string('identification_number')->nullable()->change();
        });
    }
};
