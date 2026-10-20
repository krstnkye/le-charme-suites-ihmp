<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\HotelTransaction;
use App\Models\HousekeepingTask;
use App\Models\RoomStatusLog;
use App\Services\GuestBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
            ->latest('checked_out_at')
            ->paginate(10)
            ->withQueryString();

        $activeCheckIn = CheckIn::with(['guest', 'room', 'reservation'])
            ->where('status', 'active')
            ->latest('checked_in_at')
            ->first();

        $folioTransactions = HotelTransaction::with(['guest', 'room'])
            ->latest('transaction_date')
            ->limit(6)
            ->get();

        $receiptsByReservation = CheckOut::whereIn(
            'reservation_id',
            $folioTransactions->pluck('reservation_id')->filter()->unique()
        )->get()->keyBy('reservation_id');

        return view('check_outs.index', [
            'activeCheckIn' => $activeCheckIn,
            'checkOuts' => $checkOuts,
            'folioTransactions' => $folioTransactions,
            'receiptsByReservation' => $receiptsByReservation,
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
        $checkIns = CheckIn::with(['guest', 'room', 'reservation'])
            ->where('status', 'active')
            ->latest('checked_in_at')
            ->get();

        return view('check_outs.create', [
            'checkIns' => $checkIns,
            'guestBills' => $checkIns->mapWithKeys(fn (CheckIn $checkIn): array => [
                $checkIn->id => app(GuestBillService::class)->forReservation($checkIn->reservation, $checkIn->room),
            ]),
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

    public function receipt(CheckOut $checkOut): View
    {
        $checkOut->load(['guest', 'room', 'checkIn', 'reservation']);

        $reservation = $checkOut->reservation;
        $folio = app(GuestBillService::class)->forReservation($reservation, $checkOut->room);
        $amountPaid = round((float) $checkOut->amount_paid, 2);

        return view('check_outs.receipt', [
            'checkOut' => $checkOut,
            'reservation' => $reservation,
            ...$folio,
            'amountPaid' => $amountPaid,
            'balance' => max(0, round($folio['balanceDue'] - $amountPaid, 2)),
        ]);
    }

    public function billPreview(CheckIn $checkIn): View
    {
        $checkIn->load(['guest', 'room', 'reservation']);

        abort_if($checkIn->status !== 'active', 404);

        return view('check_outs.bill-preview', [
            'checkIn' => $checkIn,
            'reservation' => $checkIn->reservation,
            ...app(GuestBillService::class)->forReservation($checkIn->reservation, $checkIn->room),
        ]);
    }

    private function ensurePaymentMatchesStatus(float $amountPaid, string $paymentStatus, float $balanceDue): void
    {
        $message = match (true) {
            $paymentStatus === 'paid' && $amountPaid < $balanceDue => 'A paid bill must collect the full PHP '.number_format($balanceDue, 2).' balance due. Choose Partial if the guest paid less.',
            $paymentStatus === 'partial' && ($amountPaid <= 0 || $amountPaid >= $balanceDue) => 'A partial payment must be more than zero and less than the PHP '.number_format($balanceDue, 2).' balance due.',
            $paymentStatus === 'unpaid' && $amountPaid > 0 => 'An unpaid bill cannot record a payment. Choose Partial or Paid instead.',
            default => null,
        };

        if ($message) {
            throw ValidationException::withMessages(['amount_paid' => $message]);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'check_in_id' => ['required', 'exists:check_ins,id'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        [$checkOut, $alreadyCompleted] = DB::transaction(function () use ($data) {
            $checkIn = CheckIn::with(['reservation', 'room'])->lockForUpdate()->findOrFail($data['check_in_id']);

            if ($checkIn->status !== 'active') {
                $existingCheckOut = CheckOut::where('check_in_id', $checkIn->id)->first();

                if ($existingCheckOut) {
                    return [$existingCheckOut, true];
                }

                return [redirect()
                    ->route('check-outs.create')
                    ->withErrors(['check_in_id' => 'This guest stay is no longer active and cannot be checked out.']), false];
            }

            $this->ensurePaymentMatchesStatus(
                round((float) ($data['amount_paid'] ?? 0), 2),
                $data['payment_status'],
                app(GuestBillService::class)->forReservation($checkIn->reservation, $checkIn->room)['balanceDue'],
            );

            $checkOut = CheckOut::create([
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
            HousekeepingTask::create([
                'room_id' => $checkIn->room_id,
                'created_by' => auth()->id(),
                'task_type' => 'turnover',
                'title' => 'Room cleaning after check-out - Room '.$checkIn->room->room_number,
                'instructions' => 'Prepare the room for the next guest and submit completion notes for inspection.',
                'priority' => 'high',
                'status' => 'queued',
                'due_at' => now()->addHours(2),
            ]);
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

            return [$checkOut, false];
        });

        if ($checkOut instanceof RedirectResponse) {
            return $checkOut;
        }

        return redirect()
            ->route('check-outs.receipt', ['checkOut' => $checkOut, 'print' => 1])
            ->with('status', $alreadyCompleted
                ? 'This guest was already checked out. The saved receipt is ready to print.'
                : 'Guest checked out. The receipt is ready to print.');
    }
}
