<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutBillDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_screen_shows_the_server_bill_without_invented_tax_or_deposit(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $checkIn = $this->inHouseStay(rate: 8764, nights: 2);
        $this->recordExtraCharge($checkIn, 1250);

        $response = $this->actingAs($cashier)->get(route('check-outs.create'));

        $response->assertOk();
        $this->assertSame(18778.0, $response->viewData('bills')[$checkIn->id]['totalCharges']);
        $response->assertSeeInOrder(['PHP 17,528.00', 'PHP 1,250.00', 'PHP 18,778.00']);
        $response->assertDontSee('SBMA');
        $response->assertDontSee('Deposits');
        $response->assertDontSee('taxPreview');
        $response->assertDontSee('PHP 14,465.15');
    }

    public function test_paying_the_balance_shown_on_the_checkout_screen_completes_the_checkout(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $checkIn = $this->inHouseStay(rate: 13750, nights: 1);
        $balanceShown = $this->actingAs($cashier)
            ->get(route('check-outs.create'))
            ->viewData('bills')[$checkIn->id]['totalCharges'];

        $response = $this->actingAs($cashier)->post(route('check-outs.store'), [
            'check_in_id' => $checkIn->id,
            'amount_paid' => $balanceShown,
            'payment_status' => 'paid',
        ]);

        $checkOut = CheckOut::firstOrFail();
        $response->assertRedirect(route('check-outs.receipt', ['checkOut' => $checkOut, 'print' => 1]));
        $this->assertSame('paid', $checkOut->payment_status);
        $this->assertSame('completed', $checkIn->fresh()->status);
    }

    private function inHouseStay(float $rate, int $nights): CheckIn
    {
        $room = Room::factory()->create(['rate' => $rate, 'status' => 'occupied']);
        $reservation = Reservation::factory()->checkedIn()->for($room)->create([
            'check_in_date' => today(),
            'check_out_date' => today()->addDays($nights),
        ]);

        return CheckIn::factory()->forReservation($reservation)->create();
    }

    private function recordExtraCharge(CheckIn $checkIn, float $amount): void
    {
        HotelTransaction::create([
            'guest_id' => $checkIn->guest_id,
            'reservation_id' => $checkIn->reservation_id,
            'room_id' => $checkIn->room_id,
            'transaction_type' => 'minibar',
            'amount' => $amount,
            'status' => 'recorded',
            'transaction_date' => now(),
            'description' => 'Minibar charge',
        ]);
    }
}
