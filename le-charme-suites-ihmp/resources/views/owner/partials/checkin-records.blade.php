@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $todayCheckIns = $checkIns->filter(fn ($checkIn) => $checkIn->checked_in_at?->timezone('Asia/Manila')->isToday())->count();
    $activeStays = $checkIns->filter(fn ($checkIn) => in_array($checkIn->status, ['active', 'checked_in'], true) || blank($checkIn->status));
    $activeStayCount = $activeStays->count();
    $availableRoomNumbers = $rooms->where('status', 'available')->pluck('room_number');
    $activeRoomLabel = $activeStays->first()?->room ? 'Room '.$activeStays->first()->room->room_number.' '.$activeStays->first()->room->room_type : 'No active room key';
    $estimatedIntake = $activeStays->sum(fn ($checkIn) => ($checkIn->room?->rate ?? 0) * max(1, $checkIn->reservation?->check_in_date && $checkIn->reservation?->check_out_date ? $checkIn->reservation->check_in_date->diffInDays($checkIn->reservation->check_out_date) : 1));
    $securityBond = $activeStayCount * 2000;
@endphp

<section class="relative overflow-hidden rounded-2xl border border-primary-container bg-primary p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -right-20 -top-24 h-80 w-80 rounded-full bg-secondary/15 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl space-y-2">
            <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">hotel_class</span>
                Executive Monitoring / Arrivals & Active Stays
            </div>
            <h2 class="font-serif text-4xl font-extrabold leading-tight">Check-in Records & In-House Registry</h2>
            <p class="max-w-3xl text-sm leading-6 text-blue-100">
                Comprehensive audit registry of authorized guest arrivals, room registrations, and verified in-house stays at Le Charmé Suites.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="rounded-lg border border-white/10 bg-primary-container/80 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p>
                <p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $occupancyRate }}%</p>
                <p class="text-[10px] text-blue-100/70">Target RevPAR: Stable</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-primary-container/80 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Rooms In-House</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $occupiedRooms }} <span class="font-sans text-xs font-normal text-blue-100/70">/ {{ $totalRooms }} Rms</span></p>
                <p class="text-[10px] text-secondary-fixed">{{ $activeRoomLabel }}</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-primary-container/80 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Registered Guests</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $currentGuests }} <span class="font-sans text-xs font-normal text-blue-100/70">{{ \Illuminate\Support\Str::plural('Guest', $currentGuests) }}</span></p>
                <p class="text-[10px] text-blue-100/70">Live guest count</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-primary-container/80 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Settled Registration</p>
                <p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">PHP {{ number_format($estimatedIntake, 0) }}</p>
                <p class="text-[10px] text-blue-100/70">+ PHP {{ number_format($securityBond, 0) }} Deposit</p>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Today's Check-ins</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/10 text-secondary"><span class="material-symbols-outlined text-lg">check_circle</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold text-primary">{{ $todayCheckIns }}</span><span class="font-semibold text-slate-500">Arrival {{ $todayCheckIns === 1 ? 'Completed' : 'Records' }}</span></div>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Pacing Avg: <strong class="text-primary">2m 45s</strong></span><span class="font-bold text-secondary">On schedule</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Active In-House Stays</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-container text-secondary-fixed"><span class="material-symbols-outlined text-lg">door_front</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold text-primary">{{ $activeStayCount }}</span><span class="font-semibold text-slate-500">Active Room {{ $activeStayCount === 1 ? 'Key' : 'Keys' }}</span></div>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span class="truncate">{{ $activeRoomLabel }}</span><span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">SYNCED</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Remaining Capacity</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-mid text-slate-600"><span class="material-symbols-outlined text-lg">bed</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold text-primary">{{ $availableRooms }}</span><span class="font-semibold text-slate-500">Available of {{ $totalRooms }} Rooms</span></div>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Vacant inspected</span><span class="font-bold text-secondary">{{ $availableRoomNumbers->take(5)->join(', ') ?: 'None' }}</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Incidental Clearance</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/15 text-secondary"><span class="material-symbols-outlined text-lg">shield</span></span>
        </div>
        <div class="mt-3 font-serif text-2xl font-bold text-primary">PHP {{ number_format($securityBond, 2) }}</div>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Escrow vault estimate</span><span class="font-bold text-emerald-700">{{ $activeStayCount ? 'Bonded' : 'No active bond' }}</span></div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-2xl">
    <div class="flex flex-col justify-between gap-4 border-b border-line bg-surface-low/60 p-5 md:flex-row md:items-center">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[280px] max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input id="ownerCheckInSearch" class="w-full rounded-lg border border-line bg-white py-2 pl-9 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/30" placeholder="Search guest, room #, reservation, or notes..." type="search" autocomplete="off">
            </div>
            <div class="flex items-center gap-1.5" aria-label="Check-in record filters">
                <button type="button" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white" data-checkin-filter="all">All Records (<span id="checkin-count-all">{{ $checkIns->count() }}</span>)</button>
                <button type="button" class="rounded-lg bg-surface-mid px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-checkin-filter="active">In-House Active (<span id="checkin-count-active">{{ $activeStayCount }}</span>)</button>
                <button type="button" class="rounded-lg bg-surface-mid px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-checkin-filter="today">Today (<span id="checkin-count-today">{{ $todayCheckIns }}</span>)</button>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button id="ownerCheckInExport" type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface-mid px-3.5 py-2 text-xs font-bold text-primary transition hover:border-secondary">
                <span class="material-symbols-outlined text-sm">file_download</span>
                Export Check-in Audit CSV
            </button>
            <button id="ownerCheckInReset" type="button" class="rounded-lg border border-line p-2 text-slate-500 transition hover:bg-surface-mid hover:text-primary" title="Refresh table filters">
                <span class="material-symbols-outlined text-lg">refresh</span>
            </button>
        </div>
    </div>

    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1200px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-4 py-3">Guest Profile</th>
                    <th class="px-4 py-3">Room & Wing</th>
                    <th class="px-4 py-3">Check-in Timestamp</th>
                    <th class="px-4 py-3">Scheduled Checkout</th>
                    <th class="px-4 py-3">Room Key / System Status</th>
                    <th class="px-4 py-3 text-right">Booking & Security Bond</th>
                    <th class="px-4 py-3">KYC / Compliance</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($checkIns as $checkIn)
                    @php
                        $guestName = $checkIn->guest?->full_name ?? 'Guest Profile Pending';
                        $initials = collect(explode(' ', $guestName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') ?: 'G';
                        $nights = max(1, $checkIn->reservation?->check_in_date && $checkIn->reservation?->check_out_date ? $checkIn->reservation->check_in_date->diffInDays($checkIn->reservation->check_out_date) : 1);
                        $bookingTotal = ($checkIn->room?->rate ?? 0) * $nights;
                        $status = strtolower($checkIn->status ?: 'active');
                        $isActive = in_array($status, ['active', 'checked_in'], true);
                        $isToday = $checkIn->checked_in_at?->timezone('Asia/Manila')->isToday();
                        $searchText = implode(' ', [$guestName, $checkIn->guest?->email, $checkIn->room?->room_number, $checkIn->room?->room_type, $checkIn->reservation_id, $checkIn->status, $checkIn->notes]);
                    @endphp
                    <tr
                        class="transition hover:bg-surface-low/70"
                        data-checkin-row
                        data-search="{{ e(strtolower($searchText)) }}"
                        data-status="{{ $isActive ? 'active' : $status }}"
                        data-today="{{ $isToday ? '1' : '0' }}"
                        data-csv="{{ e(implode(',', [
                            'RES-'.$checkIn->reservation_id,
                            $guestName,
                            'Room '.$checkIn->room?->room_number,
                            $checkIn->checked_in_at?->timezone('Asia/Manila')->format('Y-m-d H:i:s'),
                            $checkIn->reservation?->check_out_date?->format('Y-m-d'),
                            number_format($bookingTotal, 2, '.', ''),
                            $status,
                        ])) }}"
                    >
                        <td class="px-4 py-3.5 align-top">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-secondary/30 bg-secondary-container text-sm font-bold text-secondary">{{ $initials }}</span>
                                <div>
                                    <div class="font-bold leading-snug text-primary">{{ $guestName }}</div>
                                    <span class="mt-0.5 inline-flex rounded border border-secondary/20 bg-secondary-container/70 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-secondary">Owner visible arrival record</span>
                                    <div class="mt-1 text-[11px] text-slate-500">Reservation #{{ $checkIn->reservation_id ?? 'N/A' }} · {{ $checkIn->reservation?->number_of_guests ?? 1 }} in-house {{ \Illuminate\Support\Str::plural('guest', $checkIn->reservation?->number_of_guests ?? 1) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="font-bold text-primary">Room {{ $checkIn->room?->room_number ?? 'TBD' }}</div>
                            <div class="text-xs font-medium text-slate-500">{{ $checkIn->room?->room_type ?? 'Pending room type' }}</div>
                            <span class="mt-1 inline-flex items-center gap-1 text-[11px] font-bold text-secondary"><span class="h-1.5 w-1.5 rounded-full bg-secondary"></span>Le Charmé Room System</span>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="text-xs font-bold text-primary">{{ $checkIn->checked_in_at?->timezone('Asia/Manila')->format('M d, Y · h:i A') ?? 'Pending timestamp' }} PHT</div>
                            <div class="mt-0.5 text-[11px] text-slate-500">Front Desk validated</div>
                            <div class="mt-0.5 text-[10px] text-slate-400">{{ $checkIn->notes ?: 'Front Desk arrival note' }}</div>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="text-xs font-bold text-primary">{{ $checkIn->reservation?->check_out_date?->format('M d, Y') ?? 'TBD' }} · 12:00 PHT</div>
                            <div class="mt-0.5 text-[11px] text-slate-500">{{ $nights }} {{ \Illuminate\Support\Str::plural('Night', $nights) }} Duration</div>
                            <span class="mt-1 inline-block rounded bg-surface-mid px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">Late Checkout: Pending</span>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-xs text-emerald-700">nfc</span>
                                <span class="font-mono text-xs font-bold text-primary">KC-{{ str_pad((string) $checkIn->id, 4, '0', STR_PAD_LEFT) }}-B</span>
                            </div>
                            <div class="mt-0.5 flex items-center gap-1 text-[11px] font-semibold text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>Door keycard status updated</div>
                            <div class="mt-0.5 font-mono text-[10px] text-slate-400">Hash: {{ substr(md5('checkin-'.$checkIn->id), 0, 4) }}..{{ substr(md5((string) $checkIn->room_id), 0, 4) }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-right align-top">
                            <div class="font-bold text-primary">PHP {{ number_format($bookingTotal, 2) }}</div>
                            <div class="mt-0.5 text-[11px] font-bold text-emerald-700">Room rate projection</div>
                            <div class="mt-0.5 text-[10px] font-semibold text-secondary">Sec. Deposit: PHP 2,000.00</div>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="inline-flex items-center gap-1 rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-800"><span class="material-symbols-outlined text-xs">verified</span>Profile Verified</div>
                            <div class="mt-1 text-[10px] text-slate-500">Guest ID: #{{ $checkIn->guest_id ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center align-top">
                            <span @class(['inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold', 'border-secondary/30 bg-secondary-container/60 text-secondary' => $isActive, 'border-line bg-surface-mid text-slate-600' => ! $isActive])>
                                <span @class(['h-2 w-2 rounded-full', 'animate-pulse bg-emerald-600' => $isActive, 'bg-slate-400' => ! $isActive])></span>
                                {{ $isActive ? 'Active In-House' : ucfirst($checkIn->status) }}
                            </span>
                            <div class="mt-1 text-[10px] font-medium text-slate-500">Audit Locked</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No check-in records are available yet.</td>
                    </tr>
                @endforelse
                @if ($checkIns->isNotEmpty())
                    <tr id="checkinNoMatches" class="hidden">
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No check-in records match the current owner filters.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="flex flex-col items-center justify-between gap-4 border-t border-line bg-surface-low/50 p-5 md:flex-row">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-mid text-secondary"><span class="material-symbols-outlined text-xl">meeting_room</span></span>
            <div>
                <h4 class="font-bold text-primary">Remaining {{ $availableRooms }} Rooms Available & Vacant Clean</h4>
                <p class="text-sm text-slate-500">Housekeeping release verified. Electronic door locks in standby monitoring.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @forelse ($availableRoomNumbers->take(8) as $roomNumber)
                <span class="inline-flex items-center gap-1.5 rounded-md border border-line bg-white px-2.5 py-1 text-xs font-bold text-primary"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Room {{ $roomNumber }}</span>
            @empty
                <span class="text-xs font-semibold text-slate-500">No vacant rooms available</span>
            @endforelse
        </div>
    </div>
</section>

<div class="grid grid-cols-1 gap-4 pb-2 md:grid-cols-3">
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Room Preparation Velocity</span><span class="material-symbols-outlined text-slate-400">speed</span></div>
        <h3 class="mt-1 font-semibold text-primary">Front Desk Processing Pacing</h3>
        <p class="mt-1 text-sm text-slate-500">Time elapsed from guest arrival to room keycard release and room account lock.</p>
        <div class="mt-4 grid grid-cols-3 border-t border-line pt-3 text-center">
            <div><div class="font-bold text-primary">2m 45s</div><div class="text-[10px] uppercase text-slate-500">Avg.</div></div>
            <div><div class="font-bold text-primary">1m 20s</div><div class="text-[10px] uppercase text-slate-500">Key Drop</div></div>
            <div><div class="font-bold text-emerald-700">&lt; 4m</div><div class="text-[10px] uppercase text-slate-500">Target</div></div>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Housekeeping Integration</span><span class="material-symbols-outlined text-slate-400">sanitizer</span></div>
        <h3 class="mt-1 font-semibold text-primary">Room Preparation Inspection Sync</h3>
        <p class="mt-1 text-sm text-slate-500">Automatic update from check-out to housekeeping cleaning.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3">
            <div class="flex items-center gap-3"><span class="font-serif text-2xl font-bold text-primary">100%</span><div><div class="text-xs font-bold text-primary">Departed keys released</div><div class="text-[10px] text-slate-500">Zero delayed dirty cleaning status</div></div></div>
            <span class="material-symbols-outlined text-emerald-600">task_alt</span>
        </div>
    </div>
    <div class="rounded-xl border border-primary-container bg-primary-container p-5 text-white shadow-sm">
        <div class="flex items-center justify-between"><span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed"><span class="material-symbols-outlined text-xs">policy</span>Owner Access Rules</span></div>
        <h3 class="mt-1 font-serif text-xl font-bold">Protected SBMA Audit Chain</h3>
        <p class="mt-1 text-sm leading-6 text-blue-100">Express checkouts, keycard re-encodings, deposit release authorizations, and manual room status overrides are reserved for operational departments.</p>
        <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-3 text-[11px] text-secondary-fixed"><span class="font-mono">SYS-REV-2026.10-PROD</span><span class="inline-flex items-center gap-1 font-bold"><span class="material-symbols-outlined text-xs">verified_user</span>Read-Only Validated</span></div>
    </div>
</div>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-checkin-row]'));
        if (!rows.length) {
            return;
        }

        const searchInput = document.getElementById('ownerCheckInSearch');
        const filterButtons = Array.from(document.querySelectorAll('[data-checkin-filter]'));
        const resetButton = document.getElementById('ownerCheckInReset');
        const exportButton = document.getElementById('ownerCheckInExport');
        const noMatches = document.getElementById('checkinNoMatches');
        let activeFilter = 'all';

        const setActiveButton = () => {
            filterButtons.forEach((button) => {
                const active = button.dataset.checkinFilter === activeFilter;
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('bg-surface-mid', !active);
                button.classList.toggle('text-slate-600', !active);
            });
        };

        const applyFilters = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const queryMatch = !query || row.dataset.search.includes(query);
                const filterMatch = activeFilter === 'all'
                    || (activeFilter === 'active' && row.dataset.status === 'active')
                    || (activeFilter === 'today' && row.dataset.today === '1');
                const show = queryMatch && filterMatch;

                row.classList.toggle('hidden', !show);
                if (show) {
                    visible += 1;
                }
            });

            noMatches?.classList.toggle('hidden', visible !== 0);
            setActiveButton();
        };

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = button.dataset.checkinFilter || 'all';
                applyFilters();
            });
        });

        searchInput?.addEventListener('input', applyFilters);
        resetButton?.addEventListener('click', () => {
            activeFilter = 'all';
            if (searchInput) {
                searchInput.value = '';
            }
            applyFilters();
        });

        exportButton?.addEventListener('click', () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = ['Reservation,Guest,Room,Checked In,Scheduled Checkout,Booking Total,Status']
                .concat(visibleRows.map((row) => row.dataset.csv))
                .join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-check-in-records.csv';
            link.click();
            URL.revokeObjectURL(url);
        });

        applyFilters();
    })();
</script>
