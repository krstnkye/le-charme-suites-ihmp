<?php

namespace App\Http\Controllers;

use App\Services\CreateOnlineReservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineBookingApiController extends Controller
{
    public function store(Request $request, CreateOnlineReservation $creator): JsonResponse
    {
        $data = $request->validate([
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
        ]);

        $reservation = $creator->handle($data, 'hotel_website_api', $request->ip());

        return response()->json([
            'message' => 'Booking request received and sent to the Le Charmé front desk.',
            'booking_reference' => $reservation->booking_reference,
            'status' => $reservation->status,
            'room_type' => $reservation->room->room_type,
            'check_in_date' => $reservation->check_in_date->toDateString(),
            'check_out_date' => $reservation->check_out_date->toDateString(),
        ], 201);
    }
}
