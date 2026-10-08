@php
    $billingRecords = $transactions
        ->groupBy(function ($transaction) {
            return implode(':', [
                $transaction->reservation_id ?: 'no-reservation',
                $transaction->guest_id ?: 'guest-none',
                $transaction->room_id ?: 'room-none',
            ]);
        })
        ->map(function ($items, $key) {
            $ordered = $items->sortBy('transaction_date');
            $first = $ordered->first();
            $last = $items->sortByDesc('transaction_date')->first();
            $statusList = $items->pluck('status')->map(fn ($status) => strtolower((string) $status));
            $settledStatuses = ['paid', 'settled', 'completed', 'recorded', 'cleared'];
            $isSettled = $statusList->isNotEmpty() && $statusList->every(fn ($status) => in_array($status, $settledStatuses, true));
            $hasDeposit = $items->contains(fn ($transaction) => str_contains(strtolower((string) $transaction->transaction_type), 'deposit'));
            $roomRate = (float) ($first?->room?->rate ?? 0);
            $total = (float) $items->sum('amount');
            $tax = $total > 0 ? $total * 0.12 : 0;

            return [
                'key' => $key,
                'invoice' => 'INV-'.str_pad((string) ($first?->reservation_id ?: $first?->id ?: 0), 5, '0', STR_PAD_LEFT),
                'account' => 'ACCT-'.str_pad((string) ($first?->guest_id ?: $first?->id ?: 0), 4, '0', STR_PAD_LEFT),
                'guest' => $first?->guest?->full_name ?? 'Unassigned guest',
                'email' => $first?->guest?->email ?? 'No email recorded',
                'room' => $first?->room?->room_number ? 'Room '.$first->room->room_number : 'No room assigned',
                'room_type' => $first?->room?->type ?? 'Unspecified type',
                'stay' => $first?->reservation
                    ? $first->reservation->check_in_date?->format('M d').' - '.$first->reservation->check_out_date?->format('M d, Y')
                    : 'Walk-in / direct billing',
                'category' => $hasDeposit ? 'Deposit / Incidental' : ($first?->reservation ? 'Guest Stay Billing' : 'Direct Charge'),
                'status' => $isSettled ? 'settled' : ($hasDeposit ? 'deposit' : 'active'),
                'status_label' => $isSettled ? 'Settled & Archived' : ($hasDeposit ? 'Deposit Tracked' : 'Active Account'),
                'channel' => $items->pluck('transaction_type')->map(fn ($type) => str($type)->replace('_', ' ')->title()->toString())->unique()->implode(', ') ?: 'Manual ledger',
                'total' => $total,
                'tax' => $tax,
                'room_rate' => $roomRate,
                'line_count' => $items->count(),
                'latest' => $last?->transaction_date,
                'audit' => substr(strtoupper(hash('crc32b', $key.$total.$items->count())), 0, 8),
                'descriptions' => $items->pluck('description')->filter()->take(3)->implode(' | '),
            ];
        })
        ->sortByDesc('latest')
        ->values();

    $recordCount = $billingRecords->count();
    $settledCount = $billingRecords->where('status', 'settled')->count();
    $activeCount = $billingRecords->where('status', 'active')->count();
    $depositCount = $billingRecords->where('status', 'deposit')->count();
    $grossBilledVolume = (float) $billingRecords->sum('total');
    $taxTracked = (float) $billingRecords->sum('tax');
    $outstandingTotal = (float) $billingRecords->where('status', 'active')->sum('total');
    $auditHash = substr(strtoupper(hash('sha256', 'owner-billing-records-'.$recordCount.'-'.$grossBilledVolume)), 0, 12);
@endphp

