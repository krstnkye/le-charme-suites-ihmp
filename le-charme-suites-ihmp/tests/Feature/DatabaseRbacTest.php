<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_role_permissions_are_enforced(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($receptionist)->get(route('rooms.index'))->assertOk();
        $this->actingAs($cashier)->get(route('rooms.index'))->assertForbidden();
    }

    public function test_saved_permission_overrides_are_persisted_and_enforced(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($admin)
            ->post(route('admin.permissions.save'), [
                'role' => 'receptionist',
                'permissions' => [
                    'reservations' => ['read' => '1'],
                ],
            ])
            ->assertRedirect(route('admin.permissions.index', ['role' => 'receptionist']));

        $this->assertDatabaseHas('role_permissions', [
            'role' => 'receptionist',
            'permission' => 'rooms.read',
            'granted' => false,
        ]);

        $this->actingAs($receptionist)->get(route('rooms.index'))->assertForbidden();
    }

    public function test_reset_removes_overrides_and_restores_reviewed_defaults(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        RolePermission::create([
            'role' => 'receptionist',
            'permission' => 'rooms.read',
            'granted' => false,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.permissions.reset'), ['role' => 'receptionist'])
            ->assertRedirect(route('admin.permissions.index', ['role' => 'receptionist']));

        $this->assertDatabaseMissing('role_permissions', ['role' => 'receptionist']);
        $this->actingAs($receptionist)->get(route('rooms.index'))->assertOk();
    }
}
