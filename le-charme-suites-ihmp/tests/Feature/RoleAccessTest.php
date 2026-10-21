<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_sees_front_desk_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Front Desk Dashboard');
    }

    public function test_admin_sees_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Property Systems Integrity & Control', false)
            ->assertSee('User & Role Management', false)
            ->assertSee('User &amp; Role Management', false)
            ->assertSee('Room Inventory')
            ->assertSee('Room Types')
            ->assertSee('Room Rates')
            ->assertSee('Transactions / Billing')
            ->assertSee('Reports')
            ->assertSee('Activity Logs')
            ->assertSee('System Settings')
            ->assertSee('My Profile');
    }

    public function test_admin_can_view_user_and_role_management_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'Front Desk Sample',
            'role' => 'receptionist',
        ]);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Role-Based Access Control Console')
            ->assertSee('Active Personnel Directory')
            ->assertSee('Permissions Table Inspector')
            ->assertSee('Front Desk Sample');
    }

    public function test_admin_can_update_and_delete_user_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create([
            'name' => 'Original Staff',
            'email' => 'original.staff@lecharmehotel.com',
            'role' => 'receptionist',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('data-user-edit', false)
            ->assertSee('data-user-delete', false);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Provisioned Cashier',
                'email' => 'provisioned.billing@lecharmehotel.com',
                'role' => 'cashier',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'name' => 'Provisioned Cashier',
            'email' => 'provisioned.billing@lecharmehotel.com',
            'role' => 'cashier',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $staff), [
                'name' => 'Updated Staff',
                'email' => 'updated.staff@lecharmehotel.com',
                'role' => 'cashier',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Updated Staff',
            'email' => 'updated.staff@lecharmehotel.com',
            'role' => 'cashier',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $staff))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', [
            'id' => $staff->id,
        ]);
    }

    public function test_admin_cannot_delete_their_own_active_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_can_view_roles_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Role Directory & Access Tiers', false)
            ->assertSee('System Admin')
            ->assertSee('Front Desk')
            ->assertSee('Open Permissions Table')
            ->assertSee('My Profile');
    }

    public function test_admin_can_view_and_save_permissions_matrix(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('admin.permissions.index', ['role' => 'receptionist']))
            ->assertOk()
            ->assertSee('Granular Permissions Table')
            ->assertSee('Front Desk Lead')
            ->assertSee('Access Table');

        $this->actingAs($user)
            ->post(route('admin.permissions.save'), [
                'role' => 'receptionist',
                'permissions' => [
                    'reservations' => ['read' => '1', 'write' => '1'],
                ],
            ])
            ->assertRedirect(route('admin.permissions.index', ['role' => 'receptionist']))
            ->assertSessionHas('status');
    }

    public function test_front_desk_cannot_access_permissions_matrix(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($user)
            ->get(route('admin.permissions.index'))
            ->assertForbidden();
    }

    public function test_admin_operations_use_dedicated_administrator_workspace(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        foreach (['reservations', 'check-ins', 'check-outs', 'housekeeping', 'transactions', 'room-inventory', 'room-types', 'room-rates', 'room-status', 'reports'] as $module) {
            $response = $this->actingAs($user)
                ->get(route('admin.operations.index', ['module' => $module]))
                ->assertOk()
                ->assertSee('User &amp; Role Management', false)
                ->assertSee('Guest Management')
                ->assertSee('Transactions / Billing')
                ->assertSee('Room Inventory')
                ->assertSee('Room Types')
                ->assertSee('Room Rates')
                ->assertSee('Reports')
                ->assertSee('Activity Logs')
                ->assertSee('System Settings')
                ->assertSee('My Profile');

            if ($module === 'reports') {
                $response
                    ->assertSee('Departmental Reports Center')
                    ->assertSee('Standard Institutional Report Directory')
                    ->assertSee('data-report-filter="financial"', false)
                    ->assertSee('data-report-group="housekeeping"', false);
            } elseif ($module === 'room-types') {
                $response
                    ->assertSee('Room Types &amp; Suite Specifications', false)
                    ->assertSee('Quick Inspector')
                    ->assertSee('id="exportRoomTypesCatalog"', false)
                    ->assertSee('id="addRoomTypeButton"', false)
                    ->assertSee('id="roomTypeModal"', false)
                    ->assertSee('data-room-type-action="edit"', false);
            } elseif ($module === 'room-rates') {
                $response
                    ->assertSee('Room Rate Management', false)
                    ->assertSee('Room Rates Table');
            } else {
                $response
                    ->assertSee('Administrator-only workspace')
                    ->assertSee('Administrator Operations');
            }
        }
    }

    public function test_admin_can_open_add_room_page_without_room_route_conflict(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('rooms.create'))
            ->assertOk()
            ->assertSee('Add Room Inventory Record')
            ->assertSee('Back to Room Inventory');
    }

    public function test_admin_room_type_modal_creates_real_room_inventory_record(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('admin.operations.index', ['module' => 'room-types']))
            ->assertOk()
            ->assertSee('name="room_number"', false)
            ->assertSee('name="rate"', false)
            ->assertSee('Save Room Type');

        $this->actingAs($user)
            ->post(route('rooms.store'), [
                'redirect_to' => 'admin.room-types',
                'room_number' => '401',
                'room_type' => 'Premier Bay Suite',
                'rate' => 6200,
                'capacity' => 4,
                'status' => 'available',
                'description' => 'Executive bay-facing suite.',
            ])
            ->assertRedirect(route('admin.operations.index', ['module' => 'room-types']))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('rooms', [
            'room_number' => '401',
            'room_type' => 'Premier Bay Suite',
            'rate' => 6200,
            'capacity' => 4,
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->get(route('admin.operations.index', ['module' => 'room-types']))
            ->assertOk()
            ->assertSee('Premier Bay Suite')
            ->assertSee('1 rooms')
            ->assertSee('PHP 6,200.00');

        $this->actingAs($user)
            ->get(route('admin.operations.index', ['module' => 'room-inventory']))
            ->assertOk()
            ->assertSee('401')
            ->assertSee('Premier Bay Suite');

        $this->actingAs($user)
            ->get(route('admin.operations.index', ['module' => 'room-rates']))
            ->assertOk()
            ->assertSee('Premier Bay Suite')
            ->assertSee('PHP 6,200.00');
    }

    public function test_front_desk_cannot_access_administrator_operations_workspace(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($user)
            ->get(route('admin.operations.index', ['module' => 'reservations']))
            ->assertForbidden();
    }

    public function test_admin_can_view_system_settings_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Global System Settings')
            ->assertSeeText('Property Identity & Licensing')
            ->assertSee('Front Desk Operational Defaults')
            ->assertSeeText('Data Retention & Backups');
    }

    public function test_admin_can_view_security_and_audit_logs_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSeeText('Security & Compliance Activity Log')
            ->assertSee('Protected Activity History')
            ->assertSeeText('IHMP Access Control & Security Policies')
            ->assertSee('Active Sessions');
    }

    public function test_admin_has_separate_guest_management_console(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Guest::create([
            'first_name' => 'Admin',
            'last_name' => 'Guest',
            'email' => 'admin.guest@example.com',
            'phone' => '09170000000',
            'identification_type' => 'Passport',
            'identification_number' => 'P1234567',
        ]);

        $this->actingAs($user)
            ->get(route('admin.guests.index'))
            ->assertOk()
            ->assertSee('Administrative Guest Registry')
            ->assertSee('Master Guest Registry')
            ->assertSee('Lifetime Billing')
            ->assertSee('Admin Guest');
    }

    public function test_front_desk_cannot_access_admin_guest_console(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($user)
            ->get(route('admin.guests.index'))
            ->assertForbidden();
    }

    public function test_employee_can_view_and_update_only_their_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Front Desk Employee',
            'role' => 'receptionist',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSeeText('Front Desk Agent Profile & Station Access')
            ->assertSeeText('Station Access')
            ->assertSeeText('Security & Account Status')
            ->assertSee('Front Desk Employee')
            ->assertSee('receptionist');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Front Desk Employee',
                'email' => $user->email,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Front Desk Employee',
            'role' => 'receptionist',
        ]);
    }

    public function test_admin_profile_keeps_administrator_sidebar(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('User &amp; Role Management', false)
            ->assertSee('Guest Management')
            ->assertSee('Room Inventory')
            ->assertSee('Reports')
            ->assertSee('My Profile');
    }

    public function test_receptionist_can_view_reservation_management_page(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($user)
            ->get('/reservations')
            ->assertOk()
            ->assertSee('Reservation Management');
    }

    public function test_receptionist_can_create_reservation_with_manual_guest_details(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);
        $room = Room::create([
            'room_number' => '201',
            'room_type' => 'Deluxe',
            'rate' => 2800,
            'capacity' => 3,
            'status' => 'available',
        ]);

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'manual_guest_first_name' => 'Juan',
            'manual_guest_last_name' => 'Dela Cruz',
            'manual_guest_email' => 'juan@example.com',
            'manual_guest_phone' => '09171234567',
            'room_id' => $room->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'number_of_guests' => 2,
            'notes' => 'Walk-in guest details.',
        ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('guests', [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
        ]);
        $this->assertDatabaseHas('reservations', [
            'room_id' => $room->id,
            'status' => 'reserved',
        ]);
    }

    public function test_receptionist_can_view_reservation_details_page(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);
        $guest = Guest::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.com',
        ]);
        $room = Room::create([
            'room_number' => '101',
            'room_type' => 'Standard',
            'rate' => 1800,
            'capacity' => 2,
            'status' => 'reserved',
        ]);
        $reservation = Reservation::create([
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'number_of_guests' => 2,
            'status' => 'reserved',
        ]);

        $this->actingAs($user)
            ->get(route('reservations.show', $reservation))
            ->assertOk()
            ->assertSee('Reservation Details')
            ->assertSee('Maria Santos');
    }

    public function test_housekeeping_can_access_rooms_but_not_guest_management(): void
    {
        $user = User::factory()->create(['role' => 'housekeeping']);

        $this->actingAs($user)->get('/rooms')->assertOk();
        $this->actingAs($user)->get('/guests')->assertForbidden();
    }

    public function test_housekeeping_sees_role_specific_dashboard_and_menu(): void
    {
        $user = User::factory()->create(['role' => 'housekeeping']);
        Room::create([
            'room_number' => '501',
            'room_type' => 'Suite',
            'rate' => 4500,
            'capacity' => 4,
            'status' => 'cleaning',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Housekeeping Dashboard')
            ->assertSee('Room Status')
            ->assertSee('My Assigned Rooms')
            ->assertSee('Room Inspection')
            ->assertSee('Cleaning Tasks')
            ->assertSee('Priority Rooms')
            ->assertSee('Service Requests')
            ->assertSee('Maintenance Requests')
            ->assertSee('Lost &amp; Found', false)
            ->assertDontSee('Linen &amp; Supplies', false)
            ->assertSee('Notifications')
            ->assertSee('Room 501');
    }

    public function test_housekeeping_can_view_room_status_page(): void
    {
        $user = User::factory()->create(['role' => 'housekeeping']);

        $this->actingAs($user)
            ->get('/rooms')
            ->assertOk()
            ->assertSee('Room Status & Live Floor Table', false)
            ->assertSee('Room Management')
            ->assertSee('Housekeeping Tasks')
            ->assertDontSee('Reservations');
    }

    public function test_housekeeping_room_detail_edit_and_profile_keep_housekeeping_workspace(): void
    {
        $user = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::create([
            'room_number' => '601',
            'room_type' => 'Deluxe',
            'rate' => 3200,
            'capacity' => 2,
            'status' => 'cleaning',
        ]);

        foreach ([
            route('rooms.show', $room) => 'Housekeeping / Room Status',
            route('rooms.edit', $room) => 'Update Room 601',
            route('profile.edit') => 'Housekeeping Account',
        ] as $url => $heading) {
            $this->actingAs($user)
                ->get($url)
                ->assertOk()
                ->assertSee($heading)
                ->assertSee('Housekeeping Tasks')
                ->assertDontSee('Reservations');
        }

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Executive Housekeeper Profile &amp; Digital Duty Login Details', false)
            ->assertSee('Personal Details &amp; Contact Information', false)
            ->assertSee('Secure Access &amp; Digital Login Details', false)
            ->assertSee('Shift &amp; Duty Roster Preferences', false)
            ->assertSee('Certifications &amp; Hospitality Records', false);
    }

    public function test_housekeeping_sidebar_links_open_dedicated_housekeeping_pages(): void
    {
        $user = User::factory()->create(['role' => 'housekeeping']);
        $cleaningRoom = Room::create([
            'room_number' => '701',
            'room_type' => 'Standard',
            'rate' => 1800,
            'capacity' => 2,
            'status' => 'cleaning',
        ]);
        Room::create([
            'room_number' => '702',
            'room_type' => 'Deluxe',
            'rate' => 2800,
            'capacity' => 3,
            'status' => 'maintenance',
        ]);
        Room::create([
            'room_number' => '703',
            'room_type' => 'Suite',
            'rate' => 4500,
            'capacity' => 4,
            'status' => 'occupied',
        ]);
        RoomStatusLog::create([
            'room_id' => $cleaningRoom->id,
            'status' => 'available',
            'notes' => 'Turnover certified by housekeeping supervisor.',
            'changed_at' => now(),
        ]);

        foreach ([
            'assigned-rooms' => 'My Assigned Rooms',
            'room-inspection' => 'Room Inspection',
            'tasks' => 'Housekeeping Tasks',
            'cleaning-tasks' => 'Cleaning Tasks',
            'priority-rooms' => 'Priority Rooms',
            'completed-tasks' => 'Completed Tasks',
            'service-requests' => 'Service Requests',
            'guest-requests' => 'Guest Requests',
            'special-cleaning' => 'Special Cleaning',
            'maintenance-requests' => 'Maintenance Requests',
            'report-issue' => 'Report Issue',
            'reported-issues' => 'Reported Issues',
            'lost-found' => 'Lost &amp; Found',
            'reports' => 'Housekeeping Reports',
            'notifications' => 'Notifications',
        ] as $page => $heading) {
            $this->actingAs($user)
                ->get(route('housekeeping.page', $page))
                ->assertOk()
                ->assertSee($heading, false)
                ->assertSee('Housekeeping Workspace')
                ->assertSee('Dashboard')
                ->assertDontSee('Reservations');
        }

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'cleaning-tasks'))
            ->assertOk()
            ->assertSee('Unit Room Preparation &amp; Cleaning Tasks List', false)
            ->assertSee('Live Cleaning Task Board')
            ->assertSee('Attendant Workload')
            ->assertSee('Room 701');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'priority-rooms'))
            ->assertOk()
            ->assertSee('Priority Rooms &amp; Fast Cleaning', false)
            ->assertSee('Housekeeping Rush Ready Status Command')
            ->assertSee('Front Desk Messages')
            ->assertSee('Room 701')
            ->assertSee('Room 702');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'guest-requests'))
            ->assertOk()
            ->assertSee('Guest Service Requests &amp; In-Room Amenities Delivery', false)
            ->assertSee('Live Request Assignment Board')
            ->assertSee('Completed Requests Log &amp; Confirmation Signatures', false)
            ->assertSee('Room 703');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'special-cleaning'))
            ->assertOk()
            ->assertSee('Special Cleaning &amp; Room Sanitation', false)
            ->assertSee('Active Special Cleaning Tasks')
            ->assertSee('Safety &amp; Compliance', false)
            ->assertSee('Room 701')
            ->assertSee('Room 702');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'completed-tasks'))
            ->assertOk()
            ->assertSee('Completed Tasks &amp; Shift Cleaning History', false)
            ->assertSee('Shift 1 Completed Units Records')
            ->assertSee('Audit Certificate Preview')
            ->assertSee('Turnover certified by housekeeping supervisor.')
            ->assertSee('701');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'report-issue'))
            ->assertOk()
            ->assertSee('Report Maintenance Defect or Room Discrepancy')
            ->assertSee('action="'.route('maintenance-reports.store').'"', false)
            ->assertSee('Submit Report')
            ->assertSee('Message Front Desk')
            ->assertSee('Room 701');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'reported-issues'))
            ->assertOk()
            ->assertSee('Reported Issues &amp; Facilities Work Orders Log', false)
            ->assertSee('Open &amp; In Progress', false)
            ->assertSee('Report New Issue');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'lost-found'))
            ->assertOk()
            ->assertSee('Lost &amp; Found Storage and Item History', false)
            ->assertSee('Log a Found Item')
            ->assertSee('action="'.route('lost-found-items.store').'"', false)
            ->assertSee('Room 701');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'reports'))
            ->assertOk()
            ->assertSee('Housekeeping Operational Reports &amp; Response Time Analytics', false)
            ->assertSee('Attendant Productivity &amp; Quality Audit Records', false)
            ->assertSee('Daily Housekeeper Performance &amp; Pacing Records', false)
            ->assertSee('Custom Report Builder');

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'notifications'))
            ->assertOk()
            ->assertSee('Housekeeping Assignment Notifications &amp; Operational Alerts', false)
            ->assertSee('Live Assignment Center')
            ->assertSee('Radio Broadcast Log')
            ->assertSee('Supervisor Assignment Summary');

        foreach (['linen-supplies', 'linen-records', 'housekeeping-supplies'] as $removedPage) {
            $this->actingAs($user)
                ->get(route('housekeeping.page', $removedPage))
                ->assertNotFound();
        }
    }

    public function test_other_departments_cannot_open_housekeeping_pages(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($user)
            ->get(route('housekeeping.page', 'assigned-rooms'))
            ->assertForbidden();
    }

    public function test_cashier_can_access_reports_but_not_reservations(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)
            ->get('/reports')
            ->assertOk()
            ->assertSee('Shift Financial &amp; Cashier Reports', false)
            ->assertSee('Standard Cashier Report Directory')
            ->assertSee('Billing Department Access')
            ->assertSee('Run Shift Balance Report');

        $this->actingAs($user)->get('/reservations')->assertForbidden();
    }

    public function test_cashier_sees_billing_department_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Cashier &amp; Billing Dashboard', false)
            ->assertSee('Billing Department Access')
            ->assertSee('Departures &amp; Guest Bills for Payment', false)
            ->assertSee('Recent Transactions')
            ->assertSee('Dashboard')
            ->assertSee('Billing')
            ->assertSee('Transactions')
            ->assertSee('Transaction History')
            ->assertSee('Reports')
            ->assertSee('My Profile')
            ->assertSee('Logout');
    }

    public function test_cashier_can_view_transaction_console(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)
            ->get('/check-outs')
            ->assertOk()
            ->assertSee('Guest Charges &amp; Payments', false);
    }

    public function test_cashier_can_view_transaction_history(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)
            ->get('/check-outs/history')
            ->assertOk()
            ->assertSee('Transaction &amp; Payment History', false);
    }

    public function test_cashier_profile_uses_billing_department_workspace(): void
    {
        $user = User::factory()->create([
            'name' => 'Billing Staff',
            'role' => 'cashier',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Cashier Profile &amp; Account', false)
            ->assertSee('Billing Identity Vault')
            ->assertSee('Billing Department Access')
            ->assertSee('Authorized Billing Devices')
            ->assertSee('Billing Alerts')
            ->assertSee('Billing Staff');
    }

    public function test_owner_is_not_an_operational_department_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();

        $this->assertFalse(app('router')->has('owner.page'));
    }

    public function test_owner_cannot_access_admin_only_or_change_operational_records(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $room = Room::create([
            'room_number' => '801',
            'room_type' => 'Suite',
            'rate' => 4500,
            'capacity' => 4,
            'status' => 'available',
        ]);

        $this->actingAs($owner)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('admin.settings.index'))->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('rooms.update', $room), [
                'room_number' => '801',
                'room_type' => 'Suite',
                'rate' => 4500,
                'capacity' => 4,
                'status' => 'maintenance',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'available',
        ]);
    }
}
