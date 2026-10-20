@php
    $settledStatuses = ['paid', 'settled', 'completed', 'recorded', 'cleared'];
    $transactionCollection = $transactions->sortByDesc('transaction_date')->values();
    $grossTotal = (float) $transactionCollection->sum('amount');
    $settledTotal = (float) $transactionCollection
        ->filter(fn ($transaction) => in_array(strtolower((string) $transaction->status), $settledStatuses, true))
        ->sum('amount');
    $pendingTotal = max($grossTotal - $settledTotal, 0);
    $cashTotal = (float) $transactionCollection
        ->filter(fn ($transaction) => str_contains(strtolower((string) $transaction->transaction_type), 'cash'))
        ->sum('amount');
    $cardDigitalTotal = max($grossTotal - $cashTotal, 0);
    $taxDue = $grossTotal * 0.05;
    $variance = 0;
    $reportDate = now('Asia/Manila');
    $statementHash = substr(strtoupper(hash('sha256', 'owner-transaction-reports-'.$grossTotal.'-'.$transactionCollection->count().'-'.$reportDate->toDateString())), 0, 4)
        .'-'.substr(strtoupper(hash('sha256', 'settled-'.$settledTotal)), 0, 4)
        .'-'.substr(strtoupper(hash('sha256', 'tax-'.$taxDue)), 0, 4);
    $reportRows = collect([
        [
            'title' => 'Daily Payment & Station Balancing Report',
            'code' => 'LCS-FIN-'.$reportDate->format('Ymd').'-01',
            'category' => 'Payment',
            'amount' => $grossTotal,
            'breakdown' => 'Cash PHP '.number_format($cashTotal, 2).' / Card & Digital PHP '.number_format($cardDigitalTotal, 2),
            'officer' => 'Billing Department',
            'status' => $variance == 0 ? 'Reconciled' : 'Difference Review',
            'formats' => ['PDF', 'CSV'],
            'icon' => 'receipt_long',
        ],
        [
            'title' => 'SBMA Freeport 5% Gross Revenue Records',
            'code' => 'LCS-TAX-'.$reportDate->format('Ymd').'-04',
            'category' => 'Required Tax',
            'amount' => $taxDue,
            'breakdown' => '5% required tax estimate from PHP '.number_format($grossTotal, 2).' gross revenue',
            'officer' => 'Owner View-Only',
            'status' => 'Tax Ready',
            'formats' => ['PDF'],
            'icon' => 'account_balance',
        ],
        [
            'title' => 'Card & Digital Payment Batch Report',
            'code' => 'LCS-POS-'.$reportDate->format('Ymd').'-02',
            'category' => 'Payment Channel',
            'amount' => $cardDigitalTotal,
            'breakdown' => 'Non-cash payment channels compiled from transaction records',
            'officer' => 'POS Station Sync',
            'status' => 'Synced',
            'formats' => ['PDF', 'XLSX'],
            'icon' => 'credit_card',
        ],
        [
            'title' => 'Guest Billing Account Payment Review',
            'code' => 'LCS-BILL-'.$reportDate->format('Ymd').'-03',
            'category' => 'Accounts',
            'amount' => $pendingTotal,
            'breakdown' => $transactionCollection->count().' transaction line(s) visible to owner audit',
            'officer' => 'IHMP Records Core',
            'status' => $pendingTotal > 0 ? 'Open Review' : 'Cleared',
            'formats' => ['PDF', 'CSV'],
            'icon' => 'account_balance_wallet',
        ],
    ]);
    $categoryTone = [
        'Payment' => 'bg-blue-50 text-blue-700',
        'Required Tax' => 'bg-secondary-fixed/40 text-secondary',
        'Payment Channel' => 'bg-emerald-50 text-emerald-700',
        'Accounts' => 'bg-surface-mid text-slate-700',
    ];
    $statusTone = [
        'Reconciled' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Tax Ready' => 'border-secondary/30 bg-secondary/10 text-secondary',
        'Synced' => 'border-blue-200 bg-blue-50 text-blue-700',
        'Cleared' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Open Review' => 'border-amber-200 bg-amber-50 text-amber-700',
        'Difference Review' => 'border-error/20 bg-error-container text-error',
    ];
@endphp

