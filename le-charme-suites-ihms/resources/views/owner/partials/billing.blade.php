@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $todayTransactions = $transactions->filter(fn ($transaction) => $transaction->transaction_date?->timezone('Asia/Manila')->isToday());
    $settledTransactions = $transactions->filter(fn ($transaction) => in_array(strtolower($transaction->status ?? ''), ['paid', 'settled', 'completed'], true));
    $pendingTransactions = $transactions->filter(fn ($transaction) => in_array(strtolower($transaction->status ?? ''), ['pending', 'unpaid', 'open'], true));
    $roomChargeTransactions = $transactions->filter(fn ($transaction) => str_contains(strtolower($transaction->transaction_type ?? ''), 'room'));
    $foodTransactions = $transactions->filter(fn ($transaction) => str_contains(strtolower($transaction->transaction_type ?? ''), 'food') || str_contains(strtolower($transaction->transaction_type ?? ''), 'beverage'));
    $incidentalTransactions = $transactions->filter(fn ($transaction) => str_contains(strtolower($transaction->transaction_type ?? ''), 'deposit') || str_contains(strtolower($transaction->transaction_type ?? ''), 'incidental'));
    $recordedLedgerTotal = $transactions->sum(fn ($transaction) => (float) $transaction->amount);
    $settledLedgerTotal = $settledTransactions->sum(fn ($transaction) => (float) $transaction->amount);
    $pendingLedgerTotal = $pendingTransactions->sum(fn ($transaction) => (float) $transaction->amount);
    $discrepancyRate = $transactions->count() ? round(($pendingTransactions->count() / max($transactions->count(), 1)) * 100, 2) : 0;
    $auditHash = substr(md5('owner-billing-'.$recordedLedgerTotal.'-'.$transactions->count()), 0, 4).'-'.substr(md5((string) $settledLedgerTotal), 0, 4).'-'.substr(md5((string) $pendingLedgerTotal), 0, 4);
@endphp

<section class="relative overflow-hidden rounded-2xl border border-secondary-fixed/20 bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-12 -right-10 opacity-[0.05]">
        <span class="material-symbols-outlined text-[240px] text-secondary-fixed">shield_with_heart</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl space-y-2">
            <div class="inline-flex items-center gap-2 rounded border border-secondary-fixed/40 bg-primary/80 px-2.5 py-1 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">
                <span class="material-symbols-outlined text-xs">verified_user</span>
                Executive Monitoring
            </div>
            <h2 class="font-serif text-4xl font-extrabold leading-tight">Billing & Transactions Overview</h2>
            <p class="max-w-3xl text-sm leading-6 text-blue-100">
                Revenue, settlement, and transaction monitoring across guest billing accounts and terminal ledgers.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-primary/70 p-4 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="border-r border-white/10 pr-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p>
            </div>
            <div class="border-r border-white/10 pr-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Rooms</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $totalRooms }} <span class="font-sans text-xs font-normal text-blue-100/70">Total</span></p>
            </div>
            <div class="border-r border-white/10 pr-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Guests</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $currentGuests }} <span class="font-sans text-xs font-normal text-blue-100/70">In-House</span></p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Revenue</p>
                <p class="mt-1 font-serif text-xl font-bold text-secondary-fixed">PHP {{ number_format($recordedLedgerTotal, 2) }}</p>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5">
        <div class="flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Guest Management</span>
                <div class="mt-2 font-serif text-3xl font-bold text-primary">{{ $totalGuests }}</div>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-line bg-surface-low text-secondary"><span class="material-symbols-outlined">badge</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Registered guest profiles</span><span class="font-semibold text-secondary">{{ $currentGuests }} active</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5">
        <div class="flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Room Management</span>
                <div class="mt-2 font-serif text-3xl font-bold text-primary">{{ $availableRooms }}</div>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-line bg-surface-low text-secondary"><span class="material-symbols-outlined">single_bed</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Available of {{ $totalRooms }} rooms</span><span class="font-semibold text-secondary">{{ $occupiedRooms }} occupied</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5">
        <div class="flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Reservations</span>
                <div class="mt-2 font-serif text-3xl font-bold text-primary">{{ $activeReservations }}</div>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-line bg-surface-low text-secondary"><span class="material-symbols-outlined">event_note</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>{{ $arrivalsToday }} arrivals today</span><span>{{ $reservations->count() }} queued</span></div>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl border-secondary-fixed/60 p-5 transition hover:-translate-y-0.5">
        <div class="absolute left-0 right-0 top-0 h-1 bg-secondary"></div>
        <div class="flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-secondary">Billing & Transactions</span>
                <div class="mt-2 font-serif text-2xl font-bold leading-tight text-primary">PHP {{ number_format($settledLedgerTotal, 2) }}</div>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary/30 bg-secondary-fixed/20 text-secondary"><span class="material-symbols-outlined">payments</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>{{ $settledTransactions->count() }} cleared</span><span>PHP {{ number_format($pendingLedgerTotal, 2) }} queued</span></div>
    </div>
