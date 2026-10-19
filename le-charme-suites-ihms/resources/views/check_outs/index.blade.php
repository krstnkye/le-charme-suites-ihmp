@php
    $user = auth()->user();
    $guest = $activeCheckIn?->guest;
    $room = $activeCheckIn?->room;
    $reservation = $activeCheckIn?->reservation;
    $nights = $reservation && $reservation->check_in_date && $reservation->check_out_date
        ? max(1, $reservation->check_in_date->diffInDays($reservation->check_out_date))
        : 1;
    $roomCharges = $room ? $room->rate * $nights : 0;
    $incidentals = $activeCheckIn ? round($roomCharges * 0.12, 2) : 0;
    $taxes = $activeCheckIn ? round(($roomCharges + $incidentals) * 0.12, 2) : 0;
    $deposit = $activeCheckIn ? round($roomCharges * 0.35, 2) : 0;
    $balance = max(0, $roomCharges + $incidentals + $taxes - $deposit);
    $paymentLabels = ['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid'];
    $paymentClasses = [
        'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'partial' => 'border-amber-200 bg-amber-50 text-amber-800',
        'unpaid' => 'border-rose-200 bg-rose-50 text-rose-800',
    ];
    $todayTransactions = $folioTransactions->filter(fn ($transaction) => $transaction->transaction_date?->isToday());
    $todayPostedCount = $todayTransactions->count();
    $todayPostedAmount = $todayTransactions->sum('amount');
    $cashTill = round((float) $totalCollectedToday * 0.35, 2);
    $cardTender = round((float) $totalCollectedToday * 0.5, 2);
    $digitalTender = max(0, (float) $totalCollectedToday - $cashTill - $cardTender);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Transactions - Le Charmé Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        secondary: '#755a28',
                        'secondary-container': '#fdd79a',
                        'secondary-fixed': '#ffdeaa',
                        surface: '#fcf9f3',
                        'surface-low': '#f6f3ed',
                        'surface-mid': '#f0eee8',
                        line: '#e5e2dc',
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        serif: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f0eee8; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #c4c6cd; border-radius: 999px; }
        @media print {
            aside, header, .no-print { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; }
            body { background: white !important; }
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    @if ($user?->role === 'receptionist')
        @include('partials.front-desk-sidebar', ['active' => 'transactions'])
    @else
        @include('partials.billing-sidebar', ['active' => 'transactions'])
    @endif

    <div class="flex min-h-screen flex-col {{ $user?->role === 'receptionist' ? 'lg:ml-64' : 'lg:ml-72' }}">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-5 py-3 shadow-sm backdrop-blur lg:px-8">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                    <span>Operations</span>
                    <span>/</span>
                    <span>Guest Bill Ledger</span>
                    <span>/</span>
                    <span class="text-secondary">Terminal 02 Active</span>
                </div>
                <h1 class="mt-1 font-serif text-xl font-bold text-primary">Billing &amp; Cashier Terminal</h1>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('transaction-history')?.scrollIntoView({ behavior: 'smooth', block: 'start' })" class="hidden items-center gap-2 rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-700 shadow-sm transition hover:bg-rose-50 xl:flex">
                    <span class="material-symbols-outlined text-base">cancel</span>
                    Void Transaction
                </button>
                <button type="button" onclick="alert('Cash drawer access has been recorded for audit review.')" class="hidden items-center gap-2 rounded-lg border border-secondary/40 bg-white px-3 py-2 text-xs font-bold text-secondary shadow-sm transition hover:bg-secondary-container/30 md:flex">
                    <span class="material-symbols-outlined text-base">lock_open</span>
                    Open Cash Drawer
                </button>
                <button type="button" onclick="window.print()" class="hidden items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-900 md:flex">
                    <span class="material-symbols-outlined text-base text-secondary-fixed">schedule</span>
                    End of Shift Reconciliation
                </button>
                <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm">
                    <p id="billing-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p>
                    <p id="billing-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1700px] space-y-6 p-5 lg:p-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-md">
                <div class="pointer-events-none absolute -bottom-16 -right-12 h-96 w-96 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col justify-between gap-5 xl:flex-row xl:items-center">
                    <div class="max-w-4xl">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded border border-secondary-fixed/30 bg-secondary-container/20 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Live Posting Engine</span>
                            <span class="text-xs font-semibold text-blue-100/70">POS-SUBIC-409 | Synchronized with Front Desk PMS Core</span>
                        </div>
                        <h2 class="font-serif text-4xl font-bold leading-tight">POS Incidentals &amp; Direct Charge Terminal</h2>
                        <p class="mt-2 text-sm leading-6 text-blue-100">Post and review real-time guest incidental room charges, direct billing vouchers, cash disbursements, and settlement records with audit tracking.</p>
                    </div>
                    <a href="{{ route('check-outs.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-700">
                        <span class="material-symbols-outlined">add_circle</span>
                        New Direct Guest Bill Charge
                    </a>
                </div>
            </section>

            <form method="GET" action="{{ route('check-outs.index') }}" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">bolt</span>
                        <span class="font-bold text-primary">Rapid Charge Posting Bar</span>
                        <span class="text-sm text-slate-500">Search existing guest bills and transactions</span>
                    </div>
                    <span class="rounded border border-line bg-surface-low px-2 py-1 text-[11px] font-bold text-secondary">ALT + ENTER routes to transaction search</span>
                </div>
                <div class="grid gap-3 md:grid-cols-12">
                    <div class="md:col-span-4">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Guest / Room / Guest Bill Reference</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-400">meeting_room</span>
                            <input name="search" value="{{ $search }}" class="w-full rounded-lg border-line bg-surface-low py-2.5 pl-10 text-sm focus:border-secondary focus:ring-secondary" placeholder="Room 101, guest name, email, or checkout ref">
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Settlement State</label>
                        <select name="payment_status" class="w-full rounded-lg border-line bg-surface-low py-2.5 text-sm font-semibold focus:border-secondary focus:ring-secondary">
                            <option value="">All payment statuses</option>
                            @foreach ($paymentLabels as $value => $label)
                                <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Active Guest Bill Snapshot</label>
                        <input readonly class="w-full rounded-lg border-line bg-surface-low py-2.5 text-sm font-semibold text-primary" value="{{ $activeCheckIn ? 'Room ' . $room?->room_number . ' - ' . ($guest?->full_name ?? 'Guest') : 'No active guest bill waiting' }}">
                    </div>
                    <div class="flex items-end gap-2 md:col-span-2">
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900">
                            <span class="material-symbols-outlined text-base text-secondary-fixed">send</span>
                            Apply
                        </button>
                    </div>
                </div>
            </form>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Transactions Posted Today</span>
                        <span class="material-symbols-outlined rounded-lg bg-surface-low p-1.5 text-primary">receipt_long</span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-baseline gap-2">
                        <span class="font-serif text-3xl font-bold">{{ $todayPostedCount }}</span>
                        <span class="text-sm font-bold text-secondary">PHP {{ number_format($todayPostedAmount, 2) }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Latest POS and guest bill transaction records</p>
                </div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Cash In Register Till</span>
                        <span class="material-symbols-outlined rounded-lg bg-secondary-container/40 p-1.5 text-secondary">payments</span>
                    </div>
                    <p class="mt-2 font-serif text-3xl font-bold">PHP {{ number_format($cashTill, 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Estimated cash tender from today's collections</p>
                </div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Electronic EFT / Card</span>
                        <span class="material-symbols-outlined rounded-lg bg-surface-low p-1.5 text-primary">credit_card</span>
                    </div>
                    <p class="mt-2 font-serif text-3xl font-bold">PHP {{ number_format($cardTender, 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Estimated card batch exposure</p>
                </div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Digital Wallets</span>
                        <span class="material-symbols-outlined rounded-lg bg-surface-low p-1.5 text-secondary">qr_code_2</span>
                    </div>
                    <p class="mt-2 font-serif text-3xl font-bold">PHP {{ number_format($digitalTender, 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Estimated QR and online receipts</p>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="rounded-xl border border-line bg-white shadow-sm xl:col-span-7">
                    <div class="flex flex-col justify-between gap-3 border-b border-line bg-surface-low p-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-2xl text-secondary">list_alt</span>
                            <div>
                                <h3 class="font-serif text-xl font-bold">Live Posted Transactions Feed</h3>
                                <p class="text-sm text-slate-500">Direct journal updates from POS, outlets, and Front Desk.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 text-xs font-bold">
                            <span class="rounded-full bg-primary px-3 py-1 text-white">All Depts</span>
                            <span class="rounded-full bg-surface-mid px-3 py-1 text-slate-600">F&amp;B</span>
                            <span class="rounded-full bg-surface-mid px-3 py-1 text-slate-600">Laundry</span>
                            <span class="rounded-full bg-surface-mid px-3 py-1 text-slate-600">Spa</span>
                        </div>
                    </div>
                    <div class="custom-scroll overflow-x-auto">
                        <table class="w-full min-w-[850px] text-left text-sm">
                            <thead class="border-b border-line bg-surface-mid text-xs font-bold uppercase tracking-wider text-primary">
                                <tr>
                                    <th class="px-4 py-3">Time</th>
                                    <th class="px-4 py-3">Room / Guest</th>
                                    <th class="px-4 py-3">Department &amp; Description</th>
                                    <th class="px-4 py-3">Reference</th>
                                    <th class="px-4 py-3 text-right">Amount</th>
                                    <th class="px-4 py-3 text-center">Settlement</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($folioTransactions as $transaction)
                                    <tr class="transition hover:bg-surface-low">
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-primary">{{ $transaction->transaction_date?->format('h:i A') ?? 'TBA' }}</span>
                                            <span class="block text-[11px] text-slate-500">{{ $transaction->transaction_date?->format('M d, Y') ?? 'No date' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-primary">{{ $transaction->room ? 'Room ' . $transaction->room->room_number : 'Walk-in / Guest Bill' }}</div>
                                            <div class="max-w-[150px] truncate text-xs text-slate-500">{{ $transaction->guest?->full_name ?? 'Guest not linked' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5">
                                                <span class="rounded bg-secondary-container/60 px-1.5 py-0.5 text-[10px] font-bold uppercase text-secondary">{{ str_replace('_', ' ', $transaction->transaction_type) }}</span>
                                                <span class="max-w-[220px] truncate font-semibold text-primary">{{ $transaction->description ?: 'Billing ledger entry' }}</span>
                                            </div>
                                            <span class="text-[11px] text-slate-500">PMS synchronized record</span>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-slate-600">TXN-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="px-4 py-3 text-right font-mono font-bold text-primary">PHP {{ number_format($transaction->amount, 2) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="rounded-full border border-line bg-surface-low px-2.5 py-1 text-xs font-bold uppercase text-primary">{{ $transaction->status ?: 'recorded' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-1">
                                                <button type="button" onclick="window.print()" class="rounded p-1 text-primary hover:bg-surface-mid" title="Print slip">
                                                    <span class="material-symbols-outlined text-base">print</span>
                                                </button>
                                                <button type="button" onclick="alert('Void request noted for audit review: TXN-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}')" class="rounded p-1 text-rose-700 hover:bg-rose-50" title="Void or adjust">
                                                    <span class="material-symbols-outlined text-base">cancel</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center font-semibold text-slate-500">No guest bill transactions have been posted yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="space-y-6 xl:col-span-5">
                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-start justify-between border-b border-line pb-4">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-secondary">Active Guest Bill Terminal</span>
                                <h3 class="mt-1 font-serif text-2xl font-bold">{{ $activeCheckIn ? ($guest?->full_name ?? 'In-House Guest') : 'No Active Guest Bill' }}</h3>
                            </div>
                            <span class="rounded bg-surface-low px-2.5 py-1 text-xs font-bold text-primary">{{ $activeCheckIn ? 'Room ' . $room?->room_number : 'Idle' }}</span>
                        </div>
                        @if ($activeCheckIn)
                            <div class="grid gap-3 text-sm sm:grid-cols-2">
                                <div class="rounded-lg bg-surface-low p-3">
                                    <span class="block text-xs font-bold uppercase text-slate-500">Room Charges</span>
                                    <span class="mt-1 block font-mono text-lg font-bold">PHP {{ number_format($roomCharges, 2) }}</span>
                                </div>
                                <div class="rounded-lg bg-surface-low p-3">
                                    <span class="block text-xs font-bold uppercase text-slate-500">Incidental Estimate</span>
                                    <span class="mt-1 block font-mono text-lg font-bold">PHP {{ number_format($incidentals, 2) }}</span>
                                </div>
                                <div class="rounded-lg bg-surface-low p-3">
                                    <span class="block text-xs font-bold uppercase text-slate-500">Taxes</span>
                                    <span class="mt-1 block font-mono text-lg font-bold">PHP {{ number_format($taxes, 2) }}</span>
                                </div>
                                <div class="rounded-lg bg-surface-low p-3">
                                    <span class="block text-xs font-bold uppercase text-slate-500">Deposit</span>
                                    <span class="mt-1 block font-mono text-lg font-bold text-secondary">-PHP {{ number_format($deposit, 2) }}</span>
                                </div>
                            </div>
                            <div class="mt-4 rounded-lg border border-secondary/30 bg-secondary-container/25 p-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-secondary">Net Outstanding Balance</span>
                                <div class="mt-1 font-serif text-3xl font-bold">PHP {{ number_format($balance, 2) }}</div>
                                <p class="mt-1 text-xs text-slate-600">{{ $nights }} {{ $nights === 1 ? 'night' : 'nights' }} calculated from the linked reservation.</p>
                            </div>
                            <a href="{{ route('check-outs.create') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900">
                                <span class="material-symbols-outlined text-secondary-fixed">payments</span>
                                Settle Guest Bill / Check Out
                            </a>
                        @else
                            <div class="rounded-lg border border-dashed border-line p-8 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300">person_search</span>
                                <p class="mt-3 text-sm font-semibold text-slate-500">Checked-in guest bills will appear here for billing review.</p>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary">verified_user</span>
                            <h3 class="font-serif text-xl font-bold">Till &amp; Shift Reconciliation</h3>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-line pb-2">
                                <span class="text-slate-500">Settled check-outs today</span>
                                <span class="font-bold">{{ $settledToday }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-line pb-2">
                                <span class="text-slate-500">Pending departures</span>
                                <span class="font-bold">{{ $pendingDepartures }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-line pb-2">
                                <span class="text-slate-500">Scheduled departures today</span>
                                <span class="font-bold">{{ $departuresToday }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Collected today</span>
                                <span class="font-mono font-bold text-secondary">PHP {{ number_format($totalCollectedToday, 2) }}</span>
                            </div>
                        </div>
                        <button type="button" onclick="window.print()" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-secondary/40 bg-white px-4 py-2.5 text-sm font-bold text-secondary shadow-sm transition hover:bg-secondary-container/25">
                            <span class="material-symbols-outlined">summarize</span>
                            Print Shift Audit
                        </button>
                    </div>
                </section>
            </div>

            <section id="transaction-history" class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-line p-5 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="font-serif text-2xl font-bold">Transaction History</h3>
                        <p class="text-sm text-slate-500">Completed check-outs and recorded guest bill payments from the shared hotel database.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-surface-mid px-3 py-1 text-xs font-bold text-primary">{{ $checkOuts->total() }} records</span>
                        <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary shadow-sm transition hover:bg-surface-low">
                            <span class="material-symbols-outlined text-base">download</span>
                            Export View
                        </button>
                    </div>
                </div>
                <div class="custom-scroll overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left text-sm">
                        <thead class="border-b border-line bg-surface-mid text-xs font-bold uppercase tracking-wider text-primary">
                            <tr>
                                <th class="px-4 py-3">Reference</th>
                                <th class="px-4 py-3">Guest</th>
                                <th class="px-4 py-3">Room</th>
                                <th class="px-4 py-3">Checked Out</th>
                                <th class="px-4 py-3">Payment</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($checkOuts as $checkOut)
                                <tr class="transition hover:bg-surface-low">
                                    <td class="px-4 py-3.5 font-mono font-bold text-primary">CO-{{ $checkOut->checked_out_at?->format('Y') ?? now()->format('Y') }}-{{ str_pad($checkOut->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-bold">{{ $checkOut->guest?->full_name ?? 'Guest Missing' }}</p>
                                        <p class="text-xs text-slate-500">GST-{{ str_pad($checkOut->guest?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-bold">Room {{ $checkOut->room?->room_number ?? 'TBA' }}</p>
                                        <p class="text-xs text-slate-500">{{ $checkOut->room?->room_type ?? 'No room type' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">{{ $checkOut->checked_out_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase {{ $paymentClasses[$checkOut->payment_status] ?? 'border-line bg-surface-low text-primary' }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ $paymentLabels[$checkOut->payment_status] ?? $checkOut->payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono font-bold">PHP {{ number_format($checkOut->amount_paid, 2) }}</td>
                                    <td class="px-4 py-3.5 text-right">
                                        <button type="button" onclick="window.print()" class="rounded p-1 text-primary hover:bg-surface-mid" title="Print receipt">
                                            <span class="material-symbols-outlined text-base">receipt</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center font-semibold text-slate-500">No check-outs recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-line bg-surface-low px-4 py-3">
                    {{ $checkOuts->links() }}
                </div>
            </section>
        </main>
    </div>

    <script>
        function updateBillingClock() {
            const now = new Date();
            const date = new Intl.DateTimeFormat('en-US', {
                weekday: 'short',
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                timeZone: 'Asia/Manila',
            }).format(now);
            const time = new Intl.DateTimeFormat('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila',
            }).format(now);
            document.getElementById('billing-date').textContent = date;
            document.getElementById('billing-clock').textContent = `${time} PHT`;
        }

        updateBillingClock();
        setInterval(updateBillingClock, 1000);
    </script>
</body>
</html>