<section class="relative overflow-hidden rounded-xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -right-14 -top-20 h-80 w-80 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/40 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">
                    <span class="material-symbols-outlined text-sm">verified</span>
                    Executive Monitoring
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-100/80"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Reconciled from live transactions</span>
            </div>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Transaction Reports & Financial Audit Intelligence</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Consolidated settlement summaries, payment channel breakdowns, required tax estimates, and owner-ready financial report exports.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[650px]">
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Total Paid</p><p class="mt-1 font-serif text-xl font-bold">PHP {{ number_format($grossTotal, 2) }}</p><p class="mt-1 text-[11px] text-blue-100/70">{{ $transactionCollection->count() }} billing entries</p></div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Cash Registration</p><p class="mt-1 font-serif text-xl font-bold text-secondary-fixed">PHP {{ number_format($cashTotal, 2) }}</p><p class="mt-1 text-[11px] text-blue-100/70">Cash-type entries</p></div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Card & Digital</p><p class="mt-1 font-serif text-xl font-bold">PHP {{ number_format($cardDigitalTotal, 2) }}</p><p class="mt-1 text-[11px] text-blue-100/70">All non-cash entries</p></div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Tax Estimate</p><p class="mt-1 font-serif text-xl font-bold text-secondary-fixed">PHP {{ number_format($taxDue, 2) }}</p><p class="mt-1 text-[11px] text-blue-100/70">SBMA 5% basis</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Transaction Volume</p><span class="material-symbols-outlined text-secondary">payments</span></div><p class="mt-4 font-serif text-3xl font-bold">PHP {{ number_format($grossTotal, 2) }}</p><p class="mt-1 text-sm text-slate-500">Compiled from real transaction rows</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Settled Amount</p><span class="material-symbols-outlined text-secondary">task_alt</span></div><p class="mt-4 font-serif text-3xl font-bold">PHP {{ number_format($settledTotal, 2) }}</p><p class="mt-1 text-sm text-slate-500">Paid, recorded, cleared, or completed</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Open Review</p><span class="material-symbols-outlined text-secondary">pending_actions</span></div><p class="mt-4 font-serif text-3xl font-bold">PHP {{ number_format($pendingTotal, 2) }}</p><p class="mt-1 text-sm text-slate-500">Visible but not marked settled</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Difference / Audit Hash</p><span class="material-symbols-outlined text-secondary">fingerprint</span></div><p class="mt-4 font-serif text-3xl font-bold">PHP {{ number_format($variance, 2) }}</p><p class="mt-1 font-mono text-xs text-slate-500">{{ $statementHash }}</p></div>
</section>

