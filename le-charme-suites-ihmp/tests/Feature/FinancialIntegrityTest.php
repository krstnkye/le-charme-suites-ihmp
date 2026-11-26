<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\FolioCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_folio_includes_agreed_rate_and_extra_guest_charges(): void
    {
        $room = Room::factory()->create(['rate' => 99999, 'base_occupancy' => 2, 'extra_guest_rate' => 2500]);
        $reservation = Reservation::factory()->for($room)->create([
            'check_in_date' => '2026-11-01',
            'check_out_date' => '2026-11-03',
            'number_of_guests' => 3,
            'agreed_room_rate' => 8000,
            'agreed_base_occupancy' => 2,
            'agreed_extra_guest_rate' => 1500,
        ]);

        $folio = app(FolioCalculator::class)->forReservation($reservation, $room);

        $this->assertSame(16000.0, $folio['roomCharges']);
        $this->assertSame(3000.0, $folio['extraGuestCharges']);
        $this->assertSame(19000.0, $folio['totalCharges']);
    }

    public function test_saved_folio_line_items_do_not_change_when_source_records_are_edited(): void
    {
        $room = Room::factory()->create(['rate' => 8000]);
        $reservation = Reservation::factory()->for($room)->create([
            'agreed_room_rate' => 8000,
            'agreed_base_occupancy' => 2,
            'agreed_extra_guest_rate' => 0,
        ]);
        $charge = HotelTransaction::create([
            'guest_id' => $reservation->guest_id,
            'reservation_id' => $reservation->id,
            'room_id' => $room->id,
            'transaction_type' => 'restaurant',
            'amount' => 1250,
            'status' => 'posted',
            'transaction_date' => now(),
            'description' => 'Original restaurant charge',
        ]);
        $calculator = app(FolioCalculator::class);
        $snapshot = $calculator->snapshot($calculator->forReservation($reservation, $room));
        $checkOut = new CheckOut(['folio_snapshot' => $snapshot]);

        $charge->update(['amount' => 9999, 'description' => 'Changed later']);
        $room->update(['rate' => 25000]);
        $saved = $calculator->forCheckOut($checkOut);

        $this->assertSame(1250.0, $saved['additionalTotal']);
        $this->assertSame('Original restaurant charge', $saved['additionalCharges']->first()->description);
        $this->assertSame(17250.0, $saved['totalCharges']);
    }
}
