@php
    $user = auth()->user();
    $guest = $reservation->guest;
    $room = $reservation->room;
    $nights = $reservation->check_in_date && $reservation->check_out_date
        ? max(1, $reservation->check_in_date->diffInDays($reservation->check_out_date))
        : 1;
    $roomTotal = ($room?->rate ?? 0) * $nights;
    $taxes = round($roomTotal * 0.12, 2);
    $totalStay = $roomTotal + $taxes;
    $deposit = round($totalStay * 0.5, 2);
    $balance = max(0, $totalStay - $deposit);
    $statusLabels = [
        'reserved' => 'Reserved',
        'checked_in' => 'Checked In',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    $statusClasses = [
        'reserved' => 'bg-secondary-container/50 border-secondary text-on-secondary-container',
        'checked_in' => 'bg-primary/10 border-primary/20 text-primary',
        'completed' => 'bg-emerald-50 border-emerald-300 text-emerald-800',
        'cancelled' => 'bg-error-container border-error/30 text-error',
    ];
    $canCheckIn = $reservation->status === 'reserved';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation #LC-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }} - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        'on-primary-container': '#7f93ae',
                        secondary: '#755a28',
                        'secondary-container': '#fdd79a',
                        'secondary-fixed': '#ffdeaa',
                        'on-secondary-container': '#785c2a',
                        surface: '#fcf9f3',
                        background: '#fcf9f3',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f6f3ed',
                        'surface-container': '#f0eee8',
                        'surface-variant': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        'on-surface': '#1c1c18',
                        'on-surface-variant': '#44474d',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6',
                    },
                    fontFamily: {
                        body: ['Manrope', 'sans-serif'],
                        headline: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-body text-on-surface antialiased">
    @include('partials.front-desk-sidebar', ['active' => 'reservations'])

    <div class="lg:ml-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-variant bg-surface px-4 shadow-sm lg:px-7">
            <div class="flex items-center gap-6">
                <span class="hidden font-headline text-xl font-semibold tracking-wide text-primary md:block">Le Charme Suites IHMP</span>
                <div class="relative hidden w-80 lg:block">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-lg text-on-surface-variant">search</span>
                    <input class="w-full rounded-lg border-surface-variant bg-white py-2 pl-9 pr-4 text-sm focus:border-secondary focus:ring-secondary" placeholder="Search guests, rooms, or booking numbers...">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden text-right sm:block">
                    <p class="text-xs font-bold uppercase tracking-wider text-primary">Role: {{ ucwords(str_replace('_', ' ', $user?->role ?? 'Staff')) }}</p>
                    <p id="reservation-clock" class="text-[11px] text-on-surface-variant" data-timezone="{{ config('app.timezone') }}">{{ now(config('app.timezone'))->format('M d, Y - h:i:s A') }}</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-secondary-container text-sm font-bold text-secondary shadow-sm ring-1 ring-secondary/30">
                    {{ strtoupper(substr($user?->name ?? 'F', 0, 1)) }}
                </div>
            </div>
        </header>

        <main class="mx-auto flex max-w-[1600px] flex-col gap-6 p-4 lg:p-8">
            <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant">
                <a href="{{ route('dashboard') }}" class="transition hover:text-primary">IHMP</a>
                <span class="material-symbols-outlined text-xs text-outline">chevron_right</span>
                <a href="{{ route('reservations.index') }}" class="transition hover:text-primary">Reservations</a>
                <span class="material-symbols-outlined text-xs text-outline">chevron_right</span>
                <span class="text-primary">Details #LC-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</span>
            </nav>

            <section class="flex flex-col justify-between gap-4 border-b border-surface-variant/70 pb-4 lg:flex-row lg:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="font-headline text-3xl font-semibold tracking-tight text-primary">Reservation Details: #LC-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold tracking-wide {{ $statusClasses[$reservation->status] ?? 'bg-surface-container border-outline-variant text-primary' }}">
                            <span class="h-2 w-2 rounded-full bg-current"></span>
                            {{ strtoupper($statusLabels[$reservation->status] ?? $reservation->status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-on-surface-variant">Created: {{ $reservation->created_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }} by {{ $user?->name ?? 'Front Desk Staff' }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <button onclick="window.print()" class="flex items-center gap-2 rounded-lg border border-surface-variant bg-white px-3.5 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-surface-container">
                        <span class="material-symbols-outlined text-base text-secondary">print</span>
                        Print Registration Card
                    </button>
                    <a href="{{ route('reservations.edit', $reservation) }}" class="flex items-center gap-1.5 rounded-lg border border-error/40 bg-white px-3.5 py-2.5 text-sm font-bold text-error shadow-sm transition hover:bg-error-container/40">
                        <span class="material-symbols-outlined text-base">cancel</span>
                        Edit / Cancel
                    </a>
                    <a href="{{ route('reservations.edit', $reservation) }}" class="flex items-center gap-1.5 rounded-lg border border-surface-variant bg-white px-3.5 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-surface-container">
                        <span class="material-symbols-outlined text-base">edit</span>
                        Edit Reservation
                    </a>
                    @if ($canCheckIn)
                        <a href="{{ route('check-ins.create', ['reservation_id' => $reservation->id]) }}" class="group flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-primary-container">
                            Proceed to Check-In
                            <span class="material-symbols-outlined text-base text-secondary-fixed transition group-hover:translate-x-0.5">arrow_forward</span>
                        </a>
                    @endif
                </div>
            </section>

            <div class="grid grid-cols-12 gap-5">
                <section class="relative col-span-12 flex flex-col justify-between overflow-hidden rounded-xl border border-surface-variant bg-white p-6 shadow-sm lg:col-span-4">
                    <div class="absolute left-0 right-0 top-0 h-1 bg-primary"></div>
                    <div>
                        <div class="mb-4 flex items-center justify-between border-b border-surface-variant pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">receipt_long</span>
                                <h3 class="text-lg font-bold text-primary">Reservation Information</h3>
                            </div>
                            <span class="rounded bg-surface-container px-2 py-0.5 text-xs font-bold text-on-surface-variant">IPT-MOD-01</span>
                        </div>
                        <dl class="space-y-3.5">
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Reservation ID</dt>
                                <dd class="mt-0.5 text-base font-bold text-primary">#LC-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Creation Timestamp</dt>
                                <dd class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold">
                                    <span class="material-symbols-outlined text-sm text-outline">schedule</span>
                                    {{ $reservation->created_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Booking Source</dt>
                                <dd class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold">
                                    <span class="material-symbols-outlined text-sm text-secondary">storefront</span>
                                    Direct Front Desk Intake
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Current Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded border border-secondary/40 bg-secondary-fixed/30 px-2.5 py-1 text-xs font-bold text-secondary">
                                        <span class="material-symbols-outlined text-xs">verified</span>
                                        {{ $statusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div class="mt-6 flex items-center justify-between border-t border-surface-variant pt-3 text-xs text-on-surface-variant">
                        <span>Guaranteed Channel</span>
                        <span class="font-bold text-primary">Reservation Details</span>
                    </div>
                </section>

                <section class="relative col-span-12 flex flex-col justify-between overflow-hidden rounded-xl border border-surface-variant bg-white p-6 shadow-sm lg:col-span-8">
                    <div class="absolute left-0 right-0 top-0 h-1 bg-secondary"></div>
                    <div>
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-surface-variant pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">person_pin</span>
                                <h3 class="text-lg font-bold text-primary">Guest Information</h3>
                            </div>
                            <span class="flex items-center gap-1 rounded-full border border-secondary bg-secondary-container/60 px-3 py-1 text-xs font-bold text-secondary">
                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                Registered Guest
                            </span>
                        </div>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Guest ID</span>
                                <span class="font-bold text-primary">GST-{{ str_pad($guest?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Full Name</span>
                                <span class="font-headline text-xl font-semibold text-primary">{{ $guest?->full_name ?? 'Guest profile missing' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Contact Number</span>
                                <span class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold">
                                    <span class="material-symbols-outlined text-sm text-primary">call</span>
                                    {{ $guest?->phone ?: 'Not provided' }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Email Address</span>
                                <span class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold">
                                    <span class="material-symbols-outlined text-sm text-primary">mail</span>
                                    {{ $guest?->email ?: 'Not provided' }}
                                </span>
                            </div>
                            <div class="md:col-span-2">
                                <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Residential Address</span>
                                <span class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold">
                                    <span class="material-symbols-outlined text-sm text-outline">location_on</span>
                                    {{ $guest?->address ?: 'No address on file' }}
                                </span>
                            </div>
                            <div class="rounded-lg border border-surface-variant bg-surface-container-low p-3 md:col-span-2">
                                <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Identification Documents On File</span>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="rounded border border-outline-variant/30 bg-white px-2 py-0.5 text-xs font-semibold">{{ $guest?->identification_type ?: 'ID type not provided' }}</span>
                                    <span class="flex items-center gap-1 rounded border border-secondary/40 bg-white px-2 py-0.5 text-xs font-bold text-primary">
                                        <span class="material-symbols-outlined text-xs text-secondary">verified_user</span>
                                        {{ $guest?->identification_number ?: 'ID number not provided' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-surface-variant pt-3 text-xs text-on-surface-variant">
                        <span>Special Requests</span>
                        <span class="font-bold text-primary">{{ $reservation->notes ?: 'No special requests recorded' }}</span>
                    </div>
                </section>

                <section class="relative col-span-12 flex flex-col justify-between overflow-hidden rounded-xl border border-surface-variant bg-white p-6 shadow-sm lg:col-span-7">
                    <div class="absolute left-0 right-0 top-0 h-1 bg-primary-container"></div>
                    <div>
                        <div class="mb-4 flex items-center justify-between border-b border-surface-variant pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">hotel</span>
                                <h3 class="text-lg font-bold text-primary">Stay & Room Information</h3>
                            </div>
                            <span class="rounded bg-secondary-fixed/40 px-2 py-0.5 text-xs font-bold text-secondary">Guaranteed Stay</span>
                        </div>
                        <div class="mb-5 grid grid-cols-2 gap-3 rounded-lg border border-surface-variant bg-surface-container-low p-3 sm:grid-cols-4">
                            <div>
                                <span class="block text-xs font-bold uppercase text-on-surface-variant">Assigned Room</span>
                                <span class="text-lg font-bold text-primary">Room {{ $room?->room_number ?? 'TBA' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase text-on-surface-variant">Room Type</span>
                                <span class="text-sm font-bold">{{ $room?->room_type ?? 'Not assigned' }}</span>
                                <span class="block text-[11px] text-on-surface-variant">Capacity {{ $room?->capacity ?? 0 }} guest(s)</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase text-on-surface-variant">Duration</span>
                                <span class="text-lg font-bold text-primary">{{ $nights }} {{ $nights === 1 ? 'Night' : 'Nights' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase text-on-surface-variant">Nightly Rate</span>
                                <span class="font-bold text-primary">PHP {{ number_format($room?->rate ?? 0, 2) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 border-b border-surface-variant pb-4 md:grid-cols-2">
                            <div class="rounded-lg border border-surface-variant bg-white p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase text-on-surface-variant">Check-In Schedule</span>
                                    <span class="material-symbols-outlined text-sm text-secondary">login</span>
                                </div>
                                <p class="mt-1 font-bold text-primary">{{ $reservation->check_in_date?->format('M d, Y') }}</p>
                                <p class="text-xs text-on-surface-variant">Standard arrival window</p>
                            </div>
                            <div class="rounded-lg border border-surface-variant bg-white p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase text-on-surface-variant">Check-Out Schedule</span>
                                    <span class="material-symbols-outlined text-sm text-outline">logout</span>
                                </div>
                                <p class="mt-1 font-bold text-primary">{{ $reservation->check_out_date?->format('M d, Y') }}</p>
                                <p class="text-xs text-on-surface-variant">Standard departure window</p>
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Guest Bill & Financial Summary</span>
                            <div class="flex justify-between text-sm">
                                <span>Room Accommodations ({{ $nights }} {{ $nights === 1 ? 'Night' : 'Nights' }} @ PHP {{ number_format($room?->rate ?? 0, 2) }})</span>
                                <span class="font-semibold">PHP {{ number_format($roomTotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-on-surface-variant">
                                <span>Estimated VAT & hospitality surcharge</span>
                                <span class="font-semibold">PHP {{ number_format($taxes, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-surface-variant pt-2 font-bold text-primary">
                                <span>Total Stay Charge</span>
                                <span>PHP {{ number_format($totalStay, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded bg-surface-container-low px-3 py-2 text-sm">
                                <span class="flex items-center gap-1.5 font-semibold">
                                    <span class="material-symbols-outlined text-sm text-secondary">credit_card</span>
                                    Estimated Deposit / Guarantee
                                </span>
                                <span class="font-bold text-primary">- PHP {{ number_format($deposit, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 text-lg font-bold text-secondary">
                                <span>Remaining Guest Bill Balance</span>
                                <span>PHP {{ number_format($balance, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 flex items-center justify-between border-t border-surface-variant pt-3 text-xs text-on-surface-variant">
                        <span>Billing Method</span>
                        <span class="font-bold text-primary">Front Desk payment / card guarantee</span>
                    </div>
                </section>

                <section class="relative col-span-12 flex flex-col justify-between overflow-hidden rounded-xl border border-surface-variant bg-white p-6 shadow-sm lg:col-span-5">
                    <div class="absolute left-0 right-0 top-0 h-1 bg-primary"></div>
                    <div>
                        <div class="mb-4 flex items-center justify-between border-b border-surface-variant pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">schema</span>
                                <h3 class="text-lg font-bold text-primary">Lifecycle Records</h3>
                            </div>
                            <span class="rounded bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">Integration Pipeline</span>
                        </div>
                        <div class="space-y-4">
                            <div class="rounded-lg border border-secondary/30 bg-secondary-container/10 p-3.5">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                                        <span class="material-symbols-outlined text-base text-secondary">sensor_door</span>
                                        Check-In Status
                                    </span>
                                    <span class="rounded-full bg-secondary-fixed px-2 py-0.5 text-xs font-bold text-secondary">
                                        {{ $reservation->checkIn ? ucfirst($reservation->checkIn->status) : ($canCheckIn ? 'Ready' : 'Pending') }}
                                    </span>
                                </div>
                                <p class="mt-2 flex items-center justify-between text-sm font-bold text-primary">
                                    <span>{{ $reservation->checkIn ? 'Checked in at ' . $reservation->checkIn->checked_in_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') : 'Pending check-in' }}</span>
                                    <span class="material-symbols-outlined text-lg text-secondary">pending</span>
                                </p>
                            </div>

                            <div class="rounded-lg border border-surface-variant bg-surface-container-low p-3">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                                        <span class="material-symbols-outlined text-base text-outline">departure_board</span>
                                        Check-Out Status
                                    </span>
                                    <span class="text-xs font-semibold text-on-surface-variant">Scheduled</span>
                                </div>
                                <p class="mt-1 text-sm font-semibold">Scheduled {{ $reservation->check_out_date?->format('M d, Y') }}</p>
                            </div>

                            <div class="rounded-lg border border-surface-variant bg-surface-container-low p-3">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                                        <span class="material-symbols-outlined text-base text-primary">grid_view</span>
                                        Room Inventory Status
                                    </span>
                                    <span class="text-xs font-bold text-primary">{{ $room ? 'Floor ' . substr($room->room_number, 0, 1) : 'Unassigned' }}</span>
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-primary"></span>
                                    <p class="text-sm font-bold text-primary">{{ $room ? 'Room is marked ' . ucfirst($room->status) : 'No room assigned yet' }}</p>
                                </div>
                            </div>

                            <div class="rounded-lg border border-surface-variant bg-surface-container-low p-3">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                                        <span class="material-symbols-outlined text-base text-secondary">account_balance_wallet</span>
                                        Transaction History
                                    </span>
                                    <span class="text-xs font-bold text-secondary">{{ $transactions->count() }} record(s)</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    @forelse ($transactions->take(2) as $transaction)
                                        <p class="flex items-center justify-between text-sm font-semibold">
                                            <span>{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</span>
                                            <span class="rounded border border-outline-variant bg-white px-2 py-0.5 font-mono text-xs text-primary">TRX-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </p>
                                    @empty
                                        <p class="text-sm font-semibold text-on-surface-variant">No transaction records yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-surface-variant pt-3 text-xs text-on-surface-variant">
                        <span>IPT101 Lifecycle Protocol</span>
                        <span class="font-bold text-primary">{{ $reservation->status === 'reserved' ? 'Phase 2: Active Pre-Arrival' : 'Active Record' }}</span>
                    </div>
                </section>
            </div>

            <section class="mt-2 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-surface-variant bg-surface-container-low p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary/20 text-secondary">
                        <span class="material-symbols-outlined text-base">room_service</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-primary">Concierge & Front Desk Amenities Note</h4>
                        <p class="text-sm text-on-surface-variant">{{ $reservation->notes ?: 'No amenities or service notes have been added for this reservation.' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('reservations.edit', $reservation) }}" class="rounded-lg border border-surface-variant bg-white px-3 py-1.5 text-sm font-bold text-primary transition hover:bg-surface-container">Add Service Request</a>
                    @if ($canCheckIn)
                        <a href="{{ route('check-ins.create', ['reservation_id' => $reservation->id]) }}" class="rounded-lg bg-secondary px-3 py-1.5 text-sm font-bold text-white shadow-sm transition hover:brightness-110">Assign RFID Key Card</a>
                    @endif
                </div>
            </section>

            <footer class="mt-4 flex flex-col items-center justify-between gap-2 border-t border-surface-variant pt-4 text-xs text-on-surface-variant sm:flex-row">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-outline">fingerprint</span>
                    <span>Last validated by {{ $user?->name ?? 'Front Desk Staff' }} - {{ now(config('app.timezone'))->format('M d, Y h:i A') }}</span>
                </div>
                <div class="flex items-center gap-4 text-[11px]">
                    <span>Record Hash: RES-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}-IPT101</span>
                    <span>•</span>
                    <span class="font-bold text-secondary">Le Charme Suites IHMP</span>
                </div>
            </footer>
        </main>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const clock = document.getElementById('reservation-clock');

            if (!clock) {
                return;
            }

            const formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: clock.dataset.timezone || 'Asia/Manila',
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });

            const refreshClock = () => {
                clock.textContent = formatter.format(new Date()).replace(',', '').replace(',', ' -').toUpperCase();
            };

            refreshClock();
            setInterval(refreshClock, 1000);
        });
    </script>
</body>
</html>
