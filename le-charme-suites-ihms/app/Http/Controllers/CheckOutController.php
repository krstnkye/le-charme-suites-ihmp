<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\HotelTransaction;
use App\Models\RoomStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckOutController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');
        $paymentStatus = (string) $request->string('payment_status');

        $checkOuts = CheckOut::with(['guest', 'room', 'checkIn'])
            ->when($paymentStatus !== '', fn ($query) => $query->where('payment_status', $paymentStatus))
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
            ->latest('checked_out_at')
            ->paginate(10)
            ->withQueryString();

        $activeCheckIn = CheckIn::with(['guest', 'room', 'reservation'])
            ->where('status', 'active')
            ->latest('checked_in_at')
            ->first();

        return view('check_outs.index', [
            'activeCheckIn' => $activeCheckIn,
            'checkOuts' => $checkOuts,
            'folioTransactions' => HotelTransaction::with(['guest', 'room'])
                ->latest('transaction_date')
                ->limit(6)
                ->get(),
            'paymentStatus' => $paymentStatus,
            'search' => $search,
            'settledToday' => CheckOut::whereDate('checked_out_at', today())->count(),
            'totalCollectedToday' => CheckOut::whereDate('checked_out_at', today())->sum('amount_paid'),
            'pendingDepartures' => CheckIn::where('status', 'active')->count(),
            'departuresToday' => CheckIn::where('status', 'active')
                ->whereHas('reservation', fn ($query) => $query->whereDate('check_out_date', today()))
                ->count(),
        ]);
    }

    public function create(): View
    {
        return view('check_outs.create', [
            'checkIns' => CheckIn::with(['guest', 'room', 'reservation'])
                ->where('status', 'active')
                ->latest('checked_in_at')
                ->get(),
        ]);
    }

    public function history(Request $request): View
    {
        $search = (string) $request->string('search');
        $paymentStatus = (string) $request->string('payment_status');

        $checkOuts = CheckOut::with(['guest', 'room', 'checkIn'])
            ->when($paymentStatus !== '', fn ($query) => $query->where('payment_status', $paymentStatus))
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
            ->latest('checked_out_at')
            ->paginate(12)
            ->withQueryString();

        $ledgerTransactions = HotelTransaction::with(['guest', 'room'])
            ->latest('transaction_date')
            ->limit(8)
            ->get();

        return view('check_outs.history', [
            'checkOuts' => $checkOuts,
            'ledgerTransactions' => $ledgerTransactions,
            'paymentStatus' => $paymentStatus,
            'search' => $search,
            'settledToday' => CheckOut::whereDate('checked_out_at', today())->count(),
            'collectedToday' => CheckOut::whereDate('checked_out_at', today())->sum('amount_paid'),
            'totalArchived' => CheckOut::count(),
            'voidReviewCount' => HotelTransaction::where('status', 'void')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'check_in_id' => ['required', 'exists:check_ins,id'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $checkIn = CheckIn::with(['reservation', 'room'])->lockForUpdate()->findOrFail($data['check_in_id']);

            abort_if($checkIn->status !== 'active', 422, 'Only active check-ins can be checked out.');

            CheckOut::create([
                'check_in_id' => $checkIn->id,
                'reservation_id' => $checkIn->reservation_id,
                'guest_id' => $checkIn->guest_id,
                'room_id' => $checkIn->room_id,
                'checked_out_at' => now(),
                'amount_paid' => $data['amount_paid'] ?? 0,
                'payment_status' => $data['payment_status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $checkIn->update(['status' => 'completed']);
            $checkIn->reservation->update(['status' => 'completed']);
            $checkIn->room->update(['status' => 'cleaning']);
            RoomStatusLog::create([
                'room_id' => $checkIn->room_id,
                'status' => 'cleaning',
                'notes' => 'Guest checked out; room requires cleaning.',
                'changed_at' => now(),
            ]);
            HotelTransaction::create([
                'guest_id' => $checkIn->guest_id,
                'reservation_id' => $checkIn->reservation_id,
                'room_id' => $checkIn->room_id,
                'transaction_type' => 'check_out',
                'amount' => $data['amount_paid'] ?? 0,
                'status' => $data['payment_status'],
                'transaction_date' => now(),
                'description' => 'Guest checked out.',
            ]);
        });

        return redirect()->route('check-outs.index')->with('status', 'Guest checked out.');
    }
}
