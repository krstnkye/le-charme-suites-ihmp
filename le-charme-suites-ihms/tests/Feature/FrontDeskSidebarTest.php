<?php

namespace Tests\Feature;

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
}
