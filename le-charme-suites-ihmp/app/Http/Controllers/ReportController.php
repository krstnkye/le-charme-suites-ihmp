<?php

namespace App\Http\Controllers;

use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(): View
    {
        return view('reports.index', [
            'transactions' => HotelTransaction::with(['guest', 'room', 'reservation'])
                ->latest('transaction_date')
                ->paginate(15),
            'roomStatusCounts' => Room::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'reservationStatusCounts' => Reservation::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
