<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_share_the_responsive_admin_sidebar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-toggle', false)
            ->assertDontSee('data-page-back', false);

        foreach ([
            route('admin.users.index'),
            route('admin.roles.index'),
            route('admin.permissions.index'),
            route('admin.operations.index', ['module' => 'reservations']),
            route('admin.audit-logs.index'),
            route('admin.settings.index'),
            route('profile.edit'),
        ] as $url) {
            $this->actingAs($admin)->get($url)
                ->assertOk()
                ->assertSee('aria-label="Administrator Sidebar"', false)
                ->assertSee('data-universal-sidebar', false)
                ->assertSee('data-sidebar-toggle', false)
                ->assertSee('data-page-back', false);
        }
    }

    public function test_housekeeping_pages_share_the_responsive_housekeeping_sidebar(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);

        $this->actingAs($housekeeper)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-toggle', false)
            ->assertDontSee('data-page-back', false);

        foreach ([
            route('rooms.index'),
            route('housekeeping.page', ['page' => 'cleaning-tasks']),
            route('housekeeping.page', ['page' => 'guest-requests']),
            route('housekeeping.page', ['page' => 'report-issue']),
            route('housekeeping.page', ['page' => 'linen-records']),
            route('housekeeping.page', ['page' => 'notifications']),
            route('profile.edit'),
        ] as $url) {
            $this->actingAs($housekeeper)->get($url)
                ->assertOk()
                ->assertSee('aria-label="Housekeeping Sidebar"', false)
                ->assertSee('data-universal-sidebar', false)
                ->assertSee('data-sidebar-toggle', false)
                ->assertSee('data-page-back', false);
        }
    }

    public function test_billing_pages_share_the_responsive_billing_sidebar(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-toggle', false)
            ->assertDontSee('data-page-back', false);

        foreach ([
            route('check-outs.create'),
            route('check-outs.index'),
            route('check-outs.history'),
            route('reports.index'),
            route('profile.edit'),
        ] as $url) {
            $this->actingAs($cashier)->get($url)
                ->assertOk()
                ->assertSee('aria-label="Billing Sidebar"', false)
                ->assertSee('data-universal-sidebar', false)
                ->assertSee('data-sidebar-toggle', false)
                ->assertSee('data-page-back', false);
        }
    }

    public function test_owner_pages_share_the_responsive_owner_sidebar(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-toggle', false)
            ->assertDontSee('data-page-back', false);

        foreach ([
            route('owner.page', ['page' => 'rooms']),
            route('owner.page', ['page' => 'reservations']),
            route('owner.page', ['page' => 'billing-records']),
            route('owner.page', ['page' => 'housekeeping']),
            route('owner.page', ['page' => 'reports']),
            route('owner.page', ['page' => 'profile']),
        ] as $url) {
            $this->actingAs($owner)->get($url)
                ->assertOk()
                ->assertSee('aria-label="Owner Sidebar"', false)
                ->assertSee('data-universal-sidebar', false)
                ->assertSee('data-sidebar-toggle', false)
                ->assertSee('data-page-back', false);
        }
    }
}
