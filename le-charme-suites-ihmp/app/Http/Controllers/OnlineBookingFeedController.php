<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;

class OnlineBookingFeedController extends Controller
{
    /**
     * Pending online booking requests, polled by the front desk screen so new website
     * bookings appear without a page refresh.
     */
    public function __invoke(): JsonResponse
    {
        $pending = Reservation::query()
            ->with(['guest', 'room'])
            ->where('status', 'pending')
            ->latest('id')
            ->limit(5)
            ->get();

        return response()->json([
            'pending_count' => Reservation::where('status', 'pending')->count(),
            'latest_id' => $pending->first()?->id,
            'latest' => $pending->map(fn (Reservation $reservation): array => [
                'id' => $reservation->id,
                'booking_reference' => $reservation->booking_reference,
                'guest' => $reservation->guest?->full_name,
                'room_type' => $reservation->room?->room_type,
                'check_in_date' => $reservation->check_in_date?->format('M d, Y'),
                'source' => config("integrations.booking_sources.{$reservation->source}", $reservation->source),
                'url' => route('reservations.show', $reservation),
            ])->values(),
        ]);
    }
}
