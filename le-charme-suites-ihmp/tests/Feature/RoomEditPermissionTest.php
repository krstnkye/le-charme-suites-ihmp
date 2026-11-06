<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoomEditPermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, string|int|float}>
     */
    public static function staffDetailChanges(): array
    {
        $changes = [
            'room number' => ['room_number', '999'],
            'room type' => ['room_type', 'Presidential Suite'],
            'rate' => ['rate', 99.5],
            'capacity' => ['capacity', 9],
        ];
        $cases = [];

        foreach (['housekeeping', 'receptionist'] as $role) {
            foreach ($changes as $label => [$field, $value]) {
                $cases["{$role} changes {$label}"] = [$role, $field, $value];
            }
        }

        return $cases;
    }

    #[DataProvider('staffDetailChanges')]
    public function test_staff_cannot_change_room_master_data(string $role, string $field, string|int|float $value): void
    {
        $user = User::factory()->create(['role' => $role]);
        $room = $this->customRoom();

        $response = $this->actingAs($user)->put(route('rooms.update', $room), [
            ...$this->currentValues($room),
            $field => $value,
        ]);

        $response->assertSessionHasErrors(['room_number' => 'Only an administrator can change the room number, type, rate, or capacity.']);
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'room_number' => '410',
            'room_type' => 'Garden Loft',
            'rate' => 5000,
            'capacity' => 2,
        ]);
    }

    public function test_housekeeping_can_still_change_status_and_notes(): void
    {
        $housekeeper = User::factory()->create(['role' => 'housekeeping']);
        $room = $this->customRoom();

        $response = $this->actingAs($housekeeper)->put(route('rooms.update', $room), [
            ...$this->currentValues($room),
            'rate' => '5000.00',
            'status' => 'maintenance',
            'description' => 'Air conditioner leaking.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'maintenance',
            'description' => 'Air conditioner leaking.',
            'rate' => 5000,
        ]);
        $this->assertDatabaseHas('room_status_logs', ['room_id' => $room->id, 'status' => 'maintenance']);
    }

    public function test_administrator_can_change_room_master_data(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);
        $room = $this->customRoom();

        $this->actingAs($administrator)->put(route('rooms.update', $room), [
            ...$this->currentValues($room),
            'room_number' => '411',
            'rate' => 5200,
            'capacity' => 3,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'room_number' => '411', 'rate' => 5200, 'capacity' => 3]);
    }

    public function test_room_edit_form_marks_master_data_read_only_for_staff(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($receptionist)
            ->get(route('rooms.edit', $this->customRoom()))
            ->assertOk()
            ->assertSee('Room number, type, rate, and capacity can only be changed by an administrator.');
    }

    private function customRoom(): Room
    {
        return Room::factory()->create([
            'room_number' => '410',
            'room_type' => 'Garden Loft',
            'rate' => 5000,
            'capacity' => 2,
            'status' => 'available',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function currentValues(Room $room): array
    {
        return [
            'room_number' => $room->room_number,
            'room_type' => $room->room_type,
            'rate' => $room->rate,
            'capacity' => $room->capacity,
            'status' => $room->status,
            'description' => $room->description,
        ];
    }
}
