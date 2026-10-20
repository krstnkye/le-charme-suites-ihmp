<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\GuestIdentificationReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestIdentificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_desk_can_save_the_guest_id_type_number_and_photo(): void
    {
        Storage::fake('local');
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $guest = Guest::factory()->create();

        $response = $this->actingAs($receptionist)->post(route('guests.identification.update', $guest), [
            'identification_type' => 'PhilSys National ID',
            'identification_number' => '1234-5678-9012-3456',
            'identification_photo' => $this->idPhoto(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('identification_status', 'Guest ID saved.');
        $guest->refresh();
        $this->assertSame('PhilSys National ID', $guest->identification_type);
        $this->assertSame('1234-5678-9012-3456', $guest->identification_number);
        Storage::disk('local')->assertExists($guest->identification_photo_path);
        $this->assertTrue($guest->hasCompleteIdentification());
    }

    public function test_a_photo_is_required_when_none_is_on_file(): void
    {
        Storage::fake('local');
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $guest = Guest::factory()->create();

        $response = $this->actingAs($receptionist)->post(route('guests.identification.update', $guest), [
            'identification_type' => 'UMID',
            'identification_number' => 'CRN-0111-2222333-4',
        ]);

        $response->assertSessionHasErrors(['identification_photo' => 'Upload a photo of the guest ID.']);
        $this->assertNull($guest->fresh()->identification_number);
    }

    public function test_unknown_id_types_are_rejected(): void
    {
        Storage::fake('local');
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $guest = Guest::factory()->create();

        $response = $this->actingAs($receptionist)->post(route('guests.identification.update', $guest), [
            'identification_type' => 'Library Card',
            'identification_number' => 'LIB-1',
            'identification_photo' => $this->idPhoto(),
        ]);

        $response->assertSessionHasErrors('identification_type');
        Storage::disk('local')->assertDirectoryEmpty('guest-ids');
    }

    public function test_front_desk_can_email_the_guest_an_id_reminder(): void
    {
        Notification::fake();
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $guest = Guest::factory()->create(['email' => 'demo.professor@example.com']);
        $reservation = Reservation::factory()->for($guest)->create();

        $response = $this->actingAs($receptionist)->post(route('reservations.identification-reminder', $reservation));

        $response->assertSessionHas('identification_status', 'ID reminder sent to demo.professor@example.com.');
        Notification::assertSentOnDemand(
            GuestIdentificationReminder::class,
            fn (GuestIdentificationReminder $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'demo.professor@example.com'
                && $notification->reservation->is($reservation),
        );
        $this->assertNotNull($guest->fresh()->identification_reminded_at);
    }

    public function test_reminder_needs_a_guest_email(): void
    {
        Notification::fake();
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = Reservation::factory()->for(Guest::factory()->create(['email' => null]))->create();

        $response = $this->actingAs($receptionist)->post(route('reservations.identification-reminder', $reservation));

        $response->assertSessionHasErrors('identification');
        Notification::assertNothingSent();
    }

    public function test_only_staff_with_guest_access_can_view_the_id_photo(): void
    {
        Storage::fake('local');
        $guest = Guest::factory()->create([
            'identification_photo_path' => $this->idPhoto()->store('guest-ids', 'local'),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'housekeeping']))
            ->get(route('guests.identification.photo', $guest))
            ->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'receptionist']))
            ->get(route('guests.identification.photo', $guest))
            ->assertOk();
    }

    public function test_reservation_page_offers_the_reminder_and_upload_buttons(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($receptionist)->get(route('reservations.show', $reservation));

        $response->assertOk();
        $response->assertSeeText('ID still needed');
        $response->assertSeeText('Remind Guest to Provide ID');
        $response->assertSeeText('Upload ID');
        $response->assertSee('action="'.route('guests.identification.update', $reservation->guest).'"', false);
    }

    /**
     * A real JPEG from the project; the test environment has no GD extension for fake images.
     */
    private function idPhoto(): UploadedFile
    {
        return new UploadedFile(public_path('images/rooms/standard-room.jpg'), 'guest-id.jpg', 'image/jpeg', null, true);
    }
}
