<?php

namespace App\Http\Controllers;

use App\Rules\WorkEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $view = match ($request->user()?->role) {
            'housekeeping' => 'profile.housekeeping-edit',
            'cashier' => 'profile.billing-edit',
            default => 'profile.edit',
        };

        return view($view, [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=128,min_height=128,max_width=6000,max_height=6000'],
            'camera_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=128,min_height=128,max_width=6000,max_height=6000'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        unset($data['profile_photo'], $data['camera_photo'], $data['remove_profile_photo']);

        $photo = $request->file('camera_photo') ?: $request->file('profile_photo');

        if ($photo) {
            $newPath = $photo->store('profile-photos', 'local');

            if ($user->profile_photo_path) {
                Storage::disk('local')->delete($user->profile_photo_path);
            }

            $data['profile_photo_path'] = $newPath;
        } elseif ($request->boolean('remove_profile_photo') && $user->profile_photo_path) {
            Storage::disk('local')->delete($user->profile_photo_path);
            $data['profile_photo_path'] = null;
        }

        $user->update($data);

        return back()->with('status', 'Your profile was updated successfully.');
    }

    public function photo(Request $request): BinaryFileResponse
    {
        $path = $request->user()?->profile_photo_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
