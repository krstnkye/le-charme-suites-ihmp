<?php

namespace Tests\Feature;

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
            'cleaning-tasks' => 'Unit Turnover & Cleaning Tasks Queue',
            'priority-rooms' => 'Priority Rooms & Expedited Turnovers',
            'guest-requests' => 'Live Request Dispatch Board',
            'special-cleaning' => 'Specialized Cleaning & Environmental Sanitization Protocol',
            'completed-tasks' => 'Completed Tasks & Shift Turnover History',
            'report-issue' => 'Report Maintenance Defect or Room Discrepancy',
            'reported-issues' => 'Reported Issues & Facilities Work Orders Log',
            'lost-found' => 'Lost & Found Vault & Chain of Custody Registry',
            'housekeeping-supplies' => 'Housekeeping Supplies & Chemical Par Stock Hub',
            'reports' => 'Housekeeping Operational Reports & SLA Analytics',
            'linen-records' => 'Linen Inventory, Par Levels & Laundry Cycles',
            'notifications' => 'Housekeeping Dispatch Notifications & Operational Alerts',
            'tasks' => 'Housekeeping Tasks & Operational Dispatch',
            'service-requests' => 'Service Requests Hub & Guest Amenities Delivery',
            'maintenance-requests' => 'Maintenance Requests & Engineering Dispatch Queue',
            'linen-supplies' => 'Linen & Supplies Operational Command',
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
            ->assertSeeText('Executive Housekeeper Profile & Digital Duty Credentials');
    }

    public function test_billing_reference_designs_are_rendered_on_their_intended_pages(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $pages = [
            route('dashboard') => 'Cashier & Billing Operations Console',
            route('check-outs.index') => 'POS Incidentals & Direct Charge Terminal',
            route('check-outs.create') => 'Guest Bill Ledger & Invoicing Management',
            route('check-outs.history') => 'Historical Transactions & Cryptographic Audit Ledger',
            route('reports.index') => 'Shift Financial & Cashier Reports',
            route('profile.edit') => 'Cashier Profile & Terminal Credentials',
        ];

        foreach ($pages as $url => $signature) {
            $this->actingAs($cashier)
                ->get($url)
                ->assertOk()
                ->assertSeeText($signature)
                ->assertSee('aria-label="Billing Sidebar"', false);
        }
    }

    public function test_owner_reference_designs_are_rendered_on_their_intended_pages(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $pages = [
            'dashboard' => 'Hotel Ownership & Executive Performance Portal',
            'guests' => 'Guest Management & Corporate Roster Overview',
            'rooms' => 'Room Management Overview',
            'room-inventory' => 'Room Inventory Overview',
            'room-availability' => 'Room Availability & Booking Pace Matrix',
            'room-status' => 'Housekeeping Room Status Matrix',
            'reservations' => 'Reservations Overview',
            'checkins' => 'Check-in / Check-out Overview',
            'checkin-records' => 'Check-in Records & In-House Registry',
            'checkout-records' => 'Check-out Records & Turnover Archive',
            'billing' => 'Billing & Transactions Overview',
            'billing-records' => 'Billing Records & Account Archive',
            'housekeeping' => 'Housekeeping Department Overview',
            'housekeeping-requests' => 'Housekeeping Requests Ledger',
            'reports' => 'Reports & Records Overview',
            'operational-reports' => 'Operational Reports & Departmental Telemetry',
            'transaction-reports' => 'Transaction Reports & Financial Audit Intelligence',
            'records' => 'Master Operational & Historical Records Archive',
            'profile' => 'Owner Executive Profile & Oversight Security',
        ];

        foreach ($pages as $page => $signature) {
            $url = $page === 'dashboard'
                ? route('dashboard')
                : route('owner.page', ['page' => $page]);

            $this->actingAs($owner)
                ->get($url)
                ->assertOk()
                ->assertSeeText($signature)
                ->assertSee('aria-label="Owner Sidebar"', false);
        }
    }
}
