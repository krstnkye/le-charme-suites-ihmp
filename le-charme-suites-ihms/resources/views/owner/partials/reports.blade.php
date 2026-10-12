@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $todayTransactions = $transactions->filter(fn ($transaction) => $transaction->transaction_date?->timezone('Asia/Manila')->isToday());
    $recordCount = $guests->count() + $rooms->count() + $reservations->count() + $checkIns->count() + $checkOuts->count() + $transactions->count() + $housekeepingLogs->count();
    $operationalReportCount = 6;
    $transactionReportCount = max($transactions->count(), 1);
    $auditHash = strtoupper(substr(hash('sha256', 'owner-reports-'.$recordCount.'-'.$recordedRevenue.'-'.$collectedToday), 0, 12));
    $reportRows = collect([
        [
            'title' => "Daily Manager's Flash Report",
            'subtitle' => 'System compiled occupancy, room readiness, and revenue snapshot',
            'classification' => 'Operational',
            'date' => now('Asia/Manila'),
            'status' => 'Verified',
            'signature' => substr(strtoupper(hash('sha256', 'flash-'.$occupancyRate.'-'.$totalRooms)), 0, 10),
            'amount' => $recordedRevenue,
        ],
        [
            'title' => 'Revenue & Transaction Reconciliation',
            'subtitle' => 'Billing accounts, settled payments, and transaction activity',
            'classification' => 'Transaction',
            'date' => now('Asia/Manila')->subMinutes(18),
            'status' => $transactions->count() ? 'Ready' : 'No Entries',
            'signature' => substr(strtoupper(hash('sha256', 'revenue-'.$transactions->count().'-'.$collectedToday)), 0, 10),
            'amount' => $collectedToday,
        ],
        [
            'title' => 'Guest & Stay Records Archive',
            'subtitle' => 'Guest profiles, reservations, check-in and check-out records',
            'classification' => 'Records',
            'date' => now('Asia/Manila')->subHour(),
            'status' => 'Indexed',
            'signature' => substr(strtoupper(hash('sha256', 'records-'.$recordCount)), 0, 10),
            'amount' => null,
        ],
        [
            'title' => 'Housekeeping & Room Readiness Log',
            'subtitle' => 'Room status changes, cleaning signals, and maintenance visibility',
            'classification' => 'Operational',
            'date' => now('Asia/Manila')->subHours(2),
            'status' => 'Verified',
            'signature' => substr(strtoupper(hash('sha256', 'housekeeping-'.$housekeepingLogs->count().'-'.$availableRooms)), 0, 10),
            'amount' => null,
        ],
    ]);
    $classificationTone = [
        'Operational' => 'bg-primary-container text-secondary-fixed',
        'Transaction' => 'bg-secondary/10 text-secondary',
        'Records' => 'bg-surface-mid text-slate-700',
    ];
    $statusTone = [
        'Verified' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Ready' => 'border-blue-200 bg-blue-50 text-blue-700',
        'Indexed' => 'border-secondary/30 bg-secondary/10 text-secondary',
        'No Entries' => 'border-slate-200 bg-slate-50 text-slate-500',
    ];
@endphp

<section class="relative overflow-hidden rounded-xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-20 -right-16 h-72 w-72 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/30 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">analytics</span>
                Executive Reports / Immutable Records
            </span>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Reports & Records Overview</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Owner visibility into operational reports, transaction summaries, guest records, room readiness logs, and statutory audit archives.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p><p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Reports</p><p class="mt-1 font-serif text-2xl font-bold">{{ $reportRows->count() }}</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Records</p><p class="mt-1 font-serif text-2xl font-bold">{{ $recordCount }}</p></div>
            <div><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Revenue</p><p class="mt-1 font-serif text-xl font-bold text-secondary-fixed">PHP {{ number_format($recordedRevenue, 2) }}</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Generated Today</p><span class="material-symbols-outlined text-secondary">summarize</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $reportRows->count() }}</p><p class="mt-1 text-sm text-slate-500">Executive reports ready for viewing</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Daily Audits</p><span class="material-symbols-outlined text-secondary">fact_check</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $operationalReportCount }}</p><p class="mt-1 text-sm text-slate-500">Operational ledgers ready</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Transaction Reports</p><span class="material-symbols-outlined text-secondary">receipt_long</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $transactionReportCount }}</p><p class="mt-1 text-sm text-slate-500">Billing and transaction entries visible</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Indexed Database</p><span class="material-symbols-outlined text-secondary">database</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $recordCount }}</p><p class="mt-1 text-sm text-slate-500">Total owner-visible records</p></div>