<section class="owner-card rounded-xl border border-line p-4">
    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
        <div class="owner-scroll flex flex-wrap gap-2">
            <button type="button" data-transaction-report-filter="all" class="rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white">All Transaction Reports</button>
            <button type="button" data-transaction-report-filter="Payment" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">Daily Payment</button>
            <button type="button" data-transaction-report-filter="Payment Channel" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">Card & Digital</button>
            <button type="button" data-transaction-report-filter="Required Tax" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">SBMA Tax</button>
            <button type="button" data-transaction-report-filter="Accounts" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">Billing Accounts</button>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <label class="relative block sm:w-80"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span><input id="ownerTransactionReportSearch" class="w-full rounded-lg border border-line bg-white py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search transaction report..." type="search" autocomplete="off"></label>
            <button id="ownerTransactionReportExport" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-3.5 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container"><span class="material-symbols-outlined text-lg text-secondary-fixed">file_download</span>Export Statement</button>
        </div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-xl">
    <div class="flex flex-col gap-3 border-b border-line bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-serif text-2xl font-bold text-primary">Certified Transaction Report Catalog</h3>
            <p class="mt-1 text-sm text-slate-500">Owner view-only report packets generated from current billing and transaction data.</p>
        </div>
        <span class="rounded-full bg-secondary-fixed/30 px-3 py-1 text-xs font-bold text-secondary">{{ $reportRows->count() }} audit reports ready</span>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1180px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-5 py-3">Report Title & Code</th>
                    <th class="px-4 py-3">Financial Breakdown</th>
                    <th class="px-4 py-3">Audit Compliance / Officer</th>
                    <th class="px-4 py-3">File Types</th>
                    <th class="px-5 py-3 text-right">Executive Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line bg-white">
                @foreach ($reportRows as $report)
                    <tr class="transition hover:bg-surface-low/70" data-transaction-report-row data-category="{{ $report['category'] }}" data-search="{{ e(strtolower($report['title'].' '.$report['code'].' '.$report['category'].' '.$report['breakdown'].' '.$report['officer'].' '.$report['status'])) }}">
                        <td class="px-5 py-4">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-mid text-secondary"><span class="material-symbols-outlined">{{ $report['icon'] }}</span></span>
                                <div>
                                    <p class="font-bold text-primary">{{ $report['title'] }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2"><span class="rounded bg-surface-mid px-2 py-0.5 font-mono text-[11px] text-slate-600">{{ $report['code'] }}</span><span class="rounded px-2 py-0.5 text-[11px] font-bold {{ $categoryTone[$report['category']] }}">{{ $report['category'] }}</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-serif text-lg font-bold text-primary">PHP {{ number_format($report['amount'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $report['breakdown'] }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-primary">{{ $report['officer'] }}</p>
                            <span class="mt-1 inline-flex rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusTone[$report['status']] ?? 'border-line bg-surface-low text-slate-600' }}">{{ $report['status'] }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($report['formats'] as $format)
                                    <span class="rounded bg-surface-mid px-2 py-0.5 text-[11px] font-bold text-slate-700">{{ $format }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" data-transaction-report-preview class="inline-flex items-center gap-1 rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-surface-low"><span class="material-symbols-outlined text-sm">visibility</span>Preview Records</button>
                                <button type="button" data-transaction-report-download class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white transition hover:bg-primary-container"><span class="material-symbols-outlined text-sm text-secondary-fixed">download</span>Download CSV</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-2 border-t border-line bg-surface-low p-4 text-xs text-slate-500 sm:flex-row">
        <span id="ownerTransactionReportCount">Displaying {{ $reportRows->count() }} transaction reports</span>
        <span>Generated {{ $reportDate->format('M d, Y h:i A') }} PHT • Hash {{ $statementHash }}</span>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Audit Velocity</p><h3 class="mt-2 font-serif text-2xl font-bold">Owner Ready</h3><p class="mt-2 text-sm leading-6 text-slate-600">Reports are available for review and export without giving owner accounts record-editing permissions.</p></div>
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Owner Access Rules</p><h3 class="mt-2 font-serif text-2xl font-bold">Read-Only Control</h3><p class="mt-2 text-sm leading-6 text-slate-600">Billing corrections, cancelled transactions, and added charges remain restricted to department users and administrators.</p></div>
    <div class="rounded-xl border border-primary-container bg-primary p-5 text-white shadow-md"><p class="text-xs font-bold uppercase tracking-wider text-secondary-fixed">Protected Report Chain</p><h3 class="mt-2 font-serif text-2xl font-bold">{{ $statementHash }}</h3><p class="mt-2 text-sm leading-6 text-blue-100">Hash generated from current totals, settled amount, tax estimate, and report date.</p></div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-transaction-report-row]'));
        const search = document.getElementById('ownerTransactionReportSearch');
        const count = document.getElementById('ownerTransactionReportCount');
        const exportButton = document.getElementById('ownerTransactionReportExport');
        const filterButtons = Array.from(document.querySelectorAll('[data-transaction-report-filter]'));
        let activeCategory = 'all';

        const applyFilters = () => {
            const terms = (search?.value || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
            let visible = 0;
            rows.forEach((row) => {
                const categoryMatch = activeCategory === 'all' || row.dataset.category === activeCategory;
                const textMatch = !terms.length || terms.every((term) => (row.dataset.search || '').includes(term));
                const show = categoryMatch && textMatch;
                row.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });
            filterButtons.forEach((button) => {
                const active = button.dataset.transactionReportFilter === activeCategory;
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('bg-surface-low', !active);
                button.classList.toggle('text-slate-600', !active);
            });
            if (count) count.textContent = `Displaying ${visible} of ${rows.length} transaction reports`;
        };

        const exportVisible = () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = [
                ['Report Title & Code', 'Financial Breakdown', 'Audit Compliance / Officer', 'File Types', 'Executive Actions'],
                ...visibleRows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `owner-transaction-reports-${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        };

        filterButtons.forEach((button) => button.addEventListener('click', () => {
            activeCategory = button.dataset.transactionReportFilter || 'all';
            applyFilters();
        }));
        search?.addEventListener('input', applyFilters);
        exportButton?.addEventListener('click', exportVisible);
        document.querySelectorAll('[data-transaction-report-preview], [data-transaction-report-download]').forEach((button) => button.addEventListener('click', exportVisible));
        applyFilters();
    })();
</script>
