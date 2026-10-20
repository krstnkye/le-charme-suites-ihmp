@php
    $currentUser = auth()->user();
    $selectedReservationId = old('reservation_id', request()->integer('reservation_id'));
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
            'address' => $guest?->address ?: 'Not provided',
            'identification' => trim(implode(' ', array_filter([
                $guest?->identification_type,
                $guest?->identification_number,
            ]))) ?: 'Not recorded',
            'room' => $room?->room_number ?: 'Unassigned',
            'room_type' => $room?->room_type ?: 'Room',
            'room_status' => ucfirst($room?->status ?: 'unknown'),
            'capacity' => $room?->capacity ?: 1,
            'guests' => $reservation->number_of_guests,
            'check_in' => $checkInDate?->format('M d, Y') ?: 'Not recorded',
            'check_out' => $checkOutDate?->format('M d, Y') ?: 'Pending',
            'nights' => $nights,
            'room_rate' => $roomRate,
            'room_total' => $roomTotal,
            'deposit' => $deposit,
            'folio' => 'RES-' . str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT),
            'booking_reference' => $reservation->booking_reference ?: 'RES-' . str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT),
            'source' => str($reservation->source ?: 'front_desk')->replace('_', ' ')->title()->toString(),
        ];
    })->values();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Check-in - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
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

        .registration-card {
            display: none;
        }

        .receipt-sheet {
            box-sizing: border-box;
            border: 1px solid #c4c6cd;
            background: #ffffff;
            color: #1c1c18;
            min-height: 270mm;
            padding: 9mm 10mm;
            position: relative;
        }

        .receipt-section-title {
            align-items: center;
            background: #ebe8e2;
            border-left: 4px solid #755a28;
            color: #01162d;
            display: flex;
            font-size: 8pt;
            font-weight: 800;
            justify-content: space-between;
            letter-spacing: 0.7px;
            margin-bottom: 2.5mm;
            padding: 1.8mm 3mm;
            text-transform: uppercase;
        }

        .receipt-grid {
            display: grid;
            gap: 2.5mm;
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .receipt-field {
            border: 1px solid #c4c6cd;
            border-radius: 2px;
            padding: 2.4mm 3mm;
        }

        .receipt-label {
            color: #64748b;
            display: block;
            font-size: 6.8pt;
            font-weight: 700;
            letter-spacing: 0.45px;
            text-transform: uppercase;
        }

        .receipt-value {
            color: #01162d;
            display: block;
            font-size: 9pt;
            font-weight: 700;
            margin-top: 1mm;
            overflow-wrap: anywhere;
        }

        .receipt-barcode {
            background: repeating-linear-gradient(90deg, #01162d 0, #01162d 1px, transparent 1px, transparent 3px, #01162d 3px, #01162d 5px, transparent 5px, transparent 7px);
            height: 10mm;
            width: 43mm;
        }

        .receipt-perforation {
            border-top: 2px dashed #74777e;
            margin-top: 5mm;
            padding-top: 4mm;
            position: relative;
        }

        .receipt-perforation::before {
            background: white;
            color: #64748b;
            content: 'CUT HERE  -  GUEST COPY';
            font-family: monospace;
            font-size: 6.5pt;
            left: 5mm;
            padding: 0 2mm;
            position: absolute;
            top: -2.3mm;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            .screen-shell {
                display: none !important;
            }

            .registration-card {
                display: block !important;
                color: #01162d;
                font-family: Arial, sans-serif;
            }

            .registration-card * {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    <div class="screen-shell flex min-h-screen">
        @include('partials.front-desk-sidebar', ['active' => 'check-ins'])

        <main class="min-h-screen flex-1 lg:ml-64">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-variant bg-surface-container-lowest px-8 shadow-sm">
                <div class="flex items-center gap-4">
                    <h2 class="font-serif text-xl font-bold">Le Charme Suites IHMP</h2>
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
                                        @if ($reservations->isNotEmpty())
                                            <div class="mb-3">
                                                <label for="reservation_search" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Find a guest or booking</label>
                                                <div class="flex flex-col gap-2 sm:flex-row">
                                                    <div class="relative min-w-0 flex-1">
                                                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">person_search</span>
                                                        <input
                                                            id="reservation_search"
                                                            type="search"
                                                            class="w-full rounded-lg border border-outline-variant bg-white py-2.5 pl-11 pr-3 text-sm shadow-sm focus:border-secondary focus:ring-secondary"
                                                            placeholder="Guest name, reservation, email, phone, or room"
                                                            autocomplete="off"
                                                        >
                                                    </div>
                                                    <button id="reservationSearchButton" type="button" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2">
                                                        <span class="material-symbols-outlined text-[18px]">search</span>
                                                        Search
                                                    </button>
                                                    <button id="reservationSearchClear" type="button" class="hidden shrink-0 items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm font-bold text-primary transition hover:bg-surface-container" aria-label="Clear reservation search">
                                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                                        Clear
                                                    </button>
                                                </div>
                                                <p id="reservationSearchStatus" class="mt-1.5 text-xs text-slate-500" role="status" aria-live="polite">Search across {{ $reservations->count() }} confirmed {{ Str::plural('reservation', $reservations->count()) }}.</p>
                                            </div>
                                        @endif
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
                                                    <option
                                                        value="{{ $reservation->id }}"
                                                        data-search="{{ Str::lower(implode(' ', [
                                                            'RES-' . str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT),
                                                            $reservation->booking_reference,
                                                            $guest?->full_name,
                                                            $guest?->email,
                                                            $guest?->phone,
                                                            $room?->room_number,
                                                            $room?->room_type,
                                                        ])) }}"
                                                        @selected((string) $selectedReservationId === (string) $reservation->id)
                                                    >
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
                                                <input id="keycardOne" type="text" class="rounded-lg border border-outline-variant bg-white px-3 py-2 text-xs font-mono focus:border-secondary focus:ring-secondary" placeholder="Keycard UID #01">
                                                <input id="keycardTwo" type="text" class="rounded-lg border border-outline-variant bg-white px-3 py-2 text-xs font-mono focus:border-secondary focus:ring-secondary" placeholder="Keycard UID #02">
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
                                                <input id="identityVerified" type="checkbox" class="mt-0.5 rounded border-outline-variant text-secondary focus:ring-secondary">
                                                <span>
                                                    <strong class="block">Identity verified</strong>
                                                    <span class="text-xs text-slate-500">Valid ID or guest profile checked before issuing access.</span>
                                                </span>
                                            </label>
                                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-outline-variant/40 bg-surface-container p-3 text-sm transition hover:bg-surface-container-low">
                                                <input id="guaranteeConfirmed" type="checkbox" class="mt-0.5 rounded border-outline-variant text-secondary focus:ring-secondary">
                                                <span>
                                                    <strong class="block">Deposit or guarantee confirmed</strong>
                                                    <span class="text-xs text-slate-500">Front desk confirms payment guarantee or hotel policy requirement.</span>
                                                </span>
                                            </label>
                                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-outline-variant/40 bg-surface-container p-3 text-sm transition hover:bg-surface-container-low">
                                                <input id="roomReady" type="checkbox" class="mt-0.5 rounded border-outline-variant text-secondary focus:ring-secondary">
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
                                            <button id="printArrivalButton" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-3 text-sm font-bold text-primary shadow-sm transition hover:border-secondary disabled:cursor-not-allowed disabled:opacity-50" disabled>
                                                <span class="material-symbols-outlined text-[18px]">print</span>
                                                Print Registration Card & Receipt
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

    <section id="registrationCard" class="registration-card" aria-hidden="true">
        <div class="receipt-sheet">
            <header style="border-bottom: 2px solid #01162d; padding-bottom: 4mm;">
                <div style="display: flex; justify-content: space-between; gap: 8mm;">
                    <div style="display: flex; align-items: center; gap: 4mm;">
                        <img src="{{ asset('images/le-charme-logo-white.png') }}" alt="Le Charme Suites" style="width: 40mm; height: 17mm; object-fit: contain;">
                        <div>
                            <strong style="display: block; color: #01162d; font-family: Georgia, serif; font-size: 15pt;">LE CHARMÉ SUITES</strong>
                            <span style="display: block; color: #64748b; font-size: 7.5pt; margin-top: 1mm;">{{ config('hotel.location') }}</span>
                            <span style="display: block; color: #755a28; font-size: 7pt; font-weight: 700; margin-top: 1mm;">Integrated Hotel Management Portal (IHMP)</span>
                        </div>
                    </div>
                    <div style="min-width: 49mm; text-align: right;">
                        <span style="background: #f0eee8; border: 1px solid #c4c6cd; color: #64748b; display: inline-block; font-size: 6.8pt; font-weight: 700; letter-spacing: 0.6px; padding: 1mm 2mm; text-transform: uppercase;">Official Registration Receipt</span>
                        <strong id="cardReference" style="display: block; color: #01162d; font-family: monospace; font-size: 12pt; margin-top: 1.5mm;">--</strong>
                        <span id="cardPreparedAt" style="color: #64748b; display: block; font-size: 7pt; margin-top: 1mm;"></span>
                    </div>
                </div>
                <div style="align-items: end; border-top: 1px solid #e5e2dc; display: flex; justify-content: space-between; margin-top: 3mm; padding-top: 2.5mm;">
                    <div>
                        <strong style="color: #01162d; font-size: 8.5pt; letter-spacing: 0.7px; text-transform: uppercase;">Guest Registration Card & Stay Receipt</strong>
                        <span style="color: #64748b; display: block; font-size: 6.8pt; margin-top: 0.8mm;">Prepared for guest verification, arrival intake, and stay acknowledgement</span>
                    </div>
                    <div>
                        <div class="receipt-barcode" aria-hidden="true"></div>
                        <span id="cardBarcodeText" style="display: block; font-family: monospace; font-size: 6.5pt; letter-spacing: 1px; margin-top: 0.8mm; text-align: center;">--</span>
                    </div>
                </div>
            </header>

            <section style="margin-top: 4mm;">
                <div class="receipt-section-title"><span>1. Guest Identification & Contact</span><span id="cardIdentityBadge" style="color: #755a28; font-size: 6.5pt;">Verification pending</span></div>
                <div class="receipt-grid">
                    <div class="receipt-field" style="grid-column: span 7;"><span class="receipt-label">Principal guest</span><span id="cardGuest" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="grid-column: span 5;"><span class="receipt-label">Government ID / Passport</span><span id="cardIdentification" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="grid-column: span 5;"><span class="receipt-label">Email address</span><span id="cardEmail" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="grid-column: span 3;"><span class="receipt-label">Mobile number</span><span id="cardPhone" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="grid-column: span 4;"><span class="receipt-label">Booking source</span><span id="cardSource" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="grid-column: span 12;"><span class="receipt-label">Residential / billing address</span><span id="cardAddress" class="receipt-value">--</span></div>
                </div>
            </section>

            <section style="margin-top: 4mm;">
                <div class="receipt-section-title"><span>2. Room Allocation & Stay Charge Summary</span><span>Rates recorded in PHP</span></div>
                <div class="receipt-grid">
                    <div class="receipt-field" style="background: #f6f3ed; grid-column: span 3;"><span class="receipt-label">Room assigned</span><span id="cardRoom" class="receipt-value">--</span><span id="cardRoomCapacity" style="color: #64748b; display: block; font-size: 6.8pt; margin-top: 1mm;">--</span></div>
                    <div class="receipt-field" style="grid-column: span 3;"><span class="receipt-label">Arrival / check-in</span><span id="cardArrival" class="receipt-value">--</span><span style="color: #64748b; display: block; font-size: 6.8pt; margin-top: 1mm;">{{ config('hotel.check_in_time') }}</span></div>
                    <div class="receipt-field" style="grid-column: span 3;"><span class="receipt-label">Departure / check-out</span><span id="cardDeparture" class="receipt-value">--</span><span style="color: #64748b; display: block; font-size: 6.8pt; margin-top: 1mm;">{{ config('hotel.check_out_time') }}</span></div>
                    <div class="receipt-field" style="grid-column: span 3;"><span class="receipt-label">Registered occupancy</span><span id="cardStay" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="grid-column: span 4;"><span class="receipt-label">Nightly room rate</span><span id="cardRate" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="grid-column: span 4;"><span class="receipt-label">Estimated room total</span><span id="cardTotal" class="receipt-value">--</span></div>
                    <div class="receipt-field" style="background: #fff8eb; grid-column: span 4;"><span class="receipt-label">Suggested guarantee / deposit</span><span id="cardDeposit" class="receipt-value" style="color: #755a28;">--</span></div>
                </div>
            </section>

            <section style="margin-top: 4mm;">
                <div class="receipt-section-title"><span>3. Front Desk Arrival Verification</span><span id="cardPreparedByTop">--</span></div>
                <div class="receipt-grid">
                    <div class="receipt-field" style="grid-column: span 5;">
                        <span class="receipt-label">Operational checklist</span>
                        <span id="cardIdentityStatus" style="display: block; font-size: 8pt; margin-top: 2mm;">[ ] Identity verified</span>
                        <span id="cardGuaranteeStatus" style="display: block; font-size: 8pt; margin-top: 1.5mm;">[ ] Deposit or guarantee confirmed</span>
                        <span id="cardRoomStatus" style="display: block; font-size: 8pt; margin-top: 1.5mm;">[ ] Room and keycards ready</span>
                    </div>
                    <div class="receipt-field" style="grid-column: span 3;"><span class="receipt-label">RFID / keycard assignment</span><span id="cardKeycards" class="receipt-value">Not assigned</span></div>
                    <div class="receipt-field" style="grid-column: span 4;"><span class="receipt-label">Front Desk notes</span><span id="cardNotes" style="display: block; font-size: 8pt; margin-top: 1.5mm; min-height: 14mm; white-space: pre-wrap;">None recorded.</span></div>
                </div>
            </section>

            <section style="border: 1px solid #c4c6cd; margin-top: 4mm; padding: 3mm;">
                <strong style="color: #01162d; display: block; font-size: 7.5pt; letter-spacing: 0.55px; text-transform: uppercase;">4. Guest Acknowledgement & Data Handling</strong>
                <div style="column-gap: 6mm; columns: 2; color: #475569; font-size: 6.8pt; line-height: 1.45; margin-top: 2mm;">
                    <p style="margin: 0 0 1.5mm;"><strong>Stay details.</strong> The guest confirms the room, occupancy, arrival date, departure date, and estimated charges shown above.</p>
                    <p style="margin: 0 0 1.5mm;"><strong>Hotel policies.</strong> The guest agrees to observe published hotel rules, the non-smoking policy, property-care requirements, and the standard {{ config('hotel.check_out_time') }} check-out time.</p>
                    <p style="margin: 0 0 1.5mm;"><strong>Settlement.</strong> Final charges may include authorized room, service, incidental, damage, adjustment, or payment records posted to the guest bill.</p>
                    <p style="margin: 0;"><strong>Privacy.</strong> Personal information is processed for reservations, guest service, security, billing, reporting, and legally required hotel records. Access remains role-controlled within IHMP.</p>
                </div>
            </section>

            <section style="display: grid; gap: 14mm; grid-template-columns: 1fr 1fr; margin-top: 12mm;">
                <div style="border-top: 1px solid #01162d; color: #475569; font-size: 7.5pt; padding-top: 2mm; text-align: center;">Guest signature over printed name / date</div>
                <div style="border-top: 1px solid #01162d; color: #475569; font-size: 7.5pt; padding-top: 2mm; text-align: center;">Front Desk representative: <strong id="cardPreparedBy">--</strong></div>
            </section>

            <section class="receipt-perforation">
                <div style="align-items: center; background: #f6f3ed; border: 1px solid #c4c6cd; display: flex; gap: 4mm; justify-content: space-between; padding: 3mm 4mm;">
                    <div style="background: #01162d; color: #ffdeaa; display: flex; font-size: 13pt; font-weight: 800; height: 13mm; align-items: center; justify-content: center; min-width: 18mm; padding: 0 2mm;"><span id="cardCounterRoom">--</span></div>
                    <div style="flex: 1;">
                        <strong id="cardCounterGuest" style="color: #01162d; display: block; font-size: 9pt;">--</strong>
                        <span id="cardCounterStay" style="color: #64748b; display: block; font-size: 7pt; margin-top: 1mm;">--</span>
                        <span id="cardCounterReference" style="color: #755a28; display: block; font-family: monospace; font-size: 6.8pt; margin-top: 1mm;">--</span>
                    </div>
                    <div style="color: #475569; font-size: 6.8pt; line-height: 1.5; text-align: right;">
                        <div>Front Desk: <strong>{{ config('hotel.phone') }}</strong></div>
                        <div>{{ config('hotel.email') }}</div>
                    </div>
                </div>
            </section>

            <footer style="border-top: 1px solid #e5e2dc; color: #64748b; display: flex; font-family: monospace; font-size: 6.2pt; justify-content: space-between; margin-top: 3mm; padding-top: 2mm;">
                <span>IHMP GUEST REGISTRATION RECEIPT &middot; {{ config('hotel.address') }}</span>
                <span id="cardAuditReference">--</span>
            </footer>
        </div>
    </section>

    <script>
        const reservations = @json($reservationPreview);
        const reservationSelect = document.getElementById('reservation_id');
        const reservationSearch = document.getElementById('reservation_search');
        const reservationSearchButton = document.getElementById('reservationSearchButton');
        const reservationSearchClear = document.getElementById('reservationSearchClear');
        const reservationSearchStatus = document.getElementById('reservationSearchStatus');
        const printArrivalButton = document.getElementById('printArrivalButton');

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
                booking_reference: '--',
                source: 'Not recorded',
                address: 'Not provided',
                identification: 'Not recorded',
                capacity: 0,
                guests: 0,
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
            printArrivalButton.disabled = !selected;
        }

        function setCardText(id, value) {
            document.getElementById(id).textContent = value || '--';
        }

        function verificationMark(checked, label) {
            return `${checked ? '[X]' : '[ ]'} ${label}`;
        }

        function printRegistrationCard() {
            const selected = selectedReservation();

            if (!selected) {
                reservationSelect.focus();
                return;
            }

            const keycards = [
                document.getElementById('keycardOne').value.trim(),
                document.getElementById('keycardTwo').value.trim(),
            ].filter(Boolean);
            const notes = document.getElementById('notes').value.trim();
            const preparedBy = @json($currentUser?->name ?? 'Front Desk Staff');
            const preparedAt = new Intl.DateTimeFormat('en-PH', {
                timeZone: @json(config('app.timezone')),
                dateStyle: 'medium',
                timeStyle: 'short',
            }).format(new Date());
            const identityVerified = document.getElementById('identityVerified').checked;
            const guaranteeConfirmed = document.getElementById('guaranteeConfirmed').checked;
            const roomReady = document.getElementById('roomReady').checked;

            setCardText('cardReference', selected.booking_reference);
            setCardText('cardBarcodeText', selected.booking_reference);
            setCardText('cardPreparedAt', `Printed ${preparedAt}`);
            setCardText('cardGuest', selected.guest);
            setCardText('cardEmail', selected.email);
            setCardText('cardPhone', selected.phone);
            setCardText('cardAddress', selected.address);
            setCardText('cardIdentification', selected.identification);
            setCardText('cardIdentityBadge', identityVerified ? 'Identity verified' : 'Verification pending');
            setCardText('cardSource', selected.source);
            setCardText('cardArrival', selected.check_in);
            setCardText('cardDeparture', selected.check_out);
            setCardText('cardRoom', `Room ${selected.room} - ${selected.room_type}`);
            setCardText('cardRoomCapacity', `Capacity ${selected.capacity} ${Number(selected.capacity) === 1 ? 'guest' : 'guests'}`);
            setCardText('cardStay', `${selected.guests} ${Number(selected.guests) === 1 ? 'guest' : 'guests'} / ${selected.nights} ${Number(selected.nights) === 1 ? 'night' : 'nights'}`);
            setCardText('cardRate', currency(selected.room_rate));
            setCardText('cardTotal', currency(selected.room_total));
            setCardText('cardDeposit', currency(selected.deposit));
            setCardText('cardKeycards', keycards.length ? keycards.join(' / ') : 'Not assigned');
            setCardText('cardNotes', notes || 'None recorded.');
            setCardText('cardPreparedBy', preparedBy);
            setCardText('cardPreparedByTop', `Prepared by ${preparedBy}`);
            setCardText('cardIdentityStatus', verificationMark(identityVerified, 'Identity verified'));
            setCardText('cardGuaranteeStatus', verificationMark(guaranteeConfirmed, 'Deposit or guarantee confirmed'));
            setCardText('cardRoomStatus', verificationMark(roomReady, 'Room and keycards ready'));
            setCardText('cardCounterRoom', selected.room);
            setCardText('cardCounterGuest', selected.guest);
            setCardText('cardCounterStay', `${selected.check_in} to ${selected.check_out} - ${selected.room_type}`);
            setCardText('cardCounterReference', selected.booking_reference);
            setCardText('cardAuditReference', `${selected.booking_reference} - PRINTED ${preparedAt}`);

            window.print();
        }

        function normalizeSearch(value) {
            return String(value || '').trim().toLocaleLowerCase();
        }

        function filterReservations() {
            if (!reservationSearch) {
                return;
            }

            const query = normalizeSearch(reservationSearch.value);
            const options = Array.from(reservationSelect.options).slice(1);
            let matches = 0;
            let onlyMatch = null;

            options.forEach((option) => {
                const matchesQuery = !query || normalizeSearch(option.dataset.search).includes(query);
                option.hidden = !matchesQuery;
                option.disabled = !matchesQuery;

                if (matchesQuery) {
                    matches += 1;
                    onlyMatch = option;
                }
            });

            if (query && matches === 1 && onlyMatch) {
                reservationSelect.value = onlyMatch.value;
                updatePreview();
            } else if (query && reservationSelect.selectedOptions[0]?.disabled) {
                reservationSelect.value = '';
                updatePreview();
            }

            reservationSelect.options[0].textContent = matches === 0
                ? 'No matching reserved bookings'
                : 'Select reserved booking';

            reservationSearchStatus.textContent = query
                ? `${matches} matching ${matches === 1 ? 'reservation' : 'reservations'} found.`
                : `Search across ${options.length} confirmed ${options.length === 1 ? 'reservation' : 'reservations'}.`;
            reservationSearchClear.classList.toggle('hidden', !query);
            reservationSearchClear.classList.toggle('inline-flex', Boolean(query));

            if (matches > 1) {
                reservationSelect.focus();
            }
        }

        function clearReservationSearch() {
            reservationSearch.value = '';
            filterReservations();
            reservationSearch.focus();
        }

        reservationSelect.addEventListener('change', updatePreview);
        reservationSearchButton?.addEventListener('click', filterReservations);
        reservationSearchClear?.addEventListener('click', clearReservationSearch);
        reservationSearch?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                filterReservations();
            }
        });
        reservationSearch?.addEventListener('input', () => {
            if (!reservationSearch.value) {
                filterReservations();
            }
        });
        printArrivalButton.addEventListener('click', printRegistrationCard);

        updatePreview();
    </script>
</body>
</html>
