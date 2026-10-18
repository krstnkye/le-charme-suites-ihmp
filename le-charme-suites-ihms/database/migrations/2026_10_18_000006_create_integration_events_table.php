<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction', 20)->index();
            $table->string('channel', 30)->index();
            $table->string('dataset', 60)->index();
            $table->string('format', 20);
            $table->unsignedInteger('record_count')->default(0);
            $table->string('status', 20)->default('completed')->index();
            $table->string('checksum', 64)->nullable();
            $table->string('endpoint')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_events');
    }
};
