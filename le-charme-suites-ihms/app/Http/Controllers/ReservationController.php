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
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->string('status');
        $search = (string) $request->string('search');

        $reservations = Reservation::with(['guest', 'room'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'like', '%' . $search . '%')
                        ->orWhereHas('guest', function ($query) use ($search) {
                            $query->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('room', function ($query) use ($search) {
                            $query->where('room_number', 'like', '%' . $search . '%')
                                ->orWhere('room_type', 'like', '%' . $search . '%');
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
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            $room = Room::whereKey($data['room_id'])->lockForUpdate()->firstOrFail();

            abort_if($room->status !== 'available', 422, 'The selected room is not available.');

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

            $reservation = Reservation::create($data + ['status' => 'reserved']);
            $room->update(['status' => 'reserved']);
            RoomStatusLog::create([
                'room_id' => $room->id,
                'status' => 'reserved',
                'notes' => 'Room reserved through reservation #' . $reservation->id,
                'changed_at' => now(),
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

    public function edit(Reservation $reservation): View
    {
        return view('reservations.edit', [
            'reservation' => $reservation,
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
        $data = $this->validatedData($request);
        $data['status'] = $request->validate([
            'status' => ['required', 'in:reserved,checked_in,completed,cancelled'],
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

            $reservation->update($data);

            if ($oldRoomId !== (int) $data['room_id']) {
                Room::whereKey($oldRoomId)->update(['status' => 'available']);
                Room::whereKey($data['room_id'])->update([
                    'status' => $data['status'] === 'checked_in' ? 'occupied' : 'reserved',
                ]);
            }

            if ($data['status'] === 'cancelled') {
                $reservation->room()->update(['status' => 'available']);
            }
        });

        return redirect()->route('reservations.index')->with('status', 'Reservation updated.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        DB::transaction(function () use ($reservation) {
            if (in_array($reservation->status, ['reserved', 'cancelled'], true)) {
                $reservation->room()->update(['status' => 'available']);
            }

            $reservation->delete();
        });

        return redirect()->route('reservations.index')->with('status', 'Reservation deleted.');
    }

    private function validatedData(Request $request): array
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
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
