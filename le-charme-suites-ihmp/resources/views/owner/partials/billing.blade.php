@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $todayTransactions = $transactions->filter(fn ($transaction) => $transaction->transaction_date?->timezone('Asia/Manila')->isToday());
    $settledTransactions = $transactions->filter(fn ($transaction) => in_array(strtolower($transaction->status ?? ''), ['paid', 'settled', 'completed', 'recorded', 'cleared'], true));
    $pendingTransactions = $transactions->filter(fn ($transaction) => in_array(strtolower($transaction->status ?? ''), ['pending', 'unpaid', 'open'], true));
    $roomChargeTransactions = $transactions->filter(fn ($transaction) => str_contains(strtolower($transaction->transaction_type ?? ''), 'room'));
    $foodTransactions = $transactions->filter(fn ($transaction) => str_contains(strtolower($transaction->transaction_type ?? ''), 'food') || str_contains(strtolower($transaction->transaction_type ?? ''), 'beverage'));
    $depositTransactions = $transactions->filter(fn ($transaction) => str_contains(strtolower($transaction->transaction_type ?? ''), 'deposit') || str_contains(strtolower($transaction->transaction_type ?? ''), 'incidental'));
    $recordedLedgerTotal = (float) $transactions->sum(fn ($transaction) => (float) $transaction->amount);
    $todayLedgerTotal = (float) $todayTransactions->sum(fn ($transaction) => (float) $transaction->amount);
    $settledLedgerTotal = (float) $settledTransactions->sum(fn ($transaction) => (float) $transaction->amount);
    $pendingLedgerTotal = (float) $pendingTransactions->sum(fn ($transaction) => (float) $transaction->amount);
    $cashLikeTotal = (float) $transactions->filter(fn ($transaction) => str_contains(strtolower(($transaction->description ?? '').' '.($transaction->transaction_type ?? '')), 'cash'))->sum(fn ($transaction) => (float) $transaction->amount);
    $electronicTotal = max($recordedLedgerTotal - $cashLikeTotal, 0);
    $varianceCount = $pendingTransactions->count();
    $discrepancyRate = $transactions->count() ? round(($varianceCount / max($transactions->count(), 1)) * 100, 2) : 0;
    $auditHash = substr(strtoupper(hash('sha256', 'owner-transactions-'.$transactions->count().'-'.$recordedLedgerTotal)), 0, 4).'-'.substr(strtoupper(hash('sha256', 'settled-'.$settledLedgerTotal)), 0, 4).'-'.substr(strtoupper(hash('sha256', 'pending-'.$pendingLedgerTotal)), 0, 4);
@endphp

<section class="relative overflow-hidden rounded-xl border border-secondary-fixed/20 bg-primary p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-20 -right-16 h-64 w-64 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full border border-secondary-fixed/30 bg-secondary-fixed/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Live Audit Feed</span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-100/80"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Station desks and central billing records active</span>
            </div>
            <h2 class="mt-3 font-serif text-4xl font-bold leading-tight">Billing & Transactions Overview</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">Revenue, settlement, and transaction monitoring across guest billing accounts, department computers, and cashier drawers in real time.</p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p><p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Transactions</p><p class="mt-1 font-serif text-2xl font-bold">{{ $transactions->count() }}</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Settled</p><p class="mt-1 font-serif text-2xl font-bold">{{ $settledTransactions->count() }}</p></div>
            <div><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Recorded</p><p class="mt-1 font-serif text-xl font-bold text-secondary-fixed">PHP {{ number_format($recordedLedgerTotal, 2) }}</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary">
        <div class="flex items-start justify-between">
            <div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Recorded Registration Today</p><h3 class="mt-2 font-serif text-3xl font-bold text-primary">PHP {{ number_format($todayLedgerTotal, 2) }}</h3></div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary/10 text-secondary"><span class="material-symbols-outlined">account_balance_wallet</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>{{ $todayTransactions->count() }} posted today</span><span class="font-bold text-emerald-700">Live</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary">
        <div class="flex items-start justify-between">
            <div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Cash on Hand</p><h3 class="mt-2 font-serif text-3xl font-bold text-primary">PHP {{ number_format($cashLikeTotal, 2) }}</h3></div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary/10 text-secondary"><span class="material-symbols-outlined">payments</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Cash-like records</span><span class="font-bold text-secondary">Audit view</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary">
        <div class="flex items-start justify-between">
            <div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Electronic Batch</p><h3 class="mt-2 font-serif text-3xl font-bold text-primary">PHP {{ number_format($electronicTotal, 2) }}</h3></div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary/10 text-secondary"><span class="material-symbols-outlined">credit_card</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Card and account records</span><span class="font-bold text-secondary">{{ $settledTransactions->count() }} settled</span></div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary">
        <div class="flex items-start justify-between">
            <div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Unreviewed Difference</p><h3 class="mt-2 font-serif text-3xl font-bold text-primary">PHP {{ number_format($pendingLedgerTotal, 2) }}</h3></div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><span class="material-symbols-outlined">verified</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>{{ $varianceCount }} pending records</span><span class="font-bold text-secondary">{{ number_format($discrepancyRate, 2) }}%</span></div>
    </div>
