<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedInteger('adult_guests')->nullable()->after('number_of_guests');
            $table->unsignedInteger('child_guests')->default(0)->after('adult_guests');
            $table->string('official_booking_status', 30)->nullable()->after('source');
            $table->string('payment_status', 30)->nullable()->after('official_booking_status');
            $table->string('currency', 3)->nullable()->after('payment_status');
            $table->decimal('gross_amount', 12, 2)->nullable()->after('currency');
            $table->decimal('discount_amount', 12, 2)->nullable()->after('gross_amount');
            $table->decimal('total_amount', 12, 2)->nullable()->after('discount_amount');
            $table->string('rate_plan')->nullable()->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'adult_guests',
                'child_guests',
                'official_booking_status',
                'payment_status',
                'currency',
                'gross_amount',
                'discount_amount',
                'total_amount',
                'rate_plan',
            ]);
        });
    }
};
