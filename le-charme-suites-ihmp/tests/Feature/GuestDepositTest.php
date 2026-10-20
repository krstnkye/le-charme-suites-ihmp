<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestDepositTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_desk_can_record_a_downpayment(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = $this->twoNightStandardStay();

        $response = $this->actingAs($receptionist)->post(route('reservations.deposits.store', $reservation), [
            'amount' => 5000,
            'payment_method' => 'gcash',
            'payment_reference' => 'GC-778899',
        ]);

        $response->assertSessionHas('status', 'Deposit of PHP 5,000.00 recorded and deducted from the balance due.');
        $this->assertDatabaseHas('hotel_transactions', [
            'reservation_id' => $reservation->id,
            'transaction_type' => 'deposit',
            'amount' => 5000,
            'status' => 'paid',
            'description' => 'Deposit / downpayment received via GCash. Ref: GC-778899',
        ]);
    }

    public function test_deposit_cannot_exceed_the_balance_due(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = $this->twoNightStandardStay();

        $response = $this->actingAs($receptionist)->post(route('reservations.deposits.store', $reservation), [
            'amount' => 20000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors(['amount' => 'The deposit cannot be more than the PHP 17,528.00 balance due.']);
        $this->assertDatabaseMissing('hotel_transactions', ['transaction_type' => 'deposit']);
    }

    public function test_housekeeping_cannot_record_deposits(): void
    {
        $reservation = $this->twoNightStandardStay();

        $response = $this->actingAs(User::factory()->create(['role' => 'housekeeping']))
            ->post(route('reservations.deposits.store', $reservation), ['amount' => 1000, 'payment_method' => 'cash']);

        $response->assertForbidden();
    }

    public function test_checkout_collects_only_the_balance_left_after_the_deposit(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $reservation = $this->twoNightStandardStay(['status' => 'checked_in']);
        $checkIn = CheckIn::factory()->forReservation($reservation)->create();
        $this->actingAs($cashier)->post(route('reservations.deposits.store', $reservation), ['amount' => 5000, 'payment_method' => 'cash']);

        $this->actingAs($cashier)->get(route('check-outs.create'))
            ->assertOk()
            ->assertSee('"deposit":5000', false)
            ->assertSee('"balance":12528', false);
        $response = $this->actingAs($cashier)->post(route('check-outs.store'), [
            'check_in_id' => $checkIn->id,
            'amount_paid' => 12528,
            'payment_status' => 'paid',
        ]);

        $checkOut = CheckOut::firstOrFail();
        $response->assertSessionHasNoErrors();
        $this->actingAs($cashier)->get(route('check-outs.receipt', $checkOut))
            ->assertOk()
            ->assertSeeText('Less: deposit / downpayment')
            ->assertSeeText('- PHP 5,000.00');
    }

    public function test_paid_status_still_requires_the_full_balance_after_the_deposit(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $reservation = $this->twoNightStandardStay(['status' => 'checked_in']);
        $checkIn = CheckIn::factory()->forReservation($reservation)->create();
        $this->actingAs($cashier)->post(route('reservations.deposits.store', $reservation), ['amount' => 5000, 'payment_method' => 'cash']);

        $response = $this->actingAs($cashier)->post(route('check-outs.store'), [
            'check_in_id' => $checkIn->id,
            'amount_paid' => 10000,
            'payment_status' => 'paid',
        ]);

        $response->assertSessionHasErrors(['amount_paid' => 'A paid bill must collect the full PHP 12,528.00 balance due. Choose Partial if the guest paid less.']);
        $this->assertDatabaseCount('check_outs', 0);
    }

    /**
     * Two nights in a PHP 8,764.00 room: PHP 17,528.00 before deposits.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function twoNightStandardStay(array $attributes = []): Reservation
    {
        return Reservation::factory()
            ->for(Room::factory()->create(['rate' => 8764, 'status' => 'occupied']))
            ->create([
                'check_in_date' => today(),
                'check_out_date' => today()->addDays(2),
                ...$attributes,
            ]);
    }
}
