<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\GuestBillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestChargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_can_post_an_extra_charge_that_raises_the_balance_due(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $reservation = $this->inHouseStay();

        $response = $this->actingAs($cashier)->post(route('reservations.charges.store', $reservation), [
            'charge_category' => 'minibar',
            'charge_amount' => 450,
            'charge_description' => '2 bottled water, 1 soda',
        ]);

        $response->assertSessionHas('status', 'Minibar charge of PHP 450.00 added to the guest bill.');
        $this->assertDatabaseHas('hotel_transactions', [
            'reservation_id' => $reservation->id,
            'transaction_type' => 'minibar',
            'amount' => 450,
            'description' => 'Minibar: 2 bottled water, 1 soda',
        ]);
        $bill = app(GuestBillService::class)->forReservation($reservation->fresh());
        $this->assertSame(450.0, $bill['additionalTotal']);
        $this->assertSame(17978.0, $bill['balanceDue']);
    }

    public function test_charges_are_only_posted_to_checked_in_guests(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($cashier)->post(route('reservations.charges.store', $reservation), [
            'charge_category' => 'laundry',
            'charge_amount' => 300,
        ]);

        $response->assertSessionHasErrors(['charge_amount' => 'Extra charges can only be posted to guests who are currently checked in.']);
        $this->assertDatabaseMissing('hotel_transactions', ['transaction_type' => 'laundry']);
    }

    public function test_unknown_charge_types_are_rejected(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $reservation = $this->inHouseStay();

        $response = $this->actingAs($cashier)->post(route('reservations.charges.store', $reservation), [
            'charge_category' => 'deposit',
            'charge_amount' => 1000,
        ]);

        $response->assertSessionHasErrors('charge_category');
        $this->assertDatabaseMissing('hotel_transactions', ['amount' => 1000]);
    }

    public function test_housekeeping_cannot_post_charges(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $reservation = $this->inHouseStay();

        $response = $this->actingAs($housekeeper)->post(route('reservations.charges.store', $reservation), [
            'charge_category' => 'minibar',
            'charge_amount' => 100,
        ]);

        $response->assertForbidden();
    }

    public function test_guest_bill_rows_offer_an_add_charge_action(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->inHouseStay();

        $response = $this->actingAs($cashier)->get(route('check-outs.create'));

        $response->assertOk();
        $response->assertSee('data-folio-action="charge"', false);
        $response->assertSeeText('Add an extra charge');
    }

    private function inHouseStay(): Reservation
    {
        $reservation = Reservation::factory()
            ->checkedIn()
            ->for(Room::factory()->create(['rate' => 8764, 'status' => 'occupied']))
            ->create(['check_in_date' => today(), 'check_out_date' => today()->addDays(2)]);
        CheckIn::factory()->forReservation($reservation)->create();

        return $reservation;
    }
}
