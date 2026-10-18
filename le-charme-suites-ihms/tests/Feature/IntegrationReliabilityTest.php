<?php

namespace Tests\Feature;

use App\Models\DepartmentMessage;
use App\Models\IntegrationEvent;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_open_the_integration_center(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($admin)
            ->get(route('admin.integration.index'))
            ->assertOk()
            ->assertSee('Integration & Reliability Center', false)
            ->assertSee('Embedded System Flipbook');

        $this->actingAs($receptionist)
            ->get(route('admin.integration.index'))
            ->assertForbidden();
    }

    public function test_department_messages_and_read_history_are_shared(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);

        $this->actingAs($receptionist)->post(route('messages.store'), [
            'recipient_department' => 'housekeeping',
            'subject' => 'Priority turnover request',
            'body' => 'Please prepare Room 301 before the next arrival.',
            'priority' => 'urgent',
        ])->assertRedirect();

        $message = DepartmentMessage::firstOrFail();

        $this->actingAs($housekeeper)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('Priority turnover request')
            ->assertSee('Unread');

        $this->actingAs($housekeeper)
            ->post(route('messages.read', $message))
            ->assertRedirect();

        $this->assertDatabaseHas('department_message_reads', [
            'department_message_id' => $message->id,
            'user_id' => $housekeeper->id,
        ]);
    }

    public function test_file_export_creates_a_checksum_transfer_history_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Room::create([
            'room_number' => '909',
            'room_type' => 'Standard Room',
            'rate' => 8764,
            'capacity' => 2,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.integration.export', ['dataset' => 'rooms', 'format' => 'csv']));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('x-ihms-checksum-sha256');

        $this->assertDatabaseHas('integration_events', [
            'actor_id' => $admin->id,
            'channel' => 'file_download',
            'dataset' => 'rooms',
            'format' => 'csv',
            'record_count' => 1,
            'status' => 'completed',
        ]);
    }

    public function test_api_requires_a_key_and_records_successful_data_transfer(): void
    {
        config(['integrations.api_key' => 'test-integration-key']);
        Room::create([
            'room_number' => '910',
            'room_type' => 'Deluxe King',
            'rate' => 13750,
            'capacity' => 3,
            'status' => 'available',
        ]);

        $this->getJson('/api/v1/datasets/rooms')->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer test-integration-key')
            ->getJson('/api/v1/datasets/rooms?limit=10')
            ->assertOk()
            ->assertJsonPath('dataset', 'rooms')
            ->assertJsonPath('record_count', 1)
            ->assertJsonPath('data.0.room_number', '910');

        $this->assertDatabaseHas('integration_events', [
            'channel' => 'api',
            'dataset' => 'rooms',
            'record_count' => 1,
        ]);
    }

    public function test_api_is_disabled_when_no_integration_key_is_configured(): void
    {
        config(['integrations.api_key' => null]);

        $this->getJson('/api/v1/status')
            ->assertStatus(503)
            ->assertJsonPath('message', 'The IHMS integration API is disabled until IHMS_API_KEY is configured.');

        $this->assertSame(0, IntegrationEvent::count());
    }

    public function test_interface_preferences_are_saved_per_user(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)->putJson(route('ui-preferences.update'), [
            'density' => 'compact',
            'font_scale' => 'large',
            'sidebar_default' => 'collapsed',
        ])->assertOk();

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertSame('compact', $user->fresh()->ui_preferences['density']);
        $this->assertSame('large', $user->fresh()->ui_preferences['font_scale']);
    }

    public function test_authenticated_staff_can_open_the_local_system_flipbook(): void
    {
        $user = User::factory()->create(['role' => 'housekeeping']);

        $this->actingAs($user)
            ->get(route('system-manual'))
            ->assertOk()
            ->assertSee('IHMS Integration Flipbook')
            ->assertSee('Shared Database')
            ->assertSee('API &amp; File Transfer', false);
    }
}
