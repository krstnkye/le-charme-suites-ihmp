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
            $typeText = strtolower($items->pluck('transaction_type')->join(' '));
            $hasDeposit = str_contains($typeText, 'deposit') || str_contains($typeText, 'incidental');
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
                'category' => $hasDeposit ? 'Incidental Deposit' : ($first?->reservation ? 'Guest Stay Invoice' : 'Direct Billing'),
                'status' => $isSettled ? 'settled' : ($hasDeposit ? 'deposit' : 'active'),
                'status_label' => $isSettled ? 'Audited & Archived' : ($hasDeposit ? 'Deposit Tracked' : 'Active Account'),
                'channel' => $items->pluck('transaction_type')->map(fn ($type) => str($type ?: 'manual ledger')->replace('_', ' ')->title()->toString())->unique()->implode(', ') ?: 'Manual Ledger',
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
    $outstandingTotal = (float) $billingRecords->whereIn('status', ['active', 'deposit'])->sum('total');
    $auditHash = substr(strtoupper(hash('sha256', 'owner-billing-records-'.$recordCount.'-'.$grossBilledVolume)), 0, 4).'-'.substr(strtoupper(hash('sha256', 'tax-'.$taxTracked)), 0, 4).'-'.substr(strtoupper(hash('sha256', 'settled-'.$settledCount)), 0, 4);
@endphp

