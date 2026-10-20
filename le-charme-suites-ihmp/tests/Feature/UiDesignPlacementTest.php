<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiDesignPlacementTest extends TestCase
{
    use RefreshDatabase;

    public function test_housekeeping_reference_designs_are_rendered_on_their_intended_pages(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);

        $pages = [
            'cleaning-tasks' => 'Unit Room Preparation & Cleaning Tasks List',
            'priority-rooms' => 'Priority Rooms & Fast Cleaning',
            'guest-requests' => 'Live Request Assignment Board',
            'special-cleaning' => 'Special Cleaning & Room Sanitation',
            'completed-tasks' => 'Completed Tasks & Shift Cleaning History',
            'report-issue' => 'Report Maintenance Defect or Room Discrepancy',
            'reported-issues' => 'Reported Issues & Facilities Work Orders Log',
            'lost-found' => 'Lost & Found Storage and Item History',
            'reports' => 'Housekeeping Operational Reports & Response Time Analytics',
            'notifications' => 'Housekeeping Assignment Notifications & Operational Alerts',
            'tasks' => 'Housekeeping Tasks & Operational Assignment',
            'service-requests' => 'Service Requests Hub & Guest Amenities Delivery',
            'maintenance-requests' => 'Maintenance Requests & Engineering Assignment List',
        ];

        foreach ($pages as $page => $signature) {
            $this->actingAs($housekeeper)
                ->get(route('housekeeping.page', ['page' => $page]))
                ->assertOk()
                ->assertSeeText($signature)
                ->assertSee('aria-label="Housekeeping Sidebar"', false);
        }

        $this->actingAs($housekeeper)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('Executive Housekeeper Profile & Digital Duty Login Details');

        foreach (['linen-supplies', 'linen-records', 'housekeeping-supplies'] as $removedPage) {
            $this->actingAs($housekeeper)
                ->get(route('housekeeping.page', ['page' => $removedPage]))
                ->assertNotFound();
        }
    }

    public function test_billing_reference_designs_are_rendered_on_their_intended_pages(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $pages = [
            route('dashboard') => 'Cashier & Billing Dashboard',
            route('check-outs.index') => 'Guest Charges & Payments',
            route('check-outs.create') => 'Guest Bills & Checkout',
            route('check-outs.history') => 'Transaction & Payment History',
            route('reports.index') => 'Shift Financial & Cashier Reports',
            route('profile.edit') => 'Cashier Profile & Account',
        ];

        foreach ($pages as $url => $signature) {
            $this->actingAs($cashier)
                ->get($url)
                ->assertOk()
                ->assertSeeText($signature)
                ->assertSee('aria-label="Billing Sidebar"', false);
        }
    }

    public function test_billing_pages_use_clear_everyday_terms(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $pages = [
            route('dashboard'),
            route('check-outs.index'),
            route('check-outs.create'),
            route('check-outs.history'),
            route('reports.index'),
            route('profile.edit'),
        ];

        $technicalTerms = [
            'Cash In Register Till',
            'Guest Bill Ledger',
            'POS Incidentals',
            'Settlement State',
            'End of Shift Reconciliation',
            'Electronic EFT',
            'PMS synchronized record',
        ];

        foreach ($pages as $url) {
            $response = $this->actingAs($cashier)->get($url)->assertOk();

            foreach ($technicalTerms as $term) {
                $response->assertDontSeeText($term);
            }
        }

        $this->actingAs($cashier)
            ->get(route('check-outs.create'))
            ->assertOk()
            ->assertDontSeeText('Open POS Terminal')
            ->assertDontSeeText('Open Transactions');
    }

    public function test_public_booking_uses_the_official_hotel_identity_and_room_catalog(): void
    {
        $this->get(route('booking.create'))
            ->assertOk()
            ->assertSeeText('Find your perfect stay')
            ->assertSeeText('Deluxe King')
            ->assertSeeText('Charmé Suite')
            ->assertSeeText('European-inspired luxury with modern Filipino hospitality');
    }

    public function test_front_desk_design_system_covers_operational_detail_and_edit_pages(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $guest = Guest::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.test',
        ]);
        $room = Room::create([
            'room_number' => '101',
            'room_type' => 'Deluxe Twin',
            'rate' => 15840,
            'capacity' => 4,
            'status' => 'available',
        ]);
        $reservation = Reservation::create([
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'number_of_guests' => 2,
            'status' => 'reserved',
        ]);

        $pages = [
            route('guests.show', $guest) => 'Guest Profile & Stay History',
            route('guests.edit', $guest) => 'Edit Guest Profile & Stay Preferences',
            route('reservations.show', $reservation) => 'Reservation Details',
            route('reservations.edit', $reservation) => 'Edit Reservation & Stay Assignment',
            route('rooms.show', $room) => 'Room Operations & Availability Detail',
            route('rooms.edit', $room) => 'Update Room Configuration & Status',
        ];

        foreach ($pages as $url => $signature) {
            $this->actingAs($receptionist)
                ->get($url)
                ->assertOk()
                ->assertSeeText($signature)
                ->assertSee('aria-label="Front Desk Sidebar"', false);
        }
    }
}