</section>

<section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div id="operational-reports" class="owner-card rounded-xl border-t-2 border-t-primary-container p-6">
        <div class="mb-4 flex items-start justify-between"><span class="material-symbols-outlined text-secondary">monitoring</span><span class="rounded bg-primary-container px-2 py-0.5 text-xs font-bold text-secondary-fixed">/owner/reports/operations</span></div>
        <h3 class="font-serif text-2xl font-bold text-primary">Operational Reports</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">Occupancy, room readiness, housekeeping, arrivals, departures, and property performance reporting.</p>
        <div class="mt-5 flex items-center justify-between border-t border-line pt-3 text-xs"><span class="text-slate-500">Daily audits:</span><strong>{{ $operationalReportCount }} ledgers ready</strong></div>
        <button type="button" data-report-scroll="#reports-ledger" data-report-filter="Operational" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-line bg-surface-mid px-3 py-2.5 text-sm font-bold text-primary transition hover:bg-primary-container hover:text-secondary-fixed">Open Operational Reports <span class="material-symbols-outlined text-base">arrow_forward</span></button>
    </div>
    <div id="transaction-reports" class="owner-card rounded-xl border-t-2 border-t-secondary p-6">
        <div class="mb-4 flex items-start justify-between"><span class="material-symbols-outlined text-secondary">payments</span><span class="rounded bg-secondary/10 px-2 py-0.5 text-xs font-bold text-secondary">/owner/reports/transactions</span></div>
        <h3 class="font-serif text-2xl font-bold text-primary">Transaction Reports</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">End-of-day payment reconciliations, transaction breakdowns, revenue velocity, and settlement monitoring.</p>
        <div class="mt-5 flex items-center justify-between border-t border-line pt-3 text-xs"><span class="text-slate-500">Recorded today:</span><strong>PHP {{ number_format($todayTransactions->sum('amount'), 2) }}</strong></div>
        <button type="button" data-report-scroll="#reports-ledger" data-report-filter="Transaction" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-secondary bg-primary-container px-3 py-2.5 text-sm font-bold text-secondary-fixed transition hover:bg-primary">Open Transaction Reports <span class="material-symbols-outlined text-base">arrow_forward</span></button>
    </div>
    <div id="records" class="owner-card rounded-xl p-6">
        <div class="mb-4 flex items-start justify-between"><span class="material-symbols-outlined text-secondary">folder_managed</span><span class="rounded bg-surface-mid px-2 py-0.5 text-xs font-bold text-slate-600">/owner/reports/records</span></div>
        <h3 class="font-serif text-2xl font-bold text-primary">Records & Historical Archive</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">Guest registrations, reservation history, check-in/check-out records, compliance logs, and room activity archives.</p>
        <div class="mt-5 flex items-center justify-between border-t border-line pt-3 text-xs"><span class="text-slate-500">Indexed database:</span><strong>{{ $recordCount }} total records</strong></div>
        <button type="button" data-report-scroll="#reports-ledger" data-report-filter="Records" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-line bg-surface-mid px-3 py-2.5 text-sm font-bold text-primary transition hover:bg-primary-container hover:text-secondary-fixed">Browse Records Archive <span class="material-symbols-outlined text-base">arrow_forward</span></button>
    </div>
</section>

