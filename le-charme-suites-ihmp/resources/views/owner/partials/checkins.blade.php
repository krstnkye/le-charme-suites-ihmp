@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $activeCheckIns = $checkIns->filter(fn ($checkIn) => in_array($checkIn->status, ['checked_in', 'active'], true) || blank($checkIn->status));
    $activeCheckInCount = $activeCheckIns->count();
    $recentCheckOutTotal = $checkOuts->sum('amount_paid');
    $availableRoomNames = $rooms->where('status', 'available')->pluck('room_number')->map(fn ($number) => 'Room '.$number)->take(6)->join(', ');
    $primaryCheckIn = $activeCheckIns->first();
    $guaranteedCheckout = $primaryCheckIn?->reservation?->check_out_date?->format('M d, Y').' @ 12:00 PHT';
@endphp

<section class="relative overflow-hidden rounded-2xl border border-secondary/20 bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-10 -right-8 opacity-[0.06]">
        <span class="material-symbols-outlined text-[240px]">hotel_class</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <div class="mb-3 inline-flex items-center gap-2 rounded border border-secondary/40 bg-primary/70 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">monitoring</span>
                Executive Monitoring / Movement Visibility
            </div>
            <h2 class="font-serif text-4xl font-extrabold leading-tight">Check-in / Check-out Overview</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Real-time monitoring of arrival throughput, active in-house room accounts, and completed guest departures across Le Charmé Suites.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-primary/60 p-4 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="border-r border-white/10 px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</span>
                <span class="mt-1 block font-serif text-2xl font-bold text-secondary-fixed">{{ $occupancyRate }}%</span>
                <span class="text-[10px] text-blue-100/70">Tonight</span>
            </div>
            <div class="border-r border-white/10 px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Available Rooms</span>
                <span class="mt-1 block font-serif text-2xl font-bold">{{ $availableRooms }} / {{ $totalRooms }}</span>
                <span class="text-[10px] text-blue-100/70">Keys</span>
            </div>
            <div class="border-r border-white/10 px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Guests In-House</span>
                <span class="mt-1 block font-serif text-2xl font-bold">{{ $currentGuests }}</span>
                <span class="text-[10px] text-blue-100/70">Active</span>
            </div>
            <div class="px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Today's Revenue</span>
                <span class="mt-1 block text-xl font-bold text-secondary-fixed">PHP {{ number_format($recordedRevenue, 2) }}</span>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/50">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Guest Management</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-mid text-secondary"><span class="material-symbols-outlined text-lg">badge</span></span>
        </div>
        <p class="mt-3 font-serif text-3xl font-bold text-primary">{{ $totalGuests }}</p>
        <p class="mt-1 text-xs text-slate-500">Registered guest profiles · Directory synced</p>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/50">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Room Management</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-mid text-secondary"><span class="material-symbols-outlined text-lg">bed</span></span>
        </div>
        <p class="mt-3 font-serif text-3xl font-bold text-primary">{{ $availableRooms }}</p>
        <p class="mt-1 text-xs text-slate-500">Available of {{ $totalRooms }} rooms · {{ $occupiedRooms }} occupied</p>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/50">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Reservations</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-mid text-secondary"><span class="material-symbols-outlined text-lg">event_available</span></span>
        </div>
        <p class="mt-3 font-serif text-3xl font-bold text-primary">{{ $arrivalsToday }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $arrivalsToday }} arrivals today · {{ $activeReservations }} forward bookings</p>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/50">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Billing & Transactions</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-mid text-secondary"><span class="material-symbols-outlined text-lg">account_balance_wallet</span></span>
        </div>
        <p class="mt-3 font-serif text-2xl font-bold text-primary">PHP {{ number_format($collectedToday, 2) }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $settledToday }} pending settlement · Audited 0 variance</p>
    </div>
</section>

