<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_take_or_upload_a_private_profile_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create([
            'email' => 'receptionist@lecharmehotel.com',
            'role' => 'receptionist',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'camera_photo' => new UploadedFile(
                    public_path('images/rooms/standard-room.jpg'),
                    'camera-photo.jpg',
                    'image/jpeg',
                    null,
                    true,
                ),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('local')->assertExists($user->profile_photo_path);

        $this->get(route('profile.photo'))->assertOk();
    }

    public function test_profile_page_contains_camera_upload_preview_and_remove_controls(): void
    {
        Storage::fake('local');
        $user = User::factory()->create([
            'email' => 'employee@lecharmehotel.com',
            'role' => 'receptionist',
            'profile_photo_path' => 'profile-photos/existing.jpg',
        ]);
        Storage::disk('local')->put($user->profile_photo_path, 'photo');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Take Photo')
            ->assertSee('Choose Photo')
            ->assertSee('Remove Photo')
            ->assertSee('capture="user"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('data-profile-photo-preview', false);
    }

    public function test_user_can_remove_their_profile_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create([
            'email' => 'billing@lecharmehotel.com',
            'role' => 'cashier',
            'profile_photo_path' => 'profile-photos/remove-me.jpg',
        ]);
        Storage::disk('local')->put($user->profile_photo_path, 'photo');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'remove_profile_photo' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->profile_photo_path);
        Storage::disk('local')->assertMissing('profile-photos/remove-me.jpg');
    }

    public function test_non_image_profile_upload_is_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create([
            'email' => 'housekeeping@lecharmehotel.com',
            'role' => 'housekeeping',
        ]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => UploadedFile::fake()->create('not-a-photo.txt', 10, 'text/plain'),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('profile_photo');
    }
}