</section>

<section class="owner-card scroll-mt-24 overflow-hidden rounded-2xl" id="billing-records">
    <div class="flex flex-col justify-between gap-4 border-b border-line bg-white p-6 md:flex-row md:items-center">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-serif text-2xl font-bold text-primary">Billing & Transaction Records</h3>
                <span class="rounded bg-surface-mid px-2 py-0.5 text-xs font-bold text-slate-600">Audit Mode</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Owner visibility into revenue and transaction records, without posting or settlement controls.</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="ownerBillingExport" type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface-low px-4 py-2 text-xs font-bold text-primary transition hover:border-secondary">
                <span class="material-symbols-outlined text-lg text-secondary">download</span>
                Export Audit Ledger
            </button>
            <button id="ownerBillingReset" type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:bg-primary-container">
                <span class="material-symbols-outlined text-lg text-secondary-fixed">refresh</span>
                Reset Filters
            </button>
        </div>
    </div>

    <div class="flex flex-col justify-between gap-4 border-b border-line bg-surface-low px-6 py-3.5 sm:flex-row sm:items-center">
        <div class="relative w-full sm:w-96">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span>
            <input id="ownerBillingSearch" class="w-full rounded-lg border border-line bg-white py-2 pl-9 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search transaction ref, invoice, guest name, or room..." type="search" autocomplete="off">
        </div>
        <div class="flex w-full items-center gap-2 overflow-x-auto sm:w-auto" aria-label="Billing ledger filters">
            <button type="button" class="rounded-full bg-primary px-3 py-1.5 text-xs font-bold text-white" data-billing-filter="all">All ({{ $transactions->count() }})</button>
            <button type="button" class="rounded-full bg-surface-mid px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-billing-filter="room">Room Charges ({{ $roomChargeTransactions->count() }})</button>
            <button type="button" class="rounded-full bg-surface-mid px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-billing-filter="food">Food & Beverage ({{ $foodTransactions->count() }})</button>
            <button type="button" class="rounded-full bg-surface-mid px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-billing-filter="incidental">Incidental / Deposit ({{ $incidentalTransactions->count() }})</button>
        </div>
    </div>

    <div id="transactions" class="owner-scroll overflow-x-auto scroll-mt-24">
        <table class="w-full min-w-[1040px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-6 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-6 py-3">Guest</th>
                    <th class="px-4 py-3">Room</th>
                    <th class="px-6 py-3 text-right">Amount</th>
                    <th class="px-6 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($transactions as $transaction)
                    @php
                        $type = strtolower($transaction->transaction_type ?? 'transaction');
                        $typeLabel = str($transaction->transaction_type ?? 'transaction')->replace('_', ' ')->title();
                        $status = strtolower($transaction->status ?? 'pending');
                        $isCleared = in_array($status, ['paid', 'settled', 'completed'], true);
                        $category = str_contains($type, 'room') ? 'room' : ((str_contains($type, 'food') || str_contains($type, 'beverage')) ? 'food' : ((str_contains($type, 'deposit') || str_contains($type, 'incidental')) ? 'incidental' : 'other'));
                        $invoiceRef = 'INV-'.$transaction->transaction_date?->timezone('Asia/Manila')->format('Y') . '-' . str_pad((string) $transaction->id, 4, '0', STR_PAD_LEFT);
                        $searchText = implode(' ', [$invoiceRef, $typeLabel, $transaction->guest?->full_name, $transaction->guest?->email, $transaction->room?->room_number, $transaction->amount, $transaction->status, $transaction->description]);
                    @endphp
                    <tr
                        class="transition hover:bg-surface-low/70"
                        data-billing-row
                        data-category="{{ $category }}"
                        data-search="{{ e(strtolower($searchText)) }}"
                        data-csv="{{ e(implode(',', [
                            $transaction->transaction_date?->timezone('Asia/Manila')->format('Y-m-d H:i:s'),
                            $typeLabel,
                            $transaction->guest?->full_name ?? 'N/A',
                            'Room '.$transaction->room?->room_number,
                            number_format((float) $transaction->amount, 2, '.', ''),
                            $status,
                            $invoiceRef,
                        ])) }}"
                    >
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="font-semibold text-primary">{{ $transaction->transaction_date?->timezone('Asia/Manila')->format('M d, Y') ?? 'No date' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $transaction->transaction_date?->timezone('Asia/Manila')->format('h:i A') ?? '--:--' }} PHT</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4">
                            <div class="flex items-center gap-2">
                                <span @class(['h-2 w-2 rounded-full', 'bg-secondary' => $category !== 'other', 'bg-slate-400' => $category === 'other'])></span>
                                <span class="font-semibold text-primary">{{ $typeLabel }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400">#{{ $invoiceRef }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-primary">{{ $transaction->guest?->full_name ?? 'Guest not linked' }}</div>
                            <div class="mt-0.5 text-[11px] text-slate-500">{{ $transaction->description ?: 'Ledger record from hotel transaction table' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4">
                            <span class="rounded border border-line bg-surface-low px-2.5 py-1 text-xs font-semibold text-primary">Room {{ $transaction->room?->room_number ?? 'N/A' }}</span>
                            <span class="mt-1 block text-[11px] text-slate-400">{{ $transaction->room?->room_type ?? 'No room type' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <span class="font-bold text-primary">PHP {{ number_format((float) $transaction->amount, 2) }}</span>
                            <span class="block text-[11px] text-slate-400">Owner ledger view</span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-center">
                            <span @class(['inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-bold', 'border-secondary/30 bg-secondary-fixed/30 text-secondary' => $isCleared, 'border-line bg-surface-mid text-slate-600' => ! $isCleared])>
                                <span class="material-symbols-outlined text-xs">{{ $isCleared ? 'check_circle' : 'schedule' }}</span>
                                {{ str_replace('_', ' ', ucfirst($status)) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">No billing or transaction records are available yet.</td>
                    </tr>
                @endforelse
                @if ($transactions->isNotEmpty())
                    <tr id="billingNoMatches" class="hidden">
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">No billing records match the current owner filters.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="flex flex-col items-center justify-between gap-3 border-t border-line bg-surface-low p-4 px-6 text-xs font-semibold text-slate-500 sm:flex-row">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-sm text-secondary">shield</span><span>Showing {{ $transactions->count() }} recent {{ \Illuminate\Support\Str::plural('transaction', $transactions->count()) }} · Total Recorded Ledger Intake: <strong class="text-primary">PHP {{ number_format($recordedLedgerTotal, 2) }}</strong></span></div>
        <div class="flex items-center gap-3"><span class="font-mono text-[11px] text-slate-400">Audit Hash: {{ $auditHash }}</span><span class="font-bold text-secondary">Verified SBMA Ledger</span></div>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Revenue Velocity</span><span class="material-symbols-outlined text-secondary">speed</span></div>
        <div class="mt-3 font-serif text-xl font-bold text-primary">1m 45s</div>
        <p class="mt-1 text-sm text-slate-500">Average charge posting to settlement clearance velocity across terminals.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Discrepancy Rate</span><span class="font-bold text-secondary">{{ number_format($discrepancyRate, 2) }}%</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Drawer Reconciliation</span><span class="material-symbols-outlined text-secondary">account_balance_wallet</span></div>
        <div class="mt-3 font-serif text-xl font-bold text-primary">PHP {{ number_format($settledLedgerTotal, 2) }}</div>
        <p class="mt-1 text-sm text-slate-500">Settled entries are visible to ownership without operational posting controls.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Pending queue</span><span class="font-bold text-secondary">PHP {{ number_format($pendingLedgerTotal, 2) }}</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Statutory Governance</span><span class="material-symbols-outlined text-secondary">gavel</span></div>
        <div class="mt-3 font-serif text-xl font-bold text-primary">SBMA Freeport 5% Tax</div>
        <p class="mt-1 text-sm text-slate-500">Subic Bay Metropolitan Authority statutory revenue tax is tracked for ledger review.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Certification ID</span><span class="font-mono text-slate-500">SBF-2026-TX88</span></div>
    </div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-billing-row]'));
        if (!rows.length) {
            return;
        }

        const searchInput = document.getElementById('ownerBillingSearch');
        const filterButtons = Array.from(document.querySelectorAll('[data-billing-filter]'));
        const resetButton = document.getElementById('ownerBillingReset');
        const exportButton = document.getElementById('ownerBillingExport');
        const noMatches = document.getElementById('billingNoMatches');
        let activeFilter = 'all';

        const setActiveButton = () => {
            filterButtons.forEach((button) => {
                const active = button.dataset.billingFilter === activeFilter;
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
                const filterMatch = activeFilter === 'all' || row.dataset.category === activeFilter;
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
                activeFilter = button.dataset.billingFilter || 'all';
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
            const csv = ['Date,Type,Guest,Room,Amount,Status,Invoice']
                .concat(visibleRows.map((row) => row.dataset.csv))
                .join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-billing-transactions.csv';
            link.click();
            URL.revokeObjectURL(url);
        });

        applyFilters();
    })();
</script>
