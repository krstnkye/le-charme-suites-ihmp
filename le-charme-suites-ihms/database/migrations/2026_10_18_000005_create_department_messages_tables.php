<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_department', 30)->index();
            $table->string('subject', 140);
            $table->text('body');
            $table->string('priority', 20)->default('normal')->index();
            $table->timestamps();
        });

        Schema::create('department_message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->unique(['department_message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_message_reads');
        Schema::dropIfExists('department_messages');
    }
};
