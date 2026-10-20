<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * Statuses the edit form may move a reservation to. Check-in and check-out have
     * their own workflows (room assignment, folio, receipt), so they are not offered here.
     *
     * @var array<string, list<string>>
     */
    private const EDITABLE_STATUS_TRANSITIONS = [
        'pending' => ['pending', 'reserved', 'cancelled'],
        'reserved' => ['reserved', 'cancelled'],
        'cancelled' => ['cancelled', 'reserved'],
        'checked_in' => ['checked_in'],
    ];

    public function index(Request $request): View
    {
        $status = (string) $request->string('status');
        $search = (string) $request->string('search');

        $reservations = Reservation::with(['guest', 'room'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'like', '%'.$search.'%')
                        ->orWhereHas('guest', function ($query) use ($search) {
                            $query->where('first_name', 'like', '%'.$search.'%')
                                ->orWhere('last_name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('room', function ($query) use ($search) {
                            $query->where('room_number', 'like', '%'.$search.'%')
                                ->orWhere('room_type', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reservations.index', [
            'reservations' => $reservations,
            'status' => $status,
            'search' => $search,
            'selectedReservation' => $reservations->first(),
            'statusCounts' => Reservation::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('reservations.create', [
            'guests' => Guest::orderBy('last_name')->get(),
            'rooms' => Room::where('status', 'available')->orderBy('room_number')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request, isNewReservation: true);

        DB::transaction(function () use ($data) {
            $room = Room::whereKey($data['room_id'])->lockForUpdate()->firstOrFail();

            if ($room->status === 'maintenance') {
                throw ValidationException::withMessages([
                    'room_id' => 'The selected room is under maintenance and cannot be booked.',
                ]);
            }

            $this->ensureRoomFitsParty($room, (int) $data['number_of_guests']);
            $this->ensureRoomIsAvailable($room, $data['check_in_date'], $data['check_out_date']);

            if (empty($data['guest_id'])) {
                $guest = Guest::create([
                    'first_name' => $data['manual_guest_first_name'],
                    'last_name' => $data['manual_guest_last_name'],
                    'email' => $data['manual_guest_email'] ?? null,
                    'phone' => $data['manual_guest_phone'] ?? null,
                    'address' => $data['manual_guest_address'] ?? null,
                    'identification_type' => $data['manual_guest_identification_type'] ?? null,
                    'identification_number' => $data['manual_guest_identification_number'] ?? null,
                ]);

                $data['guest_id'] = $guest->id;
            }

            foreach ([
                'manual_guest_first_name',
                'manual_guest_last_name',
                'manual_guest_email',
                'manual_guest_phone',
                'manual_guest_address',
                'manual_guest_identification_type',
                'manual_guest_identification_number',
            ] as $manualField) {
                unset($data[$manualField]);
            }

            $reservation = Reservation::create($data + [
                'booking_reference' => 'LCS-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                'status' => 'reserved',
                'source' => 'front_desk',
                'created_by' => auth()->id(),
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);
            HotelTransaction::create([
                'guest_id' => $reservation->guest_id,
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'transaction_type' => 'reservation',
                'status' => 'recorded',
                'transaction_date' => now(),
                'description' => 'Reservation created.',
            ]);
        });

        return redirect()->route('reservations.index')->with('status', 'Reservation created.');
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['guest', 'room', 'checkIn']);
        $transactions = HotelTransaction::where('reservation_id', $reservation->id)
            ->latest('transaction_date')
            ->get();
        $roomStatusLogs = RoomStatusLog::where('room_id', $reservation->room_id)
            ->latest('changed_at')
            ->take(5)
            ->get();

        return view('reservations.show', compact('reservation', 'transactions', 'roomStatusLogs'));
    }

    public function registrationCard(Reservation $reservation): View
    {
        $reservation->load(['guest', 'room', 'checkIn', 'creator', 'confirmer']);

        return view('reservations.registration-card', compact('reservation'));
    }

    public function edit(Reservation $reservation): View|RedirectResponse
    {
        if ($reservation->status === 'completed') {
            return $this->completedStayIsLocked($reservation);
        }

        return view('reservations.edit', [
            'reservation' => $reservation,
            'statusOptions' => self::EDITABLE_STATUS_TRANSITIONS[$reservation->status] ?? [$reservation->status],
            'guests' => Guest::orderBy('last_name')->get(),
            'rooms' => Room::where(fn ($query) => $query
                ->where('status', 'available')
                ->orWhereKey($reservation->room_id))
                ->orderBy('room_number')
                ->get(),
        ]);
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status === 'completed') {
            return $this->completedStayIsLocked($reservation);
        }

        $data = $this->validatedData($request);
        $data['status'] = $request->validate([
            'status' => ['required', Rule::in(self::EDITABLE_STATUS_TRANSITIONS[$reservation->status] ?? [$reservation->status])],
        ], [
            'status.in' => 'Use the check-in and check-out screens to move a stay into or out of the hotel.',
        ])['status'];

        DB::transaction(function () use ($reservation, $data) {
            $oldRoomId = $reservation->room_id;

            if (empty($data['guest_id'])) {
                $guest = Guest::create([
                    'first_name' => $data['manual_guest_first_name'],
                    'last_name' => $data['manual_guest_last_name'],
                    'email' => $data['manual_guest_email'] ?? null,
                    'phone' => $data['manual_guest_phone'] ?? null,
                    'address' => $data['manual_guest_address'] ?? null,
                    'identification_type' => $data['manual_guest_identification_type'] ?? null,
                    'identification_number' => $data['manual_guest_identification_number'] ?? null,
                ]);

                $data['guest_id'] = $guest->id;
            }

            foreach ([
                'manual_guest_first_name',
                'manual_guest_last_name',
                'manual_guest_email',
                'manual_guest_phone',
                'manual_guest_address',
                'manual_guest_identification_type',
                'manual_guest_identification_number',
            ] as $manualField) {
                unset($data[$manualField]);
            }

            $targetRoom = Room::whereKey($data['room_id'])->lockForUpdate()->firstOrFail();

            if ($data['status'] !== 'cancelled') {
                $this->ensureRoomFitsParty($targetRoom, (int) $data['number_of_guests']);
                $this->ensureRoomIsAvailable($targetRoom, $data['check_in_date'], $data['check_out_date'], $reservation->id);
            }

            $roomChanged = $oldRoomId !== (int) $data['room_id'];

            if ($roomChanged && $data['status'] === 'checked_in' && in_array($targetRoom->status, ['occupied', 'maintenance'], true)) {
                throw ValidationException::withMessages([
                    'room_id' => 'An in-house guest can only be moved to a room that is not occupied or under maintenance.',
                ]);
            }

            $reservation->update($data);

            if ($roomChanged && $data['status'] === 'checked_in') {
                Room::whereKey($oldRoomId)->update(['status' => 'cleaning']);
                $targetRoom->update(['status' => 'occupied']);
                $reservation->checkIn()->where('status', 'active')->update(['room_id' => $targetRoom->id]);
                RoomStatusLog::create([
                    'room_id' => $oldRoomId,
                    'status' => 'cleaning',
                    'notes' => 'In-house guest moved to room '.$targetRoom->room_number.'.',
                    'changed_at' => now(),
                ]);
                RoomStatusLog::create([
                    'room_id' => $targetRoom->id,
                    'status' => 'occupied',
                    'notes' => 'In-house guest moved in from another room.',
                    'changed_at' => now(),
                ]);
            }

            if ($data['status'] === 'cancelled' && $reservation->room?->status === 'reserved') {
                $reservation->room()->update(['status' => 'available']);
            }
        });

        return redirect()->route('reservations.index')->with('status', 'Reservation updated.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        if (in_array($reservation->status, ['checked_in', 'completed'], true)) {
            return back()->withErrors([
                'reservation' => 'Reservations with a check-in or receipt cannot be deleted. Cancel upcoming bookings instead.',
            ]);
        }

        DB::transaction(function () use ($reservation) {
            if ($reservation->room?->status === 'reserved') {
                $reservation->room()->update(['status' => 'available']);
            }

            $reservation->delete();
        });

        return redirect()->route('reservations.index')->with('status', 'Reservation deleted.');
    }

    private function completedStayIsLocked(Reservation $reservation): RedirectResponse
    {
        return redirect()
            ->route('reservations.show', $reservation)
            ->withErrors(['status' => 'Completed stays are locked because their receipt has already been issued.']);
    }

    private function validatedData(Request $request, bool $isNewReservation = false): array
    {
        return $request->validate([
            'guest_id' => ['nullable', 'exists:guests,id'],
            'manual_guest_first_name' => ['required_without:guest_id', 'nullable', 'string', 'max:100'],
            'manual_guest_last_name' => ['required_without:guest_id', 'nullable', 'string', 'max:100'],
            'manual_guest_email' => ['nullable', 'email', 'max:255'],
            'manual_guest_phone' => ['nullable', 'string', 'max:50'],
            'manual_guest_address' => ['nullable', 'string'],
            'manual_guest_identification_type' => ['nullable', 'string', 'max:100'],
            'manual_guest_identification_number' => ['nullable', 'string', 'max:100'],
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', ...($isNewReservation ? ['after_or_equal:today'] : [])],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function ensureRoomFitsParty(Room $room, int $numberOfGuests): void
    {
        if ($numberOfGuests > $room->capacity) {
            throw ValidationException::withMessages([
                'number_of_guests' => "Room {$room->room_number} sleeps at most {$room->capacity} guests.",
            ]);
        }
    }

    private function ensureRoomIsAvailable(Room $room, string $checkInDate, string $checkOutDate, ?int $exceptReservationId = null): void
    {
        $conflict = Reservation::query()
            ->where('room_id', $room->id)
            ->when($exceptReservationId, fn ($query) => $query->whereKeyNot($exceptReservationId))
            ->whereIn('status', ['pending', 'reserved', 'checked_in'])
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'room_id' => 'This room already has a booking that overlaps the selected dates.',
            ]);
        }
    }
}