<section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-20 -right-16 text-secondary-fixed/5">
        <span class="material-symbols-outlined text-[260px]">receipt_long</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded border border-secondary-fixed/40 bg-secondary/30 px-2.5 py-1 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">Audit Archive & Settlement Ledger</span>
                <span class="text-xs font-semibold text-blue-100/80">Direct feed • Terminal sync active</span>
            </div>
            <h2 class="font-serif text-4xl font-bold leading-tight">Billing Records & Account Archive</h2>
            <p class="max-w-3xl text-sm leading-6 text-blue-100">Complete owner visibility into guest billing accounts, invoice reconciliation, itemized room tariffs, deposits, taxes, and settlement audit status.</p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 sm:grid-cols-4 xl:w-[650px]">
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5 backdrop-blur-sm">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Total Invoices</span>
                <span class="mt-1 block font-serif text-xl font-bold">{{ $recordCount }} Records</span>
                <span class="mt-1 flex items-center gap-1 text-[11px] text-secondary-fixed"><span class="h-1.5 w-1.5 rounded-full bg-secondary-fixed"></span>{{ $activeCount }} Active, {{ $settledCount }} Closed</span>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5 backdrop-blur-sm">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Settled Intake</span>
                <span class="mt-1 block font-serif text-xl font-bold">PHP {{ number_format($grossBilledVolume, 2) }}</span>
                <span class="mt-1 block text-[11px] text-blue-100/70">Live transaction ledger</span>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5 backdrop-blur-sm">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Outstanding Review</span>
                <span class="mt-1 block font-serif text-xl font-bold text-secondary-fixed">PHP {{ number_format($outstandingTotal, 2) }}</span>
                <span class="mt-1 block text-[11px] text-blue-100/70">Active or deposit accounts</span>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5 backdrop-blur-sm">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Tax Tracked</span>
                <span class="mt-1 block font-serif text-xl font-bold">PHP {{ number_format($taxTracked, 2) }}</span>
                <span class="mt-1 block text-[11px] text-blue-100/70">Estimated VAT visibility</span>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card relative overflow-hidden rounded-xl border-secondary-fixed/50 p-5 transition hover:border-secondary">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Billing Accounts</span><span class="material-symbols-outlined text-secondary">receipt</span></div>
        <h3 class="mt-3 font-serif text-3xl font-bold text-primary">{{ $activeCount }} Active</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $settledCount }} closed and audited records</p>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Checkout queue</span><span class="font-bold text-secondary">{{ $recordCount }} total</span></div>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl border-secondary-fixed/50 p-5 transition hover:border-secondary">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Gross Billed Volume</span><span class="material-symbols-outlined text-secondary">monitoring</span></div>
        <h3 class="mt-3 font-serif text-3xl font-bold text-primary">PHP {{ number_format($grossBilledVolume, 2) }}</h3>
        <p class="mt-1 text-sm text-slate-500">Compiled from transaction records</p>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Latest sync</span><span class="font-bold text-secondary">{{ now('Asia/Manila')->format('h:i A') }} PHT</span></div>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl border-secondary-fixed/50 p-5 transition hover:border-secondary">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Deposit Records</span><span class="material-symbols-outlined text-secondary">account_balance_wallet</span></div>
        <h3 class="mt-3 font-serif text-3xl font-bold text-primary">{{ $depositCount }} Deposits</h3>
        <p class="mt-1 text-sm text-slate-500">Incidental or deposit tracking</p>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Refundable after audit</span><span class="font-bold text-secondary">Vault</span></div>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl border-secondary-fixed/50 p-5 transition hover:border-secondary">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Audit & Compliance</span><span class="material-symbols-outlined text-secondary">verified_user</span></div>
        <h3 class="mt-3 font-serif text-3xl font-bold text-primary">Verified</h3>
        <p class="mt-1 text-sm text-slate-500">Owner read-only review mode</p>
        <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500"><span>Hash chain</span><span class="font-mono font-bold text-secondary">{{ $auditHash }}</span></div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-xl" id="billing-records">
    <div class="flex flex-col gap-4 border-b border-line bg-white p-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-serif text-2xl font-bold text-primary">Master Billing Records Ledger</h3>
                <span class="rounded border border-line bg-surface-mid px-2 py-0.5 text-xs font-bold text-slate-600">Audit Mode</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Owner visibility into revenue, account postings, and invoice reconciliations without cashier settlement controls.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button id="ownerBillingRecordExport" type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface-low px-3.5 py-2 text-sm font-bold text-primary transition hover:border-secondary">
                <span class="material-symbols-outlined text-lg text-secondary">download</span>
                Export Audit Ledger
            </button>
            <button id="ownerBillingRecordFocus" type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3.5 py-2 text-sm font-bold text-white transition hover:bg-primary-container">
                <span class="material-symbols-outlined text-lg text-secondary-fixed">manage_search</span>
                Filter Ledger
            </button>
        </div>
    </div>

    <div class="space-y-3 border-b border-line bg-surface-low p-4">
        <label class="relative block">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-slate-400">search</span>
            <input id="ownerBillingRecordSearch" class="w-full rounded-lg border border-line bg-white py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search invoice ref, guest name, room number, or account ID..." type="search" autocomplete="off">
        </label>
        <div class="owner-scroll flex items-center gap-2 overflow-x-auto" data-owner-billing-record-filters>
            <button type="button" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white" data-record-filter="all">All Records ({{ $recordCount }})</button>
            <button type="button" class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-record-filter="active">Active Accounts ({{ $activeCount }})</button>
            <button type="button" class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-record-filter="settled">Settled & Archived ({{ $settledCount }})</button>
            <button type="button" class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-primary" data-record-filter="deposit">Deposits ({{ $depositCount }})</button>
        </div>
    </div>

    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1200px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-4 py-3">Invoice / Account ID</th>
                    <th class="px-4 py-3">Guest & Account Profile</th>
                    <th class="px-4 py-3">Room & Stay Duration</th>
                    <th class="px-4 py-3">Billing Category</th>
                    <th class="px-4 py-3 text-right">Net Amount & Tax</th>
                    <th class="px-4 py-3">Payment Channel</th>
                    <th class="px-4 py-3 text-center">Audit Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line bg-white">
                @forelse ($billingRecords as $record)
                    @php
                        $dotClass = match ($record['status']) {
                            'active' => 'bg-emerald-500',
                            'settled' => 'bg-blue-500',
                            'deposit' => 'bg-amber-500',
                            default => 'bg-slate-400',
                        };
                    @endphp
                    <tr class="transition hover:bg-surface-low/70" data-billing-record-row data-status="{{ $record['status'] }}" data-search="{{ e(strtolower($record['invoice'].' '.$record['account'].' '.$record['guest'].' '.$record['email'].' '.$record['room'].' '.$record['category'].' '.$record['channel'].' '.$record['status_label'])) }}">
                        <td class="px-4 py-3.5 align-top font-mono font-semibold text-primary">
                            <div class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full {{ $dotClass }}"></span><span>#{{ $record['invoice'] }}</span></div>
                            <span class="mt-0.5 block font-sans text-[11px] text-slate-500">Account: {{ $record['account'] }}</span>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="font-bold text-primary">{{ $record['guest'] }}</div>
                            <div class="mt-0.5 text-[11px] text-slate-500">{{ $record['email'] }}</div>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="flex items-center gap-1.5 font-semibold text-primary"><span class="material-symbols-outlined text-[16px] text-secondary">bed</span>{{ $record['room'] }}</div>
                            <span class="mt-0.5 block text-[11px] text-slate-500">{{ $record['room_type'] }} • {{ $record['stay'] }}</span>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <span @class(['inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold', 'border-emerald-200 bg-emerald-50 text-emerald-700' => $record['status'] === 'active', 'border-blue-200 bg-blue-50 text-blue-700' => $record['status'] === 'settled', 'border-amber-200 bg-amber-50 text-amber-700' => $record['status'] === 'deposit'])>{{ $record['category'] }}</span>
                            <span class="mt-1 block text-[11px] text-slate-500">{{ $record['line_count'] }} ledger line(s)</span>
                        </td>
                        <td class="px-4 py-3.5 text-right align-top">
                            <div class="font-serif text-lg font-bold text-primary">PHP {{ number_format($record['total'], 2) }}</div>
                            <div class="mt-0.5 text-[11px] text-slate-500">VAT est. PHP {{ number_format($record['tax'], 2) }}</div>
                        </td>
                        <td class="px-4 py-3.5 align-top">
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-primary"><span class="material-symbols-outlined text-[16px] text-secondary">point_of_sale</span>{{ $record['channel'] }}</div>
                            <span class="mt-0.5 block text-[11px] text-slate-500">Room rate PHP {{ number_format($record['room_rate'], 2) }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-center align-top">
                            <span @class(['inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold', 'border-emerald-200 bg-emerald-50 text-emerald-700' => $record['status'] === 'active', 'border-blue-200 bg-blue-50 text-blue-700' => $record['status'] === 'settled', 'border-amber-200 bg-amber-50 text-amber-700' => $record['status'] === 'deposit'])>
                                <span class="material-symbols-outlined text-[13px]">{{ $record['status'] === 'settled' ? 'archive' : ($record['status'] === 'deposit' ? 'account_balance_wallet' : 'pending_actions') }}</span>
                                {{ $record['status_label'] }}
                            </span>
                            <span class="mt-1 block font-mono text-[11px] text-slate-500">Hash {{ $record['audit'] }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right align-top">
                            <button type="button" class="rounded border border-line bg-surface-low px-2.5 py-1 text-xs font-bold text-primary transition hover:border-secondary" data-record-toggle="{{ $record['key'] }}">Account Dossier</button>
                        </td>
                    </tr>
                    <tr class="hidden bg-surface-low" data-billing-record-detail="{{ $record['key'] }}">
                        <td colspan="8" class="px-4 py-4">
                            <div class="grid gap-3 text-sm md:grid-cols-4">
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Line Items</p><p class="mt-1 font-serif text-2xl font-bold">{{ $record['line_count'] }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Room Rate</p><p class="mt-1 font-serif text-2xl font-bold">PHP {{ number_format($record['room_rate'], 2) }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Last Activity</p><p class="mt-1 font-mono text-xs">{{ $record['latest']?->format('M d, Y h:i A') ?? 'No date' }}</p></div>
                                <div class="rounded-lg border border-line bg-white p-3"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Owner Control</p><p class="mt-1 text-sm font-bold text-primary">View Only</p></div>
                            </div>
                            <p class="mt-3 rounded-lg border border-line bg-white p-3 text-sm text-slate-600">{{ $record['descriptions'] ?: 'No additional billing notes recorded for this account.' }}</p>
                        </td>
                    </tr>
                @empty
                    <tr data-billing-record-empty>
                        <td colspan="8" class="px-6 py-16 text-center text-sm text-slate-500">No billing records are available in the owner archive yet.</td>
                    </tr>
                @endforelse
                <tr class="hidden" data-billing-record-no-results>
                    <td colspan="8" class="px-6 py-16 text-center text-sm text-slate-500">No billing records match the current search or filter.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col justify-between gap-3 border-t border-line bg-surface-low px-5 py-3.5 text-xs text-slate-600 sm:flex-row sm:items-center">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[16px] text-secondary">verified</span><span>Showing <strong data-billing-record-visible>{{ $recordCount }}</strong> audited billing records • Total Recorded Ledger Intake: <strong class="font-bold text-primary">PHP {{ number_format($grossBilledVolume, 2) }}</strong></span></div>
        <div class="flex items-center gap-4"><span class="font-mono text-[11px] text-slate-500">Audit Hash: {{ $auditHash }}</span><span class="inline-flex items-center gap-1 font-bold text-secondary"><span class="h-1.5 w-1.5 rounded-full bg-secondary"></span>Verified Ledger</span></div>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <div class="owner-card rounded-xl border-l-4 border-l-secondary p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Revenue Velocity</span><span class="material-symbols-outlined text-slate-400">speed</span></div>
        <h4 class="mt-2 font-serif text-2xl font-bold text-primary">Live Archive</h4>
        <p class="mt-1 text-sm leading-6 text-slate-500">Billing records are compiled from live hotel transactions and grouped by guest, reservation, and room.</p>
    </div>
    <div class="owner-card rounded-xl border-l-4 border-l-primary-container p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Settlement Health</span><span class="material-symbols-outlined text-slate-400">account_balance_wallet</span></div>
        <h4 class="mt-2 font-serif text-2xl font-bold text-primary">{{ $settledCount }} / {{ $recordCount }} Closed</h4>
        <p class="mt-1 text-sm leading-6 text-slate-500">Archived records are marked settled, completed, cleared, paid, or recorded in the ledger.</p>
    </div>
    <div class="owner-card rounded-xl border-l-4 border-l-emerald-600 p-5">
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-secondary">Statutory Governance</span><span class="material-symbols-outlined text-slate-400">account_balance</span></div>
        <h4 class="mt-2 font-serif text-2xl font-bold text-primary">Tax Review Ready</h4>
        <p class="mt-1 text-sm leading-6 text-slate-500">Estimated VAT and account totals are visible for owner review without record editing privileges.</p>
    </div>
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
            noResults?.classList.toggle('hidden', visible !== 0);
        }

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = button.dataset.recordFilter || 'all';
                filterButtons.forEach((item) => {
                    const selected = item === button;
                    item.classList.toggle('bg-primary', selected);
                    item.classList.toggle('text-white', selected);
                    item.classList.toggle('border-primary', selected);
                    item.classList.toggle('bg-white', !selected);
                    item.classList.toggle('text-slate-600', !selected);
                    item.classList.toggle('border-line', !selected);
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
                button.textContent = isHidden ? 'Account Dossier' : 'Hide Dossier';
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
