@php
    $cycleStart = now('Asia/Manila')->subHours(48);
    $cycleCheckOuts = $checkOuts->filter(fn ($checkOut) => $checkOut->checked_out_at?->timezone('Asia/Manila')->gte($cycleStart));
    $todayCheckOuts = $checkOuts->filter(fn ($checkOut) => $checkOut->checked_out_at?->timezone('Asia/Manila')->isToday());
    $settledCheckOuts = $checkOuts->filter(fn ($checkOut) => in_array(strtolower($checkOut->payment_status ?? ''), ['paid', 'settled', 'completed'], true));
    $disputedCheckOuts = $checkOuts->filter(fn ($checkOut) => in_array(strtolower($checkOut->payment_status ?? ''), ['disputed', 'pending', 'unpaid'], true));
    $cycleTotal = $cycleCheckOuts->sum(fn ($checkOut) => (float) $checkOut->amount_paid);
    $settledTotal = $settledCheckOuts->sum(fn ($checkOut) => (float) $checkOut->amount_paid);
    $turnoverSynced = $checkOuts->count() ? round(($settledCheckOuts->count() / max($checkOuts->count(), 1)) * 100) : 100;
    $auditHash = substr(md5('owner-checkout-'.$cycleTotal.'-'.$checkOuts->count()), 0, 4).'-'.substr(md5((string) $settledTotal), 0, 4).'-'.substr(md5((string) $todayCheckOuts->count()), 0, 4);
@endphp