</section>

<section class="owner-card scroll-mt-24 overflow-hidden rounded-xl" id="transactions">
    <div class="space-y-4 border-b border-line bg-white p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-serif text-2xl font-bold text-primary">Transaction Stream</h3>
                    <span class="rounded bg-secondary-fixed/30 px-2 py-0.5 text-xs font-bold uppercase text-secondary">Audit Mode</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-600"></span>Live Connection</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Chronological billing history across guest billing accounts, settlement activity, and department charges.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button id="ownerTxnExport" type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface-low px-3.5 py-2 text-sm font-bold text-primary transition hover:border-secondary">
                    <span class="material-symbols-outlined text-lg text-secondary">file_download</span>
                    Export Audit Records
                </button>
                <button id="ownerTxnReset" type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-bold text-white transition hover:bg-primary-container">
                    <span class="material-symbols-outlined text-lg text-secondary-fixed">refresh</span>
                    Reset Filters
                </button>
            </div>
        </div>
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <label class="relative block w-full lg:max-w-lg">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span>
                <input id="ownerTxnSearch" class="w-full rounded-lg border border-line bg-white py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search transaction ref, invoice, guest name, or room #..." type="search" autocomplete="off">
            </label>
            <div class="owner-scroll flex items-center gap-2 overflow-x-auto" aria-label="Transaction category filters">
                <button type="button" class="shrink-0 rounded-lg border border-primary bg-primary px-3 py-2 text-xs font-bold text-white" data-owner-txn-filter="all">All Records <span class="ml-1 rounded bg-white/15 px-1.5">{{ $transactions->count() }}</span></button>
                <button type="button" class="shrink-0 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:text-primary" data-owner-txn-filter="room">Room Charges <span class="ml-1 rounded bg-surface-mid px-1.5">{{ $roomChargeTransactions->count() }}</span></button>
                <button type="button" class="shrink-0 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:text-primary" data-owner-txn-filter="food">Outlet / F&B <span class="ml-1 rounded bg-surface-mid px-1.5">{{ $foodTransactions->count() }}</span></button>
                <button type="button" class="shrink-0 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:text-primary" data-owner-txn-filter="deposit">Deposits <span class="ml-1 rounded bg-surface-mid px-1.5">{{ $depositTransactions->count() }}</span></button>
            </div>
        </div>
    </div>

    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1180px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-low text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-4 py-3">Time & Reference</th>
                    <th class="px-4 py-3">Workstation / Source</th>
                    <th class="px-4 py-3">Guest / Invoice</th>
                    <th class="px-4 py-3">Room / Category</th>
                    <th class="px-4 py-3 text-right">Amount (PHP)</th>
                    <th class="px-4 py-3 text-center">Payment Status</th>
                    <th class="px-4 py-3 text-right">Audit Hash</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($transactions as $transaction)
                    @php
                        $rawType = strtolower($transaction->transaction_type ?? 'transaction');
                        $typeLabel = str($transaction->transaction_type ?? 'transaction')->replace('_', ' ')->title();
                        $status = strtolower($transaction->status ?? 'pending');
                        $isCleared = in_array($status, ['paid', 'settled', 'completed', 'recorded', 'cleared'], true);
                        $category = str_contains($rawType, 'room') ? 'room' : ((str_contains($rawType, 'food') || str_contains($rawType, 'beverage')) ? 'food' : ((str_contains($rawType, 'deposit') || str_contains($rawType, 'incidental')) ? 'deposit' : 'other'));
                        $txnRef = 'TXN-'.$transaction->transaction_date?->timezone('Asia/Manila')->format('Y').'-'.str_pad((string) $transaction->id, 5, '0', STR_PAD_LEFT);
                        $invoiceRef = 'INV-'.$transaction->transaction_date?->timezone('Asia/Manila')->format('Ym').'-'.str_pad((string) ($transaction->reservation_id ?: $transaction->id), 4, '0', STR_PAD_LEFT);
                        $source = match ($category) {
                            'room' => 'Front Office',
                            'food' => 'Outlet Station',
                            'deposit' => 'Billing Counter',
                            default => 'Records Desk',
                        };
                        $audit = substr(strtoupper(hash('crc32b', $txnRef.$transaction->amount.$status)), 0, 8);
                        $searchText = strtolower(implode(' ', [$txnRef, $invoiceRef, $typeLabel, $transaction->guest?->full_name, $transaction->guest?->email, $transaction->room?->room_number, $transaction->amount, $transaction->status, $transaction->description, $source]));
                        $csv = implode(',', [
                            $transaction->transaction_date?->timezone('Asia/Manila')->format('Y-m-d H:i:s'),
                            $txnRef,
                            $source,
                            $transaction->guest?->full_name ?? 'N/A',
                            $transaction->room?->room_number ?? 'N/A',
                            $typeLabel,
                            number_format((float) $transaction->amount, 2, '.', ''),
                            $status,
                            $invoiceRef,
                            $audit,
                        ]);
                    @endphp
                    <tr class="group transition hover:bg-surface-low/70" data-owner-txn-row data-category="{{ $category }}" data-search="{{ e($searchText) }}" data-csv="{{ e($csv) }}">
                        <td class="px-4 py-3.5 align-top">
                            <div class="font-bold text-primary">{{ $transaction->transaction_date?->timezone('Asia/Manila')->format('h:i:s A') ?? '--:--' }}</div>
                            <div class="mt-0.5 font-mono text-[11px] text-slate-500">{{ $txnRef }}</div>
                            <div class="text-[11px] text-slate-400">{{ $transaction->transaction_date?->timezone('Asia/Manila')->format('M d, Y') ?? 'No date' }}</div>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="flex items-center gap-2">
                                <span @class(['flex h-8 w-8 items-center justify-center rounded-lg', 'bg-blue-50 text-blue-700' => $category === 'room', 'bg-amber-50 text-amber-700' => $category === 'food', 'bg-emerald-50 text-emerald-700' => $category === 'deposit', 'bg-slate-100 text-slate-600' => $category === 'other'])>
                                    <span class="material-symbols-outlined text-lg">{{ $category === 'room' ? 'bed' : ($category === 'food' ? 'restaurant' : ($category === 'deposit' ? 'account_balance_wallet' : 'receipt_long')) }}</span>
                                </span>
                                <div><p class="font-bold text-primary">{{ $source }}</p><p class="text-xs text-slate-500">{{ $typeLabel }}</p></div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <p class="font-bold text-primary">{{ $transaction->guest?->full_name ?? 'Guest not linked' }}</p>
                            <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $invoiceRef }}</p>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <span class="rounded border border-line bg-surface-low px-2.5 py-1 text-xs font-semibold text-primary">Room {{ $transaction->room?->room_number ?? 'N/A' }}</span>
                            <span class="mt-1 block text-[11px] text-slate-400">{{ ucfirst($category) }} transaction record</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-right align-top">
                            <span class="font-serif text-lg font-bold text-primary">{{ number_format((float) $transaction->amount, 2) }}</span>
                            <span class="block text-[11px] text-slate-400">Owner review</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-center align-top">
                            <span @class(['inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold', 'border-emerald-200 bg-emerald-50 text-emerald-700' => $isCleared, 'border-amber-200 bg-amber-50 text-amber-700' => ! $isCleared])>
                                <span class="material-symbols-outlined text-[13px]">{{ $isCleared ? 'check_circle' : 'schedule' }}</span>
                                {{ str_replace('_', ' ', ucfirst($status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right align-top">
                            <p class="font-mono text-xs font-bold text-primary">{{ $audit }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">{{ $transaction->description ?: 'Synced billing entry' }}</p>
                        </td>
                        <td class="px-4 py-3.5 text-right align-top">
                            <button type="button" class="rounded border border-line bg-white px-2.5 py-1 text-xs font-bold text-primary transition hover:border-secondary" data-owner-txn-details="{{ $txnRef }}">Details</button>
                        </td>
                    </tr>
                    <tr class="hidden bg-surface-low" data-owner-txn-detail="{{ $txnRef }}">
                        <td colspan="8" class="px-4 py-4">
                            <div class="grid gap-3 md:grid-cols-4">
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Transaction</p><p class="mt-1 font-mono text-xs font-bold">{{ $txnRef }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Invoice</p><p class="mt-1 font-mono text-xs font-bold">{{ $invoiceRef }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Source</p><p class="mt-1 text-sm font-bold">{{ $source }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Control</p><p class="mt-1 text-sm font-bold text-primary">Owner View Only</p></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center text-sm text-slate-500">No transaction records are available in the owner stream yet.</td>
                    </tr>
                @endforelse
                @if ($transactions->isNotEmpty())
                    <tr id="ownerTxnNoMatches" class="hidden">
                        <td colspan="8" class="px-6 py-16 text-center text-sm text-slate-500">No transactions match the current owner filters.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="flex flex-col justify-between gap-3 border-t border-line bg-surface-low p-4 text-sm text-slate-600 md:flex-row md:items-center">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">shield</span><span>Showing <strong class="font-bold text-primary" data-owner-txn-visible>{{ $transactions->count() }}</strong> recent transactions • Recorded Shift Registration: <strong class="font-bold text-primary">PHP {{ number_format($recordedLedgerTotal, 2) }}</strong></span></div>
        <div class="flex items-center gap-3"><span class="font-mono text-xs text-slate-500">Audit Hash: {{ $auditHash }}</span><span class="rounded bg-secondary-fixed/30 px-2 py-0.5 text-xs font-bold text-secondary">Verified Records</span></div>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Revenue Velocity</span><span class="material-symbols-outlined text-secondary">speed</span></div>
        <h4 class="mt-3 font-serif text-2xl font-bold text-primary">Live Records</h4>
        <p class="mt-2 text-sm leading-6 text-slate-500">Average transaction posting and settlement visibility across all desk terminals.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Discrepancy Rate</span><span class="font-bold text-emerald-700">{{ number_format($discrepancyRate, 2) }}%</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Payment Balancing</span><span class="material-symbols-outlined text-secondary">account_balance_wallet</span></div>
        <h4 class="mt-3 font-serif text-2xl font-bold text-primary">PHP {{ number_format($pendingLedgerTotal, 2) }} Difference</h4>
        <p class="mt-2 text-sm leading-6 text-slate-500">Open or unpaid records remain visible for owner review without correction controls.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Pending list</span><span class="font-bold text-secondary">{{ $varianceCount }}</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Required Tax Management</span><span class="material-symbols-outlined text-secondary">gavel</span></div>
        <h4 class="mt-3 font-serif text-2xl font-bold text-primary">SBMA Tax Review</h4>
        <p class="mt-2 text-sm leading-6 text-slate-500">Transaction records are available for executive monitoring and required tax review.</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Certification ID</span><span class="font-mono font-bold text-primary">SBF-2026-TX88</span></div>
    </div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-owner-txn-row]'));
        if (!rows.length) {
            return;
        }

        const searchInput = document.getElementById('ownerTxnSearch');
        const filterButtons = Array.from(document.querySelectorAll('[data-owner-txn-filter]'));
        const resetButton = document.getElementById('ownerTxnReset');
        const exportButton = document.getElementById('ownerTxnExport');
        const noMatches = document.getElementById('ownerTxnNoMatches');
        const visibleCounter = document.querySelector('[data-owner-txn-visible]');
        let activeFilter = 'all';

        const detailsFor = (key) => document.querySelector(`[data-owner-txn-detail="${CSS.escape(key)}"]`);

        function setActiveButton() {
            filterButtons.forEach((button) => {
                const active = button.dataset.ownerTxnFilter === activeFilter;
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('border-primary', active);
                button.classList.toggle('bg-white', !active);
                button.classList.toggle('text-slate-600', !active);
                button.classList.toggle('border-line', !active);
            });
        }

        function applyFilters() {
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const queryMatch = !query || (row.dataset.search || '').includes(query);
                const filterMatch = activeFilter === 'all' || row.dataset.category === activeFilter;
                const show = queryMatch && filterMatch;
                row.classList.toggle('hidden', !show);

                const toggle = row.querySelector('[data-owner-txn-details]');
                const detail = detailsFor(toggle?.dataset.ownerTxnDetails || '');
                if (!show && detail) {
                    detail.classList.add('hidden');
                    if (toggle) {
                        toggle.textContent = 'Details';
                    }
                }

                if (show) {
                    visible += 1;
                }
            });

            visibleCounter && (visibleCounter.textContent = visible);
            noMatches?.classList.toggle('hidden', visible !== 0);
            setActiveButton();
        }

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = button.dataset.ownerTxnFilter || 'all';
                applyFilters();
            });
        });

        document.querySelectorAll('[data-owner-txn-details]').forEach((button) => {
            button.addEventListener('click', () => {
                const detail = detailsFor(button.dataset.ownerTxnDetails || '');
                if (!detail) {
                    return;
                }
                const hidden = detail.classList.toggle('hidden');
                button.textContent = hidden ? 'Details' : 'Hide';
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
            const csv = ['Date,Transaction,Source,Guest,Room,Type,Amount,Status,Invoice,Audit']
                .concat(visibleRows.map((row) => row.dataset.csv))
                .join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `owner-transactions-${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        });

        applyFilters();
    })();
</script>
