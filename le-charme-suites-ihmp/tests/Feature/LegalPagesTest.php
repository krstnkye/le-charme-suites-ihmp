<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_pages_are_public(): void
    {
        $this->get(route('legal.privacy'))->assertOk()->assertSeeText('Data Privacy Act of 2012');
        $this->get(route('legal.terms'))->assertOk()->assertSeeText('Terms of Service');
        $this->get(route('legal.refunds'))->assertOk()->assertSeeText('All confirmed reservations are non-refundable.');
        $this->get(route('legal.cookies'))->assertOk()->assertSeeText(config('session.cookie'));
    }

    public function test_booking_page_links_policies_and_shows_cookie_notice(): void
    {
        $this->get(route('booking.create'))
            ->assertOk()
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee('id="cookie-notice"', false)
            ->assertSeeText('Price summary');
    }

    public function test_booking_page_counts_only_rooms_free_today(): void
    {
        Room::factory()->create(['room_number' => '101', 'room_type' => 'Deluxe King', 'status' => 'available']);
        Room::factory()->create(['room_number' => '102', 'room_type' => 'Deluxe King', 'status' => 'occupied']);

        $this->get(route('booking.create'))
            ->assertOk()
            ->assertSeeText('1 available today');
    }
}
