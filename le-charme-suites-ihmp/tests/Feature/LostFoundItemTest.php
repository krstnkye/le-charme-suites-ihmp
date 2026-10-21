<?php

namespace Tests\Feature;

use App\Models\LostFoundItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LostFoundItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_housekeeper_can_log_a_found_item(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = Room::factory()->create(['room_number' => '308']);

        $response = $this->actingAs($housekeeper)->post(route('lost-found-items.store'), [
            'item_name' => 'Black leather wallet',
            'category' => 'valuables',
            'room_id' => $room->id,
            'storage_location' => 'Front office safe',
        ]);

        $item = LostFoundItem::firstOrFail();
        $response->assertSessionHas('status', 'Black leather wallet logged as '.$item->reference().'.');
        $this->assertSame('in_custody', $item->status);
        $this->assertSame($housekeeper->id, $item->found_by);
        $this->assertSame($room->id, $item->room_id);
    }

    public function test_an_item_needs_a_room_or_location(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);

        $response = $this->actingAs($housekeeper)->post(route('lost-found-items.store'), [
            'item_name' => 'Umbrella',
            'category' => 'other',
            'storage_location' => 'Housekeeping office',
        ]);

        $response->assertSessionHasErrors(['found_location' => 'Choose the room or describe where the item was found.']);
        $this->assertDatabaseCount('lost_found_items', 0);
    }

    public function test_releasing_to_an_owner_records_who_claimed_it_and_their_id(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $item = LostFoundItem::factory()->create();

        $response = $this->actingAs($housekeeper)->patch(route('lost-found-items.update', $item), [
            'status' => 'claimed',
            'claimed_by_name' => 'Maria Santos',
            'claimant_id_details' => 'Passport P1234567A',
        ]);

        $response->assertSessionHas('status', $item->reference().' released to Maria Santos.');
        $item->refresh();
        $this->assertSame('claimed', $item->status);
        $this->assertSame($housekeeper->id, $item->released_by);
        $this->assertNotNull($item->released_at);
    }

    public function test_claims_need_the_claimant_name_and_id(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $item = LostFoundItem::factory()->create();

        $response = $this->actingAs($housekeeper)->patch(route('lost-found-items.update', $item), ['status' => 'claimed']);

        $response->assertSessionHasErrors(['claimed_by_name', 'claimant_id_details']);
        $this->assertSame('in_custody', $item->fresh()->status);
    }

    public function test_billing_staff_cannot_log_items(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $response = $this->actingAs($cashier)->post(route('lost-found-items.store'), [
            'item_name' => 'Keys',
            'category' => 'other',
            'found_location' => 'Lobby',
            'storage_location' => 'Front desk',
        ]);

        $response->assertForbidden();
    }

    public function test_lost_and_found_page_lists_items_in_custody(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        LostFoundItem::factory()->create(['item_name' => 'Silver bracelet', 'category' => 'valuables']);
        LostFoundItem::factory()->create(['item_name' => 'Old newspaper', 'status' => 'disposed']);

        $response = $this->actingAs($housekeeper)->get(route('housekeeping.page', 'lost-found'));

        $response->assertOk();
        $response->assertSeeText('Silver bracelet');
        $response->assertDontSeeText('Old newspaper');
    }
}