<section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-md">
    <div class="pointer-events-none absolute -right-20 top-0 h-72 w-72 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-end">
        <div>
            <span class="rounded border border-secondary-fixed/40 bg-secondary/30 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Financial Governance</span>
            <h2 class="mt-3 font-serif text-4xl font-bold leading-tight">Billing Records & Account Archive</h2>
            <p class="mt-2 max-w-4xl text-sm leading-6 text-blue-100">Read-only owner view of grouped guest billing accounts, invoice totals, taxes, settlement status, and audit traceability.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 rounded-lg border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:w-[620px]">
            <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Records</p><p class="mt-1 font-serif text-2xl font-bold">{{ $recordCount }}</p></div>
            <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Active</p><p class="mt-1 font-serif text-2xl font-bold">{{ $activeCount }}</p></div>
            <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Settled</p><p class="mt-1 font-serif text-2xl font-bold">{{ $settledCount }}</p></div>
            <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Gross Billed</p><p class="mt-1 font-serif text-xl font-bold">PHP {{ number_format($grossBilledVolume, 2) }}</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Billing Accounts</span><span class="material-symbols-outlined text-secondary">receipt_long</span></div><p class="mt-3 font-serif text-3xl font-bold">{{ $recordCount }}</p><p class="mt-1 text-sm text-slate-500">Grouped from transaction records</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Outstanding Review</span><span class="material-symbols-outlined text-secondary">pending_actions</span></div><p class="mt-3 font-serif text-3xl font-bold">PHP {{ number_format($outstandingTotal, 2) }}</p><p class="mt-1 text-sm text-slate-500">{{ $activeCount }} active accounts</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Deposit Records</span><span class="material-symbols-outlined text-secondary">account_balance_wallet</span></div><p class="mt-3 font-serif text-3xl font-bold">{{ $depositCount }}</p><p class="mt-1 text-sm text-slate-500">Incidental or deposit tracking</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tax Visibility</span><span class="material-symbols-outlined text-secondary">verified_user</span></div><p class="mt-3 font-serif text-3xl font-bold">PHP {{ number_format($taxTracked, 2) }}</p><p class="mt-1 text-sm text-slate-500">Estimated VAT from records</p></div>
</section>