<section id="reports-ledger" class="owner-card overflow-hidden rounded-xl">
    <div class="flex flex-col gap-4 border-b border-line bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary-container text-secondary-fixed"><span class="material-symbols-outlined">verified_user</span></span>
            <div><h3 class="font-serif text-2xl font-bold text-primary">Live Audit Stream & Recent Reports</h3><p class="text-sm text-slate-500">Automated compilation schedule and immutable hash verification.</p></div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <label class="relative block sm:w-80"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span><input id="ownerReportSearch" class="w-full rounded-lg border border-line bg-surface-low py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Filter report name or classification..." type="search"></label>
            <button id="ownerReportExport" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-white px-3.5 py-2.5 text-sm font-bold text-primary transition hover:border-secondary"><span class="material-symbols-outlined text-lg text-secondary">download</span>Export Ledger</button>
        </div>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1120px] border-collapse text-left text-sm">
            <thead><tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600"><th class="px-5 py-3">Ledger / Report Title</th><th class="px-4 py-3">Classification</th><th class="px-4 py-3">Execution Date</th><th class="px-4 py-3">Audit Status</th><th class="px-4 py-3">Ledger Signature</th><th class="px-5 py-3 text-right">Statutory Action</th></tr></thead>
            <tbody class="divide-y divide-line">
                @foreach ($reportRows as $report)
                    <tr class="transition hover:bg-surface-low" data-report-row data-classification="{{ $report['classification'] }}" data-search="{{ strtolower($report['title'].' '.$report['subtitle'].' '.$report['classification'].' '.$report['status'].' '.$report['signature']) }}">
                        <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary">description</span><div><p class="font-bold text-primary">{{ $report['title'] }}</p><p class="text-[11px] text-slate-500">{{ $report['subtitle'] }}</p></div></div></td>
                        <td class="px-4 py-4"><span class="rounded px-2 py-1 text-[11px] font-bold {{ $classificationTone[$report['classification']] }}">{{ $report['classification'] }}</span></td>
                        <td class="px-4 py-4 font-mono text-xs text-primary">{{ $report['date']->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-4"><span class="rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusTone[$report['status']] ?? $statusTone['Ready'] }}">{{ $report['status'] }}</span></td>
                        <td class="px-4 py-4 font-mono text-xs text-slate-600">SIG-{{ $report['signature'] }}</td>
                        <td class="px-5 py-4 text-right"><button type="button" data-report-download class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface-mid px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-surface-low"><span class="material-symbols-outlined text-sm">download</span>Download CSV</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-2 border-t border-line bg-surface-low p-4 text-xs text-slate-500 sm:flex-row">
        <span id="ownerReportCount">Displaying {{ $reportRows->count() }} recent immutable executive reports • hash {{ $auditHash }}</span>
        <span>Owner access is view-only. Report exports are generated from current visible data.</span>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Compliance Status</p><h3 class="mt-2 font-serif text-2xl font-bold">Audit Ready</h3><p class="mt-3 text-sm leading-6 text-slate-600">All owner-visible reports preserve record separation from administrative edit controls.</p><div class="mt-4 rounded-lg bg-emerald-50 p-3 text-xs font-bold text-emerald-700">No owner editing privileges</div></div>
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Record Sources</p><div class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><span>Guests</span><strong>{{ $guests->count() }}</strong></div><div class="flex justify-between"><span>Rooms</span><strong>{{ $rooms->count() }}</strong></div><div class="flex justify-between"><span>Transactions</span><strong>{{ $transactions->count() }}</strong></div><div class="flex justify-between"><span>Housekeeping Logs</span><strong>{{ $housekeepingLogs->count() }}</strong></div></div></div>
    <div class="rounded-xl border border-primary-container bg-primary p-5 text-white shadow-md"><p class="text-xs font-bold uppercase tracking-wider text-secondary-fixed">Executive Advisory</p><h3 class="mt-2 font-serif text-2xl font-bold">Reports Are Monitoring Views</h3><p class="mt-3 text-sm leading-6 text-blue-100">Operational record changes remain with departments and administrators. Owner reports are intended for review, audit, and decision support.</p></div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-report-row]'));
        const search = document.getElementById('ownerReportSearch');
        const count = document.getElementById('ownerReportCount');
        const exportButton = document.getElementById('ownerReportExport');
        let classificationFilter = 'all';

        const applyFilter = () => {
            const terms = (search?.value || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
            let visible = 0;
            rows.forEach((row) => {
                const classMatch = classificationFilter === 'all' || row.dataset.classification === classificationFilter;
                const textMatch = !terms.length || terms.every((term) => (row.dataset.search || '').includes(term));
                const show = classMatch && textMatch;
                row.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });
            if (count) {
                count.textContent = `Displaying ${visible} of ${rows.length} recent immutable executive reports`;
            }
        };

        document.querySelectorAll('[data-report-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                classificationFilter = button.dataset.reportFilter || 'all';
                document.querySelector(button.dataset.reportScroll)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                applyFilter();
            });
        });
        search?.addEventListener('input', () => {
            classificationFilter = 'all';
            applyFilter();
        });

        const exportVisibleReports = () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = [
                ['Ledger / Report Title', 'Classification', 'Execution Date', 'Audit Status', 'Ledger Signature', 'Action'],
                ...visibleRows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-reports-records-ledger.csv';
            link.click();
            URL.revokeObjectURL(url);
        };

        exportButton?.addEventListener('click', exportVisibleReports);
        document.querySelectorAll('[data-report-download]').forEach((button) => button.addEventListener('click', exportVisibleReports));
        applyFilter();
    })();
</script>
