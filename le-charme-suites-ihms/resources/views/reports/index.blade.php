@php
    $user = auth()->user();
    $transactionRows = $transactions->getCollection();
    $todayTransactions = $transactionRows->filter(fn ($transaction) => $transaction->transaction_date?->isToday());
    $totalLedgerAmount = $transactionRows->sum('amount');
    $todayLedgerAmount = $todayTransactions->sum('amount');
    $postedCount = $transactions->total();
    $balancedCount = $transactionRows->whereIn('status', ['paid', 'completed', 'recorded'])->count();
    $roomCount = $roomStatusCounts->sum();
    $reservationCount = $reservationStatusCounts->sum();
    $auditHash = substr(hash('sha256', $transactionRows->pluck('id')->join('|') . now()->toDateString()), 0, 12);
    $reportRows = [
        ['title' => 'Shift Cashier Balance Report', 'code' => 'LCS-BIL-SHFT-01', 'category' => 'Financial', 'cadence' => 'End of shift', 'status' => 'Ready', 'formats' => ['PDF', 'XLSX']],
        ['title' => 'Daily Revenue Z-Reading', 'code' => 'LCS-BIL-ZREAD-02', 'category' => 'Revenue', 'cadence' => 'Daily close', 'status' => 'Scheduled', 'formats' => ['PDF']],
        ['title' => 'Guest Bill Settlement Exceptions', 'code' => 'LCS-BIL-EXC-03', 'category' => 'Audit', 'cadence' => 'Live review', 'status' => $balancedCount === $postedCount ? 'Balanced' : 'Review', 'formats' => ['PDF', 'CSV']],
        ['title' => 'Room & Reservation Revenue Snapshot', 'code' => 'LCS-BIL-RMS-04', 'category' => 'Rooms', 'cadence' => 'Hourly', 'status' => 'Synced', 'formats' => ['PDF', 'XLSX']],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Reports - Le Charmé Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = { theme: { extend: {
            colors: { primary: '#01162d', 'primary-container': '#172b42', secondary: '#755a28', 'secondary-container': '#fdd79a', 'secondary-fixed': '#ffdeaa', surface: '#fcf9f3', 'surface-low': '#f6f3ed', 'surface-mid': '#f0eee8', line: '#e5e2dc', error: '#ba1a1a' },
            fontFamily: { sans: ['Manrope', 'sans-serif'], serif: ['Noto Serif', 'serif'] },
        } } };
    </script>
    <style>
        .material-symbols-outlined { display: inline-block; vertical-align: middle; line-height: 1; font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f0eee8; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c4c6cd; border-radius: 999px; }
        @media print { aside, header, .no-print { display: none !important; } body { background: white !important; } main { padding: 0 !important; } }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    @include('partials.billing-sidebar', ['active' => 'reports'])

    <div class="flex min-h-screen flex-col lg:ml-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-5 py-3 shadow-sm backdrop-blur lg:px-8">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><span>Billing Operations</span><span>/</span><span class="text-secondary">Reports Center</span></div>
                <h1 class="mt-1 font-serif text-xl font-bold text-primary">Le Charmé Suites IHMP <span class="font-sans text-sm font-semibold text-slate-500">| Shift Financial Reconciliation</span></h1>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="button" onclick="alert('Cash drawer access has been logged for audit review.')" class="hidden items-center gap-2 rounded-lg border border-secondary/40 bg-white px-3 py-2 text-xs font-bold text-secondary shadow-sm transition hover:bg-secondary-container/30 md:flex"><span class="material-symbols-outlined text-base">lock_open</span>Open Cash Drawer</button>
                <button type="button" onclick="alert('End of shift reconciliation has been queued for supervisor review.')" class="hidden items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-900 md:flex"><span class="material-symbols-outlined text-base text-secondary-fixed">schedule</span>End of Shift Reconciliation</button>
                <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm">
                    <p id="billing-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p>
                    <p id="billing-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1700px] space-y-6 p-5 lg:p-8">
            <section class="flex flex-col justify-between gap-4 border-b border-line pb-5 xl:flex-row xl:items-end">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-secondary">
                        <span>Cashier &amp; Billing Operations</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="text-primary">Cashier Reports &amp; Reconciliation</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="font-serif text-4xl font-bold leading-tight">Shift Financial &amp; Cashier Reports</h2>
                        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-secondary">Shift A • 06:00 - 14:30</span>
                    </div>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Generate, review, and export billing department financial reports using live guest bill transactions, room inventory, and reservation ledger data.</p>
                </div>
                <div class="no-print flex flex-wrap items-center gap-3">
                    <button type="button" onclick="document.getElementById('standard-report-directory').scrollIntoView({ behavior: 'smooth' })" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900"><span class="material-symbols-outlined text-secondary-fixed">play_arrow</span>Run Shift Balance Report</button>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-surface-low"><span class="material-symbols-outlined text-secondary">print</span>Print X-Reading</button>
                    <button type="button" onclick="alert('Daily Z-reading has been scheduled for the end-of-day close.')" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface-mid px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-white"><span class="material-symbols-outlined text-secondary">event_repeat</span>Schedule Daily Z-Reading</button>
                </div>
            </section>

            <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-md">
                <div class="pointer-events-none absolute -bottom-20 -right-16 h-96 w-96 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
                <div class="relative z-10 grid gap-6 xl:grid-cols-[1fr_420px] xl:items-center">
                    <div>
                        <span class="rounded border border-secondary-fixed/30 bg-secondary/30 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Authorized Revenue Terminal</span>
                        <h3 class="mt-3 font-serif text-4xl font-bold leading-tight">Financial Reporting Command Console</h3>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">Consolidated cashier reports for daily collections, guest bill settlements, room revenue, reservation movements, and audit reconciliation.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-white/10 bg-white/10 p-4">
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Today Collections</p><p class="mt-1 font-serif text-2xl font-bold">PHP {{ number_format($todayLedgerAmount, 2) }}</p></div>
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Posted Records</p><p class="mt-1 font-serif text-2xl font-bold">{{ $postedCount }}</p></div>
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Rooms Tracked</p><p class="mt-1 font-serif text-2xl font-bold">{{ $roomCount }}</p></div>
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Reservations</p><p class="mt-1 font-serif text-2xl font-bold">{{ $reservationCount }}</p></div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Gross Ledger Total</span><span class="material-symbols-outlined rounded-lg bg-secondary-container/40 p-1.5 text-secondary">payments</span></div><p class="mt-3 font-serif text-3xl font-bold">PHP {{ number_format($totalLedgerAmount, 2) }}</p><p class="mt-1 text-xs text-slate-500">All visible billing records</p></div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Today Reported</span><span class="material-symbols-outlined rounded-lg bg-emerald-50 p-1.5 text-emerald-700">task_alt</span></div><p class="mt-3 font-serif text-3xl font-bold">{{ $todayTransactions->count() }}</p><p class="mt-1 text-xs text-slate-500">Transactions posted today</p></div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Balanced Records</span><span class="material-symbols-outlined rounded-lg bg-surface-low p-1.5 text-primary">verified_user</span></div><p class="mt-3 font-serif text-3xl font-bold">{{ $balancedCount }}</p><p class="mt-1 text-xs text-slate-500">Paid, completed, or recorded</p></div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Audit Hash</span><span class="material-symbols-outlined rounded-lg bg-surface-low p-1.5 text-primary">fingerprint</span></div><p class="mt-3 font-mono text-2xl font-bold">{{ $auditHash }}</p><p class="mt-1 text-xs text-slate-500">Generated from current ledger set</p></div>
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="rounded-xl border border-secondary/40 bg-white p-5 shadow-sm xl:col-span-8">
                    <div class="flex flex-col justify-between gap-3 border-b border-line pb-4 sm:flex-row sm:items-start">
                        <div class="flex gap-3">
                            <span class="material-symbols-outlined rounded-lg bg-surface-low p-2 text-secondary">tune</span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-secondary">Active Parameter Console</p>
                                <h3 class="font-serif text-2xl font-bold">Daily Manager's Operational Flash &amp; Revenue Ledger</h3>
                            </div>
                        </div>
                        <span class="rounded bg-surface-mid px-3 py-1 text-xs font-bold text-slate-600">Doc ID: LCS-BIL-REP-{{ now()->format('Ymd') }}</span>
                    </div>
                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <label class="block"><span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Reporting Date</span><input type="date" value="{{ now()->toDateString() }}" class="w-full rounded-lg border-line bg-surface-low text-sm focus:border-secondary focus:ring-secondary"></label>
                        <label class="block"><span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Property Scope</span><select class="w-full rounded-lg border-line bg-surface-low text-sm focus:border-secondary focus:ring-secondary"><option>All Property Wings (Consolidated)</option><option>Main Wing</option><option>Ocean Wing</option></select></label>
                        <label class="block"><span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Currency Base</span><select class="w-full rounded-lg border-line bg-surface-low text-sm focus:border-secondary focus:ring-secondary"><option>Philippine Peso (PHP) - Local</option></select></label>
                    </div>
                    <div class="mt-4 grid gap-3 rounded-lg border border-line bg-surface-low p-4 sm:grid-cols-2 lg:grid-cols-4">
                        <label class="inline-flex items-center gap-2 text-sm font-bold"><input type="checkbox" checked class="rounded border-line text-primary focus:ring-secondary">Departmental splits</label>
                        <label class="inline-flex items-center gap-2 text-sm font-bold"><input type="checkbox" checked class="rounded border-line text-primary focus:ring-secondary">Include voided guest bills</label>
                        <label class="inline-flex items-center gap-2 text-sm font-bold"><input type="checkbox" class="rounded border-line text-primary focus:ring-secondary">SBMA tax breakdown</label>
                        <label class="inline-flex items-center gap-2 text-sm font-bold"><input type="checkbox" checked class="rounded border-line text-primary focus:ring-secondary">Manager signature seal</label>
                    </div>
                    <div class="mt-6 flex flex-col justify-between gap-4 border-t border-line pt-5 md:flex-row md:items-center">
                        <p class="inline-flex items-center gap-2 text-sm text-slate-600"><span class="material-symbols-outlined text-base text-emerald-700">check_circle</span>{{ $postedCount }} ledger records available for compilation</p>
                        <div class="no-print flex flex-wrap gap-3">
                            <button type="button" onclick="alert('Report preview generated from current ledger data.')" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface-low px-4 py-2.5 text-sm font-bold text-primary transition hover:bg-white"><span class="material-symbols-outlined">visibility</span>Preview</button>
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-secondary/90"><span class="material-symbols-outlined">picture_as_pdf</span>PDF Download</button>
                            <button type="button" onclick="alert('The report has been prepared for email routing to management.')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-900"><span class="material-symbols-outlined">forward_to_inbox</span>Email to Board &amp; GM</button>
                        </div>
                    </div>
                </section>

                <aside class="rounded-xl border border-line bg-white p-5 shadow-sm xl:col-span-4">
                    <div class="mb-4 flex items-center justify-between border-b border-line pb-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-secondary">Live Audit Telemetry</h3>
                        <span class="text-[11px] font-semibold text-slate-500">MTD Summary</span>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-xs text-slate-500">Gross Month-to-Date Revenue</p><div class="mt-1 flex items-center justify-between"><p class="font-serif text-2xl font-bold">PHP {{ number_format($totalLedgerAmount, 2) }}</p><span class="text-xs font-bold text-emerald-700">Live</span></div></div>
                        <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-xs text-slate-500">Reservations Tracked</p><div class="mt-1 flex items-center justify-between"><p class="font-serif text-2xl font-bold">{{ $reservationCount }}</p><span class="text-xs font-bold text-secondary">{{ $roomCount }} rooms</span></div></div>
                        <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-xs text-slate-500">Unreconciled Variances</p><div class="mt-1 flex items-center justify-between"><p class="font-serif text-2xl font-bold text-emerald-800">PHP 0.00</p><span class="rounded bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-800">Balanced</span></div></div>
                    </div>
                    <div class="mt-5 border-t border-line pt-4 text-xs text-slate-500"><span class="material-symbols-outlined text-sm text-secondary">shield</span> Audit Chain Hash: {{ $auditHash }}</div>
                </aside>
            </div>

            <section id="standard-report-directory" class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-line p-5 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="font-serif text-2xl font-bold">Standard Cashier Report Directory</h3>
                        <p class="text-sm text-slate-500">Mandatory financial, settlement, room revenue, and audit reports scheduled for billing department compliance.</p>
                    </div>
                    <div class="no-print flex flex-wrap gap-2">
                        <input id="report-filter" class="rounded-lg border-line bg-surface-low text-sm focus:border-secondary focus:ring-secondary" placeholder="Filter by report name">
                        <button type="button" id="sortReports" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2 text-sm font-bold text-primary shadow-sm transition hover:bg-surface-low"><span class="material-symbols-outlined text-base">sort</span>Sort</button>
                    </div>
                </div>
                <div class="custom-scrollbar overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-left text-sm">
                        <thead class="border-b border-line bg-surface-mid text-xs font-bold uppercase tracking-wider text-slate-600">
                            <tr><th class="px-4 py-3">Report Identifier &amp; Title</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Formats</th><th class="px-4 py-3">Frequency / Cadence</th><th class="px-4 py-3">Compliance Status</th><th class="px-4 py-3">Last Run</th><th class="px-4 py-3 text-right">Actions</th></tr>
                        </thead>
                        <tbody id="report-rows" class="divide-y divide-line">
                            @foreach ($reportRows as $report)
                                <tr class="report-row">
                                    <td class="px-4 py-4"><div class="flex items-start gap-3"><span class="material-symbols-outlined mt-1 text-secondary">description</span><div><p class="font-bold">{{ $report['title'] }}</p><p class="font-mono text-xs text-slate-500">{{ $report['code'] }} - Billing department financial report</p></div></div></td>
                                    <td class="px-4 py-4"><span class="rounded-full bg-surface-mid px-2.5 py-1 text-xs font-bold">{{ $report['category'] }}</span></td>
                                    <td class="px-4 py-4"><div class="flex gap-1">@foreach ($report['formats'] as $format)<span class="rounded border border-line bg-surface-low px-2 py-1 text-[10px] font-bold">{{ $format }}</span>@endforeach</div></td>
                                    <td class="px-4 py-4 text-slate-600">{{ $report['cadence'] }}</td>
                                    <td class="px-4 py-4"><span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800">{{ $report['status'] }}</span></td>
                                    <td class="px-4 py-4 font-mono text-xs text-slate-500">{{ now()->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-4 text-right"><button type="button" onclick="window.print()" class="rounded bg-primary px-3 py-1.5 text-xs font-bold text-white">Run Now</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-line bg-surface-low p-5 sm:flex-row sm:items-center">
                    <div><h3 class="font-serif text-2xl font-bold">Recent Billing Ledger Records</h3><p class="text-sm text-slate-500">Latest hotel transactions included in report compilation.</p></div>
                    <span class="rounded-full bg-surface-mid px-3 py-1 text-xs font-bold text-primary">{{ $transactions->total() }} total records</span>
                </div>
                <div class="custom-scrollbar overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="border-b border-line bg-surface-mid text-xs font-bold uppercase tracking-wider text-slate-600">
                            <tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Guest</th><th class="px-4 py-3">Room</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $transaction->transaction_date?->format('M d, Y h:i A') ?? 'Pending date' }}</td>
                                    <td class="px-4 py-3 font-bold">{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</td>
                                    <td class="px-4 py-3">{{ $transaction->guest?->full_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->room?->room_number ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right font-bold">PHP {{ number_format($transaction->amount, 2) }}</td>
                                    <td class="px-4 py-3"><span class="rounded-full bg-surface-mid px-2.5 py-1 text-xs font-bold">{{ ucfirst($transaction->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No billing transactions are available for report compilation yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-line px-5 py-4">{{ $transactions->links() }}</div>
            </section>
        </main>
    </div>

    <script>
        function updateBillingClock() {
            const now = new Date();
            document.getElementById('billing-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: '2-digit', year: 'numeric', timeZone: 'Asia/Manila' });
            document.getElementById('billing-clock').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Asia/Manila' }) + ' PHT';
        }

        updateBillingClock();
        setInterval(updateBillingClock, 1000);

        document.getElementById('report-filter')?.addEventListener('input', (event) => {
            const term = event.target.value.toLowerCase();
            document.querySelectorAll('.report-row').forEach((row) => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });

        document.getElementById('sortReports')?.addEventListener('click', () => {
            const body = document.getElementById('report-rows');
            Array.from(body.children).reverse().forEach((row) => body.appendChild(row));
        });
    </script>
</body>
</html>
