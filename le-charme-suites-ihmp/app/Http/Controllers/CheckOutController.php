<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\CheckOutPayment;
use App\Models\HotelTransaction;
use App\Models\HousekeepingTask;
use App\Models\RoomStatusLog;
use App\Services\FolioCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckOutController extends Controller
{
    public function index(Request $request, FolioCalculator $calculator): View
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
            'activeFolio' => $activeCheckIn
                ? $calculator->forReservation($activeCheckIn->reservation, $activeCheckIn->room)
                : null,
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

    public function create(FolioCalculator $calculator): View
    {
        $checkIns = CheckIn::with(['guest', 'room', 'reservation'])
            ->where('status', 'active')
            ->latest('checked_in_at')
            ->get();

        return view('check_outs.create', [
            'checkIns' => $checkIns,
            'folios' => $checkIns->mapWithKeys(fn (CheckIn $checkIn): array => [
                $checkIn->id => $calculator->forReservation($checkIn->reservation, $checkIn->room),
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

    public function receipt(CheckOut $checkOut, FolioCalculator $calculator): View
    {
        $checkOut->load(['guest', 'room', 'checkIn', 'reservation']);

        $reservation = $checkOut->reservation;
        $folio = $calculator->forCheckOut($checkOut);
        $totalCharges = $folio['totalCharges'];
        $amountPaid = round((float) $checkOut->amount_paid, 2);

        return view('check_outs.receipt', [
            'checkOut' => $checkOut,
            'reservation' => $reservation,
            ...$folio,
            'amountPaid' => $amountPaid,
            'balance' => (float) $checkOut->balance_due,
        ]);
    }

    public function billPreview(CheckIn $checkIn, FolioCalculator $calculator): View
    {
        $checkIn->load(['guest', 'room', 'reservation']);

        abort_if($checkIn->status !== 'active', 404);

        return view('check_outs.bill-preview', [
            'checkIn' => $checkIn,
            'reservation' => $checkIn->reservation,
            ...$calculator->forReservation($checkIn->reservation, $checkIn->room),
        ]);
    }

    private function ensurePaymentMatchesStatus(float $amountPaid, string $paymentStatus, float $totalCharges): void
    {
        $message = match (true) {
            $paymentStatus === 'paid' && $this->toCentavos($amountPaid) !== $this->toCentavos($totalCharges) => 'A paid bill must collect exactly PHP '.number_format($totalCharges, 2).'. Record less as Partial; overpayments are not accepted.',
            $paymentStatus === 'partial' && ($amountPaid <= 0 || $amountPaid >= $totalCharges) => 'A partial payment must be more than zero and less than the PHP '.number_format($totalCharges, 2).' total.',
            $paymentStatus === 'unpaid' && $amountPaid > 0 => 'An unpaid bill cannot record a payment. Choose Partial or Paid instead.',
            default => null,
        };

        if ($message) {
            throw ValidationException::withMessages(['amount_paid' => $message]);
        }
    }

    public function store(Request $request, FolioCalculator $calculator): RedirectResponse
    {
        $data = $request->validate([
            'check_in_id' => ['required', 'exists:check_ins,id'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        [$checkOut, $alreadyCompleted] = DB::transaction(function () use ($data, $calculator) {
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

            $amountPaid = round((float) ($data['amount_paid'] ?? 0), 2);
            $folio = $calculator->forReservation($checkIn->reservation, $checkIn->room);
            $this->ensurePaymentMatchesStatus(
                $amountPaid,
                $data['payment_status'],
                $folio['totalCharges'],
            );

            $checkOut = CheckOut::create([
                'check_in_id' => $checkIn->id,
                'reservation_id' => $checkIn->reservation_id,
                'guest_id' => $checkIn->guest_id,
                'room_id' => $checkIn->room_id,
                'checked_out_at' => now(),
                'amount_paid' => $amountPaid,
                'payment_status' => $data['payment_status'],
                'room_charges' => $folio['roomCharges'],
                'extra_guest_charges' => $folio['extraGuestCharges'],
                'additional_charges' => $folio['additionalTotal'],
                'total_charges' => $folio['totalCharges'],
                'balance_due' => max(0, round($folio['totalCharges'] - $amountPaid, 2)),
                'folio_snapshot' => $calculator->snapshot($folio),
                'notes' => $data['notes'] ?? null,
            ]);

            if ($amountPaid > 0) {
                CheckOutPayment::create([
                    'check_out_id' => $checkOut->id,
                    'received_by' => auth()->id(),
                    'amount' => $amountPaid,
                    'payment_method' => 'unspecified',
                    'received_at' => now(),
                ]);
            }

            $checkIn->update(['status' => 'completed']);
            $checkIn->reservation->update(['status' => 'completed']);
            $checkIn->room->update(['status' => 'cleaning']);
            HousekeepingTask::create([
                'room_id' => $checkIn->room_id,
                'created_by' => auth()->id(),
                'task_type' => 'turnover',
                'title' => 'Departure turnover - Room '.$checkIn->room->room_number,
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
                'transaction_type' => $amountPaid > 0 ? 'payment' : 'check_out',
                'amount' => $amountPaid,
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

    public function payment(Request $request, CheckOut $checkOut): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,digital_wallet,bank_transfer,other'],
            'reference' => ['nullable', 'string', 'max:100', 'unique:check_out_payments,reference'],
        ]);

        DB::transaction(function () use ($data, $checkOut, $request): void {
            $locked = CheckOut::lockForUpdate()->findOrFail($checkOut->id);
            $paymentCentavos = $this->toCentavos((float) $data['amount']);
            $balanceCentavos = $this->toCentavos((float) $locked->balance_due);

            if ($paymentCentavos > $balanceCentavos) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment exceeds the remaining balance of PHP '.number_format($balanceCentavos / 100, 2).'.',
                ]);
            }

            CheckOutPayment::create([
                'check_out_id' => $locked->id,
                'received_by' => $request->user()->id,
                'amount' => $paymentCentavos / 100,
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'received_at' => now(),
            ]);

            $paidCentavos = $this->toCentavos((float) $locked->amount_paid) + $paymentCentavos;
            $remainingCentavos = max(0, $this->toCentavos((float) $locked->total_charges) - $paidCentavos);
            $locked->update([
                'amount_paid' => $paidCentavos / 100,
                'balance_due' => $remainingCentavos / 100,
                'payment_status' => $remainingCentavos === 0 ? 'paid' : 'partial',
            ]);

            HotelTransaction::create([
                'guest_id' => $locked->guest_id,
                'reservation_id' => $locked->reservation_id,
                'room_id' => $locked->room_id,
                'transaction_type' => 'payment',
                'amount' => $paymentCentavos / 100,
                'status' => $remainingCentavos === 0 ? 'paid' : 'partial',
                'transaction_date' => now(),
                'description' => 'Checkout balance payment received via '.str_replace('_', ' ', $data['payment_method']).'.',
            ]);
        });

        return to_route('check-outs.receipt', $checkOut)->with('status', 'Payment recorded and the outstanding balance updated.');
    }

    private function toCentavos(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
