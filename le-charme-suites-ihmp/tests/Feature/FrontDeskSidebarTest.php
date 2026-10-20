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

    public function test_admin_keeps_the_front_desk_sidebar_on_billing_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([route('check-outs.index'), route('check-outs.history'), route('check-outs.create')] as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertSee('aria-label="Front Desk Sidebar"', false)
                ->assertDontSee('aria-label="Billing Sidebar"', false);
        }
    }

    public function test_billing_staff_keep_the_billing_sidebar_on_billing_pages(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $response = $this->actingAs($cashier)->get(route('check-outs.index'));

        $response->assertOk();
        $response->assertSee('aria-label="Billing Sidebar"', false);
        $response->assertDontSee('aria-label="Front Desk Sidebar"', false);
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

    public function test_registration_card_button_prints_a_dedicated_single_page_card(): void
    {
        $receptionist = User::factory()->create([
            'name' => 'Maria Santos',
            'role' => 'receptionist',
        ]);
        $guest = Guest::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09171234567',
            'address' => 'Olongapo City, Zambales',
            'identification_type' => 'Driver License',
            'identification_number' => 'N01-23-456789',
        ]);
        $room = Room::factory()->create([
            'room_number' => '202',
            'room_type' => 'Charmé Suite',
            'rate' => 19500,
        ]);
        $reservation = Reservation::create([
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'booking_reference' => 'LCS-TEST-0001',
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'number_of_guests' => 2,
            'status' => 'reserved',
            'source' => 'front_desk',
            'created_by' => $receptionist->id,
            'confirmed_by' => $receptionist->id,
            'confirmed_at' => now(),
        ]);

        $this->actingAs($receptionist)
            ->get(route('reservations.show', $reservation))
            ->assertOk()
            ->assertSee(route('reservations.registration-card', ['reservation' => $reservation, 'print' => 1]), false)
            ->assertSeeText('Print Registration Card');

        $this->actingAs($receptionist)
            ->get(route('reservations.registration-card', ['reservation' => $reservation, 'print' => 1]))
            ->assertOk()
            ->assertSeeText('GUEST REGISTRATION CARD')
            ->assertSeeText('Juan Dela Cruz')
            ->assertSeeText('Room 202')
            ->assertSeeText('PHP 19,500.00')
            ->assertSeeText('Guest signature')
            ->assertSee('@page { size:A4 portrait;', false)
            ->assertSee('PAGE 1 OF 1')
            ->assertSee('window.print()', false)
            ->assertDontSee('aria-label="Front Desk Sidebar"', false);

        $this->actingAs($receptionist)
            ->get(route('check-ins.create', ['reservation_id' => $reservation]))
            ->assertOk()
            ->assertSee(json_encode(route('reservations.registration-card', ['reservation' => $reservation, 'print' => 1])), false)
            ->assertSeeText('Print Registration Card')
            ->assertDontSeeText('Print Registration Card & Receipt');
    }
}
