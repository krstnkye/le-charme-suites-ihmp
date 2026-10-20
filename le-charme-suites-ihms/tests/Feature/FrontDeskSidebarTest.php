<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontDeskSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_pages_share_the_universal_front_desk_sidebar(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $routes = [
            route('dashboard'),
            route('reservations.index'),
            route('reservations.create'),
            route('check-ins.index'),
            route('check-ins.create'),
            route('check-outs.create'),
            route('check-outs.index'),
            route('rooms.index'),
            route('guests.index'),
            route('guests.create'),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($receptionist)
                ->get($url)
                ->assertOk()
                ->assertSee('aria-label="Front Desk Sidebar"', false)
                ->assertSee('data-universal-sidebar', false)
                ->assertSee('data-sidebar-toggle', false)
                ->assertSee('Front Desk IHMP')
                ->assertSee('Express Check-In')
                ->assertSee('Sign Out');
        }
    }

    public function test_billing_user_keeps_the_billing_sidebar_on_shared_checkout_pages(): void
    {
        $billingUser = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($billingUser)
            ->get(route('check-outs.index'))
            ->assertOk()
            ->assertDontSee('aria-label="Front Desk Sidebar"', false)
            ->assertSee('aria-label="Billing Sidebar"', false)
            ->assertSee('data-sidebar-toggle', false);
    }

    public function test_check_in_form_includes_a_searchable_reservation_picker(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $guest = Guest::create([
            'first_name' => 'Markin Nate',
            'last_name' => 'Turla',
            'email' => 'markin@example.com',
            'phone' => '09171234567',
        ]);
        $room = Room::factory()->create(['room_number' => '202']);
        $reservation = Reservation::create([
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'number_of_guests' => 2,
            'status' => 'reserved',
        ]);

        $this->actingAs($receptionist)
            ->get(route('check-ins.create', ['reservation_id' => $reservation->id]))
            ->assertOk()
            ->assertSee('id="reservation_search"', false)
            ->assertSee('id="reservationSearchButton"', false)
            ->assertSee('Guest name, reservation, email, phone, or room')
            ->assertSee('Markin Nate Turla')
            ->assertSee('data-search="res-00001', false)
            ->assertSee('value="'.$reservation->id.'"', false)
            ->assertSee('selected', false)
            ->assertSee('function filterReservations()', false)
            ->assertSee('id="registrationCard"', false)
            ->assertSee('Guest Registration Card & Stay Receipt', false)
            ->assertSee('Official Registration Receipt')
            ->assertSee('id="cardTotal"', false)
            ->assertSee('id="cardCounterReference"', false)
            ->assertSee('function printRegistrationCard()', false);
    }
}
