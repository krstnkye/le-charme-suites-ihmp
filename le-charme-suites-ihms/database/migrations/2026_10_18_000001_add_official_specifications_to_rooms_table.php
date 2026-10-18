<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedInteger('base_occupancy')->nullable()->after('capacity');
            $table->decimal('room_size_sqm', 6, 2)->nullable()->after('base_occupancy');
            $table->string('bed_configuration')->nullable()->after('room_size_sqm');
            $table->unsignedTinyInteger('bathrooms')->nullable()->after('bed_configuration');
            $table->decimal('extra_guest_rate', 10, 2)->nullable()->after('bathrooms');
            $table->json('amenities')->nullable()->after('extra_guest_rate');
            $table->boolean('non_smoking')->default(true)->after('amenities');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'base_occupancy',
                'room_size_sqm',
                'bed_configuration',
                'bathrooms',
                'extra_guest_rate',
                'amenities',
                'non_smoking',
            ]);
        });
    }
};