<section class="owner-card overflow-hidden rounded-xl" id="billing-records">
    <div class="flex flex-col gap-4 border-b border-line p-5 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Owner Access / Billing Records</p>
            <h3 class="mt-1 font-serif text-2xl font-bold">Master Billing Records Ledger</h3>
            <p class="mt-1 text-sm text-slate-500">Invoice and account summaries compiled from the live transaction ledger.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button id="ownerBillingRecordExport" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2 text-sm font-bold text-primary shadow-sm transition hover:border-secondary">
                <span class="material-symbols-outlined text-base">download</span> Export Audit Ledger
            </button>
            <button id="ownerBillingRecordFocus" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                <span class="material-symbols-outlined text-base">filter_alt</span> Filter Ledger
            </button>
        </div>
    </div>

    <div class="border-b border-line bg-surface-low p-4">
        <div class="grid gap-3 xl:grid-cols-[1fr_auto] xl:items-center">
            <label class="relative block">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input id="ownerBillingRecordSearch" type="search" class="w-full rounded-lg border-line bg-white py-2.5 pl-10 text-sm shadow-sm focus:border-secondary focus:ring-secondary" placeholder="Search invoice, account, guest, room, or payment type...">
            </label>
            <div class="flex flex-wrap gap-2" data-owner-billing-record-filters>
                <button class="rounded-full bg-primary px-4 py-2 text-xs font-bold text-white" data-record-filter="all">All Records <span class="ml-1 rounded-full bg-white/15 px-1.5">{{ $recordCount }}</span></button>
                <button class="rounded-full bg-white px-4 py-2 text-xs font-bold text-primary ring-1 ring-line" data-record-filter="active">Active Accounts <span class="ml-1 rounded-full bg-surface-mid px-1.5">{{ $activeCount }}</span></button>
                <button class="rounded-full bg-white px-4 py-2 text-xs font-bold text-primary ring-1 ring-line" data-record-filter="settled">Settled & Archived <span class="ml-1 rounded-full bg-surface-mid px-1.5">{{ $settledCount }}</span></button>
                <button class="rounded-full bg-white px-4 py-2 text-xs font-bold text-primary ring-1 ring-line" data-record-filter="deposit">Deposits <span class="ml-1 rounded-full bg-surface-mid px-1.5">{{ $depositCount }}</span></button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Invoice / Account ID</th>
                    <th class="px-4 py-3">Guest & Account Profile</th>
                    <th class="px-4 py-3">Room & Stay Duration</th>
                    <th class="px-4 py-3">Billing Category</th>
                    <th class="px-4 py-3">Net Amount & Tax</th>
                    <th class="px-4 py-3">Payment Channel</th>
                    <th class="px-4 py-3">Audit Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody id="ownerBillingRecordRows" class="divide-y divide-line bg-white">
                @forelse ($billingRecords as $record)
                    <tr data-billing-record-row data-status="{{ $record['status'] }}" data-search="{{ strtolower($record['invoice'].' '.$record['account'].' '.$record['guest'].' '.$record['email'].' '.$record['room'].' '.$record['category'].' '.$record['channel']) }}">
                        <td class="px-4 py-4">
                            <p class="font-mono text-xs font-bold text-primary">{{ $record['invoice'] }}</p>
                            <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $record['account'] }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-bold">{{ $record['guest'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $record['email'] }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-bold">{{ $record['room'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $record['room_type'] }} • {{ $record['stay'] }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-blue-50 text-blue-700' => $record['status'] === 'active', 'bg-emerald-50 text-emerald-700' => $record['status'] === 'settled', 'bg-amber-50 text-amber-700' => $record['status'] === 'deposit'])>{{ $record['category'] }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-serif text-lg font-bold">PHP {{ number_format($record['total'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-500">Tax est. PHP {{ number_format($record['tax'], 2) }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $record['channel'] }}</td>
                        <td class="px-4 py-4">
                            <p @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-bold', 'bg-blue-50 text-blue-700' => $record['status'] === 'active', 'bg-emerald-50 text-emerald-700' => $record['status'] === 'settled', 'bg-amber-50 text-amber-700' => $record['status'] === 'deposit'])>{{ $record['status_label'] }}</p>
                            <p class="mt-1 font-mono text-[11px] text-slate-500">Hash {{ $record['audit'] }}</p>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <button class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary transition hover:border-secondary" data-record-toggle="{{ $record['key'] }}">View Summary</button>
                        </td>
                    </tr>
                    <tr class="hidden bg-surface-low" data-billing-record-detail="{{ $record['key'] }}">
                        <td colspan="8" class="px-4 py-4">
                            <div class="grid gap-3 text-sm md:grid-cols-4">
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Line Items</p><p class="mt-1 font-serif text-2xl font-bold">{{ $record['line_count'] }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Room Rate</p><p class="mt-1 font-serif text-2xl font-bold">PHP {{ number_format($record['room_rate'], 2) }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Last Activity</p><p class="mt-1 font-mono text-xs">{{ $record['latest']?->format('M d, Y h:i A') ?? 'No date' }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Owner Control</p><p class="mt-1 text-xs text-slate-600">View-only account review</p></div>
                            </div>
                            <p class="mt-3 rounded-lg border border-line bg-white p-3 text-sm text-slate-600">{{ $record['descriptions'] ?: 'No additional billing notes recorded for this account.' }}</p>
                        </td>
                    </tr>
                @empty
                    <tr data-billing-record-empty>
                        <td colspan="8" class="px-4 py-16 text-center text-slate-500">No billing records are available in the owner archive yet.</td>
                    </tr>
                @endforelse
                <tr class="hidden" data-billing-record-no-results>
                    <td colspan="8" class="px-4 py-16 text-center text-slate-500">No billing records match the current search or filter.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-line bg-surface-low px-5 py-4 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
        <span>Showing <strong data-billing-record-visible>{{ $recordCount }}</strong> of {{ $recordCount }} billing records</span>
        <span class="font-mono text-xs">Ledger audit hash: {{ $auditHash }}</span>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Revenue Velocity</p><h3 class="mt-2 font-serif text-2xl font-bold">PHP {{ number_format($grossBilledVolume, 2) }}</h3><p class="mt-2 text-sm leading-6 text-slate-500">Total billing volume reflected by current live transaction records.</p></div>
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Settlement Health</p><h3 class="mt-2 font-serif text-2xl font-bold">{{ $settledCount }} / {{ $recordCount }}</h3><p class="mt-2 text-sm leading-6 text-slate-500">Accounts marked settled, completed, cleared, or recorded in the ledger.</p></div>
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Governance Notice</p><h3 class="mt-2 font-serif text-2xl font-bold">View Only</h3><p class="mt-2 text-sm leading-6 text-slate-500">Owner accounts can inspect billing records, but posting and correction controls remain with the billing department or admin.</p></div>
</section>

<script>
    (() => {
        const searchInput = document.getElementById('ownerBillingRecordSearch');
        const focusButton = document.getElementById('ownerBillingRecordFocus');
        const exportButton = document.getElementById('ownerBillingRecordExport');
        const rows = Array.from(document.querySelectorAll('[data-billing-record-row]'));
        const noResults = document.querySelector('[data-billing-record-no-results]');
        const visibleCounter = document.querySelector('[data-billing-record-visible]');
        const filterButtons = Array.from(document.querySelectorAll('[data-record-filter]'));
        let activeFilter = 'all';

        const detailFor = (key) => document.querySelector(`[data-billing-record-detail="${CSS.escape(key)}"]`);

        function applyFilters() {
            const term = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const matchesFilter = activeFilter === 'all' || row.dataset.status === activeFilter;
                const matchesSearch = !term || (row.dataset.search || '').includes(term);
                const show = matchesFilter && matchesSearch;
                row.classList.toggle('hidden', !show);
                const detail = detailFor(row.querySelector('[data-record-toggle]')?.dataset.recordToggle || '');
                if (!show && detail) {
                    detail.classList.add('hidden');
                }
                if (show) {
                    visible += 1;
                }
            });

            if (visibleCounter) {
                visibleCounter.textContent = visible;
            }
            if (noResults) {
                noResults.classList.toggle('hidden', visible !== 0);
            }
        }

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = button.dataset.recordFilter || 'all';
                filterButtons.forEach((item) => {
                    const selected = item === button;
                    item.classList.toggle('bg-primary', selected);
                    item.classList.toggle('text-white', selected);
                    item.classList.toggle('bg-white', !selected);
                    item.classList.toggle('text-primary', !selected);
                    item.classList.toggle('ring-1', !selected);
                    item.classList.toggle('ring-line', !selected);
                });
                applyFilters();
            });
        });

        searchInput?.addEventListener('input', applyFilters);
        focusButton?.addEventListener('click', () => {
            searchInput?.focus();
            searchInput?.select();
        });

        document.querySelectorAll('[data-record-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const detail = detailFor(button.dataset.recordToggle || '');
                if (!detail) {
                    return;
                }
                const isHidden = detail.classList.toggle('hidden');
                button.textContent = isHidden ? 'View Summary' : 'Hide Summary';
            });
        });

        exportButton?.addEventListener('click', () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const header = ['Invoice', 'Account', 'Guest', 'Room', 'Category', 'Amount', 'Audit Status'];
            const csvRows = visibleRows.map((row) => {
                const cells = Array.from(row.children).slice(0, 7).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim());
                return cells.map((value) => `"${value.replace(/"/g, '""')}"`).join(',');
            });
            const blob = new Blob([[header.join(','), ...csvRows].join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `owner-billing-records-${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        });

        applyFilters();
    })();
</script>
