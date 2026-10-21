<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_found_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('found_location')->nullable();
            $table->timestamp('found_at');
            $table->foreignId('found_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('storage_location')->nullable();
            $table->string('status')->default('in_custody');
            $table->string('claimed_by_name')->nullable();
            $table->string('claimant_id_details')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('release_notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_found_items');
    }
};
