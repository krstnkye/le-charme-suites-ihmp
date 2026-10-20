<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');
        $guests = Guest::query()
            ->withCount('reservations')
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term) {
                    $query->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('id', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $today = now(config('app.timezone'))->toDateString();

        return view('guests.index', [
            'guests' => $guests,
            'search' => $search,
            'totalGuests' => Guest::count(),
            'inHouseGuests' => Guest::whereHas('reservations', fn ($query) => $query->where('status', 'checked_in'))->count(),
            'arrivingToday' => Guest::whereHas('reservations', fn ($query) => $query
                ->where('status', 'reserved')
                ->whereDate('check_in_date', $today))->count(),
            'departingToday' => Guest::whereHas('reservations', fn ($query) => $query
                ->where('status', 'checked_in')
                ->whereDate('check_out_date', $today))->count(),
        ]);
    }

    public function create(): View
    {
        return view('guests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Guest::create($this->validatedData($request));

        return redirect()->route('guests.index')->with('status', 'Guest record created.');
    }

    public function show(Guest $guest): View
    {
        $guest->load(['reservations.room']);

        return view('guests.show', compact('guest'));
    }

    public function edit(Guest $guest): View
    {
        return view('guests.edit', compact('guest'));
    }

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $guest->update($this->validatedData($request));

        return redirect()->route('guests.index')->with('status', 'Guest record updated.');
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        if ($guest->reservations()->where('status', '!=', 'cancelled')->exists()) {
            return back()->withErrors([
                'guest' => "{$guest->full_name} has bookings or stay history and cannot be deleted.",
            ]);
        }

        $guest->delete();

        return redirect()->route('guests.index')->with('status', 'Guest record deleted.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'identification_type' => ['nullable', 'string', 'max:100'],
            'identification_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
