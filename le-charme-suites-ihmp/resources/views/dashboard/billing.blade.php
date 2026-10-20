@php
    $user = auth()->user();
    $paymentClasses = [
        'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'partial' => 'border-amber-200 bg-amber-50 text-amber-800',
        'unpaid' => 'border-rose-200 bg-rose-50 text-rose-800',
        'recorded' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];
    $totalOpenExposure = $activeBillAmounts->sum('total');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Dashboard - Le Charme Suites IHMP</title>
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
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    @include('partials.billing-sidebar', ['active' => 'dashboard'])

    <div class="flex min-h-screen flex-col lg:ml-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-5 py-3 shadow-sm backdrop-blur lg:px-8">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                    <span>Billing Operations</span>
                    <span>/</span>
                    <span class="text-secondary">Command Dashboard</span>
                </div>
                <h1 class="mt-1 font-serif text-xl font-bold text-primary">Le Charmé Suites IHMP <span class="font-sans font-normal text-slate-400">|</span> <span class="font-sans text-sm font-semibold text-slate-600">Subic Bay Freeport Zone</span></h1>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <div class="hidden items-center gap-2 rounded-full border border-line bg-surface-low px-3 py-1.5 text-xs font-semibold text-primary xl:flex">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <span>{{ auth()->user()->name }}</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-600">{{ $pendingDepartures }} active guest bills</span>
                </div>
                <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm">
                    <p id="billing-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p>
                    <p id="billing-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p>
                </div>
                <a href="{{ route('check-outs.create') }}" class="hidden items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900 md:flex">
                    <span class="material-symbols-outlined text-secondary-fixed">post_add</span>
                    Add Charge / Guest Bill
                </a>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1600px] space-y-6 p-5 lg:p-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-md">
                <div class="pointer-events-none absolute -bottom-16 -right-12 h-96 w-96 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                    <div class="max-w-3xl">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded border border-secondary-fixed/30 bg-secondary-container/20 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Authorized Billing Dashboard</span>
                            <span class="text-xs font-semibold text-blue-100/70">Shared database records</span>
                        </div>
                        <h2 class="font-serif text-4xl font-bold leading-tight">Cashier &amp; Billing Dashboard</h2>
                        <p class="mt-2 text-sm leading-6 text-blue-100">Review guest bills, verify payments, add charges, and monitor hotel income for the Billing department.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('check-outs.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-700">
                            <span class="material-symbols-outlined">payments</span>
                        Pay Guest Bill / Check Out
                        </a>
                        <a href="{{ route('check-outs.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-secondary-fixed/40 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                            <span class="material-symbols-outlined text-secondary-fixed">receipt_long</span>
                            View Transactions
                        </a>
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-primary/40 px-3.5 py-2.5 text-sm font-semibold text-blue-100 transition hover:bg-primary hover:text-white">
                            <span class="material-symbols-outlined">print</span>
                        Print Cashier Report
                        </button>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm xl:col-span-2">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="rounded-lg bg-secondary-container/40 p-2 text-secondary"><span class="material-symbols-outlined">monetization_on</span></span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Daily Collections</p>
                            <p class="text-xs font-semibold text-secondary">Payments saved today</p>
                            </div>
                        </div>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">Live</span>
                    </div>
                    <p class="font-serif text-3xl font-bold text-primary">PHP {{ number_format($collectedToday, 2) }}</p>
                    <div class="mt-4 grid gap-2 border-t border-line pt-3 text-xs sm:grid-cols-3">
                        <div class="rounded border border-line bg-surface-low p-2"><span class="block text-slate-500">Paid receipts</span><strong>{{ $paidToday }}</strong></div>
                        <div class="rounded border border-line bg-surface-low p-2"><span class="block text-slate-500">Partial payments</span><strong>{{ $partialToday }}</strong></div>
                        <div class="rounded border border-line bg-surface-low p-2"><span class="block text-slate-500">Unpaid records</span><strong>{{ $unpaidToday }}</strong></div>
                    </div>
                </div>

                <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                    <div class="mb-2 flex items-center justify-between"><span class="rounded-lg bg-surface-mid p-2 text-primary"><span class="material-symbols-outlined">folder_shared</span></span><span class="rounded bg-surface-mid px-2 py-1 text-xs text-slate-600">In-House</span></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Guest Bills</p>
                    <p class="mt-2 font-serif text-2xl font-bold text-primary">{{ $pendingDepartures }} Active Guest Bills</p>
                    <p class="mt-3 border-t border-line pt-3 text-xs text-slate-500">Outstanding: <strong class="text-secondary">PHP {{ number_format($totalOpenExposure, 2) }}</strong></p>
                </div>

                <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                    <div class="mb-2 flex items-center justify-between"><span class="rounded-lg border border-secondary/20 bg-amber-50 p-2 text-secondary"><span class="material-symbols-outlined">luggage</span></span><span class="rounded bg-amber-100 px-2 py-1 text-xs font-bold text-secondary">Action Required</span></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Pending Payments</p>
                    <p class="mt-2 font-serif text-2xl font-bold text-secondary">{{ $departuresToday }} Departures</p>
                    <p class="mt-3 border-t border-line pt-3 text-xs text-slate-500">Ready for check-out bill clearing</p>
                </div>

                <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                    <div class="mb-2 flex items-center justify-between"><span class="rounded-lg bg-emerald-50 p-2 text-emerald-700"><span class="material-symbols-outlined">receipt_long</span></span><span class="rounded bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800">Database</span></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Recorded Charges Today</p>
                    <p class="mt-2 font-serif text-2xl font-bold text-emerald-700">PHP {{ number_format($recordedRevenue, 2) }}</p>
                    <p class="mt-3 border-t border-line pt-3 text-xs text-slate-500">Saved hotel transaction records</p>
                </div>
            </section>

            <section class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">
                <div class="space-y-6 xl:col-span-8">
                    <div class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                        <div class="flex flex-col justify-between gap-4 border-b border-line bg-surface-low/60 p-5 sm:flex-row sm:items-center">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-serif text-2xl font-bold text-primary">Departures &amp; Guest Bills for Payment</h3>
                                    <span class="rounded-full bg-secondary-fixed px-2 py-1 text-xs font-bold text-primary">{{ $settlementQueue->count() }} Today</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">Guest bills waiting for payment or receipt printing.</p>
                            </div>
                            <a href="{{ route('check-outs.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-sm font-bold text-primary transition hover:border-secondary">
                                    <span class="material-symbols-outlined text-base text-secondary">filter_list</span>
                                    View All Guest Bills
                            </a>
                        </div>

                        <div class="divide-y divide-line">
                            @forelse ($settlementQueue as $checkIn)
                                @php
                                    $bill = $activeBillAmounts->get($checkIn->id, ['nights' => 1, 'room_charges' => 0, 'additional_charges' => 0, 'total' => 0]);
                                    $balance = (float) $bill['total'];
                                @endphp
                                <div class="p-5 transition hover:bg-surface-low/40">
                                    <div class="flex flex-col justify-between gap-4 border-b border-line/70 pb-3 md:flex-row md:items-center">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg border border-secondary-fixed/40 bg-primary-container text-white shadow-sm">
                                                <span class="text-[10px] font-bold tracking-widest text-secondary-fixed">ROOM</span>
                                                <span class="font-serif text-base font-bold">{{ $checkIn->room?->room_number ?? 'N/A' }}</span>
                                            </div>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-bold text-primary">{{ $checkIn->guest?->full_name ?? 'Guest record unavailable' }}</span>
                                                    <span class="rounded border border-secondary-fixed/50 bg-secondary-container/40 px-2 py-0.5 text-xs font-semibold text-secondary">{{ $checkIn->room?->room_type ?? 'Room' }}</span>
                                                    <span class="font-mono text-xs text-slate-500">#STAY-{{ str_pad((string) $checkIn->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                </div>
                                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                                    <span>Check-in: <strong class="text-primary">{{ $checkIn->checked_in_at?->format('M d, Y') ?? 'N/A' }}</strong></span>
                                                    <span>Check-out: <strong class="font-bold text-secondary">{{ $checkIn->reservation?->check_out_date?->format('M d, Y') ?? 'Not set' }}</strong></span>
                                                    <span>Key Status: <strong class="text-emerald-700">Pending Return</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rounded-lg border border-line bg-surface-low px-4 py-2 text-right">
                                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Balance Due</span>
                                            <span class="font-serif text-xl font-bold text-primary">PHP {{ number_format($balance, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="grid gap-3 py-3 text-sm sm:grid-cols-4">
                                        <div class="rounded border border-line bg-surface p-2"><span class="block text-xs text-slate-500">Room charges</span><strong>PHP {{ number_format($bill['room_charges'], 2) }}</strong></div>
                                        <div class="rounded border border-line bg-surface p-2"><span class="block text-xs text-slate-500">Additional charges</span><strong>PHP {{ number_format($bill['additional_charges'], 2) }}</strong></div>
                                        <div class="rounded border border-line bg-surface p-2"><span class="block text-xs text-slate-500">Number of nights</span><strong>{{ $bill['nights'] }}</strong></div>
                                        <div class="rounded border border-emerald-200 bg-emerald-50 p-2"><span class="block text-xs text-emerald-800">Current total</span><strong class="text-emerald-700">PHP {{ number_format($bill['total'], 2) }}</strong></div>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                        <div class="flex items-center gap-2 text-xs font-semibold text-secondary"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Guest Bill pending Billing review</div>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('check-outs.bill-preview', ['checkIn' => $checkIn, 'print' => 1]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded border border-line bg-white px-3 py-1.5 text-xs font-bold text-primary transition hover:border-secondary"><span class="material-symbols-outlined text-sm">print</span>Print Itemized Bill</a>
                                            <a href="{{ route('check-outs.create') }}" class="inline-flex items-center gap-1.5 rounded bg-primary px-4 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-slate-900"><span class="material-symbols-outlined text-sm text-secondary-fixed">payments</span>Process Payment</a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-10 text-center font-semibold text-slate-500">No active guest bills are waiting for Billing right now.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">person_search</span>
                                <h3 class="text-base font-bold text-primary">Find a Guest Bill</h3>
                            </div>
                            <span class="text-xs font-semibold text-slate-500">Quick entry</span>
                        </div>
                        <form class="grid gap-3 sm:grid-cols-[1fr_auto]" action="{{ route('check-outs.index') }}" method="GET">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500">Guest name, room number, or email</label>
                                <input name="search" class="w-full rounded border border-line bg-surface-low p-2 text-sm focus:border-secondary focus:ring-secondary" placeholder="Enter a guest name, room, or email" type="search">
                            </div>
                            <div class="flex items-end">
                                <button class="flex w-full items-center justify-center gap-1 rounded bg-primary-container px-3 py-2.5 text-sm font-bold text-white transition hover:bg-primary" type="submit">
                                    <span class="material-symbols-outlined text-sm text-secondary-fixed">search</span>
                                    Find Guest Bill
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <aside class="space-y-6 xl:col-span-4">
                    <div class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-line bg-surface-low/60 p-4">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-600"></span></span>
                            <h3 class="font-bold text-primary">Recent Transactions</h3>
                            </div>
                            <span class="text-xs font-bold text-secondary">Auto-syncing</span>
                        </div>
                        <div class="divide-y divide-line">
                            @forelse ($folioActivity->take(5) as $transaction)
                                <div class="flex items-start justify-between gap-3 p-4 transition hover:bg-surface-low/40">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 rounded border border-secondary/20 bg-amber-50 p-2 text-secondary"><span class="material-symbols-outlined text-sm">payments</span></div>
                                        <div>
                                            <div class="flex items-center gap-1.5"><span class="font-bold text-primary">Room {{ $transaction->room?->room_number ?? 'N/A' }}</span><span class="text-xs text-slate-400">/</span><span class="text-xs text-slate-600">{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</span></div>
                                            <p class="text-xs text-slate-600">{{ $transaction->description ?: 'Billing record' }}</p>
                                            <span class="text-xs text-slate-400">{{ $transaction->transaction_date?->format('h:i A') }} / {{ $transaction->guest?->full_name ?? 'Guest record' }}</span>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="block text-sm font-bold text-primary">PHP {{ number_format($transaction->amount, 2) }}</span>
                                        <span class="rounded border px-1.5 py-0.5 text-[11px] font-bold {{ $paymentClasses[$transaction->status] ?? $paymentClasses['recorded'] }}">{{ ucfirst($transaction->status) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-sm font-semibold text-slate-500">No billing activity has been recorded yet.</div>
                            @endforelse
                        </div>
                        <div class="border-t border-line bg-surface-low p-3 text-center">
                        <a class="text-sm font-bold text-secondary hover:underline" href="{{ route('check-outs.history') }}">View Transaction History</a>
                        </div>
                    </div>

                    <div class="space-y-4 rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-3">
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">payments</span><h3 class="font-serif text-lg font-bold text-primary">Payment Record Summary</h3></div>
                            <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">Saved Records</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between rounded bg-surface-low p-2"><span class="text-slate-600">Paid check-outs today</span><strong class="text-emerald-700">{{ $paidToday }}</strong></div>
                            <div class="flex items-center justify-between rounded bg-surface-low p-2"><span class="text-slate-600">Partially paid check-outs</span><strong class="text-amber-700">{{ $partialToday }}</strong></div>
                            <div class="flex items-center justify-between rounded bg-surface-low p-2"><span class="text-slate-600">Unpaid check-outs</span><strong class="text-red-700">{{ $unpaidToday }}</strong></div>
                            <div class="flex items-center justify-between rounded border-l-2 border-secondary bg-surface-low p-2"><span class="font-semibold">Payments recorded today</span><strong class="text-secondary">PHP {{ number_format($collectedToday, 2) }}</strong></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <a href="{{ route('reports.index') }}" class="flex items-center justify-center gap-1 rounded border border-line bg-surface-low px-3 py-2 text-sm font-bold text-primary transition hover:bg-line"><span class="material-symbols-outlined text-sm">receipt</span>Current Cash Report</a>
                            <a href="{{ route('reports.index') }}" class="flex items-center justify-center gap-1 rounded border border-line bg-surface-low px-3 py-2 text-sm font-bold text-primary transition hover:bg-line"><span class="material-symbols-outlined text-sm">fact_check</span>End-of-Day Report</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-line bg-surface-mid p-4">
                        <span class="material-symbols-outlined text-2xl text-secondary">verified_user</span>
                        <div>
                            <p class="font-bold text-primary">Saved Payment Records</p>
                            <p class="mt-1 text-sm text-slate-600">Guest payments and check-out records are saved in the shared IHMP database for authorized review.</p>
                        </div>
                    </div>
                </aside>
            </section>
        </main>
    </div>

    <script>
        const dateEl = document.getElementById('billing-date');
        const clockEl = document.getElementById('billing-clock');

        function updateBillingClock() {
            const now = new Date();
            dateEl.textContent = new Intl.DateTimeFormat('en-US', {
                weekday: 'short',
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                timeZone: 'Asia/Manila',
            }).format(now);
            clockEl.textContent = new Intl.DateTimeFormat('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila',
            }).format(now) + ' PHT';
        }

        updateBillingClock();
        setInterval(updateBillingClock, 1000);
    </script>
</body>
</html>
