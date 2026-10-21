<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\MaintenanceReport;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_housekeeper_can_report_an_issue_and_take_the_room_out_of_service(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['room_number' => '214', 'status' => 'available']);

        $response = $this->actingAs($housekeeper)->post(route('maintenance-reports.store'), [
            'room_id' => $room->id,
            'category' => 'hvac',
            'severity' => 'critical',
            'description' => 'Aircon leaking water onto the bed.',
            'take_out_of_service' => '1',
        ]);

        $response->assertRedirect(route('housekeeping.page', 'reported-issues'));
        $response->assertSessionHas('status', 'Issue reported. Room 214 is now out of service until it is resolved.');
        $this->assertDatabaseHas('maintenance_reports', [
            'room_id' => $room->id,
            'category' => 'hvac',
            'severity' => 'critical',
            'status' => 'open',
            'room_taken_out_of_service' => true,
            'reported_by' => $housekeeper->id,
        ]);
        $this->assertSame('maintenance', $room->fresh()->status);
    }

    public function test_an_occupied_room_is_never_taken_out_of_service(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['room_number' => '305', 'status' => 'occupied']);
        CheckIn::factory()->forReservation(Reservation::factory()->checkedIn()->for($room)->create())->create();

        $response = $this->actingAs($housekeeper)->post(route('maintenance-reports.store'), [
            'room_id' => $room->id,
            'category' => 'plumbing',
            'severity' => 'high',
            'description' => 'Shower has no hot water.',
            'take_out_of_service' => '1',
        ]);

        $response->assertSessionHas('status', 'Issue reported. Room 305 stays occupied because a guest is checked in.');
        $this->assertSame('occupied', $room->fresh()->status);
        $this->assertDatabaseHas('maintenance_reports', ['room_id' => $room->id, 'room_taken_out_of_service' => false]);
    }

    public function test_a_report_needs_a_room_or_a_location(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);

        $response = $this->actingAs($housekeeper)->post(route('maintenance-reports.store'), [
            'category' => 'electrical',
            'severity' => 'normal',
            'description' => 'Flickering light.',
        ]);

        $response->assertSessionHasErrors(['location' => 'Choose a room or describe where the problem is.']);
        $this->assertDatabaseCount('maintenance_reports', 0);
    }

    public function test_resolving_a_report_returns_the_room_for_cleaning(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['status' => 'maintenance']);
        $report = MaintenanceReport::factory()->create(['room_id' => $room->id, 'room_taken_out_of_service' => true]);

        $response = $this->actingAs($housekeeper)->patch(route('maintenance-reports.update', $report), [
            'status' => 'resolved',
            'resolution_notes' => 'Replaced the aircon drain hose.',
        ]);

        $response->assertSessionHas('status', 'Maintenance report updated.');
        $report->refresh();
        $this->assertSame('resolved', $report->status);
        $this->assertSame($housekeeper->id, $report->resolved_by);
        $this->assertNotNull($report->resolved_at);
        $this->assertSame('cleaning', $room->fresh()->status);
    }

    public function test_resolving_needs_resolution_notes(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $report = MaintenanceReport::factory()->create();

        $response = $this->actingAs($housekeeper)->patch(route('maintenance-reports.update', $report), ['status' => 'resolved']);

        $response->assertSessionHasErrors(['resolution_notes' => 'Describe how the issue was fixed before marking it resolved.']);
        $this->assertSame('open', $report->fresh()->status);
    }

    public function test_billing_staff_cannot_file_maintenance_reports(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $response = $this->actingAs($cashier)->post(route('maintenance-reports.store'), [
            'location' => 'Lobby',
            'category' => 'other',
            'severity' => 'low',
            'description' => 'Loose tile.',
        ]);

        $response->assertForbidden();
    }

    public function test_reported_issues_page_lists_saved_reports(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping', 'name' => 'Cyrene Lagman']);
        $room = Room::factory()->create(['room_number' => '402']);
        MaintenanceReport::factory()->create(['room_id' => $room->id, 'reported_by' => $housekeeper->id, 'description' => 'Door lock battery low.']);

        $response = $this->actingAs($housekeeper)->get(route('housekeeping.page', 'reported-issues'));

        $response->assertOk();
        $response->assertSeeText('Room 402 - Plumbing & Water');
        $response->assertSeeText('Door lock battery low.');
        $response->assertSeeText('Reported by Cyrene Lagman');
    }
}
