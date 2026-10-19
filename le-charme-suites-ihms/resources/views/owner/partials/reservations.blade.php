@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $confirmedReservations = $reservations->filter(fn ($reservation) => in_array($reservation->status, ['reserved', 'confirmed'], true))->count();
    $pendingReservations = $reservations->filter(fn ($reservation) => in_array($reservation->status, ['pending', 'awaiting_guarantee'], true))->count();
    $inHouseReservations = $reservations->where('status', 'checked_in')->count();
    $pipelineRevenue = $reservations->sum(fn ($reservation) => ($reservation->room?->rate ?? 0) * max(1, $reservation->check_in_date && $reservation->check_out_date ? $reservation->check_in_date->diffInDays($reservation->check_out_date) : 1));
    $statusStyles = [
        'checked_in' => 'bg-primary-container text-white border-primary-container',
        'reserved' => 'bg-secondary-fixed/40 text-secondary border-secondary-fixed',
        'confirmed' => 'bg-secondary-fixed/40 text-secondary border-secondary-fixed',
        'pending' => 'bg-surface-mid text-slate-600 border-line',
        'cancelled' => 'bg-error-container text-error border-error-container',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    ];
    $channelGroups = [
        ['label' => 'Direct Hotel & Concierge', 'amount' => $pipelineRevenue * .45, 'percent' => 45, 'class' => 'bg-primary-container'],
        ['label' => 'Corporate Direct Contracts', 'amount' => $pipelineRevenue * .30, 'percent' => 30, 'class' => 'bg-secondary'],
        ['label' => 'OTA / Web Channels', 'amount' => $pipelineRevenue * .25, 'percent' => 25, 'class' => 'bg-slate-400'],
    ];
    $filterStart = now('Asia/Manila')->toDateString();
    $filterEnd = now('Asia/Manila')->addDays(14)->toDateString();
@endphp