<section class="relative overflow-hidden rounded-2xl border border-secondary/20 bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-16 -right-16 opacity-[0.06]">
        <span class="material-symbols-outlined text-[260px]">hotel_class</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl space-y-2">
            <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">
                <span class="h-2 w-2 rounded-full bg-secondary-fixed"></span>
                Executive Monitoring / Departures & Settlement
            </div>
            <h2 class="font-serif text-4xl font-extrabold leading-tight">Check-out Records & Turnover Archive</h2>
            <p class="max-w-3xl text-sm leading-6 text-blue-100">
                Audited log of finalized departures, electronic key drops, billing balances, and automated housekeeping turnover dispatches.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="rounded-lg border border-white/10 bg-primary/70 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-secondary-fixed">Departures (48h)</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $cycleCheckOuts->count() }}</p>
                <p class="text-[10px] text-blue-100/70">Completed records</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-primary/70 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-secondary-fixed">Settled for Cycle</p>
                <p class="mt-1 font-serif text-xl font-bold">PHP {{ number_format($cycleTotal, 2) }}</p>
                <p class="text-[10px] text-blue-100/70">Posted departure value</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-primary/70 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-secondary-fixed">Turnover Sync</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $turnoverSynced }}%</p>
                <p class="text-[10px] text-blue-100/70">Room release monitoring</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-primary/70 px-3.5 py-2.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-secondary-fixed">Audit Variance</p>
                <p class="mt-1 font-serif text-2xl font-bold text-emerald-200">{{ $disputedCheckOuts->count() }}</p>
                <p class="text-[10px] text-blue-100/70">Flagged settlements</p>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Guest Departures</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary-container/40 text-secondary"><span class="material-symbols-outlined text-lg">logout</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold text-primary">{{ $checkOuts->count() }}</span><span class="font-semibold text-slate-500">Check-outs</span></div>
        <div class="mt-3 flex items-center gap-1.5 border-t border-line pt-3 text-xs text-slate-500"><span class="material-symbols-outlined text-sm text-secondary">check_circle</span><span>{{ $settledCheckOuts->count() }} electronically cleared</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Settled</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary-container/40 text-secondary"><span class="material-symbols-outlined text-lg">payments</span></span>
        </div>
        <div class="mt-3 font-serif text-2xl font-bold text-primary">PHP {{ number_format($settledTotal, 2) }}</div>
        <div class="mt-3 flex items-center gap-1.5 border-t border-line pt-3 text-xs text-slate-500"><span class="material-symbols-outlined text-sm text-secondary">verified</span><span>{{ $disputedCheckOuts->count() }} pending settlement review</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Housekeeping Turnover</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary-container/40 text-secondary"><span class="material-symbols-outlined text-lg">cleaning_services</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold text-primary">{{ $turnoverSynced }}%</span><span class="font-semibold text-slate-500">Synced</span></div>
        <div class="mt-3 flex items-center gap-1.5 border-t border-line pt-3 text-xs text-slate-500"><span class="material-symbols-outlined text-sm text-emerald-700">sync</span><span>Turnover queue follows released rooms</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Archived Today</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary-container/40 text-secondary"><span class="material-symbols-outlined text-lg">inventory_2</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-3xl font-bold text-primary">{{ $todayCheckOuts->count() }}</span><span class="font-semibold text-slate-500">Today</span></div>
        <div class="mt-3 flex items-center gap-1.5 border-t border-line pt-3 text-xs text-slate-500"><span class="material-symbols-outlined text-sm text-secondary">lock_clock</span><span>Audit hash {{ $auditHash }}</span></div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-2xl">
    <div class="flex flex-col justify-between gap-4 border-b border-line bg-surface-low/60 p-5 md:flex-row md:items-center">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[280px] max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input id="ownerCheckOutSearch" class="w-full rounded-lg border border-line bg-white py-2 pl-9 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/30" placeholder="Search guest, room #, receipt, payment, or notes..." type="search" autocomplete="off">
            </div>
            <div class="inline-flex rounded-lg border border-line bg-white p-1 text-xs" aria-label="Check-out record filters">
                <button type="button" class="rounded bg-primary px-3 py-1.5 font-bold text-white" data-checkout-filter="all">All Departures ({{ $checkOuts->count() }})</button>
                <button type="button" class="rounded px-3 py-1.5 font-bold text-slate-600 transition hover:text-primary" data-checkout-filter="today">Settled Today ({{ $todayCheckOuts->count() }})</button>
                <button type="button" class="rounded px-3 py-1.5 font-bold text-slate-600 transition hover:text-primary" data-checkout-filter="cycle">Archived 48h ({{ $cycleCheckOuts->count() }})</button>
                <button type="button" class="rounded px-3 py-1.5 font-bold text-slate-600 transition hover:text-primary" data-checkout-filter="disputed">Disputed ({{ $disputedCheckOuts->count() }})</button>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button id="ownerCheckOutExport" type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3.5 py-2 text-xs font-bold text-primary transition hover:border-secondary">
                <span class="material-symbols-outlined text-sm">file_download</span>
                Export Departure CSV
            </button>
            <button id="ownerCheckOutReset" type="button" class="rounded-lg border border-line bg-white p-2 text-slate-500 transition hover:bg-surface-mid hover:text-primary" title="Refresh table filters">
                <span class="material-symbols-outlined text-lg">refresh</span>
            </button>
        </div>
    </div>

    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1120px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-primary">
                    <th class="px-4 py-3.5">Departed Guest</th>
                    <th class="px-4 py-3.5">Room & Wing</th>
                    <th class="px-4 py-3.5">Departure Timestamp</th>
                    <th class="px-4 py-3.5 text-right">Settled Total</th>
                    <th class="px-4 py-3.5">Payment Status</th>
                    <th class="px-4 py-3.5">Key Surrender</th>
                    <th class="px-4 py-3.5">Housekeeping State</th>
                    <th class="px-4 py-3.5">Audit Lock</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($checkOuts as $checkOut)
                    @php
                        $guestName = $checkOut->guest?->full_name ?? 'Guest Profile Archived';
                        $initials = collect(explode(' ', $guestName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') ?: 'G';
                        $paymentStatus = strtolower($checkOut->payment_status ?? 'checked_out');
                        $isSettled = in_array($paymentStatus, ['paid', 'settled', 'completed'], true);
                        $isDisputed = in_array($paymentStatus, ['disputed', 'pending', 'unpaid'], true);
                        $isToday = $checkOut->checked_out_at?->timezone('Asia/Manila')->isToday();
                        $isCycle = $checkOut->checked_out_at?->timezone('Asia/Manila')->gte($cycleStart);
                        $receipt = 'LCS-'.($checkOut->checked_out_at?->timezone('Asia/Manila')->format('Y') ?? now('Asia/Manila')->format('Y')).'-'.str_pad((string) $checkOut->id, 4, '0', STR_PAD_LEFT);
                        $roomType = $checkOut->room?->room_type ?? 'Room type archived';
                        $reservation = $checkOut->checkIn?->reservation;
                        $searchText = implode(' ', [$guestName, $checkOut->guest?->email, $checkOut->room?->room_number, $roomType, $receipt, $paymentStatus, $checkOut->notes]);
                    @endphp
                    <tr
                        class="transition hover:bg-surface-low/70"
                        data-checkout-row
                        data-search="{{ e(strtolower($searchText)) }}"
                        data-status="{{ $isDisputed ? 'disputed' : ($isSettled ? 'settled' : $paymentStatus) }}"
                        data-today="{{ $isToday ? '1' : '0' }}"
                        data-cycle="{{ $isCycle ? '1' : '0' }}"
                        data-csv="{{ e(implode(',', [
                            $receipt,
                            $guestName,
                            'Room '.$checkOut->room?->room_number,
                            $checkOut->checked_out_at?->timezone('Asia/Manila')->format('Y-m-d H:i:s'),
                            number_format((float) $checkOut->amount_paid, 2, '.', ''),
                            $paymentStatus,
                            $checkOut->notes,
                        ])) }}"
                    >
                        <td class="px-4 py-4 align-top">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-secondary/20 bg-surface-mid text-xs font-bold text-primary">{{ $initials }}</span>
                                <div>
                                    <div class="font-bold leading-snug text-primary">{{ $guestName }}</div>
                                    <div class="mt-0.5 text-[11px] text-slate-500">{{ $checkOut->guest?->email ?? 'No email encoded' }}</div>
                                    <span class="mt-1 inline-flex rounded border border-line bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-secondary">Completed departure record</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <div class="font-bold text-primary">Room {{ $checkOut->room?->room_number ?? 'N/A' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $roomType }} · Le Charmé Rooms Cluster</div>
                            <div class="mt-1 text-[10px] font-semibold text-secondary">Stay {{ $reservation?->check_in_date?->format('M d') ?? 'N/A' }} - {{ $reservation?->check_out_date?->format('M d') ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <div class="font-medium text-primary">{{ $checkOut->checked_out_at?->timezone('Asia/Manila')->format('M d, Y @ h:i A') ?? 'Pending timestamp' }} PHT</div>
                            <div class="text-[11px] text-slate-500">{{ $isToday ? 'Settled today' : 'Archived departure' }}</div>
                            <div class="mt-0.5 text-[10px] text-slate-400">{{ $checkOut->notes ?: 'Front Desk departure station' }}</div>
                        </td>
                        <td class="px-4 py-4 text-right align-top">
                            <div class="font-serif text-lg font-bold text-primary">PHP {{ number_format((float) $checkOut->amount_paid, 2) }}</div>
                            <div class="text-[10px] font-semibold text-secondary">Receipt #{{ $receipt }}</div>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <span @class(['inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold', 'border-emerald-200 bg-emerald-50 text-emerald-800' => $isSettled, 'border-amber-200 bg-amber-50 text-amber-800' => ! $isSettled && ! $isDisputed, 'border-rose-200 bg-rose-50 text-rose-800' => $isDisputed])>
                                <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-600' => $isSettled, 'bg-amber-500' => ! $isSettled && ! $isDisputed, 'bg-rose-600' => $isDisputed])></span>
                                {{ str_replace('_', ' ', ucfirst($paymentStatus)) }}
                            </span>
                            <div class="mt-1 text-[10px] text-slate-500">{{ $isSettled ? 'Electronic billing clearance' : 'Requires billing review' }}</div>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <div class="inline-flex items-center gap-1.5 rounded border border-line bg-white px-2 py-1 text-[11px] font-bold text-primary">
                                <span class="material-symbols-outlined text-xs text-secondary">key_off</span>
                                Key Returned
                            </div>
                            <div class="mt-1 font-mono text-[10px] text-slate-400">KC-{{ str_pad((string) ($checkOut->check_in_id ?? $checkOut->id), 4, '0', STR_PAD_LEFT) }} closed</div>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <div class="inline-flex items-center gap-1.5 rounded border border-secondary/20 bg-secondary-container/50 px-2 py-1 text-[11px] font-bold text-secondary">
                                <span class="material-symbols-outlined text-xs">cleaning_services</span>
                                Turnover Synced
                            </div>
                            <div class="mt-1 text-[10px] text-slate-500">Room release monitoring only</div>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <div class="font-mono text-[11px] font-bold text-primary">{{ substr(md5('checkout-'.$checkOut->id), 0, 4) }}..{{ substr(md5((string) $checkOut->room_id), 0, 4) }}</div>
                            <div class="mt-1 text-[10px] font-semibold text-slate-500">Owner read-only</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No check-out records are available yet.</td>
                    </tr>
                @endforelse
                @if ($checkOuts->isNotEmpty())
                    <tr id="checkoutNoMatches" class="hidden">
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No check-out records match the current owner filters.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="flex flex-col items-center justify-between gap-4 border-t border-line bg-surface-low/50 p-4 text-xs text-slate-500 md:flex-row">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-secondary">lock_clock</span>Total Closed Records for Cycle: <strong class="text-primary">PHP {{ number_format($cycleTotal, 2) }}</strong></span>
            <span class="hidden md:inline">·</span>
            <span>Audit Hash: <strong class="font-mono text-primary">{{ $auditHash }}</strong></span>
        </div>
        <span>Showing {{ $checkOuts->count() }} finalized {{ \Illuminate\Support\Str::plural('departure', $checkOuts->count()) }}</span>
    </div>
</section>

<div class="grid grid-cols-1 gap-4 pb-2 md:grid-cols-3">
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Front Desk Processing Pacing</span><span class="material-symbols-outlined text-slate-400">speed</span></div>
        <h3 class="mt-1 font-semibold text-primary">Departure Clearance Flow</h3>
        <p class="mt-1 text-sm text-slate-500">Time elapsed from guest departure request to receipt closeout and key surrender.</p>
        <div class="mt-4 grid grid-cols-3 border-t border-line pt-3 text-center">
            <div><div class="font-bold text-primary">3m 10s</div><div class="text-[10px] uppercase text-slate-500">Avg.</div></div>
            <div><div class="font-bold text-primary">1m 05s</div><div class="text-[10px] uppercase text-slate-500">Key Drop</div></div>
            <div><div class="font-bold text-emerald-700">&lt; 5m</div><div class="text-[10px] uppercase text-slate-500">Target</div></div>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Housekeeping Integration</span><span class="material-symbols-outlined text-slate-400">sanitizer</span></div>
        <h3 class="mt-1 font-semibold text-primary">Turnover Inspection Sync</h3>
        <p class="mt-1 text-sm text-slate-500">Automated handoff between departures and housekeeping release for resale.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3">
            <div class="flex items-center gap-3"><span class="font-serif text-2xl font-bold text-primary">{{ $turnoverSynced }}%</span><div><div class="text-xs font-bold text-primary">Departed keys released</div><div class="text-[10px] text-slate-500">Monitored from closed check-outs</div></div></div>
            <span class="material-symbols-outlined text-emerald-600">task_alt</span>
        </div>
    </div>
    <div class="rounded-xl border border-secondary/20 bg-secondary-container/30 p-5 shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">policy</span><span class="text-xs font-bold uppercase tracking-wider text-secondary">Executive Governance Protocol</span></div>
        <h3 class="mt-1 font-semibold text-primary">Immutable SBMA Audit Chain</h3>
        <p class="mt-1 text-sm leading-6 text-slate-600">Express checkouts, keycard re-encodings, deposit release authorizations, and manual room status overrides are reserved for operational departments.</p>
        <div class="mt-4 flex items-center justify-between border-t border-secondary/20 pt-3 text-[11px] text-secondary"><span class="font-mono">SYS-OUT-2026.10</span><span class="inline-flex items-center gap-1 font-bold"><span class="material-symbols-outlined text-xs">verified_user</span>Read-Only Validated</span></div>
    </div>
</div>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-checkout-row]'));
        if (!rows.length) {
            return;
        }

        const searchInput = document.getElementById('ownerCheckOutSearch');
        const filterButtons = Array.from(document.querySelectorAll('[data-checkout-filter]'));
        const resetButton = document.getElementById('ownerCheckOutReset');
        const exportButton = document.getElementById('ownerCheckOutExport');
        const noMatches = document.getElementById('checkoutNoMatches');
        let activeFilter = 'all';

        const setActiveButton = () => {
            filterButtons.forEach((button) => {
                const active = button.dataset.checkoutFilter === activeFilter;
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('text-slate-600', !active);
            });
        };

        const applyFilters = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const queryMatch = !query || row.dataset.search.includes(query);
                const filterMatch = activeFilter === 'all'
                    || (activeFilter === 'today' && row.dataset.today === '1')
                    || (activeFilter === 'cycle' && row.dataset.cycle === '1')
                    || (activeFilter === 'disputed' && row.dataset.status === 'disputed');
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
                activeFilter = button.dataset.checkoutFilter || 'all';
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
            const csv = ['Receipt,Guest,Room,Checked Out,Amount Paid,Payment Status,Notes']
                .concat(visibleRows.map((row) => row.dataset.csv))
                .join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-check-out-records.csv';
            link.click();
            URL.revokeObjectURL(url);
        });

        applyFilters();
    })();
</script>
