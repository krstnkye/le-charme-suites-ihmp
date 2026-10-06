<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGuestController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');

        $guests = Guest::query()
            ->withCount('reservations')
            ->withSum('transactions as lifetime_value', 'amount')
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';

                $query->where(function ($query) use ($term) {
                    $query->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('identification_number', 'like', $term)
                        ->orWhere('id', 'like', $term);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $today = now(config('app.timezone'))->toDateString();

        return view('admin.guests.index', [
            'guests' => $guests,
            'search' => $search,
            'totalGuests' => Guest::count(),
            'verifiedGuests' => Guest::whereNotNull('identification_type')
                ->whereNotNull('identification_number')
                ->count(),
            'inHouseGuests' => Guest::whereHas('reservations', fn ($query) => $query->where('status', 'checked_in'))->count(),
            'arrivingToday' => Guest::whereHas('reservations', fn ($query) => $query
                ->where('status', 'reserved')
                ->whereDate('check_in_date', $today))->count(),
            'departingToday' => Guest::whereHas('reservations', fn ($query) => $query
                ->where('status', 'checked_in')
                ->whereDate('check_out_date', $today))->count(),
            'incompleteProfiles' => Guest::whereNull('email')
                ->orWhereNull('phone')
                ->orWhereNull('identification_type')
                ->orWhereNull('identification_number')
                ->count(),
        ]);
    }
}
