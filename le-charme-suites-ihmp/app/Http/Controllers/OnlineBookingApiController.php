<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\CancelOfficialWebsiteReservation;
use App\Services\CreateOnlineReservation;
use App\Services\UpdateOfficialWebsiteReservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OnlineBookingApiController extends Controller
{
    public function store(Request $request, CreateOnlineReservation $creator): JsonResponse
    {
        $data = $this->validateBooking($request);
        $reservation = $creator->handle($data, 'hotel_website_api', $request->ip());

        return response()->json($this->responseData(
            $reservation,
            'Booking request received and sent to the Le Charmé front desk.'
        ), 201);
    }

    public function update(
        Request $request,
        string $externalReference,
        UpdateOfficialWebsiteReservation $updater,
    ): JsonResponse {
        $reservation = $this->officialReservation($externalReference);
        $data = $this->validateBooking($request, requireExternalReference: false);
        $reservation = $updater->handle($reservation, $data, $request->ip());

        return response()->json($this->responseData(
            $reservation,
            'Official website booking updated in the IHMP.'
        ));
    }

    public function cancel(
        Request $request,
        string $externalReference,
        CancelOfficialWebsiteReservation $canceller,
    ): JsonResponse {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $reservation = $canceller->handle(
            $this->officialReservation($externalReference),
            $data['reason'] ?? null,
            $request->ip(),
        );

        return response()->json($this->responseData(
            $reservation,
            'Official website booking cancelled in the IHMP.'
        ));
    }

    /** @return array<string, mixed> */
    private function validateBooking(Request $request, bool $requireExternalReference = true): array
    {
        $data = $request->validate([
            'external_reference' => [$requireExternalReference ? 'required' : 'sometimes', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'room_type' => ['required', 'string', 'in:'.implode(',', array_keys(config('hotel.room_types')))],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required_without:adults', 'nullable', 'integer', 'min:1', 'max:7'],
            'adults' => ['required_without:number_of_guests', 'nullable', 'integer', 'min:1', 'max:7'],
            'children' => ['nullable', 'integer', 'min:0', 'max:6'],
            'official_booking_status' => ['nullable', 'string', 'in:pending,confirmed'],
            'payment_status' => ['nullable', 'string', 'in:pending,unpaid,partial,paid,refunded'],
            'currency' => ['nullable', 'string', 'size:3', 'in:PHP'],
            'gross_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'total_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'rate_plan' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (isset($data['adults'])) {
            $calculatedGuests = (int) $data['adults'] + (int) ($data['children'] ?? 0);
            if (isset($data['number_of_guests']) && (int) $data['number_of_guests'] !== $calculatedGuests) {
                throw ValidationException::withMessages([
                    'number_of_guests' => 'The total guests must equal the number of adults plus children.',
                ]);
            }
            $data['number_of_guests'] = $calculatedGuests;
        }

        if (($data['discount_amount'] ?? 0) > ($data['gross_amount'] ?? PHP_FLOAT_MAX)) {
            throw ValidationException::withMessages([
                'discount_amount' => 'The discount cannot be greater than the original booking amount.',
            ]);
        }

        return $data;
    }

    private function officialReservation(string $externalReference): Reservation
    {
        return Reservation::query()
            ->where('source', 'hotel_website_api')
            ->where('external_reference', $externalReference)
            ->with(['guest', 'room'])
            ->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function responseData(Reservation $reservation, string $message): array
    {
        return [
            'message' => $message,
            'booking_reference' => $reservation->booking_reference,
            'external_reference' => $reservation->external_reference,
            'status' => $reservation->status,
            'room_type' => $reservation->room->room_type,
            'check_in_date' => $reservation->check_in_date->toDateString(),
            'check_out_date' => $reservation->check_out_date->toDateString(),
            'number_of_guests' => $reservation->number_of_guests,
            'payment_status' => $reservation->payment_status,
            'currency' => $reservation->currency,
            'total_amount' => $reservation->total_amount,
        ];
    }
}
