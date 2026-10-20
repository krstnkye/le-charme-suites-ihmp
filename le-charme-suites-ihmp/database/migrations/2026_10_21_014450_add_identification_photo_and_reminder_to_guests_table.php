<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('identification_photo_path')->nullable()->after('identification_number');
            $table->timestamp('identification_reminded_at')->nullable()->after('identification_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['identification_photo_path', 'identification_reminded_at']);
        });
    }
};
