<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
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

    public function test_privacy_policy_names_where_the_database_is_hosted(): void
    {
        $this->get(route('legal.privacy'))->assertOk()->assertSeeText('local database')->assertDontSeeText('Supabase');

        config(['database.default' => 'pgsql']);
        $cloudPolicy = view('legal.privacy')->render();
        config(['database.default' => 'sqlite']);

        $this->assertStringContainsString('hosted by Supabase', $cloudPolicy);
    }

    public function test_guests_go_back_to_the_login_page(): void
    {
        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertSeeText('Back to Login')
            ->assertDontSee('data-fallback-url', false);
    }

    public function test_signed_in_staff_go_back_to_the_previous_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('data-legal-back', false)
            ->assertSee('data-fallback-url="'.route('dashboard').'"', false)
            ->assertDontSeeText('Back to Login');
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
