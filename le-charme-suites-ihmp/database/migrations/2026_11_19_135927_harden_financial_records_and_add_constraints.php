<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->decimal('agreed_room_rate', 12, 2)->nullable();
            $table->unsignedInteger('agreed_base_occupancy')->nullable();
            $table->decimal('agreed_extra_guest_rate', 12, 2)->nullable();
            $table->index(['room_id', 'status', 'check_in_date', 'check_out_date'], 'reservations_availability_index');
        });

        Schema::table('check_outs', function (Blueprint $table): void {
            $table->decimal('room_charges', 12, 2)->default(0);
            $table->decimal('extra_guest_charges', 12, 2)->default(0);
            $table->decimal('additional_charges', 12, 2)->default(0);
            $table->decimal('total_charges', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->json('folio_snapshot')->nullable();
            $table->index(['payment_status', 'checked_out_at']);
        });

        Schema::create('check_out_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('check_out_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 40)->default('unspecified');
            $table->string('reference', 100)->nullable()->unique();
            $table->timestamp('received_at');
            $table->timestamps();
        });

        Schema::create('housekeeping_task_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('housekeeping_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
        });

        Schema::table('check_ins', function (Blueprint $table): void {
            $table->unique('reservation_id');
            $table->index(['room_id', 'status']);
        });

        Schema::table('check_outs', function (Blueprint $table): void {
            $table->unique('check_in_id');
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table): void {
            $table->index(['room_id', 'status']);
        });

        Schema::table('auth_activity_logs', function (Blueprint $table): void {
            $table->text('details')->nullable()->change();
        });

        DB::table('reservations')->orderBy('id')->each(function (object $reservation): void {
            $room = DB::table('rooms')->where('id', $reservation->room_id)->first();

            DB::table('reservations')->where('id', $reservation->id)->update([
                'agreed_room_rate' => $room?->rate ?? 0,
                'agreed_base_occupancy' => $room?->base_occupancy ?? 1,
                'agreed_extra_guest_rate' => $room?->extra_guest_rate ?? 0,
            ]);
        });

        DB::table('check_outs')->orderBy('id')->each(function (object $checkOut): void {
            $reservation = DB::table('reservations')->where('id', $checkOut->reservation_id)->first();

            if (! $reservation) {
                return;
            }

            $nights = max(1, (new DateTimeImmutable($reservation->check_in_date))->diff(new DateTimeImmutable($reservation->check_out_date))->days);
            $roomCharges = round((float) $reservation->agreed_room_rate * $nights, 2);
            $extraGuests = max(0, (int) $reservation->number_of_guests - (int) $reservation->agreed_base_occupancy);
            $extraGuestCharges = round((float) $reservation->agreed_extra_guest_rate * $extraGuests * $nights, 2);
            $additionalCharges = DB::table('hotel_transactions')
                ->where('reservation_id', $reservation->id)
                ->whereNotIn('transaction_type', ['reservation', 'check_in', 'check_out', 'payment', 'online_booking_request'])
                ->where('status', '!=', 'void')
                ->where('amount', '>', 0)
                ->get();
            $additionalTotal = round((float) $additionalCharges->sum('amount'), 2);
            $totalCharges = round($roomCharges + $extraGuestCharges + $additionalTotal, 2);

            DB::table('check_outs')->where('id', $checkOut->id)->update([
                'room_charges' => $roomCharges,
                'extra_guest_charges' => $extraGuestCharges,
                'additional_charges' => $additionalTotal,
                'total_charges' => $totalCharges,
                'balance_due' => max(0, round($totalCharges - (float) $checkOut->amount_paid, 2)),
                'folio_snapshot' => json_encode([
                    'nights' => $nights,
                    'room_charges' => $roomCharges,
                    'extra_guest_charges' => $extraGuestCharges,
                    'additional_total' => $additionalTotal,
                    'total_charges' => $totalCharges,
                    'additional_items' => $additionalCharges->map(fn (object $charge): array => [
                        'id' => $charge->id,
                        'description' => $charge->description,
                        'transaction_type' => $charge->transaction_type,
                        'transaction_date' => $charge->transaction_date,
                        'amount' => (float) $charge->amount,
                    ])->values()->all(),
                ]),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_task_status_logs');
        Schema::dropIfExists('check_out_payments');

        Schema::table('auth_activity_logs', function (Blueprint $table): void {
            $table->string('details')->nullable()->change();
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table): void {
            $table->dropIndex(['room_id', 'status']);
        });

        Schema::table('check_ins', function (Blueprint $table): void {
            $table->dropUnique(['reservation_id']);
            $table->dropIndex(['room_id', 'status']);
        });

        Schema::table('check_outs', function (Blueprint $table): void {
            $table->dropUnique(['check_in_id']);
            $table->dropIndex(['payment_status', 'checked_out_at']);
            $table->dropColumn(['room_charges', 'extra_guest_charges', 'additional_charges', 'total_charges', 'balance_due', 'folio_snapshot']);
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropIndex('reservations_availability_index');
            $table->dropColumn(['agreed_room_rate', 'agreed_base_occupancy', 'agreed_extra_guest_rate']);
        });
    }
};
