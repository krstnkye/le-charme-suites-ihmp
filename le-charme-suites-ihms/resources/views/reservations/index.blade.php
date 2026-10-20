@php
    $user = auth()->user();
    $statusOptions = ['pending', 'reserved', 'checked_in', 'completed', 'cancelled'];
    $selected = $selectedReservation;
    $selectedGuest = $selected?->guest;
    $selectedRoom = $selected?->room;
    $selectedNights = $selected && $selected->check_in_date && $selected->check_out_date
        ? max(1, $selected->check_in_date->diffInDays($selected->check_out_date))
        : 1;
    $selectedTotal = $selectedRoom ? $selectedRoom->rate * $selectedNights : 0;
    $statusLabels = [
        'pending' => 'Online Request',
        'reserved' => 'Confirmed',
        'checked_in' => 'In House',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
        'reserved' => 'bg-secondary-fixed text-on-secondary-fixed-variant border-secondary/30',
        'checked_in' => 'bg-primary/10 text-primary border-primary/20',
        'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-error-container text-on-error-container border-error/20',
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - Le Charme Suites IHMP</title>
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
                        'secondary-fixed-dim': '#e6c185',
                        'on-secondary-fixed-variant': '#5b4313',
                        surface: '#fcf9f3',
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

        .bg-ihms-grid {
            background-color: #01162d;
            background-image: radial-gradient(rgba(255, 222, 170, 0.16) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface text-on-surface antialiased">
    <div class="flex min-h-screen">
        @include('partials.front-desk-sidebar', ['active' => 'reservations'])

        <div class="flex min-w-0 flex-1 flex-col lg:ml-64">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-variant bg-surface px-4 shadow-sm sm:px-6 lg:px-8">
                <form method="GET" action="{{ route('reservations.index') }}" class="relative w-full max-w-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-primary text-[18px]">search</span>
                    <input name="search" value="{{ $search }}" class="w-full rounded-lg border-outline-variant bg-white py-2 pl-10 pr-4 text-sm focus:border-secondary focus:ring-secondary" placeholder="Search reservation ID, guest, phone, or room">
                    @if ($status !== '')
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                </form>

                <div class="ml-4 flex items-center gap-4">
                    <div class="hidden text-right md:block">
                        <p class="text-xs font-bold uppercase tracking-wider text-primary">Role: {{ ucfirst($user?->role ?? 'Staff') }}</p>
                        <p class="text-[11px] text-on-surface-variant">{{ now()->format('M d, Y - h:i A') }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-bold text-secondary-fixed ring-2 ring-secondary-fixed-dim">
                        {{ strtoupper(substr($user?->name ?? 'S', 0, 1)) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 bg-ihms-grid p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    @if (session('status'))
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <section class="rounded-xl border border-outline-variant/70 bg-surface-container-lowest p-5 shadow-xl">
                        <div class="flex flex-col justify-between gap-4 border-b border-surface-variant pb-5 lg:flex-row lg:items-center">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Reservation Module</p>
                                <h2 class="mt-1 font-serif text-3xl font-semibold tracking-tight text-primary">Reservation Management & Booking Engine</h2>
                                <p class="mt-1 text-sm text-on-surface-variant">Search guests, manage bookings, check room assignments, and move arrivals into check-in.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-white px-3.5 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-surface-container">
                                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                    Front Desk
                                </a>
                                <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-secondary-fixed-dim bg-secondary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-secondary-fixed hover:text-on-secondary-fixed-variant">
                                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                                    New Reservation
                                </a>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('reservations.index') }}" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-outline-variant/60 bg-surface-container-low p-3.5">
                            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-3">
                                <div class="relative min-w-64 flex-1">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary text-[17px]">person_search</span>
                                    <input name="search" value="{{ $search }}" class="w-full rounded-md border-outline-variant bg-white py-2 pl-9 pr-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Guest name, email, room, or reservation ID">
                                </div>
                                <select name="status" class="rounded-md border-outline-variant bg-white py-2 text-sm font-semibold text-primary focus:border-secondary focus:ring-secondary">
                                    <option value="">All Statuses</option>
                                    @foreach ($statusOptions as $option)
                                        <option value="{{ $option }}" @selected($status === $option)>{{ $statusLabels[$option] }}</option>
                                    @endforeach
                                </select>
                                <button class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                                    <span class="material-symbols-outlined text-[17px]">filter_alt</span>
                                    Apply Filters
                                </button>
                            </div>
                            <p class="text-xs font-semibold text-on-surface-variant">
                                Showing <span class="text-primary">{{ $reservations->count() }}</span> of <span class="text-primary">{{ $reservations->total() }}</span> reservations
                            </p>
                        </form>
                    </section>

                    <section class="grid grid-cols-2 gap-3 md:grid-cols-5">
                        @foreach ($statusOptions as $option)
                            <a href="{{ route('reservations.index', ['status' => $option]) }}" class="rounded-xl border border-outline-variant/70 bg-surface-container-lowest p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ $statusLabels[$option] }}</span>
                                    <span class="material-symbols-outlined text-secondary text-[19px]">
                                        {{ $option === 'pending' ? 'language' : ($option === 'reserved' ? 'event_available' : ($option === 'checked_in' ? 'hotel' : ($option === 'completed' ? 'task_alt' : 'cancel'))) }}
                                    </span>
                                </div>
                                <p class="mt-3 text-3xl font-bold text-primary">{{ $statusCounts[$option] ?? 0 }}</p>
                            </a>
                        @endforeach
                    </section>

                    <section class="rounded-xl border border-outline-variant/70 bg-surface-container-lowest p-4 shadow-lg">
                        <div class="mb-4 flex items-center justify-between border-b border-surface-variant pb-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-secondary"></span>
                                <h3 class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Sequential Reservation Pipeline</h3>
                            </div>
                            <span class="rounded-full bg-secondary-fixed/60 px-3 py-1 text-[10px] font-bold uppercase text-on-secondary-fixed-variant">Ready for front desk handoff</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-4 xl:grid-cols-8">
                            @foreach ([
                                ['01', 'Guest', 'Profile verified', 'person_check'],
                                ['02', 'Dates', 'Stay schedule', 'date_range'],
                                ['03', 'Room', 'Availability check', 'bed'],
                                ['04', 'Rate', 'Room charge', 'payments'],
                                ['05', 'Notes', 'Special requests', 'edit_note'],
                                ['06', 'Status', 'Booking stage', 'verified'],
                                ['07', 'Audit', 'Recorded log', 'history'],
                                ['08', 'Confirm', 'Ready to check in', 'key'],
                            ] as $step)
                                <div class="{{ $loop->last ? 'bg-primary text-white border-secondary' : 'bg-white text-on-surface border-outline-variant/60' }} rounded-lg border p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="{{ $loop->last ? 'text-secondary-fixed' : 'text-secondary' }} text-[10px] font-bold uppercase">{{ $step[0] }}. {{ $step[1] }}</span>
                                        <span class="material-symbols-outlined {{ $loop->last ? 'text-secondary-fixed' : 'text-secondary' }} text-[16px]">{{ $step[3] }}</span>
                                    </div>
                                    <p class="{{ $loop->last ? 'text-blue-100/80' : 'text-on-surface-variant' }} mt-2 truncate text-xs font-semibold">{{ $step[2] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <div class="grid grid-cols-12 gap-6 items-start">
                        <section class="col-span-12 overflow-hidden rounded-xl border border-outline-variant/70 bg-surface-container-lowest shadow-lg xl:col-span-8">
                            <div class="flex flex-col gap-3 border-b border-surface-variant bg-surface-container-low px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary text-[21px]">table_restaurant</span>
                                    <h3 class="text-base font-bold text-primary">Master Reservations Registry</h3>
                                </div>
                                <a href="{{ route('reservations.index') }}" class="text-xs font-bold uppercase tracking-wider text-secondary hover:underline">Clear Filters</a>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[860px] text-left text-sm">
                                    <thead class="border-b border-surface-variant bg-surface-container text-xs font-bold uppercase tracking-wider text-primary">
                                        <tr>
                                            <th class="px-4 py-3">Res ID</th>
                                            <th class="px-4 py-3">Guest Profile</th>
                                            <th class="px-4 py-3">Room & Category</th>
                                            <th class="px-4 py-3">Dates / Nights</th>
                                            <th class="px-4 py-3">Total Rate</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-surface-variant">
                                        @forelse ($reservations as $reservation)
                                            @php
                                                $guest = $reservation->guest;
                                                $room = $reservation->room;
                                                $nights = $reservation->check_in_date && $reservation->check_out_date
                                                    ? max(1, $reservation->check_in_date->diffInDays($reservation->check_out_date))
                                                    : 1;
                                                $total = $room ? $room->rate * $nights : 0;
                                                $initials = strtoupper(substr($guest?->first_name ?? 'G', 0, 1) . substr($guest?->last_name ?? '', 0, 1));
                                            @endphp
                                            <tr class="{{ $loop->first ? 'border-l-4 border-secondary bg-secondary-fixed/15' : 'hover:bg-surface-container-low' }} transition-colors">
                                                <td class="px-4 py-3.5"><p class="font-bold text-primary">{{ $reservation->booking_reference ?: '#LC-'.str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</p><p class="text-[10px] font-bold uppercase text-slate-500">{{ match ($reservation->source) { 'public_website' => 'IHMP direct booking', 'hotel_website_api' => 'Official website API', default => str_replace('_', ' ', $reservation->source ?? 'front_desk') } }}</p></td>
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-xs font-bold text-secondary-fixed">{{ $initials }}</div>
                                                        <div>
                                                            <p class="font-bold text-on-surface">{{ $guest?->full_name ?? 'Guest Profile Missing' }}</p>
                                                            <p class="text-xs text-on-surface-variant">{{ $guest?->phone ?: $guest?->email ?: 'No contact on file' }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <p class="font-bold text-primary">Room {{ $room?->room_number ?? 'TBA' }}</p>
                                                    <p class="text-xs text-on-surface-variant">{{ $room?->room_type ?? 'Awaiting room assignment' }}</p>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <p class="font-semibold text-on-surface">
                                                        {{ $reservation->check_in_date?->format('M d') }} to {{ $reservation->check_out_date?->format('M d, Y') }}
                                                    </p>
                                                    <p class="text-xs font-semibold text-secondary">{{ $nights }} {{ $nights === 1 ? 'Night' : 'Nights' }} Stay</p>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <p class="font-bold text-primary">PHP {{ number_format($total, 2) }}</p>
                                                    <p class="text-[11px] text-on-surface-variant">PHP {{ number_format($room?->rate ?? 0, 2) }} / night</p>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusClasses[$reservation->status] ?? 'bg-surface-container text-primary border-outline-variant' }}">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                        {{ strtoupper($statusLabels[$reservation->status] ?? $reservation->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3.5 text-right">
                                                    <div class="flex items-center justify-end gap-1.5">
                                                        <a href="{{ route('reservations.show', $reservation) }}" class="rounded p-1 text-primary hover:bg-surface-container" title="View details">
                                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                                        </a>
                                                        <a href="{{ route('reservations.edit', $reservation) }}" class="rounded p-1 text-primary hover:bg-surface-container" title="Edit reservation">
                                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                                        </a>
                                                        @if ($reservation->status === 'reserved')
                                                            <a href="{{ route('check-ins.create', ['reservation_id' => $reservation->id]) }}" class="inline-flex items-center gap-1 rounded bg-primary px-2.5 py-1 text-[11px] font-bold text-white shadow-sm hover:bg-primary-container">
                                                                Check-In
                                                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                                            </a>
                                                        @elseif ($reservation->status === 'pending')
                                                            <form method="POST" action="{{ route('reservations.confirmation.store', $reservation) }}">
                                                                @csrf
                                                                <button class="inline-flex items-center gap-1 rounded bg-amber-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm hover:bg-amber-700" title="Confirm online booking">
                                                                    Confirm
                                                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-10 text-center text-sm font-semibold text-on-surface-variant">
                                                    No reservations found. Try a different search or create a new reservation.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="border-t border-surface-variant bg-surface-container-low px-4 py-3">
                                {{ $reservations->links() }}
                            </div>
                        </section>

                        <aside class="col-span-12 overflow-hidden rounded-xl border-2 border-secondary/40 bg-surface-container-lowest shadow-xl xl:col-span-4">
                            @if ($selected)
                                <div class="bg-primary p-5 text-white">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="rounded bg-secondary px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Record Preview</span>
                                        <span class="font-mono text-xs font-bold text-secondary-fixed">#LC-{{ str_pad($selected->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <h4 class="mt-3 font-serif text-2xl font-semibold">{{ $selectedGuest?->full_name ?? 'Guest Profile Missing' }}</h4>
                                    <p class="text-xs text-blue-100/70">{{ $selectedGuest?->email ?? 'No email on file' }}</p>
                                </div>

                                <div class="space-y-5 p-5">
                                    <div>
                                        <span class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-outline">Guest Profile</span>
                                        <div class="grid grid-cols-2 gap-2 rounded-lg border border-outline-variant/60 bg-white p-3 text-xs">
                                            <div>
                                                <span class="block text-[11px] text-outline">Contact Phone</span>
                                                <span class="font-bold text-primary">{{ $selectedGuest?->phone ?: 'Not provided' }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[11px] text-outline">Guest ID</span>
                                                <span class="font-bold text-primary">GST-{{ str_pad($selectedGuest?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <div class="col-span-2 border-t border-surface-variant pt-2">
                                                <span class="block text-[11px] text-outline">Identification</span>
                                                <span class="font-bold text-primary">
                                                    {{ $selectedGuest?->identification_type ?: 'Not provided' }}
                                                    {{ $selectedGuest?->identification_number ? '- ' . $selectedGuest->identification_number : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-outline">Room Allocation & Schedule</span>
                                        <div class="space-y-3 rounded-lg border border-outline-variant/60 bg-white p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-secondary text-[22px]">bedroom_parent</span>
                                                    <div>
                                                        <span class="block text-base font-bold leading-none text-primary">Room {{ $selectedRoom?->room_number ?? 'TBA' }}</span>
                                                        <span class="text-xs text-outline">{{ $selectedRoom?->room_type ?? 'Awaiting room assignment' }}</span>
                                                    </div>
                                                </div>
                                                <span class="rounded bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary">{{ $selectedRoom?->capacity ?? 0 }} PAX</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 border-t border-surface-variant pt-2 text-xs">
                                                <div>
                                                    <span class="block text-[11px] text-outline">Arrival</span>
                                                    <span class="font-bold text-primary">{{ $selected->check_in_date?->format('M d, Y') }}</span>
                                                </div>
                                                <div>
                                                    <span class="block text-[11px] text-outline">Checkout</span>
                                                    <span class="font-bold text-primary">{{ $selected->check_out_date?->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-outline">Billing Snapshot</span>
                                        <div class="space-y-2 rounded-lg border border-secondary/30 bg-surface-container-low p-3 text-xs">
                                            <div class="flex justify-between">
                                                <span class="text-on-surface-variant">Nightly room charge</span>
                                                <span class="font-bold text-primary">PHP {{ number_format($selectedRoom?->rate ?? 0, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-on-surface-variant">{{ $selectedNights }} {{ $selectedNights === 1 ? 'night' : 'nights' }} total</span>
                                                <span class="font-bold text-primary">PHP {{ number_format($selectedTotal, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between border-t border-surface-variant pt-2">
                                                <span class="font-bold text-primary">Reservation Status</span>
                                                <span class="font-bold text-secondary">{{ $statusLabels[$selected->status] ?? ucfirst($selected->status) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2 pt-1">
                                        @if ($selected->status === 'reserved')
                                            <a href="{{ route('check-ins.create', ['reservation_id' => $selected->id]) }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                                                <span class="material-symbols-outlined text-[18px] text-secondary-fixed">assignment_turned_in</span>
                                                Proceed to Check-In Console
                                            </a>
                                        @endif
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('reservations.edit', $selected) }}" class="flex items-center justify-center gap-1.5 rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm font-bold text-primary transition hover:bg-surface-container">
                                                <span class="material-symbols-outlined text-[16px]">edit_calendar</span>
                                                Edit
                                            </a>
                                            <a href="{{ route('reservations.show', $selected) }}" class="flex items-center justify-center gap-1.5 rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm font-bold text-primary transition hover:bg-surface-container">
                                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                                View
                                            </a>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 rounded-lg border border-outline-variant/40 bg-white p-2.5 text-[11px] text-on-surface-variant">
                                        <span class="material-symbols-outlined text-outline text-[16px]">verified_user</span>
                                        <span>Audit stamp: reservation data loaded from the IHMP database.</span>
                                    </div>
                                </div>
                            @else
                                <div class="p-8 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-surface-container text-secondary">
                                        <span class="material-symbols-outlined text-3xl">event_busy</span>
                                    </div>
                                    <h4 class="mt-4 font-serif text-xl font-semibold text-primary">No Reservation Selected</h4>
                                    <p class="mt-2 text-sm text-on-surface-variant">Create a new reservation or adjust the filters to show a booking preview.</p>
                                    <a href="{{ route('reservations.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                        New Reservation
                                    </a>
                                </div>
                            @endif
                        </aside>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
