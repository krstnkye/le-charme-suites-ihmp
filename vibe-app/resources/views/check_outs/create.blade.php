@php
    $currentUser = auth()->user();
    $activeCheckIns = $checkIns->map(function ($checkIn) {
        $reservation = $checkIn->reservation;
        $room = $checkIn->room;
        $guest = $checkIn->guest;
        $checkInDate = $reservation?->check_in_date ?? $checkIn->checked_in_at;
        $checkOutDate = $reservation?->check_out_date ?? now();
        $nights = max(1, $checkInDate && $checkOutDate ? $checkInDate->diffInDays($checkOutDate) : 1);
        $roomRate = (float) ($room?->rate ?? 0);
        $roomTotal = $roomRate * $nights;

        return [
            'id' => $checkIn->id,
            'guest' => $guest?->full_name ?: 'Guest profile unavailable',
            'email' => $guest?->email ?: 'No email on file',
            'room' => $room?->room_number ?: 'Unassigned',
            'room_type' => $room?->room_type ?: 'Room',
            'nights' => $nights,
            'room_total' => $roomTotal,
            'balance' => $roomTotal,
            'check_in' => $checkInDate?->format('M d, Y') ?: 'Not recorded',
            'check_out' => $checkOutDate?->format('M d, Y') ?: 'Pending',
            'folio' => 'IHMS-' . str_pad((string) $checkIn->id, 5, '0', STR_PAD_LEFT),
        ];
    })->values();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Check-out - Le Charme Suites IHMS</title>
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

        @media print {
            body * {
                visibility: hidden;
            }

            #folioReceipt,
            #folioReceipt * {
                visibility: visible;
            }

            #folioReceipt {
                position: absolute;
                inset: 0;
                width: 100%;
                box-shadow: none;
                border: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    <div class="flex min-h-screen">
        <aside class="nav-shell fixed inset-y-0 left-0 z-40 flex w-64 flex-col justify-between border-r border-white/10 px-5 py-6 text-white">
            <div class="space-y-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg border border-secondary-fixed/40 bg-primary-container text-secondary-fixed">
                        <span class="material-symbols-outlined text-[26px]">hotel_class</span>
                    </div>
                    <div>
                        <h1 class="font-serif text-xl font-bold leading-tight">Le Charme Suites</h1>
                        <p class="mt-1 text-xs font-medium text-[#7f93ae]">Subic Bay - IHMS</p>
                    </div>
                </div>

                <a href="{{ route('check-ins.create') }}" class="flex items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#5b4313]">
                    <span class="material-symbols-outlined text-[18px]">bolt</span>
                    Express Check-In
                </a>

                @include('partials.front-desk-nav', ['active' => 'check-outs'])
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
                    <span class="text-xs font-bold uppercase tracking-wider text-secondary">Check-outs</span>
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
                <div class="mx-auto max-w-6xl">
                    <div class="mb-6 flex flex-col gap-4 border-b border-surface-variant pb-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-[0.22em] text-secondary">Front Office Settlement</span>
                                <span class="h-1 w-1 rounded-full bg-secondary"></span>
                                <span class="text-xs font-semibold text-slate-500">Live Folio Sync</span>
                            </div>
                            <h1 class="font-serif text-4xl font-semibold tracking-tight">Record Check-out</h1>
                            <p class="mt-2 text-sm text-slate-600">Select an active stay, confirm payment, and move the room into turnover status.</p>
                        </div>
                        <a href="{{ route('check-outs.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:border-secondary">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            Back to Check-outs
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
                        <section class="lg:col-span-8">
                            <form method="POST" action="{{ route('check-outs.store') }}" class="lux-card rounded-xl border border-surface-variant bg-white p-6 md:p-8">
                                @csrf
                                <div class="mb-6 flex items-start justify-between border-b border-surface-variant pb-5">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-secondary">assignment_return</span>
                                            <h2 class="text-lg font-bold">Guest Departure Settlement</h2>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500">Only active check-ins are available for processing.</p>
                                    </div>
                                    <span class="rounded bg-surface-container px-2.5 py-1 text-[11px] font-bold text-slate-600">IPT-MOD-CO</span>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label for="check_in_id" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-700">Active check-in <span class="text-error">*</span></label>
                                        <div class="relative">
                                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-secondary">sensor_door</span>
                                            <select id="check_in_id" name="check_in_id" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-3 pl-11 pr-10 text-sm shadow-sm focus:border-secondary focus:ring-secondary" required>
                                                <option value="">Select active guest</option>
                                                @foreach ($checkIns as $checkIn)
                                                    @php
                                                        $reservation = $checkIn->reservation;
                                                        $room = $checkIn->room;
                                                        $guest = $checkIn->guest;
                                                        $checkInDate = $reservation?->check_in_date ?? $checkIn->checked_in_at;
                                                        $checkOutDate = $reservation?->check_out_date;
                                                        $nights = max(1, $checkInDate && $checkOutDate ? $checkInDate->diffInDays($checkOutDate) : 1);
                                                    @endphp
                                                    <option value="{{ $checkIn->id }}" @selected(old('check_in_id') == $checkIn->id)>
                                                        Room {{ $room?->room_number ?? 'N/A' }} - {{ $guest?->full_name ?? 'Guest profile unavailable' }} ({{ $room?->room_type ?? 'Room' }} - {{ $nights }} {{ Str::plural('night', $nights) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if ($checkIns->isEmpty())
                                            <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800">No active check-ins are ready for check-out yet.</p>
                                        @else
                                            <p class="mt-2 text-xs text-slate-500">Choose the guest currently checking out from the hotel.</p>
                                        @endif
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="amount_paid" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-700">Amount paid <span class="text-error">*</span></label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-secondary">PHP</span>
                                                <input id="amount_paid" name="amount_paid" type="number" step="0.01" min="0" value="{{ old('amount_paid', 0) }}" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-3 pl-14 pr-4 text-sm font-semibold shadow-sm focus:border-secondary focus:ring-secondary">
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                                <span>Estimated balance: <strong id="balancePreview" class="text-primary">PHP 0.00</strong></span>
                                                <button id="payInFullButton" type="button" class="font-bold text-secondary hover:underline">Pay in full</button>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="payment_status" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-700">Payment status <span class="text-error">*</span></label>
                                            <div class="relative">
                                                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-secondary">payments</span>
                                                <select id="payment_status" name="payment_status" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest py-3 pl-11 pr-10 text-sm shadow-sm focus:border-secondary focus:ring-secondary" required>
                                                    <option value="unpaid" @selected(old('payment_status') === 'unpaid')>Unpaid</option>
                                                    <option value="partial" @selected(old('payment_status') === 'partial')>Partial</option>
                                                    <option value="paid" @selected(old('payment_status') === 'paid')>Paid</option>
                                                </select>
                                            </div>
                                            <p class="mt-2 text-xs text-slate-500">Use paid only when the folio balance has been fully settled.</p>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border border-surface-variant bg-surface-container-low p-4">
                                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-primary">Pre-departure checklist</p>
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                                <input type="checkbox" class="rounded border-outline-variant text-secondary focus:ring-secondary">
                                                Keys returned
                                            </label>
                                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                                <input type="checkbox" class="rounded border-outline-variant text-secondary focus:ring-secondary">
                                                Room inspected
                                            </label>
                                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                                <input type="checkbox" class="rounded border-outline-variant text-secondary focus:ring-secondary">
                                                Charges reviewed
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="mb-2 flex items-center justify-between">
                                            <label for="notes" class="block text-xs font-bold uppercase tracking-wide text-slate-700">Notes</label>
                                            <span class="text-xs text-slate-500">Optional operational log</span>
                                        </div>
                                        <textarea id="notes" name="notes" rows="4" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest p-3 text-sm shadow-sm focus:border-secondary focus:ring-secondary" placeholder="Enter check-out notes, payment details, room turnover remarks, or guest feedback...">{{ old('notes') }}</textarea>
                                    </div>
                                </div>

                                <div class="mt-7 flex flex-wrap items-center justify-between gap-3 border-t border-surface-variant pt-5">
                                    <div class="flex flex-wrap gap-3">
                                        <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-primary-container" @disabled($checkIns->isEmpty())>
                                            <span class="material-symbols-outlined text-[19px]">check_circle</span>
                                            Check Out
                                        </button>
                                        <button id="openFolioButton" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-3 text-sm font-bold text-primary shadow-sm transition hover:border-secondary">
                                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                            Print Receipt
                                        </button>
                                    </div>
                                    <a href="{{ route('check-outs.index') }}" class="px-3 py-2 text-sm font-bold text-slate-500 transition hover:text-error">Cancel</a>
                                </div>
                            </form>
                        </section>

                        <aside class="space-y-5 lg:col-span-4">
                            <section class="lux-card relative overflow-hidden rounded-xl border border-surface-variant bg-white p-5">
                                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-secondary-fixed via-secondary to-secondary-fixed"></div>
                                <div class="flex items-start justify-between pt-2">
                                    <div>
                                        <span class="rounded bg-secondary-fixed/60 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-[#5b4313]">Selected Stay</span>
                                        <h3 id="guestPreview" class="mt-2 text-lg font-bold">No guest selected</h3>
                                        <p id="emailPreview" class="text-sm text-slate-500">Choose an active check-in to view folio details.</p>
                                    </div>
                                    <div class="text-right">
                                        <p id="roomPreview" class="font-serif text-2xl font-bold">--</p>
                                        <p id="roomTypePreview" class="text-xs font-bold text-secondary">Room</p>
                                    </div>
                                </div>

                                <div class="my-4 grid grid-cols-2 gap-3 border-y border-surface-variant py-4 text-sm">
                                    <div>
                                        <p class="text-xs text-slate-500">Check-in</p>
                                        <p id="checkInPreview" class="font-bold">Not selected</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Scheduled check-out</p>
                                        <p id="checkOutPreview" class="font-bold">Not selected</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Folio number</p>
                                        <p id="folioPreview" class="font-mono text-xs font-bold">--</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Length of stay</p>
                                        <p id="nightsPreview" class="font-bold">0 nights</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <p class="text-xs font-bold uppercase tracking-wide">Folio breakdown</p>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Room charges</span>
                                        <span id="roomChargesPreview" class="font-bold">PHP 0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Other posted charges</span>
                                        <span class="font-bold">PHP 0.00</span>
                                    </div>
                                    <div class="border-t border-surface-variant pt-3">
                                        <div class="flex items-baseline justify-between">
                                            <span class="font-bold">Balance due</span>
                                            <span id="balanceDuePreview" class="font-serif text-2xl font-bold">PHP 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-xl border border-surface-variant bg-surface-container-low p-5">
                                <div class="flex gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-secondary">
                                        <span class="material-symbols-outlined text-[20px]">cleaning_services</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold">Room Turnover Notice</h3>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">After saving, the stay is marked completed, the room moves to cleaning, and the action is recorded in room status logs and transactions.</p>
                                    </div>
                                </div>
                            </section>
                        </aside>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="folioModal" class="no-print fixed inset-0 z-50 hidden items-center justify-center bg-primary/70 px-4 py-8">
        <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-surface-variant px-6 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Folio Receipt Preview</p>
                    <h2 class="mt-1 font-serif text-2xl font-bold">Guest Check-out Receipt</h2>
                </div>
                <button id="closeFolioButton" type="button" class="rounded-lg p-2 text-slate-500 transition hover:bg-surface-container hover:text-primary" aria-label="Close receipt preview">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div id="folioReceipt" class="bg-white p-6 text-primary">
                <div class="border-b border-surface-variant pb-5 text-center">
                    <p class="font-serif text-3xl font-bold">Le Charme Suites</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.24em] text-secondary">Subic Bay Philippines</p>
                    <p class="mt-3 text-sm text-slate-500">Official guest folio prepared by the Front Desk</p>
                </div>

                <div class="grid gap-4 border-b border-surface-variant py-5 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Guest</p>
                        <p id="receiptGuest" class="mt-1 text-lg font-bold">No guest selected</p>
                        <p id="receiptEmail" class="text-sm text-slate-500">--</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Folio Number</p>
                        <p id="receiptFolio" class="mt-1 font-mono text-lg font-bold">--</p>
                        <p id="receiptIssuedAt" class="text-sm text-slate-500">{{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p>
                    </div>
                </div>

                <div class="grid gap-4 border-b border-surface-variant py-5 sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-slate-500">Room</p>
                        <p id="receiptRoom" class="font-bold">--</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Room Type</p>
                        <p id="receiptRoomType" class="font-bold">--</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Check-in</p>
                        <p id="receiptCheckIn" class="font-bold">--</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Check-out</p>
                        <p id="receiptCheckOut" class="font-bold">--</p>
                    </div>
                </div>

                <div class="space-y-3 py-5">
                    <div class="flex justify-between text-sm">
                        <span>Room charges</span>
                        <span id="receiptRoomCharges" class="font-bold">PHP 0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Other posted charges</span>
                        <span class="font-bold">PHP 0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Amount paid</span>
                        <span id="receiptAmountPaid" class="font-bold">PHP 0.00</span>
                    </div>
                    <div class="flex justify-between border-t border-surface-variant pt-4 text-lg">
                        <span class="font-bold">Balance due</span>
                        <span id="receiptBalance" class="font-serif text-2xl font-bold">PHP 0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Payment status</span>
                        <span id="receiptPaymentStatus" class="font-bold uppercase">Unpaid</span>
                    </div>
                </div>

                <div class="rounded-lg border border-surface-variant bg-surface-container-low p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Front Desk Notes</p>
                    <p id="receiptNotes" class="mt-2 text-sm leading-6 text-slate-700">No notes entered.</p>
                </div>

                <div class="mt-6 grid gap-6 text-sm sm:grid-cols-2">
                    <div class="border-t border-slate-300 pt-2 text-center">Guest Signature</div>
                    <div class="border-t border-slate-300 pt-2 text-center">Front Desk Staff</div>
                </div>
            </div>

            <div class="no-print flex flex-wrap items-center justify-end gap-3 border-t border-surface-variant px-6 py-4">
                <button id="cancelFolioButton" type="button" class="rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-bold text-primary transition hover:border-secondary">Close</button>
                <button id="printReceiptButton" type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-primary-container">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    Print Receipt
                </button>
            </div>
        </div>
    </div>

    <script>
        const activeCheckIns = @json($activeCheckIns);
        const checkInSelect = document.getElementById('check_in_id');
        const amountInput = document.getElementById('amount_paid');
        const paymentStatusSelect = document.getElementById('payment_status');
        const notesInput = document.getElementById('notes');
        const payInFullButton = document.getElementById('payInFullButton');
        const openFolioButton = document.getElementById('openFolioButton');
        const folioModal = document.getElementById('folioModal');
        const closeFolioButton = document.getElementById('closeFolioButton');
        const cancelFolioButton = document.getElementById('cancelFolioButton');
        const printReceiptButton = document.getElementById('printReceiptButton');

        const currency = (value) => new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(Number(value || 0));

        function selectedStay() {
            return activeCheckIns.find((stay) => String(stay.id) === String(checkInSelect.value));
        }

        function updatePreview() {
            const stay = selectedStay();
            const data = stay || {
                guest: 'No guest selected',
                email: 'Choose an active check-in to view folio details.',
                room: '--',
                room_type: 'Room',
                check_in: 'Not selected',
                check_out: 'Not selected',
                folio: '--',
                nights: 0,
                room_total: 0,
                balance: 0,
            };

            document.getElementById('guestPreview').textContent = data.guest;
            document.getElementById('emailPreview').textContent = data.email;
            document.getElementById('roomPreview').textContent = data.room === '--' ? '--' : `Rm ${data.room}`;
            document.getElementById('roomTypePreview').textContent = data.room_type;
            document.getElementById('checkInPreview').textContent = data.check_in;
            document.getElementById('checkOutPreview').textContent = data.check_out;
            document.getElementById('folioPreview').textContent = data.folio;
            document.getElementById('nightsPreview').textContent = `${data.nights} ${Number(data.nights) === 1 ? 'night' : 'nights'}`;
            document.getElementById('roomChargesPreview').textContent = currency(data.room_total);
            document.getElementById('balanceDuePreview').textContent = currency(data.balance);
            document.getElementById('balancePreview').textContent = currency(data.balance);
        }

        checkInSelect.addEventListener('change', () => {
            const stay = selectedStay();
            if (stay && Number(amountInput.value || 0) === 0) {
                amountInput.value = Number(stay.balance).toFixed(2);
            }
            updatePreview();
        });

        payInFullButton.addEventListener('click', () => {
            const stay = selectedStay();
            amountInput.value = stay ? Number(stay.balance).toFixed(2) : '0.00';
        });

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
            const paymentStatusLabel = paymentStatusSelect.options[paymentStatusSelect.selectedIndex]?.text || 'Unpaid';
            const notes = notesInput.value.trim();

            document.getElementById('receiptGuest').textContent = stay.guest;
            document.getElementById('receiptEmail').textContent = stay.email;
            document.getElementById('receiptFolio').textContent = stay.folio;
            document.getElementById('receiptRoom').textContent = `Room ${stay.room}`;
            document.getElementById('receiptRoomType').textContent = stay.room_type;
            document.getElementById('receiptCheckIn').textContent = stay.check_in;
            document.getElementById('receiptCheckOut').textContent = stay.check_out;
            document.getElementById('receiptRoomCharges').textContent = currency(stay.room_total);
            document.getElementById('receiptAmountPaid').textContent = currency(amountPaid);
            document.getElementById('receiptBalance').textContent = currency(balance);
            document.getElementById('receiptPaymentStatus').textContent = paymentStatusLabel;
            document.getElementById('receiptNotes').textContent = notes || 'No notes entered.';
        }

        function openFolioModal() {
            const stay = selectedStay();
            if (!stay) {
                showFolioMessage('Please select an active guest/check-in first before printing a folio receipt.');
                return;
            }

            const existing = document.getElementById('folioActionMessage');
            if (existing) {
                existing.remove();
            }

            updateReceipt(stay);
            folioModal.classList.remove('hidden');
            folioModal.classList.add('flex');
        }

        function closeFolioModal() {
            folioModal.classList.add('hidden');
            folioModal.classList.remove('flex');
        }

        openFolioButton.addEventListener('click', openFolioModal);
        closeFolioButton.addEventListener('click', closeFolioModal);
        cancelFolioButton.addEventListener('click', closeFolioModal);
        printReceiptButton.addEventListener('click', () => window.print());
        folioModal.addEventListener('click', (event) => {
            if (event.target === folioModal) {
                closeFolioModal();
            }
        });

        updatePreview();
    </script>
</body>
</html>
