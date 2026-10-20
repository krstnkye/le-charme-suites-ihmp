<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Notifications\GuestIdentificationReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuestIdentificationController extends Controller
{
    /**
     * Government IDs accepted by Philippine hotels for guest registration.
     */
    public const ACCEPTED_ID_TYPES = [
        'Philippine Passport',
        'Foreign Passport',
        'PhilSys National ID',
        "Driver's License",
        'UMID',
        'SSS ID',
        'PRC ID',
        'Postal ID',
        "Voter's ID",
        'Senior Citizen ID',
        'Other Government ID',
    ];

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $data = $request->validate([
            'identification_type' => ['required', Rule::in(self::ACCEPTED_ID_TYPES)],
            'identification_number' => ['required', 'string', 'max:100'],
            'identification_photo' => [
                Rule::requiredIf(blank($guest->identification_photo_path)),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'identification_photo.required' => 'Upload a photo of the guest ID.',
        ]);

        if ($request->hasFile('identification_photo')) {
            $newPath = $request->file('identification_photo')->store('guest-ids', 'local');

            if ($guest->identification_photo_path) {
                Storage::disk('local')->delete($guest->identification_photo_path);
            }

            $guest->identification_photo_path = $newPath;
        }

        $guest->fill([
            'identification_type' => $data['identification_type'],
            'identification_number' => $data['identification_number'],
        ])->save();

        return back()->with('identification_status', 'Guest ID saved.');
    }

    public function remind(Reservation $reservation): RedirectResponse
    {
        $guest = $reservation->guest;

        if (blank($guest?->email)) {
            return back()->withErrors(['identification' => 'This guest has no email address on file. Add one before sending a reminder.']);
        }

        Notification::route('mail', $guest->email)->notify(new GuestIdentificationReminder($reservation));
        $guest->update(['identification_reminded_at' => now()]);

        return back()->with('identification_status', "ID reminder sent to {$guest->email}.");
    }

    public function photo(Guest $guest): BinaryFileResponse
    {
        $path = $guest->identification_photo_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