<section class="relative overflow-hidden rounded-2xl border border-secondary-fixed/20 bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-10 -right-10 opacity-[0.06]">
        <span class="material-symbols-outlined text-[260px]">hotel_class</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl space-y-3">
            <div class="inline-flex items-center gap-2 rounded border border-secondary-fixed/30 bg-secondary-fixed/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-secondary-fixed"></span>
                Executive Monitoring / Booking Pipeline
            </div>
            <h2 class="font-serif text-4xl font-extrabold leading-tight">Reservations Overview</h2>
            <p class="max-w-3xl text-sm leading-6 text-blue-100">
                Booking pipeline and reservation status monitoring across all Le Charmé suites. Real-time synchronisation with front desk, VIP concierge, and distribution channels.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-primary/60 p-4 sm:grid-cols-4 xl:min-w-[600px]">
            <div class="border-r border-white/10 px-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p>
                <p class="text-[10px] font-medium text-secondary-fixed">Tonight</p>
            </div>
            <div class="border-r border-white/10 px-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Rooms</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $availableRooms }}</p>
                <p class="text-[10px] text-blue-100/70">Available inventory</p>
            </div>
            <div class="border-r border-white/10 px-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Guests</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $arrivalsToday }}</p>
                <p class="text-[10px] text-blue-100/70">Pending arrival</p>
            </div>
            <div class="px-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Revenue</p>
                <p class="mt-1 truncate text-xl font-bold text-secondary-fixed">PHP {{ number_format($collectedToday, 2) }}</p>
                <p class="text-[10px] text-blue-100/70">Settled today</p>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Guest Management</span>
            <span class="flex h-8 w-8 items-center justify-center rounded bg-surface-mid text-secondary"><span class="material-symbols-outlined text-lg">badge</span></span>
        </div>
        <p class="font-serif text-3xl font-bold text-primary">{{ $totalGuests }}</p>
        <div class="mt-1 flex items-center justify-between text-xs text-slate-500"><span>Registered guest profiles</span><span class="font-semibold text-secondary">Directory</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Room Management</span>
            <span class="flex h-8 w-8 items-center justify-center rounded bg-surface-mid text-secondary"><span class="material-symbols-outlined text-lg">bed</span></span>
        </div>
        <p class="font-serif text-3xl font-bold text-primary">{{ $availableRooms }}</p>
        <div class="mt-1 flex items-center justify-between text-xs text-slate-500"><span>Available of {{ $totalRooms }} rooms</span><span class="font-bold text-secondary">{{ $occupiedRooms }} Occupied</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 ring-1 ring-secondary/20 transition hover:-translate-y-0.5">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-secondary">Reservations</span>
            <span class="flex h-8 w-8 items-center justify-center rounded bg-secondary-fixed/30 text-secondary"><span class="material-symbols-outlined text-lg">calendar_today</span></span>
        </div>
        <p class="font-serif text-3xl font-bold text-primary">{{ $activeReservations }}</p>
        <div class="mt-1 flex items-center justify-between text-xs text-slate-500"><span>{{ $arrivalsToday }} arrivals today</span><span class="rounded bg-surface-mid px-1.5 py-0.5 font-semibold text-primary">{{ $confirmedReservations }} Forward</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Billing & Transactions</span>
            <span class="flex h-8 w-8 items-center justify-center rounded bg-surface-mid text-secondary"><span class="material-symbols-outlined text-lg">payments</span></span>
        </div>
        <p class="truncate font-serif text-2xl font-bold text-primary">PHP {{ number_format($collectedToday, 2) }}</p>
        <div class="mt-1 flex items-center justify-between text-xs text-slate-500"><span>{{ $settledToday }} settled today</span><span class="inline-flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-secondary"></span>0 variance</span></div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-2xl">
    <div class="border-b border-line p-6">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h3 class="font-serif text-2xl font-bold text-primary">Reservation Pipeline</h3>
                <p class="mt-1 text-xs text-slate-500">Recent reservations and their current state. Forward bookings synchronized from Subic Bay Central concierge and distribution channels.</p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 rounded-lg bg-surface-mid p-1 text-xs font-bold" aria-label="Reservation status filters">
                <button type="button" class="rounded bg-white px-3 py-1.5 text-primary shadow-sm" data-reservation-filter="all">All (<span id="reservation-count-all">{{ $reservations->count() }}</span>)</button>
                <button type="button" class="px-3 py-1.5 text-slate-600 transition hover:text-primary" data-reservation-filter="confirmed">Confirmed (<span id="reservation-count-confirmed">{{ $confirmedReservations }}</span>)</button>
                <button type="button" class="px-3 py-1.5 text-slate-600 transition hover:text-primary" data-reservation-filter="pending">Pending (<span id="reservation-count-pending">{{ $pendingReservations }}</span>)</button>
                <button type="button" class="px-3 py-1.5 text-slate-600 transition hover:text-primary" data-reservation-filter="occupied">Occupied (<span id="reservation-count-occupied">{{ $inHouseReservations }}</span>)</button>
            </div>
        </div>
        <div class="mt-5 flex flex-col items-center gap-3 sm:flex-row">
            <div class="relative w-full flex-1">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input id="ownerReservationSearch" class="w-full rounded border border-line bg-surface py-2 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/30" placeholder="Search reservation #, guest name, contact, or booking record..." type="search" autocomplete="off">
            </div>
            <div class="flex w-full items-center gap-2 sm:w-auto">
                <label class="inline-flex items-center gap-2 rounded border border-line bg-surface px-3 py-1.5 text-xs text-slate-600">
                    <span class="material-symbols-outlined text-sm text-secondary">date_range</span>
                    <input id="ownerReservationStart" class="w-32 border-0 bg-transparent p-0 text-xs focus:ring-0" type="date" value="{{ $filterStart }}">
                    <span>-</span>
                    <input id="ownerReservationEnd" class="w-32 border-0 bg-transparent p-0 text-xs focus:ring-0" type="date" value="{{ $filterEnd }}">
                </label>
                <button id="ownerReservationReset" type="button" class="inline-flex items-center rounded border border-line bg-surface px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-secondary hover:text-primary"><span class="material-symbols-outlined mr-1 text-sm">restart_alt</span>Reset</button>
            </div>
        </div>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1080px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-5 py-3">Reservation</th>
                    <th class="px-4 py-3">Guest</th>
                    <th class="px-4 py-3">Room</th>
                    <th class="px-4 py-3">Stay Dates</th>
                    <th class="px-4 py-3">Channel</th>
                    <th class="px-4 py-3">Booking Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Audit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($reservations as $reservation)
                    @php
                        $nights = max(1, $reservation->check_in_date && $reservation->check_out_date ? $reservation->check_in_date->diffInDays($reservation->check_out_date) : 1);
                        $total = ($reservation->room?->rate ?? 0) * $nights;
                        $status = strtolower($reservation->status);
                    @endphp
                    @php
                        $statusGroup = match (true) {
                            in_array($status, ['reserved', 'confirmed'], true) => 'confirmed',
                            in_array($status, ['pending', 'awaiting_guarantee'], true) => 'pending',
                            $status === 'checked_in' => 'occupied',
                            default => $status,
                        };
                        $searchText = implode(' ', [
                            'RES-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT),
                            'Booking Record '.$reservation->id,
                            $reservation->guest?->full_name,
                            $reservation->guest?->email,
                            $reservation->room?->room_number,
                            $reservation->room?->room_type,
                            $reservation->status,
                        ]);
                    @endphp
                    <tr
                        @class(['transition hover:bg-surface-low', 'bg-secondary-fixed/10' => $status === 'checked_in'])
                        data-reservation-row
                        data-search="{{ e(strtolower($searchText)) }}"
                        data-status="{{ $statusGroup }}"
                        data-check-in="{{ $reservation->check_in_date?->toDateString() }}"
                        data-total="{{ $total }}"
                    >
                        <td class="px-5 py-3.5">
                            <div class="font-mono text-xs font-bold text-primary">#RES-{{ str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="text-[11px] text-slate-400">Booking Record #{{ $reservation->id }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5 font-semibold text-primary">
                                {{ $reservation->guest?->full_name ?? 'Guest Profile Pending' }}
                                @if (($reservation->guest?->reservations_count ?? 0) > 1)
                                    <span class="rounded bg-secondary-fixed/40 px-1.5 py-0.5 text-[10px] font-bold text-secondary">Returning</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400">{{ $reservation->guest?->email ?? 'No email recorded' }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-primary">Room {{ $reservation->room?->room_number ?? 'TBD' }}</div>
                            <div class="text-xs text-slate-500">{{ $reservation->room?->room_type ?? 'Pending assignment' }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-medium text-primary">{{ $reservation->check_in_date?->format('M d') ?? 'TBD' }} - {{ $reservation->check_out_date?->format('M d, Y') ?? 'TBD' }}</div>
                            <div class="text-xs font-semibold text-secondary">{{ $nights }} {{ \Illuminate\Support\Str::plural('Night', $nights) }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-primary"><span class="material-symbols-outlined text-sm text-secondary">concierge</span>Direct Front Desk</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-primary">PHP {{ number_format($total, 2) }}</div>
                            <span class="inline-block rounded bg-surface-mid px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">Owner read-only</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1 rounded border px-2.5 py-1 text-[11px] font-bold capitalize {{ $statusStyles[$status] ?? 'bg-surface-mid text-slate-600 border-line' }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ str_replace('_', ' ', $reservation->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="inline-flex items-center gap-1 rounded border border-line bg-white px-2.5 py-1 text-xs font-semibold text-primary"><span class="material-symbols-outlined text-sm text-secondary">visibility</span>Dossier</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No reservation records are waiting in the owner pipeline right now.</td>
                    </tr>
                @endforelse
                @if ($reservations->isNotEmpty())
                    <tr id="reservationNoMatches" class="hidden">
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No reservation records match the current owner filters.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-3 border-t border-line bg-surface px-5 py-4 text-xs text-slate-500 sm:flex-row">
        <span>Showing <strong id="reservationVisibleCount">{{ $reservations->count() }}</strong> active forward reservations</span>
        <span>Total Guaranteed Pipeline: <strong id="reservationVisibleTotal" class="text-primary">PHP {{ number_format($pipelineRevenue, 2) }}</strong></span>
    </div>
</section>

<section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="owner-card rounded-2xl p-6 lg:col-span-2">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h3 class="font-serif text-xl font-bold text-primary">Channel & Yield Distribution</h3>
                <p class="mt-1 text-xs text-slate-500">Booking origin breakdown and estimated room revenue contribution.</p>
            </div>
            <span class="rounded bg-secondary-fixed/30 px-2.5 py-1 text-xs font-bold text-secondary">Subic Bay Market Index</span>
        </div>
        <div class="space-y-4">
            @foreach ($channelGroups as $channel)
                <div>
                    <div class="mb-1 flex justify-between text-xs font-semibold">
                        <span class="flex items-center gap-1.5 text-primary"><span class="h-2.5 w-2.5 rounded-sm {{ $channel['class'] }}"></span>{{ $channel['label'] }}</span>
                        <span class="font-bold text-secondary">{{ $channel['percent'] }}% (PHP {{ number_format($channel['amount'], 2) }})</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded bg-surface-mid">
                        <div class="h-full {{ $channel['class'] }}" style="width: {{ $channel['percent'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-secondary-fixed/40 bg-surface-low p-6 shadow-sm">
        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg border border-secondary-fixed/50 bg-secondary-fixed/20 text-secondary">
            <span class="material-symbols-outlined">policy</span>
        </div>
        <h3 class="font-serif text-xl font-bold text-primary">Executive Advisory</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">
            Direct reservation creation, room rate discounting, and cancellation penalty waivers require department or administrator credentials under IHMP governance protocol.
        </p>
        <div class="mt-5 flex items-center justify-between border-t border-line pt-4 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-sm">security</span>Governance ID #880-SBMA</span>
            <span class="font-bold text-secondary">Audit Enabled</span>
        </div>
    </div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-reservation-row]'));
        if (!rows.length) {
            return;
        }

        const searchInput = document.getElementById('ownerReservationSearch');
        const startInput = document.getElementById('ownerReservationStart');
        const endInput = document.getElementById('ownerReservationEnd');
        const resetButton = document.getElementById('ownerReservationReset');
        const filterButtons = Array.from(document.querySelectorAll('[data-reservation-filter]'));
        const visibleCount = document.getElementById('reservationVisibleCount');
        const visibleTotal = document.getElementById('reservationVisibleTotal');
        const noMatches = document.getElementById('reservationNoMatches');
        let activeStatus = 'all';

        const peso = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2,
        });

        const setActiveButton = () => {
            filterButtons.forEach((button) => {
                const active = button.dataset.reservationFilter === activeStatus;
                button.classList.toggle('bg-white', active);
                button.classList.toggle('text-primary', active);
                button.classList.toggle('shadow-sm', active);
                button.classList.toggle('text-slate-600', !active);
            });
        };

        const applyFilters = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            const start = startInput?.value || '';
            const end = endInput?.value || '';
            let count = 0;
            let total = 0;

            rows.forEach((row) => {
                const textMatch = !query || row.dataset.search.includes(query);
                const statusMatch = activeStatus === 'all' || row.dataset.status === activeStatus;
                const checkIn = row.dataset.checkIn || '';
                const startMatch = !start || !checkIn || checkIn >= start;
                const endMatch = !end || !checkIn || checkIn <= end;
                const show = textMatch && statusMatch && startMatch && endMatch;

                row.classList.toggle('hidden', !show);
                if (show) {
                    count += 1;
                    total += Number(row.dataset.total || 0);
                }
            });

            if (visibleCount) {
                visibleCount.textContent = String(count);
            }

            if (visibleTotal) {
                visibleTotal.textContent = peso.format(total).replace('PHP', 'PHP ');
            }

            noMatches?.classList.toggle('hidden', count !== 0);
            setActiveButton();
        };

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeStatus = button.dataset.reservationFilter || 'all';
                applyFilters();
            });
        });

        [searchInput, startInput, endInput].forEach((control) => {
            control?.addEventListener('input', applyFilters);
            control?.addEventListener('change', applyFilters);
        });

        resetButton?.addEventListener('click', () => {
            activeStatus = 'all';
            if (searchInput) {
                searchInput.value = '';
            }
            if (startInput) {
                startInput.value = '{{ $filterStart }}';
            }
            if (endInput) {
                endInput.value = '{{ $filterEnd }}';
            }
            applyFilters();
        });

        applyFilters();
    })();
</script>
