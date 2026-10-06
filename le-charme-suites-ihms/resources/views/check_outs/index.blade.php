@php
    $user = auth()->user();
    $role = $user?->role ?? 'staff';
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
    $paymentLabels = [
        'paid' => 'Paid',
        'partial' => 'Partial',
        'unpaid' => 'Unpaid',
    ];
    $paymentClasses = [
        'paid' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'partial' => 'bg-secondary-fixed text-on-secondary-fixed-variant border-secondary/30',
        'unpaid' => 'bg-error-container text-on-error-container border-error/20',
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-Outs & Departures - Le Charme Suites IHMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Noto+Serif:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
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
                        'secondary-fixed-dim': '#e6c185',
                        'on-secondary-fixed': '#271900',
                        'on-secondary-fixed-variant': '#5b4313',
                        surface: '#fcf9f3',
                        background: '#fcf9f3',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f6f3ed',
                        'surface-container': '#f0eee8',
                        'surface-container-high': '#ebe8e2',
                        'surface-variant': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        'on-surface': '#1c1c18',
                        'on-surface-variant': '#44474d',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6',
                        'on-error-container': '#93000a',
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
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background text-on-surface font-sans antialiased">
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-white/10 bg-primary px-4 py-6 text-white shadow-xl lg:flex">
        <div class="flex items-center gap-3 border-b border-white/10 px-2 pb-6">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg border border-secondary/30 bg-primary-container text-secondary-fixed shadow-inner">
                <span class="material-symbols-outlined text-2xl">concierge</span>
            </div>
            <div>
                <h1 class="font-serif text-xl font-semibold leading-tight">Le Charme Suites</h1>
                <p class="text-xs text-blue-100/70">Subic Bay IHMS</p>
            </div>
        </div>

        <div class="py-4">
            @if (in_array($role, ['admin', 'receptionist'], true))
                <a href="{{ route('check-ins.create') }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-secondary-fixed px-3 py-2.5 text-sm font-bold text-on-secondary-fixed shadow-sm transition hover:bg-secondary-container">
                    <span class="material-symbols-outlined text-lg">flash_on</span>
                    Express Check-In
                </a>
            @else
                <div class="rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-center text-xs font-semibold text-blue-100/70">
                    Cashier Transaction Access
                </div>
            @endif
        </div>

        <div class="flex-1 overflow-y-auto pr-1">
            @include('partials.front-desk-nav', ['active' => 'transactions'])
        </div>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 pt-4">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-blue-100/70 transition hover:bg-primary-container hover:text-white">
                <span class="material-symbols-outlined text-lg">logout</span>
                Sign Out
            </button>
        </form>
    </aside>

    <header class="fixed left-0 right-0 top-0 z-30 flex h-16 items-center justify-between border-b border-surface-variant bg-surface px-4 shadow-sm lg:left-64 lg:px-8">
        <div class="flex min-w-0 items-center gap-5">
            <div class="hidden font-serif text-xl font-semibold tracking-wide text-primary lg:block">Le Charme Suites IHMS</div>
            <div class="hidden h-5 w-px bg-outline-variant/50 lg:block"></div>
            <div class="relative hidden w-full max-w-md md:block">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-outline">search</span>
                <input class="w-full rounded-lg border border-surface-variant bg-surface-container-low py-1.5 pl-9 pr-4 text-sm text-on-surface outline-none focus:border-secondary focus:ring-1 focus:ring-secondary/30" placeholder="Search departing guest, room number, or folio ref..." aria-label="Search departing guest, room number, or folio reference">
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="hidden text-right sm:block">
                <span class="block text-xs font-bold uppercase tracking-wider text-secondary">Role: {{ ucfirst($role) }}</span>
                <span class="text-xs text-on-surface-variant">{{ now(config('app.timezone'))->format('M d, Y • h:i A') }}</span>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-bold text-secondary-fixed ring-2 ring-secondary/40">
                {{ strtoupper(substr($user?->name ?? 'S', 0, 1)) }}
            </div>
        </div>
    </header>

    <main class="px-4 pb-8 pt-24 lg:ml-64 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-6 flex flex-col justify-between gap-4 border-b border-surface-variant pb-5 md:flex-row md:items-end">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="rounded border border-secondary/30 bg-secondary/15 px-2 py-0.5 text-xs font-bold text-secondary">IPT101 Standard Operations</span>
                        <span class="text-xs font-semibold text-on-surface-variant">Terminal {{ str_pad((string) ($user?->id ?? 1), 2, '0', STR_PAD_LEFT) }} - Subic Bay Hub</span>
                    </div>
                    <h1 class="font-serif text-3xl font-bold text-primary">Daily Check-Outs &amp; Departures Console</h1>
                    <p class="mt-1 text-sm text-on-surface-variant">Streamline guest settlement, key return validation, and housekeeping turnover dispatch.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('check-outs.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-surface-variant bg-surface px-3.5 py-2 text-xs font-semibold text-primary shadow-sm transition hover:border-secondary hover:-translate-y-0.5"><span class="material-symbols-outlined text-[18px] text-secondary">speed</span>Process Express Departure</a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-primary-container hover:-translate-y-0.5"><span class="material-symbols-outlined text-[18px] text-secondary-fixed">description</span>Generate Shift Summary</button>
                </div>
            </div>

            <div class="mb-6 flex flex-wrap items-center gap-1.5 rounded-lg border border-surface-variant bg-surface-container-low p-2 text-xs font-bold text-on-surface-variant">
                    <span class="text-primary">1. Search</span>
                    <span class="material-symbols-outlined text-xs text-outline">chevron_right</span>
                    <span class="text-primary">2. Verify Guest</span>
                    <span class="material-symbols-outlined text-xs text-outline">chevron_right</span>
                    <span class="rounded bg-secondary-fixed/50 px-1.5 py-0.5 text-secondary">3. Settle</span>
                    <span class="material-symbols-outlined text-xs text-outline">chevron_right</span>
                    <span class="text-outline">4. Turnover</span>
                </div>

            <section class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative overflow-hidden rounded-xl border border-surface-variant bg-surface-container-lowest p-4 shadow-sm"><div class="absolute inset-x-0 top-0 h-1 bg-secondary"></div>
                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Today's Departures</span>
                    <p class="mt-2 text-3xl font-bold text-primary">{{ $departuresToday }}</p>
                    <p class="mt-2 border-t border-surface-container pt-2 text-xs text-on-surface-variant">Scheduled to leave today</p>
                </div>
                <div class="rounded-xl border border-surface-variant bg-surface-container-lowest p-4 shadow-sm">
                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Pending Departures</span>
                    <p class="mt-2 text-3xl font-bold text-primary">{{ $pendingDepartures }}</p>
                    <p class="mt-2 border-t border-surface-container pt-2 text-xs text-on-surface-variant">Active guests awaiting checkout</p>
                </div>
                <div class="rounded-xl border border-surface-variant bg-surface-container-lowest p-4 shadow-sm">
                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Settled Today</span>
                    <p class="mt-2 text-3xl font-bold text-primary">{{ $settledToday }}</p>
                    <p class="mt-2 border-t border-surface-container pt-2 text-xs text-on-surface-variant">Completed departure records</p>
                </div>
                <div class="rounded-xl border border-surface-variant bg-surface-container-lowest p-4 shadow-sm">
                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Collected Today</span>
                    <p class="mt-2 text-3xl font-bold text-primary">PHP {{ number_format($totalCollectedToday, 2) }}</p>
                    <p class="mt-2 border-t border-surface-container pt-2 text-xs text-on-surface-variant">Recorded payment total</p>
                </div>
            </section>

            <form method="GET" action="{{ route('check-outs.index') }}" class="mb-6 flex flex-col items-center gap-3 rounded-xl border border-surface-variant bg-surface-container-lowest p-3 shadow-sm md:flex-row">
                <div class="relative w-full flex-1">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-primary-container">search</span>
                    <input name="search" value="{{ $search }}" class="w-full rounded-lg border-outline-variant bg-surface py-2.5 pl-11 pr-4 text-sm focus:border-secondary focus:ring-secondary" placeholder="Search guest, room, phone, or check-out reference">
                </div>
                <select name="payment_status" class="w-full rounded-lg border-outline-variant bg-surface py-2.5 text-sm font-semibold text-primary focus:border-secondary focus:ring-secondary md:w-44">
                    <option value="">All Payments</option>
                    @foreach ($paymentLabels as $value => $label)
                        <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container md:w-auto">
                    <span class="material-symbols-outlined text-lg">travel_explore</span>
                    Locate
                </button>
            </form>

            <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-12">
                <section class="space-y-6 lg:col-span-5">
                    <div class="relative overflow-hidden rounded-xl border border-surface-variant bg-surface-container-lowest p-5 shadow-sm">
                        <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-secondary to-primary-container"></div>
                        @if ($activeCheckIn)
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-secondary/40 bg-surface-container-high font-serif text-lg font-bold text-primary">
                                        {{ strtoupper(substr($guest?->first_name ?? 'G', 0, 1) . substr($guest?->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-primary">{{ $guest?->full_name ?? 'In-House Guest' }}</h2>
                                        <p class="text-xs text-on-surface-variant">Guest ID: GST-{{ str_pad($guest?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                                <span class="rounded bg-primary/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-primary">In-House</span>
                            </div>

                            <div class="mb-4 rounded-lg border border-surface-variant bg-surface p-4">
                                <div class="flex items-center justify-between border-b border-surface-variant pb-3">
                                    <div>
                                        <span class="text-xs font-bold uppercase text-secondary">Assigned Unit</span>
                                        <p class="font-serif text-2xl font-bold text-primary">Room {{ $room?->room_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs text-on-surface-variant">Room Category</span>
                                        <p class="font-semibold text-primary">{{ $room?->room_type }}</p>
                                        <span class="text-xs text-outline">Capacity {{ $room?->capacity }} guest(s)</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 pt-3 text-xs">
                                    <div class="rounded bg-surface-container-low p-2.5">
                                        <span class="mb-1 block text-on-surface-variant">Check-in Recorded</span>
                                        <span class="flex items-center gap-1 font-bold text-primary">
                                            <span class="material-symbols-outlined text-xs text-secondary">login</span>
                                            {{ $activeCheckIn->checked_in_at?->format('M d, Y h:i A') }}
                                        </span>
                                    </div>
                                    <div class="rounded bg-surface-container-low p-2.5">
                                        <span class="mb-1 block text-on-surface-variant">Scheduled Departure</span>
                                        <span class="flex items-center gap-1 font-bold text-primary">
                                            <span class="material-symbols-outlined text-xs text-secondary">logout</span>
                                            {{ $reservation?->check_out_date?->format('M d, Y') ?? 'Not set' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Room Inspection Clearance</h3>
                                <div class="flex items-start gap-3 rounded-lg border border-surface-variant bg-surface-container-low p-3.5">
                                    <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-primary text-white">
                                        <span class="material-symbols-outlined text-sm">check</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-primary">Pending final verification</p>
                                        <p class="mt-0.5 text-xs text-on-surface-variant">Confirm room condition, key return, and remaining balance before release.</p>
                                    </div>
                                </div>
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-dashed border-outline-variant p-3 transition hover:bg-surface">
                                    <input checked class="h-4 w-4 rounded border-outline text-secondary focus:ring-secondary" type="checkbox">
                                    <span class="text-sm font-semibold text-primary">Room key/card returned and ready for encoder clearing.</span>
                                </label>
                            </div>
                        @else
                            <div class="py-10 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-surface-container text-secondary">
                                    <span class="material-symbols-outlined text-3xl">person_search</span>
                                </div>
                                <h2 class="mt-4 font-serif text-xl font-bold text-primary">No Active Check-In Found</h2>
                                <p class="mt-2 text-sm text-on-surface-variant">When a guest is checked in, their folio will appear here for settlement.</p>
                                @if (in_array($role, ['admin', 'receptionist'], true))
                                    <a href="{{ route('check-ins.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white">
                                        <span class="material-symbols-outlined text-lg">login</span>
                                        Start Check-In
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex items-start gap-3.5 rounded-xl border border-secondary/30 bg-surface-container-high/60 p-4">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-secondary/15 text-secondary">
                            <span class="material-symbols-outlined">autorenew</span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-primary">Housekeeping Turnover Pipeline</h4>
                            <p class="mt-0.5 text-xs text-on-surface-variant">Confirming check-out will mark the room as cleaning and add it to the housekeeping queue.</p>
                        </div>
                    </div>
                </section>

                <section class="space-y-6 lg:col-span-7">
                    <div class="rounded-xl border border-surface-variant bg-surface-container-lowest p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-surface-variant pb-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-primary">Master Folio {{ $activeCheckIn ? '#FOL-' . now()->format('Y') . '-' . str_pad($activeCheckIn->id, 5, '0', STR_PAD_LEFT) : 'Preview' }}</h3>
                                    <span class="rounded bg-outline-variant/30 px-2 py-0.5 font-mono text-xs text-on-surface-variant">POS Term 02</span>
                                </div>
                                <p class="text-xs text-on-surface-variant">Final itemized charges and settlement account</p>
                            </div>
                            <a href="{{ route('check-outs.create') }}" class="flex items-center gap-1 text-xs font-bold text-secondary hover:underline">
                                <span class="material-symbols-outlined text-sm">add_circle</span>
                                New Check-Out
                            </a>
                        </div>

                        <div class="my-4 divide-y divide-surface-variant/80">
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div>
                                    <span class="block font-semibold text-primary">Total Room Charges ({{ $nights }} {{ $nights === 1 ? 'Night' : 'Nights' }})</span>
                                    <span class="text-xs text-on-surface-variant">Room {{ $room?->room_number ?? 'TBA' }} {{ $reservation?->check_in_date ? $reservation->check_in_date->format('M d') : '' }} - {{ $reservation?->check_out_date ? $reservation->check_out_date->format('M d') : '' }}</span>
                                </div>
                                <span class="font-mono font-bold text-primary">PHP {{ number_format($roomCharges, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div>
                                    <span class="block font-semibold text-primary">Incidental Estimate</span>
                                    <span class="text-xs text-on-surface-variant">Room service, minibar, and service adjustments</span>
                                </div>
                                <span class="font-mono font-bold text-primary">PHP {{ number_format($incidentals, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div>
                                    <span class="block font-semibold text-primary">Taxes & Surcharge</span>
                                    <span class="text-xs text-on-surface-variant">VAT and local tourism/service fees</span>
                                </div>
                                <span class="font-mono font-bold text-primary">PHP {{ number_format($taxes, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 rounded bg-surface-container-low/70 px-2 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-secondary">check_circle</span>
                                    <div>
                                        <span class="block font-semibold text-on-surface">Less: Estimated Advance Deposit</span>
                                        <span class="text-xs text-on-surface-variant">Recorded upon check-in authorization</span>
                                    </div>
                                </div>
                                <span class="font-mono font-bold text-secondary">-PHP {{ number_format($deposit, 2) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-secondary/20 bg-surface-container-low p-4">
                            <div class="mb-4 flex items-baseline justify-between gap-4">
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-secondary">Net Outstanding Balance</span>
                                    <span class="text-xs text-on-surface-variant">Payable at cashier counter</span>
                                </div>
                                <div class="text-right">
                                    <span class="block font-serif text-3xl font-extrabold text-primary">PHP {{ number_format($balance, 2) }}</span>
                                    <span class="text-[11px] font-semibold text-on-surface-variant">Philippine Peso</span>
                                </div>
                            </div>

                            <div class="border-t border-surface-variant pt-3">
                                <span class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Payment Method</span>
                                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-secondary bg-white p-2.5 text-sm font-bold text-primary shadow-sm">
                                        <input checked class="text-secondary focus:ring-secondary" name="payment_mode" type="radio">
                                        <span class="material-symbols-outlined text-base text-secondary">credit_card</span>
                                        POS
                                    </label>
                                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-surface-variant bg-surface p-2.5 text-sm font-semibold text-on-surface-variant hover:bg-surface-container">
                                        <input class="text-secondary focus:ring-secondary" name="payment_mode" type="radio">
                                        <span class="material-symbols-outlined text-base">payments</span>
                                        Cash
                                    </label>
                                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-surface-variant bg-surface p-2.5 text-sm font-semibold text-on-surface-variant hover:bg-surface-container">
                                        <input class="text-secondary focus:ring-secondary" name="payment_mode" type="radio">
                                        <span class="material-symbols-outlined text-base">account_balance</span>
                                        Direct Bill
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col items-center gap-3 sm:flex-row">
                            <a href="{{ route('check-outs.create') }}" class="flex w-full flex-1 items-center justify-center gap-2 rounded-lg border-t border-secondary/40 bg-primary px-6 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-primary-container">
                                <span class="material-symbols-outlined text-xl text-secondary-fixed">check_circle</span>
                                Confirm Check-Out & Release Room
                            </a>
                            <button type="button" onclick="window.print()" class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-outline-variant px-4 py-3.5 text-sm font-semibold text-on-surface transition hover:bg-surface-container sm:w-auto">
                                <span class="material-symbols-outlined text-lg">receipt</span>
                                Folio Preview
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <section id="transaction-history" class="overflow-hidden rounded-xl border-2 border-secondary/40 bg-surface-container-lowest shadow-xl">
                <div class="flex flex-col justify-between gap-3 border-b border-surface-variant p-5 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary-fixed text-on-secondary-fixed shadow-sm">
                            <span class="material-symbols-outlined text-2xl">task_alt</span>
                        </div>
                        <div>
                            <h3 class="font-serif text-xl font-bold text-primary">Transaction Ledger</h3>
                            <p class="text-sm text-on-surface-variant">Completed check-outs and recorded folio payments.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-sm font-bold text-secondary">
                        <span class="h-2 w-2 rounded-full bg-secondary"></span>
                        {{ $checkOuts->total() }} Recorded
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="border-b border-surface-variant bg-surface-container text-xs font-bold uppercase tracking-wider text-primary">
                            <tr>
                                <th class="px-4 py-3">Reference</th>
                                <th class="px-4 py-3">Guest</th>
                                <th class="px-4 py-3">Room</th>
                                <th class="px-4 py-3">Checked Out</th>
                                <th class="px-4 py-3">Payment</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-variant">
                            @forelse ($checkOuts as $checkOut)
                                <tr class="transition hover:bg-surface-container-low">
                                    <td class="px-4 py-3.5 font-mono font-bold text-primary">CO-{{ now()->format('Y') }}-{{ str_pad($checkOut->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-bold text-on-surface">{{ $checkOut->guest?->full_name ?? 'Guest Missing' }}</p>
                                        <p class="text-xs text-on-surface-variant">GST-{{ str_pad($checkOut->guest?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-bold text-primary">Room {{ $checkOut->room?->room_number ?? 'TBA' }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $checkOut->room?->room_type ?? 'No room type' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">{{ $checkOut->checked_out_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $paymentClasses[$checkOut->payment_status] ?? 'bg-surface-container text-primary border-outline-variant' }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ strtoupper($paymentLabels[$checkOut->payment_status] ?? $checkOut->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono font-bold text-primary">PHP {{ number_format($checkOut->amount_paid, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center font-semibold text-on-surface-variant">No check-outs recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-surface-variant bg-surface-container-low px-4 py-3">
                    {{ $checkOuts->links() }}
                </div>
            </section>
        </div>
    </main>
</body>
</html>
