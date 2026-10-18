@php
    $currentUser = auth()->user();
    $reservationPreview = $reservations->map(function ($reservation) {
        $guest = $reservation->guest;
        $room = $reservation->room;
        $checkInDate = $reservation->check_in_date;
        $checkOutDate = $reservation->check_out_date;
        $nights = max(1, $checkInDate && $checkOutDate ? $checkInDate->diffInDays($checkOutDate) : 1);
        $roomRate = (float) ($room?->rate ?? 0);
        $roomTotal = $roomRate * $nights;
        $deposit = $roomTotal > 0 ? max(1000, round($roomTotal * 0.2, 2)) : 0;

        return [
            'id' => $reservation->id,
            'guest' => $guest?->full_name ?: 'Guest profile unavailable',
            'email' => $guest?->email ?: 'No email on file',
            'phone' => $guest?->phone ?: 'No phone on file',
            'room' => $room?->room_number ?: 'Unassigned',
            'room_type' => $room?->room_type ?: 'Room',
            'room_status' => ucfirst($room?->status ?: 'unknown'),
            'capacity' => $room?->capacity ?: 1,
            'check_in' => $checkInDate?->format('M d, Y') ?: 'Not recorded',
            'check_out' => $checkOutDate?->format('M d, Y') ?: 'Pending',
            'nights' => $nights,
            'room_rate' => $roomRate,
            'room_total' => $roomTotal,
            'deposit' => $deposit,
            'folio' => 'RES-' . str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT),
        ];
    })->values();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Check-in - Le Charme Suites IHMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Noto+Serif:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        secondary: '#755a28',
                        'secondary-fixed': '#ffdeaa',
                        background: '#fcf9f3',
                        surface: '#fcf9f3',
                        'surface-container': '#f0eee8',
                        'surface-container-low': '#f6f3ed',
                        'surface-container-lowest': '#ffffff',
                        'surface-variant': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        error: '#ba1a1a',
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
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .nav-shell {
            background-color: #01162d;
            background-image: radial-gradient(rgba(255, 222, 170, 0.16) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        .lux-card {
            box-shadow: 0 18px 45px -24px rgba(1, 22, 45, 0.35), 0 2px 8px rgba(117, 90, 40, 0.08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    <div class="flex min-h-screen">
        <aside class="nav-shell fixed inset-y-0 left-0 z-40 flex w-64 flex-col justify-between border-r border-white/10 px-5 py-6 text-white">
            <div class="space-y-8">
                <a href="{{ route('dashboard') }}" class="block rounded-lg bg-white px-3 py-2 shadow-sm">
                    @include('partials.brand-logo', ['class' => 'mx-auto h-auto w-full max-w-[180px] object-contain'])
                    <span class="mt-1 block text-center text-[10px] font-bold uppercase tracking-[0.15em] text-secondary">Front Desk IHMS</span>
                </a>

                <a href="{{ route('check-ins.create') }}" class="flex items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#5b4313]">
                    <span class="material-symbols-outlined text-[18px]">bolt</span>
                    Express Check-In
                </a>

                @include('partials.front-desk-nav', ['active' => 'check-ins'])
            </div>

            <div class="border-t border-primary-container pt-5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-r-lg border-l-4 border-transparent px-3 py-3 text-left text-sm text-[#7f93ae] transition hover:bg-primary-container hover:text-white">
                        <span class="material-symbols-outlined">logout</span>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <main class="ml-64 min-h-screen flex-1">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-variant bg-surface-container-lowest px-8 shadow-sm">
                <div class="flex items-center gap-4">
                    <h2 class="font-serif text-xl font-bold">Le Charme Suites IHMS</h2>
                    <span class="h-6 w-px bg-surface-variant"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-secondary">Check-ins</span>
                </div>
                <div class="flex items-center gap-4 text-right">
                    <div>
                        <p class="text-xs font-bold uppercase">{{ ucfirst(str_replace('_', ' ', $currentUser?->role ?? 'Employee')) }}</p>
                        <p class="text-xs text-slate-500">{{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary-fixed text-sm font-bold text-primary">
                        {{ strtoupper(substr($currentUser?->name ?? 'U', 0, 1)) }}
                    </a>
                </div>
            </header>

            <div class="px-8 py-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-6 flex flex-col gap-4 border-b border-surface-variant pb-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-[0.22em] text-secondary">Front Office Arrival Intake</span>
                                <span class="h-1 w-1 rounded-full bg-secondary"></span>
                                <span class="text-xs font-semibold text-slate-500">Live Room Allocation</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="font-serif text-4xl font-semibold tracking-tight">Record Check-in</h1>
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                    Room allocation active
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">Select a confirmed reservation, verify the arrival, and move the room into occupied status.</p>
                        </div>
                        <a href="{{ route('check-ins.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:border-secondary">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            Back to Check-ins
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                            <ul class="list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid gap-6 lg:grid-cols-12">
                        <section class="lg:col-span-7">
                            <form method="POST" action="{{ route('check-ins.store') }}" class="lux-card rounded-xl border border-surface-variant bg-white">
                                @csrf
                                <div class="flex items-start justify-between border-b border-surface-variant bg-surface-container-low p-6">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-secondary">assignment_turned_in</span>
                                            <h2 class="text-xl font-bold">Guest Arrival & Room Check-In</h2>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500">Select a reserved booking, confirm arrival details, and record check-in notes.</p>
                                    </div>
                                    <span class="rounded border border-primary/20 bg-primary/5 px-2.5 py-1 text-xs font-bold">Step 1 of 1</span>
                                </div>

                                <div class="space-y-6 p-6">
                                    <div>
                                        <div class="mb-2 flex items-center justify-between">
                                            <label for="reservation_id" class="block text-sm font-bold text-primary">Reservation <span class="text-error">*</span></label>
                                            <a href="{{ route('reservations.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-secondary hover:underline">
                                                <span class="material-symbols-outlined text-[14px]">search</span>
                                                Browse all arrivals
                                            </a>
                                        </div>
                                        <div class="relative">
                                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-secondary">book_online</span>
                                            <select id="reservation_id" name="reservation_id" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-3 pl-11 pr-10 text-sm shadow-sm focus:border-secondary focus:ring-secondary" required>
                                                <option value="">Select reserved booking</option>
                                                @foreach ($reservations as $reservation)
                                                    @php
                                                        $guest = $reservation->guest;
                                                        $room = $reservation->room;
                                                        $nights = max(1, $reservation->check_in_date && $reservation->check_out_date ? $reservation->check_in_date->diffInDays($reservation->check_out_date) : 1);
                                                    @endphp
                                                    <option value="{{ $reservation->id }}" @selected(old('reservation_id') == $reservation->id)>
                                                        RES-{{ str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT) }} - {{ $guest?->full_name ?? 'Guest profile unavailable' }} (Room {{ $room?->room_number ?? 'N/A' }} - {{ $nights }} {{ Str::plural('night', $nights) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if ($reservations->isEmpty())
                                            <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800">No reserved bookings are ready for check-in yet.</p>
                                        @else
                                            <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                                                <span class="material-symbols-outlined text-[14px] text-secondary">info</span>
                                                Lists confirmed reservations that are still waiting for guest arrival.
                                            </p>
                                        @endif
                                    </div>

                                    <div class="rounded-lg border border-outline-variant/50 bg-surface-container-low p-4">
                                        <p class="mb-4 text-xs font-bold uppercase tracking-wider text-secondary">Arrival Verification Details</p>
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="rounded-lg border border-surface-variant bg-white p-4">
                                                <p class="text-xs font-medium text-slate-500">Room Assignment</p>
                                                <div class="mt-1 flex items-center gap-2 text-base font-bold">
                                                    <span class="material-symbols-outlined text-[18px] text-secondary">bed</span>
                                                    <span id="roomAssignmentPreview">No room selected</span>
                                                </div>
                                                <p id="roomReadyPreview" class="mt-2 inline-flex items-center gap-1 rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-800">
                                                    <span class="material-symbols-outlined text-[13px]">verified</span>
                                                    Awaiting reservation selection
                                                </p>
                                            </div>
                                            <div class="rounded-lg border border-surface-variant bg-white p-4">
                                                <p class="text-xs font-medium text-slate-500">Guest Profile</p>
                                                <div class="mt-1 flex items-center gap-2 text-base font-bold">
                                                    <span class="material-symbols-outlined text-[18px] text-secondary">badge</span>
                                                    <span id="identityPreview">Not selected</span>
                                                </div>
                                                <p id="contactPreview" class="mt-2 text-xs text-slate-500">Select a reservation to view contact details.</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 border-t border-outline-variant/40 pt-4">
                                            <div class="mb-3 flex items-center justify-between">
                                                <p class="flex items-center gap-2 text-sm font-bold">
                                                    <span class="material-symbols-outlined text-[18px] text-secondary">nfc</span>
                                                    Keycard Assignment
                                                </p>
                                                <span class="text-xs font-bold text-secondary">Optional demo fields</span>
                                            </div>
                                            <div class="grid gap-3 md:grid-cols-2">
                                                <input type="text" class="rounded-lg border border-outline-variant bg-white px-3 py-2 text-xs font-mono focus:border-secondary focus:ring-secondary" placeholder="Keycard UID #01">
                                                <input type="text" class="rounded-lg border border-outline-variant bg-white px-3 py-2 text-xs font-mono focus:border-secondary focus:ring-secondary" placeholder="Keycard UID #02">
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="mb-2 flex items-center justify-between">
                                            <label for="notes" class="block text-sm font-bold text-primary">Notes</label>
                                            <span class="text-xs text-slate-500">Logged to stay history</span>
                                        </div>
                                        <textarea id="notes" name="notes" rows="4" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest p-3 text-sm shadow-sm focus:border-secondary focus:ring-secondary" placeholder="Add arrival notes, guest requests, keycard remarks, or special handling instructions...">{{ old('notes') }}</textarea>
                                    </div>

                                    <div class="border-t border-surface-variant pt-4">
                                        <p class="mb-3 text-xs font-bold uppercase tracking-wider text-primary">Pre-check-in operational protocol</p>
                                        <div class="space-y-3">
                                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-outline-variant/40 bg-surface-container p-3 text-sm transition hover:bg-surface-container-low">
                                                <input type="checkbox" class="mt-0.5 rounded border-outline-variant text-secondary focus:ring-secondary">
                                                <span>
                                                    <strong class="block">Identity verified</strong>
                                                    <span class="text-xs text-slate-500">Valid ID or guest profile checked before issuing access.</span>
                                                </span>
                                            </label>
                                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-outline-variant/40 bg-surface-container p-3 text-sm transition hover:bg-surface-container-low">
                                                <input type="checkbox" class="mt-0.5 rounded border-outline-variant text-secondary focus:ring-secondary">
                                                <span>
                                                    <strong class="block">Deposit or guarantee confirmed</strong>
                                                    <span class="text-xs text-slate-500">Front desk confirms payment guarantee or hotel policy requirement.</span>
                                                </span>
                                            </label>
                                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-outline-variant/40 bg-surface-container p-3 text-sm transition hover:bg-surface-container-low">
                                                <input type="checkbox" class="mt-0.5 rounded border-outline-variant text-secondary focus:ring-secondary">
                                                <span>
                                                    <strong class="block">Room and keycards ready</strong>
                                                    <span class="text-xs text-slate-500">Room assignment is ready before completing check-in.</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-surface-variant pt-5">
                                        <div class="flex flex-wrap gap-3">
                                            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-primary-container" @disabled($reservations->isEmpty())>
                                                <span class="material-symbols-outlined text-[19px]">check</span>
                                                Check In
                                            </button>
                                            <button id="printArrivalButton" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-3 text-sm font-bold text-primary shadow-sm transition hover:border-secondary">
                                                <span class="material-symbols-outlined text-[18px]">print</span>
                                                Print Registration Card
                                            </button>
                                        </div>
                                        <a href="{{ route('check-ins.index') }}" class="px-3 py-2 text-sm font-bold text-slate-500 transition hover:text-error">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </section>

                        <aside class="space-y-5 lg:col-span-5">
                            <section class="lux-card overflow-hidden rounded-xl border border-surface-variant bg-white">
                                <div class="border-b border-surface-variant bg-gradient-to-r from-surface-container-low to-surface p-5">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="rounded bg-secondary/15 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-secondary">Inbound Reservation</span>
                                        <span id="roomBadgePreview" class="rounded bg-primary px-2.5 py-1 font-mono text-xs font-bold text-white">No room selected</span>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div id="guestInitialsPreview" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-secondary bg-secondary-fixed text-lg font-bold text-primary">--</div>
                                        <div>
                                            <h3 id="guestPreview" class="font-serif text-2xl font-semibold leading-tight">No reservation selected</h3>
                                            <p id="guestContactPreview" class="mt-1 text-sm text-slate-500">Choose a booking to view guest details.</p>
                                            <p id="folioPreview" class="mt-2 inline-flex rounded border border-secondary/30 bg-secondary/10 px-2 py-1 text-xs font-bold text-secondary">Reservation pending</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4 border-b border-surface-variant p-5">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-lg border border-outline-variant/40 bg-surface-container-low p-3">
                                            <p class="text-xs text-slate-500">Arrival Scheduled</p>
                                            <p id="arrivalPreview" class="mt-1 font-bold">Not selected</p>
                                        </div>
                                        <div class="rounded-lg border border-outline-variant/40 bg-surface-container-low p-3">
                                            <p class="text-xs text-slate-500">Departure Date</p>
                                            <p id="departurePreview" class="mt-1 font-bold">Not selected</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-between border-b border-surface-variant py-2 text-sm">
                                        <span class="text-slate-500">Reservation Confirmation</span>
                                        <span id="confirmationPreview" class="font-mono font-bold">--</span>
                                    </div>
                                    <div class="flex justify-between py-2 text-sm">
                                        <span class="text-slate-500">Stay Duration</span>
                                        <span id="durationPreview" class="font-bold">0 nights</span>
                                    </div>
                                </div>

                                <div class="space-y-3 p-5">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-bold uppercase tracking-wider">Guest Bill & Deposit Preview</p>
                                        <span class="rounded border border-emerald-200 bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800">Guaranteed</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Room Rate</span>
                                        <span id="roomRatePreview" class="font-bold">PHP 0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Estimated room total</span>
                                        <span id="roomTotalPreview" class="font-bold">PHP 0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Suggested deposit</span>
                                        <span id="depositPreview" class="font-bold">PHP 0.00</span>
                                    </div>
                                </div>

                                <div class="m-4 rounded-lg border border-secondary/30 bg-primary p-4 text-white">
                                    <div class="flex items-start gap-3">
                                        <div class="rounded bg-primary-container p-2 text-secondary-fixed">
                                            <span class="material-symbols-outlined">sync_alt</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-secondary-fixed">Automatic PMS Room Status Trigger</p>
                                            <p class="mt-1 text-xs leading-6 text-surface-container">Submitting this check-in will mark the selected room as occupied and record the action in room status logs and transactions.</p>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-xl border border-surface-variant bg-white p-5 shadow-sm">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-bold uppercase tracking-wider">Today's Reserved Arrivals</h3>
                                    <span class="text-xs font-bold text-secondary">{{ $reservations->count() }} pending</span>
                                </div>
                                <div class="space-y-2">
                                    @forelse ($reservations->take(3) as $arrival)
                                        <div class="flex items-center justify-between rounded-lg border border-surface-variant bg-surface-container-low p-3 text-sm">
                                            <div>
                                                <p class="font-bold">{{ $arrival->guest?->full_name ?? 'Guest profile unavailable' }}</p>
                                                <p class="text-xs text-slate-500">Room {{ $arrival->room?->room_number ?? 'N/A' }} - {{ $arrival->room?->room_type ?? 'Room' }}</p>
                                            </div>
                                            <span class="text-xs font-bold text-secondary">{{ $arrival->check_in_date?->format('M d') ?? 'Soon' }}</span>
                                        </div>
                                    @empty
                                        <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">No arrivals are queued yet.</p>
                                    @endforelse
                                </div>
                            </section>
                        </aside>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const reservations = @json($reservationPreview);
        const reservationSelect = document.getElementById('reservation_id');

        const currency = (value) => new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(Number(value || 0));

        function selectedReservation() {
            return reservations.find((reservation) => String(reservation.id) === String(reservationSelect.value));
        }

        function initials(name) {
            return name.split(' ').filter(Boolean).slice(0, 2).map((part) => part[0]).join('').toUpperCase() || '--';
        }

        function updatePreview() {
            const selected = selectedReservation();
            const data = selected || {
                guest: 'No reservation selected',
                email: 'Choose a booking to view guest details.',
                phone: '',
                room: 'No room selected',
                room_type: 'Room',
                room_status: 'Awaiting reservation selection',
                check_in: 'Not selected',
                check_out: 'Not selected',
                nights: 0,
                room_rate: 0,
                room_total: 0,
                deposit: 0,
                folio: '--',
                capacity: 0,
            };

            document.getElementById('roomAssignmentPreview').textContent = selected ? `Room ${data.room} - ${data.room_type}` : 'No room selected';
            document.getElementById('roomReadyPreview').lastChild.textContent = selected ? `${data.room_status} - capacity ${data.capacity}` : 'Awaiting reservation selection';
            document.getElementById('identityPreview').textContent = data.guest;
            document.getElementById('contactPreview').textContent = selected ? `${data.email} | ${data.phone}` : 'Select a reservation to view contact details.';
            document.getElementById('roomBadgePreview').textContent = selected ? `Rm ${data.room} ${data.room_type}` : 'No room selected';
            document.getElementById('guestInitialsPreview').textContent = initials(data.guest);
            document.getElementById('guestPreview').textContent = data.guest;
            document.getElementById('guestContactPreview').textContent = selected ? `${data.email} | ${data.phone}` : data.email;
            document.getElementById('folioPreview').textContent = selected ? data.folio : 'Reservation pending';
            document.getElementById('arrivalPreview').textContent = data.check_in;
            document.getElementById('departurePreview').textContent = data.check_out;
            document.getElementById('confirmationPreview').textContent = data.folio;
            document.getElementById('durationPreview').textContent = `${data.nights} ${Number(data.nights) === 1 ? 'night' : 'nights'}`;
            document.getElementById('roomRatePreview').textContent = currency(data.room_rate);
            document.getElementById('roomTotalPreview').textContent = currency(data.room_total);
            document.getElementById('depositPreview').textContent = currency(data.deposit);
        }

        reservationSelect.addEventListener('change', updatePreview);
        document.getElementById('printArrivalButton').addEventListener('click', () => window.print());

        updatePreview();
    </script>
</body>
</html>
