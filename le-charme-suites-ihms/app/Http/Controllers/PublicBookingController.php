<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Services\CreateOnlineReservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function create(): View
    {
        return view('public.booking', $this->bookingPageData(isWebsiteSimulation: false));
    }

    /**
     * Academic stand-in for the official Le Charmé website booking page. Bookings made
     * here travel through the same integration service the real website connector would use.
     */
    public function createWebsiteSimulation(): View
    {
        return view('public.booking', $this->bookingPageData(isWebsiteSimulation: true));
    }

    public function store(Request $request, CreateOnlineReservation $creator): RedirectResponse
    {
        $reservation = $creator->handle($this->validated($request), 'public_website', $request->ip());

        return redirect()->route('booking.confirmation', $reservation->booking_reference);
    }

    public function storeWebsiteSimulation(Request $request, CreateOnlineReservation $creator): RedirectResponse
    {
        $reservation = $creator->handle($this->validated($request), 'official_website_simulation', $request->ip());

        return redirect()->route('booking.confirmation', $reservation->booking_reference);
    }

    public function confirmation(string $reference): View
    {
        $reservation = Reservation::with(['guest', 'room'])
            ->where('booking_reference', $reference)
            ->firstOrFail();

        return view('public.booking-confirmation', compact('reservation'));
    }

    /**
     * @return array{roomTypes: Collection<string, array<string, mixed>>, availableCounts: Collection<string, int>, isWebsiteSimulation: bool, formAction: string}
     */
    private function bookingPageData(bool $isWebsiteSimulation): array
    {
        return [
            'roomTypes' => collect(config('hotel.room_types')),
            'availableCounts' => Room::query()
                ->whereNotIn('status', ['maintenance'])
                ->selectRaw('room_type, count(*) as total')
                ->groupBy('room_type')
                ->pluck('total', 'room_type'),
            'isWebsiteSimulation' => $isWebsiteSimulation,
            'formAction' => route($isWebsiteSimulation ? 'website-simulation.store' : 'booking.store'),
        ];
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'room_type' => ['required', 'string', 'in:'.implode(',', array_keys(config('hotel.room_types')))],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required', 'integer', 'min:1', 'max:7'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'privacy_consent' => ['accepted'],
        ]);
    }
}
