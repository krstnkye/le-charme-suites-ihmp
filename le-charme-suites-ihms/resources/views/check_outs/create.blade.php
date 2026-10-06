@php
    $currentUser = auth()->user();
    $activeCheckIns = $checkIns->map(function ($checkIn) {
        $reservation = $checkIn->reservation;
        $room = $checkIn->room;
        $guest = $checkIn->guest;
        $checkInDate = $reservation?->check_in_date ?? $checkIn->checked_in_at;
        $checkOutDate = $reservation?->check_out_date ?? now();
        $nights = max(1, $checkInDate && $checkOutDate ? $checkInDate->diffInDays($checkOutDate) : 1);
        $roomTotal = (float) ($room?->rate ?? 0) * $nights;
        $incidentals = round($roomTotal * 0.12, 2);
        $tax = round(($roomTotal + $incidentals) * 0.05, 2);
        $deposit = round($roomTotal * 0.35, 2);
        $balance = max(0, $roomTotal + $incidentals + $tax - $deposit);

        return [
            'id' => $checkIn->id,
            'guest' => $guest?->full_name ?: 'Guest profile unavailable',
            'email' => $guest?->email ?: 'No email on file',
            'room' => $room?->room_number ?: 'Unassigned',
            'room_type' => $room?->room_type ?: 'Room',
            'nights' => $nights,
            'room_total' => $roomTotal,
            'incidentals' => $incidentals,
            'tax' => $tax,
            'deposit' => $deposit,
            'balance' => $balance,
            'check_in' => $checkInDate?->format('M d, Y') ?: 'Not recorded',
            'check_out' => $checkOutDate?->format('M d, Y') ?: 'Pending',
            'folio' => 'FOL-' . now()->format('Y') . '-' . str_pad((string) $checkIn->id, 5, '0', STR_PAD_LEFT),
        ];
    })->values();
    $activeBalance = $activeCheckIns->sum('balance');
    $roomDebitTotal = $activeCheckIns->sum('room_total');
    $incidentalsTotal = $activeCheckIns->sum('incidentals');
    $billingNav = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard'), 'active' => false],
        ['label' => 'Billing', 'icon' => 'receipt_long', 'href' => route('check-outs.create'), 'active' => true],
        ['label' => 'Transactions', 'icon' => 'point_of_sale', 'href' => route('check-outs.index'), 'active' => false],
        ['label' => 'Transaction History', 'icon' => 'history', 'href' => route('check-outs.history'), 'active' => false],
        ['label' => 'Reports', 'icon' => 'bar_chart', 'href' => route('reports.index'), 'active' => false],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Guest Bill Ledger - Le Charmé Suites IHMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { primary: '#01162d', 'primary-container': '#172b42', secondary: '#755a28', 'secondary-container': '#fdd79a', 'secondary-fixed': '#ffdeaa', surface: '#fcf9f3', 'surface-low': '#f6f3ed', 'surface-mid': '#f0eee8', line: '#e5e2dc', error: '#ba1a1a' },
            fontFamily: { sans: ['Manrope', 'sans-serif'], serif: ['Noto Serif', 'serif'] },
        } } };
    </script>
    <style>
        .material-symbols-outlined { display:inline-block; vertical-align:middle; line-height:1; font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f0eee8; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #c4c6cd; border-radius: 999px; }
        @media print { aside, header, .no-print { display:none!important; } body { background:white!important; } }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    <aside class="fixed left-0 top-0 z-40 hidden h-screen w-72 flex-col justify-between border-r border-white/10 bg-primary-container text-white shadow-xl lg:flex">
        <div class="flex min-h-0 flex-1 flex-col">
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg border border-secondary-fixed/40 bg-white/10 text-secondary-fixed">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">hotel_class</span>
                </div>
                <div>
                    <div class="font-serif text-xl font-bold leading-tight">Le Charmé Suites</div>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="rounded bg-secondary-fixed px-1.5 py-0.5 text-[10px] font-bold text-primary">IHMS</span>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-100/70">Billing Command</span>
                    </div>
                </div>
            </div>

            <div class="mx-4 my-4 flex items-center gap-3 rounded-lg border border-secondary-fixed/20 bg-primary/40 p-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-secondary text-sm font-bold text-white ring-2 ring-secondary-fixed/30">
                    {{ collect(explode(' ', $currentUser?->name ?? 'Billing Staff'))->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'B' }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold">{{ $currentUser?->name ?? 'Billing Staff' }}</p>
                    <div class="flex items-center gap-2 text-xs text-secondary-fixed"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Billing Department Access</div>
                </div>
            </div>

            <nav class="custom-scroll mt-1 flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto px-3 text-sm font-semibold" aria-label="Billing Navigation">
                @foreach ($billingNav as $item)
                    <a href="{{ $item['href'] }}" @class(['flex items-center gap-3 rounded-r-lg border-l-[3px] px-4 py-3 transition', 'border-secondary-fixed bg-primary/60 text-white' => $item['active'], 'border-transparent text-blue-100/70 hover:bg-primary/40 hover:text-white' => ! $item['active']])>
                        <span @class(['material-symbols-outlined text-xl', 'text-secondary-fixed' => $item['active']])>{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="border-t border-white/10 bg-primary/20 p-3">
            <div class="mb-2 flex items-center justify-between px-3 py-1.5 text-xs text-blue-100/70"><span>SBMA Station</span><span class="font-bold text-secondary-fixed">POS-REG-02</span></div>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded px-3 py-2 text-blue-100/70 transition hover:bg-primary/40 hover:text-white"><span class="material-symbols-outlined">account_circle</span><span class="text-sm font-semibold">My Profile</span></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded px-3 py-2 text-left text-rose-100 transition hover:bg-rose-500/20"><span class="material-symbols-outlined text-rose-300">logout</span><span class="text-sm font-semibold">Logout</span></button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col lg:ml-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-5 py-3 shadow-sm backdrop-blur lg:px-8">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500"><span>Billing Operations</span><span>/</span><span class="text-secondary">Guest Bill Ledger</span></div>
                <h1 class="mt-1 font-serif text-xl font-bold text-primary">Billing &amp; Cashier Terminal <span class="font-sans text-sm font-semibold text-slate-500">| Subic Freeport Zone</span></h1>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <div class="hidden items-center gap-2 rounded-full border border-line bg-surface-low px-3 py-1.5 text-xs font-semibold text-primary xl:flex"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span><span>Shift A: 06:00 - 14:30 PHT</span><span class="text-slate-300">|</span><span class="text-slate-600">Register #02 Online</span></div>
                <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm"><p id="billing-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p><p id="billing-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p></div>
                <a href="{{ route('check-outs.index') }}" class="hidden items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900 md:flex"><span class="material-symbols-outlined text-secondary-fixed">point_of_sale</span>View Transactions</a>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1700px] space-y-6 p-5 lg:p-8">
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-gradient-to-r from-primary via-primary-container to-[#002c48] p-7 text-white shadow-md">
                <div class="absolute inset-x-0 top-0 h-[2px] bg-gradient-to-r from-transparent via-secondary-fixed to-transparent"></div>
                <div class="pointer-events-none absolute -bottom-16 -right-12 h-80 w-80 rounded-full bg-secondary/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                    <div class="max-w-4xl">
                        <div class="mb-2 flex flex-wrap items-center gap-2"><span class="rounded border border-secondary-fixed/30 bg-secondary/30 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Executive Grandeur IHMS</span><span class="text-xs font-semibold text-blue-100/70">Front Desk &amp; Night Audit Certified</span></div>
                        <h2 class="font-serif text-4xl font-bold leading-tight">Guest Bill Ledger &amp; Invoicing Management</h2>
                        <p class="mt-2 text-sm leading-6 text-blue-100">Consolidated active guest bills, incidental room routing, statutory revenue accounting, and final settlement processing for Billing department staff.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-secondary-fixed/40 bg-white/10 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-white/20"><span class="material-symbols-outlined text-secondary-fixed">layers</span>Batch Guest Bill Invoicing</button>
                        <button type="button" onclick="document.getElementById('settlementForm')?.scrollIntoView({ behavior: 'smooth', block: 'center' })" class="inline-flex items-center gap-2 rounded-lg border border-secondary-fixed/40 bg-white/10 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-white/20"><span class="material-symbols-outlined text-secondary-fixed">price_check</span>Settle Selected Balance</button>
                        <a href="{{ route('check-outs.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-secondary-fixed px-4 py-2.5 text-sm font-bold text-primary shadow-md transition hover:bg-secondary-container"><span class="material-symbols-outlined">add_card</span>Open POS Terminal</a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Active In-House Guest Bills</span><span class="material-symbols-outlined rounded-lg bg-surface-low p-2 text-primary">hotel</span></div><div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold">{{ $activeCheckIns->count() }}</span><span class="text-sm font-semibold text-secondary">Suites Occupied</span></div><p class="mt-1 text-sm text-slate-500">Outstanding: <strong class="text-primary">PHP {{ number_format($activeBalance, 2) }}</strong></p></div>
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ready for Settlement</span><span class="material-symbols-outlined rounded-lg bg-secondary-container/40 p-2 text-secondary">luggage</span></div><div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold">{{ $activeCheckIns->count() }}</span><span class="rounded-full bg-secondary-fixed px-2 py-0.5 text-xs font-bold text-primary">Today</span></div><p class="mt-1 text-sm text-slate-500">Pending checkout bill clearing</p></div>
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Room Debit Exposure</span><span class="material-symbols-outlined rounded-lg bg-surface-low p-2 text-primary">corporate_fare</span></div><p class="mt-3 font-serif text-3xl font-bold">PHP {{ number_format($roomDebitTotal, 2) }}</p><p class="mt-1 text-sm text-slate-500">Calculated from active room rates</p></div>
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Unbilled Charges</span><span class="material-symbols-outlined rounded-lg bg-surface-low p-2 text-secondary">room_service</span></div><p class="mt-3 font-serif text-3xl font-bold">PHP {{ number_format($incidentalsTotal, 2) }}</p><p class="mt-1 text-sm text-slate-500">Estimated incidentals and service charges</p></div>
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="rounded-xl border border-line bg-white shadow-sm xl:col-span-8">
                    <div class="border-b border-line p-5">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                            <div><h3 class="font-serif text-2xl font-bold">Guest Bill Accounting Ledger</h3><p class="text-sm text-slate-500">Review live room debits, verify pre-authorizations, and dispatch official invoices.</p></div>
                            <div class="flex flex-wrap items-center gap-2"><button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary shadow-sm transition hover:bg-surface-low"><span class="material-symbols-outlined text-base">print</span>Print Master Bill</button><a href="{{ route('check-outs.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary shadow-sm transition hover:bg-surface-low"><span class="material-symbols-outlined text-base">tune</span>Full Transaction Filter</a></div>
                        </div>
                    </div>

                    <div class="custom-scroll overflow-x-auto">
                        <table class="w-full min-w-[980px] text-left text-sm">
                            <thead class="border-b border-line bg-surface-mid text-xs font-bold uppercase tracking-wider text-slate-600">
                                <tr><th class="px-4 py-3">Bill / Reference</th><th class="px-4 py-3">Guest &amp; Room</th><th class="px-3 py-3 text-right">Room Debit</th><th class="px-3 py-3 text-right">Incidentals</th><th class="px-3 py-3 text-right">SBMA Tax</th><th class="px-3 py-3 text-right">Deposits</th><th class="px-4 py-3 text-right">Balance Due</th><th class="px-3 py-3 text-center">Status</th><th class="px-4 py-3 text-center">Actions</th></tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($activeCheckIns as $stay)
                                    <tr class="folio-row transition hover:bg-surface-low" data-stay-id="{{ $stay['id'] }}">
                                        <td class="px-4 py-3.5"><div class="font-mono font-bold text-primary">#{{ $stay['folio'] }}</div><div class="text-xs text-slate-500">Stay ID: {{ str_pad((string) $stay['id'], 5, '0', STR_PAD_LEFT) }}</div></td>
                                        <td class="px-4 py-3.5"><div class="flex items-center gap-1.5 font-bold text-primary"><span>Room {{ $stay['room'] }}</span><span class="h-1.5 w-1.5 rounded-full bg-secondary"></span><span class="text-xs font-normal">{{ $stay['room_type'] }}</span></div><div class="text-xs text-slate-500">{{ $stay['guest'] }}</div></td>
                                        <td class="px-3 py-3.5 text-right font-mono font-semibold">PHP {{ number_format($stay['room_total'], 2) }}</td>
                                        <td class="px-3 py-3.5 text-right font-mono font-semibold">PHP {{ number_format($stay['incidentals'], 2) }}</td>
                                        <td class="px-3 py-3.5 text-right font-mono text-slate-600">PHP {{ number_format($stay['tax'], 2) }}</td>
                                        <td class="px-3 py-3.5 text-right font-mono font-semibold text-emerald-800">-PHP {{ number_format($stay['deposit'], 2) }}</td>
                                        <td class="px-4 py-3.5 text-right"><div class="font-mono text-base font-bold text-primary">PHP {{ number_format($stay['balance'], 2) }}</div><span class="text-[10px] font-bold uppercase text-secondary">Pending Settlement</span></td>
                                        <td class="px-3 py-3.5 text-center"><span class="rounded-full bg-secondary-fixed px-2 py-1 text-xs font-bold text-primary">Pre-Auth</span></td>
                                        <td class="px-4 py-3.5 text-center"><div class="flex items-center justify-center gap-1 text-slate-500"><button type="button" class="select-folio rounded p-1 hover:bg-surface-mid hover:text-primary" data-stay-id="{{ $stay['id'] }}" title="View detailed guest bill"><span class="material-symbols-outlined text-base">visibility</span></button><button type="button" class="select-folio rounded p-1 hover:bg-surface-mid hover:text-secondary" data-stay-id="{{ $stay['id'] }}" title="Post payment"><span class="material-symbols-outlined text-base">payments</span></button><button type="button" onclick="window.print()" class="rounded p-1 hover:bg-surface-mid hover:text-primary" title="Print invoice"><span class="material-symbols-outlined text-base">print</span></button></div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="px-4 py-12 text-center font-semibold text-slate-500">No active guest bills are waiting for Billing right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <aside class="space-y-6 xl:col-span-4">
                    <form id="settlementForm" method="POST" action="{{ route('check-outs.store') }}" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        @csrf
                        <div class="mb-4 flex items-start justify-between border-b border-line pb-4"><div><span class="text-xs font-bold uppercase tracking-wider text-secondary">Selected Guest Bill Detail</span><h3 id="guestPreview" class="mt-1 font-serif text-2xl font-bold">No guest selected</h3><p id="emailPreview" class="text-sm text-slate-500">Choose a ledger row to view guest bill details.</p></div><span id="roomPreview" class="rounded bg-surface-low px-2.5 py-1 text-xs font-bold text-primary">--</span></div>

                        <label for="check_in_id" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Active guest bill <span class="text-error">*</span></label>
                        <select id="check_in_id" name="check_in_id" class="mb-4 w-full rounded-lg border-line bg-surface-low py-2.5 text-sm font-semibold focus:border-secondary focus:ring-secondary" required>
                            <option value="">Select active guest bill</option>
                            @foreach ($activeCheckIns as $stay)
                                <option value="{{ $stay['id'] }}" @selected(old('check_in_id') == $stay['id'])>Room {{ $stay['room'] }} - {{ $stay['guest'] }} ({{ $stay['folio'] }})</option>
                            @endforeach
                        </select>

                        <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-surface-low p-3"><span class="block text-xs font-bold uppercase text-slate-500">Check-in</span><span id="checkInPreview" class="mt-1 block font-bold">Not selected</span></div>
                            <div class="rounded-lg bg-surface-low p-3"><span class="block text-xs font-bold uppercase text-slate-500">Check-out</span><span id="checkOutPreview" class="mt-1 block font-bold">Not selected</span></div>
                            <div class="rounded-lg bg-surface-low p-3"><span class="block text-xs font-bold uppercase text-slate-500">Guest Bill</span><span id="folioPreview" class="mt-1 block font-mono text-xs font-bold">--</span></div>
                            <div class="rounded-lg bg-surface-low p-3"><span class="block text-xs font-bold uppercase text-slate-500">Stay</span><span id="nightsPreview" class="mt-1 block font-bold">0 nights</span></div>
                        </div>

                        <div class="mb-4 space-y-2 rounded-lg border border-line bg-surface-low p-4">
                            <div class="flex justify-between text-sm"><span class="text-slate-500">Room debit</span><span id="roomChargesPreview" class="font-mono font-bold">PHP 0.00</span></div>
                            <div class="flex justify-between text-sm"><span class="text-slate-500">Incidentals</span><span id="incidentalsPreview" class="font-mono font-bold">PHP 0.00</span></div>
                            <div class="flex justify-between text-sm"><span class="text-slate-500">SBMA tax</span><span id="taxPreview" class="font-mono font-bold">PHP 0.00</span></div>
                            <div class="flex justify-between border-t border-line pt-3"><span class="font-bold">Balance due</span><span id="balanceDuePreview" class="font-serif text-2xl font-bold">PHP 0.00</span></div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label for="amount_paid" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Amount paid <span class="text-error">*</span></label><div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-secondary">PHP</span><input id="amount_paid" name="amount_paid" type="number" step="0.01" min="0" value="{{ old('amount_paid', 0) }}" class="w-full rounded-lg border-line bg-white py-2.5 pl-14 text-sm font-semibold focus:border-secondary focus:ring-secondary"></div><button id="payInFullButton" type="button" class="mt-2 text-xs font-bold text-secondary hover:underline">Pay selected guest bill in full</button></div>
                            <div><label for="payment_status" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Payment status <span class="text-error">*</span></label><select id="payment_status" name="payment_status" class="w-full rounded-lg border-line bg-white py-2.5 text-sm font-semibold focus:border-secondary focus:ring-secondary" required><option value="unpaid" @selected(old('payment_status') === 'unpaid')>Unpaid</option><option value="partial" @selected(old('payment_status') === 'partial')>Partial</option><option value="paid" @selected(old('payment_status') === 'paid')>Paid</option></select></div>
                        </div>

                        <div class="mt-4"><label for="notes" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Billing notes</label><textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border-line bg-white p-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Payment details, invoice remarks, room turnover notes...">{{ old('notes') }}</textarea></div>

                        <div class="mt-5 flex flex-wrap gap-3 border-t border-line pt-4">
                            <button class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900" @disabled($activeCheckIns->isEmpty())><span class="material-symbols-outlined text-secondary-fixed">price_check</span>Settle Guest Bill / Checkout</button>
                            <button id="openFolioButton" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-white px-4 py-3 text-sm font-bold text-primary shadow-sm transition hover:border-secondary"><span class="material-symbols-outlined">receipt_long</span>Receipt</button>
                        </div>
                    </form>

                    <section class="rounded-xl border border-line bg-primary-container p-5 text-white shadow-sm"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary-fixed">cleaning_services</span><h3 class="font-serif text-xl font-bold">Room Turnover Notice</h3></div><p class="mt-2 text-sm leading-6 text-blue-100">Saving a settlement completes the stay, moves the room to cleaning, records a room status log, and posts a hotel transaction entry.</p></section>
                </aside>
            </div>
        </main>
    </div>

    <div id="folioModal" class="no-print fixed inset-0 z-50 hidden items-center justify-center bg-primary/70 px-4 py-8">
        <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-line px-6 py-4"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Guest Bill Receipt Preview</p><h2 class="mt-1 font-serif text-2xl font-bold">Guest Settlement Receipt</h2></div><button id="closeFolioButton" type="button" class="rounded-lg p-2 text-slate-500 transition hover:bg-surface-low hover:text-primary" aria-label="Close receipt preview"><span class="material-symbols-outlined">close</span></button></div>
            <div id="folioReceipt" class="bg-white p-6 text-primary">
                <div class="border-b border-line pb-5 text-center"><p class="font-serif text-3xl font-bold">Le Charmé Suites</p><p class="mt-1 text-xs font-bold uppercase tracking-[0.24em] text-secondary">Subic Bay Philippines</p><p class="mt-3 text-sm text-slate-500">Official guest bill prepared by Billing</p></div>
                <div class="grid gap-4 border-b border-line py-5 sm:grid-cols-2"><div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Guest</p><p id="receiptGuest" class="mt-1 text-lg font-bold">No guest selected</p><p id="receiptEmail" class="text-sm text-slate-500">--</p></div><div class="sm:text-right"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Bill Number</p><p id="receiptFolio" class="mt-1 font-mono text-lg font-bold">--</p><p class="text-sm text-slate-500">{{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p></div></div>
                <div class="space-y-3 py-5"><div class="flex justify-between text-sm"><span>Room charges</span><span id="receiptRoomCharges" class="font-bold">PHP 0.00</span></div><div class="flex justify-between text-sm"><span>Incidentals</span><span id="receiptIncidentals" class="font-bold">PHP 0.00</span></div><div class="flex justify-between text-sm"><span>Tax</span><span id="receiptTax" class="font-bold">PHP 0.00</span></div><div class="flex justify-between text-sm"><span>Amount paid</span><span id="receiptAmountPaid" class="font-bold">PHP 0.00</span></div><div class="flex justify-between border-t border-line pt-4 text-lg"><span class="font-bold">Remaining balance</span><span id="receiptBalance" class="font-serif text-2xl font-bold">PHP 0.00</span></div><div class="flex justify-between text-sm"><span>Payment status</span><span id="receiptPaymentStatus" class="font-bold uppercase">Unpaid</span></div></div>
                <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Billing Notes</p><p id="receiptNotes" class="mt-2 text-sm leading-6 text-slate-700">No notes entered.</p></div>
            </div>
            <div class="no-print flex flex-wrap items-center justify-end gap-3 border-t border-line px-6 py-4"><button id="cancelFolioButton" type="button" class="rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-primary transition hover:border-secondary">Close</button><button id="printReceiptButton" type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-primary-container"><span class="material-symbols-outlined text-[18px]">print</span>Print Receipt</button></div>
        </div>
    </div>

    <script>
        const activeCheckIns = @json($activeCheckIns);
        const checkInSelect = document.getElementById('check_in_id');
        const amountInput = document.getElementById('amount_paid');
        const paymentStatusSelect = document.getElementById('payment_status');
        const notesInput = document.getElementById('notes');
        const openFolioButton = document.getElementById('openFolioButton');
        const folioModal = document.getElementById('folioModal');
        const currency = (value) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(value || 0));
        const selectedStay = () => activeCheckIns.find((stay) => String(stay.id) === String(checkInSelect.value));

        function renderStay(stay) {
            const data = stay || { guest:'No guest selected', email:'Choose a ledger row to view guest bill details.', room:'--', check_in:'Not selected', check_out:'Not selected', folio:'--', nights:0, room_total:0, incidentals:0, tax:0, balance:0 };
            document.getElementById('guestPreview').textContent = data.guest;
            document.getElementById('emailPreview').textContent = data.email;
            document.getElementById('roomPreview').textContent = data.room === '--' ? '--' : `Room ${data.room}`;
            document.getElementById('checkInPreview').textContent = data.check_in;
            document.getElementById('checkOutPreview').textContent = data.check_out;
            document.getElementById('folioPreview').textContent = data.folio;
            document.getElementById('nightsPreview').textContent = `${data.nights} ${Number(data.nights) === 1 ? 'night' : 'nights'}`;
            document.getElementById('roomChargesPreview').textContent = currency(data.room_total);
            document.getElementById('incidentalsPreview').textContent = currency(data.incidentals);
            document.getElementById('taxPreview').textContent = currency(data.tax);
            document.getElementById('balanceDuePreview').textContent = currency(data.balance);
            document.querySelectorAll('.folio-row').forEach((row) => {
                const selected = stay && row.dataset.stayId === String(stay.id);
                row.classList.toggle('bg-secondary-container/20', selected);
                row.classList.toggle('border-l-4', selected);
                row.classList.toggle('border-secondary', selected);
            });
        }

        function chooseStay(id) {
            checkInSelect.value = id || '';
            const stay = selectedStay();
            if (stay && Number(amountInput.value || 0) === 0) amountInput.value = Number(stay.balance).toFixed(2);
            renderStay(stay);
        }

        document.querySelectorAll('.select-folio').forEach((button) => button.addEventListener('click', () => chooseStay(button.dataset.stayId)));
        checkInSelect.addEventListener('change', () => chooseStay(checkInSelect.value));
        document.getElementById('payInFullButton').addEventListener('click', () => { const stay = selectedStay(); amountInput.value = stay ? Number(stay.balance).toFixed(2) : '0.00'; if (stay) paymentStatusSelect.value = 'paid'; });

        function showFolioMessage(message) {
            let existing = document.getElementById('folioActionMessage');
            if (!existing) {
                existing = document.createElement('p');
                existing.id = 'folioActionMessage';
                existing.className = 'mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800';
                openFolioButton.closest('.flex').after(existing);
            }
            existing.textContent = message;
        }

        function updateReceipt(stay) {
            const amountPaid = Number(amountInput.value || 0);
            const balance = Math.max(Number(stay.balance || 0) - amountPaid, 0);
            document.getElementById('receiptGuest').textContent = stay.guest;
            document.getElementById('receiptEmail').textContent = stay.email;
            document.getElementById('receiptFolio').textContent = stay.folio;
            document.getElementById('receiptRoomCharges').textContent = currency(stay.room_total);
            document.getElementById('receiptIncidentals').textContent = currency(stay.incidentals);
            document.getElementById('receiptTax').textContent = currency(stay.tax);
            document.getElementById('receiptAmountPaid').textContent = currency(amountPaid);
            document.getElementById('receiptBalance').textContent = currency(balance);
            document.getElementById('receiptPaymentStatus').textContent = paymentStatusSelect.options[paymentStatusSelect.selectedIndex]?.text || 'Unpaid';
            document.getElementById('receiptNotes').textContent = notesInput.value.trim() || 'No notes entered.';
        }

        openFolioButton.addEventListener('click', () => {
            const stay = selectedStay();
            if (!stay) return showFolioMessage('Please select an active guest bill first before printing a receipt.');
            document.getElementById('folioActionMessage')?.remove();
            updateReceipt(stay);
            folioModal.classList.remove('hidden');
            folioModal.classList.add('flex');
        });
        document.getElementById('closeFolioButton').addEventListener('click', () => { folioModal.classList.add('hidden'); folioModal.classList.remove('flex'); });
        document.getElementById('cancelFolioButton').addEventListener('click', () => { folioModal.classList.add('hidden'); folioModal.classList.remove('flex'); });
        document.getElementById('printReceiptButton').addEventListener('click', () => window.print());
        folioModal.addEventListener('click', (event) => { if (event.target === folioModal) { folioModal.classList.add('hidden'); folioModal.classList.remove('flex'); } });

        function updateBillingClock() {
            const now = new Date();
            document.getElementById('billing-date').textContent = new Intl.DateTimeFormat('en-US', { weekday:'short', month:'short', day:'2-digit', year:'numeric', timeZone:'Asia/Manila' }).format(now);
            document.getElementById('billing-clock').textContent = `${new Intl.DateTimeFormat('en-US', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true, timeZone:'Asia/Manila' }).format(now)} PHT`;
        }

        if (checkInSelect.value) renderStay(selectedStay()); else if (activeCheckIns.length === 1) chooseStay(activeCheckIns[0].id); else renderStay(null);
        updateBillingClock();
        setInterval(updateBillingClock, 1000);
    </script>
</body>
</html>