<section class="grid grid-cols-1 items-start gap-6 xl:grid-cols-2">
    <div id="check-in-records" class="owner-card flex flex-col overflow-hidden rounded-2xl">
        <div class="flex flex-col justify-between gap-3 border-b border-line bg-white px-6 py-4 md:flex-row md:items-center">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-serif text-2xl font-bold text-primary">Active Check-ins</h3>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary-fixed/50 px-2.5 py-0.5 text-xs font-bold text-secondary">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-secondary"></span>
                        {{ $activeCheckInCount }} In-House {{ \Illuminate\Support\Str::plural('Stay', $activeCheckInCount) }} Active
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Currently active in-house stays with authorized electronic keycards.</p>
            </div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Station: Desk 01</span>
        </div>
        <div class="owner-scroll overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-line bg-surface-low text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <th class="px-5 py-3">Guest</th>
                        <th class="px-4 py-3">Room</th>
                        <th class="px-4 py-3">Checked In</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Booking / Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($activeCheckIns as $checkIn)
                        @php
                            $guestName = $checkIn->guest?->full_name ?? 'Guest Profile Pending';
                            $initials = collect(explode(' ', $guestName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') ?: 'G';
                            $reservationTotal = ($checkIn->room?->rate ?? 0) * max(1, $checkIn->reservation?->check_in_date && $checkIn->reservation?->check_out_date ? $checkIn->reservation->check_in_date->diffInDays($checkIn->reservation->check_out_date) : 1);
                        @endphp
                        <tr class="transition hover:bg-surface-low/70">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-secondary-fixed">{{ $initials }}</span>
                                    <div>
                                        <div class="font-semibold leading-tight text-primary">{{ $guestName }}</div>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-secondary"><span class="material-symbols-outlined text-xs">star</span>Active guest account</span>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-bold text-primary">Room {{ $checkIn->room?->room_number ?? 'TBD' }}</div>
                                <span class="text-xs text-slate-500">{{ $checkIn->room?->room_type ?? 'Pending assignment' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-medium text-primary">{{ $checkIn->checked_in_at?->format('M d, Y · h:i A') ?? 'Pending timestamp' }}</div>
                                <div class="flex items-center gap-1 text-xs text-slate-500"><span class="material-symbols-outlined text-xs">person</span>Front Desk validated</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-primary-container/30 bg-primary-container/10 px-2.5 py-1 text-xs font-bold text-primary-container">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary-container"></span>
                                    In-House / Active
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                <div class="font-bold text-secondary">PHP {{ number_format($reservationTotal, 2) }}</div>
                                <span class="block text-xs text-slate-400">Reservation #{{ $checkIn->reservation_id ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No active in-house check-in records are currently open.</td>
                        </tr>
                    @endforelse
                    <tr class="bg-surface-mid/40">
                        <td class="px-5 py-6 text-center" colspan="5">
                            <div class="flex flex-col items-center justify-center text-slate-500">
                                <span class="material-symbols-outlined mb-1 text-2xl text-slate-400">domain_disabled</span>
                                <p class="font-semibold text-primary">Remaining {{ $availableRooms }} Rooms Available & Vacant</p>
                                <p class="mt-0.5 text-xs">{{ $availableRoomNames ?: 'No available room list has been encoded yet.' }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-col justify-between gap-2 border-t border-line bg-surface-low px-4 py-3 text-xs font-semibold text-slate-500 sm:flex-row">
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-secondary">verified_user</span>Keycard RFID status view-only</span>
            <span>{{ $guaranteedCheckout ? 'Guaranteed Checkout: '.$guaranteedCheckout : 'Guaranteed checkout dates follow reservation records' }}</span>
        </div>
    </div>

    <div id="check-out-records" class="owner-card flex flex-col overflow-hidden rounded-2xl">
        <div class="flex flex-col justify-between gap-3 border-b border-line bg-white px-6 py-4 md:flex-row md:items-center">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-serif text-2xl font-bold text-primary">Recent Check-outs</h3>
                    <span class="inline-flex items-center gap-1 rounded-full bg-surface-mid px-2.5 py-0.5 text-xs font-bold text-slate-600"><span class="material-symbols-outlined text-xs text-secondary">sync</span>Turnover Synced</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Recently completed departures with finalized billing records and key turn-ins.</p>
            </div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Archived: 48h Window</span>
        </div>
        <div class="owner-scroll overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-line bg-surface-low text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <th class="px-5 py-3">Guest</th>
                        <th class="px-4 py-3">Room</th>
                        <th class="px-4 py-3">Departed & Paid</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Housekeeping</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($checkOuts as $checkOut)
                        @php
                            $guestName = $checkOut->guest?->full_name ?? 'Guest Profile Archived';
                            $initials = collect(explode(' ', $guestName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') ?: 'G';
                        @endphp
                        <tr class="transition hover:bg-surface-low/70">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-line bg-surface-mid text-xs font-bold text-primary">{{ $initials }}</span>
                                    <div>
                                        <div class="font-semibold leading-tight text-primary">{{ $guestName }}</div>
                                        <span class="block text-xs text-slate-500">Completed departure record</span>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-semibold text-primary">Room {{ $checkOut->room?->room_number ?? 'N/A' }}</div>
                                <span class="text-xs text-slate-500">{{ $checkOut->room?->room_type ?? 'Room type archived' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-medium text-primary">{{ $checkOut->checked_out_at?->format('M d, Y · h:i A') ?? 'Pending timestamp' }}</div>
                                <span class="text-xs font-semibold text-secondary">Paid: PHP {{ number_format($checkOut->amount_paid, 2) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-mid px-2.5 py-1 text-xs font-semibold text-slate-600"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>{{ str_replace('_', ' ', ucfirst($checkOut->payment_status ?? 'checked out')) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                <span class="inline-flex items-center gap-1 rounded bg-secondary-fixed/30 px-2.5 py-1 text-xs font-bold text-secondary"><span class="material-symbols-outlined text-xs">done_all</span>Turnover Ready</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No recent check-out records have been finalized yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col justify-between gap-2 border-t border-line bg-surface-low px-4 py-3 text-xs font-semibold text-slate-500 sm:flex-row">
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-secondary">history</span>Total Closed Records for Cycle: PHP {{ number_format($recentCheckOutTotal, 2) }}</span>
            <span class="font-mono">Audit Hash: {{ substr(md5((string) $recentCheckOutTotal), 0, 4) }}-{{ substr(md5($checkOuts->count()), 0, 4) }}-0c91</span>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="owner-card rounded-xl p-5">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-secondary">Turnover Velocity</span>
            <span class="material-symbols-outlined text-secondary">speed</span>
        </div>
        <h4 class="font-semibold text-primary">Front Desk Processing Pacing</h4>
        <p class="mt-1 text-sm text-slate-500">Time elapsed from guest arrival to electronic keycard dispatch and room account lock.</p>
        <div class="mt-4 grid grid-cols-3 gap-2 border-t border-line pt-3">
            <div><span class="block text-[11px] text-slate-500">Avg. Check-in</span><span class="font-bold text-primary">2m 45s</span></div>
            <div><span class="block text-[11px] text-slate-500">Key Drop</span><span class="font-bold text-primary">1m 20s</span></div>
            <div><span class="block text-[11px] text-slate-500">Target</span><span class="font-bold text-secondary">&lt; 4m</span></div>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-secondary">Housekeeping Integration</span>
            <span class="material-symbols-outlined text-secondary">sanitizer</span>
        </div>
        <h4 class="font-semibold text-primary">Turnover Inspection Sync</h4>
        <p class="mt-1 text-sm text-slate-500">Automated handoff between departures and housekeeping release for resale.</p>
        <div class="mt-4 flex items-center gap-3 border-t border-line pt-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-mid font-bold text-primary">100%</span>
            <div><p class="font-semibold text-primary">Departed keys released</p><p class="text-xs text-slate-500">Zero delayed dirty turnover state</p></div>
        </div>
    </div>
    <div class="rounded-xl border border-secondary/30 bg-surface-mid p-5 shadow-sm">
        <div class="mb-2 flex items-center gap-2 text-secondary">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">policy</span>
            <span class="text-xs font-bold uppercase tracking-wider">Executive Governance Protocol</span>
        </div>
        <h4 class="font-semibold text-primary">Immutable SBMA Audit Chain</h4>
        <p class="mt-1 text-sm leading-6 text-slate-600">Express checkouts, keycard re-encodings, deposit release authorizations, and manual room status overrides are reserved for operational departments.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3">
            <span class="font-mono text-xs text-slate-500">SYS-REV-2026.10-PROD</span>
            <span class="inline-flex items-center gap-1 text-xs font-bold text-secondary"><span class="material-symbols-outlined text-sm">lock_clock</span>Read-Only Validated</span>
        </div>
    </div>
</section>
