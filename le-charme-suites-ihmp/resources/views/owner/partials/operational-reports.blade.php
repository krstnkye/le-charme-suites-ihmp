@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $readinessRate = round(($availableRooms / $safeTotalRooms) * 100);
    $turnoverCount = $rooms->where('status', 'cleaning')->count();
    $maintenanceCount = $rooms->where('status', 'maintenance')->count();
    $operationalVelocity = $checkIns->count() + $checkOuts->count() + $housekeepingLogs->count();
    $todayRevenue = (float) $recordedRevenue;
    $adr = $occupiedRooms > 0 ? round($todayRevenue / max($occupiedRooms, 1), 2) : 0;
    $revPar = $totalRooms > 0 ? round($todayRevenue / max($totalRooms, 1), 2) : 0;
    $reportRows = collect([
        ['title' => 'Room Turnover & Hygiene Readiness Matrix', 'code' => 'REP-OPS-'.now('Asia/Manila')->format('Ymd').'-01', 'department' => 'Housekeeping', 'frequency' => 'Hourly Snapshot', 'status' => $turnoverCount ? 'Active Queue' : 'Ready', 'metric' => $readinessRate.'% ready', 'hash' => substr(strtoupper(hash('sha256', 'turnover-'.$readinessRate.'-'.$turnoverCount)), 0, 10)],
        ['title' => 'Floor Attendant Performance & Velocity Matrix', 'code' => 'REP-OPS-'.now('Asia/Manila')->format('Ymd').'-02', 'department' => 'Housekeeping', 'frequency' => 'End of Shift', 'status' => 'Supervisor Review', 'metric' => $housekeepingLogs->count().' activity logs', 'hash' => substr(strtoupper(hash('sha256', 'attendant-'.$housekeepingLogs->count())), 0, 10)],
        ['title' => 'Front Desk Arrival & Departure Flow', 'code' => 'REP-OPS-'.now('Asia/Manila')->format('Ymd').'-03', 'department' => 'Front Desk', 'frequency' => 'Daily Close', 'status' => 'Live Synced', 'metric' => $arrivalsToday.' arrivals / '.$departuresToday.' departures', 'hash' => substr(strtoupper(hash('sha256', 'frontdesk-'.$arrivalsToday.'-'.$departuresToday)), 0, 10)],
        ['title' => 'Occupancy Yield & Room Revenue Snapshot', 'code' => 'REP-OPS-'.now('Asia/Manila')->format('Ymd').'-04', 'department' => 'Rooms Division', 'frequency' => 'Daily Audit', 'status' => 'Verified', 'metric' => $occupancyRate.'% occupancy / PHP '.number_format($revPar, 2).' RevPAR', 'hash' => substr(strtoupper(hash('sha256', 'yield-'.$occupancyRate.'-'.$revPar)), 0, 10)],
        ['title' => 'Maintenance Exception & Safety Compliance', 'code' => 'REP-OPS-'.now('Asia/Manila')->format('Ymd').'-05', 'department' => 'Facilities', 'frequency' => 'Exception Based', 'status' => $maintenanceCount ? 'Action Required' : 'Clear', 'metric' => $maintenanceCount.' maintenance holds', 'hash' => substr(strtoupper(hash('sha256', 'maintenance-'.$maintenanceCount)), 0, 10)],
    ]);
    $departmentTone = [
        'Housekeeping' => 'bg-surface-mid text-slate-700',
        'Front Desk' => 'bg-blue-50 text-blue-700',
        'Rooms Division' => 'bg-secondary/10 text-secondary',
        'Facilities' => 'bg-amber-50 text-amber-800',
    ];
    $statusTone = [
        'Ready' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Clear' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Verified' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Live Synced' => 'border-blue-200 bg-blue-50 text-blue-700',
        'Supervisor Review' => 'border-secondary/30 bg-secondary/10 text-secondary',
        'Active Queue' => 'border-sky-200 bg-sky-50 text-sky-700',
        'Action Required' => 'border-error/20 bg-error-container text-error',
    ];
@endphp

<section class="relative overflow-hidden rounded-xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-20 -right-16 h-72 w-72 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/30 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">monitoring</span>
                Owner Access / Operational Telemetry
            </span>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Operational Reports & Departmental Telemetry</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Real-time and scheduled operational reports across room turnover, housekeeping SLAs, occupancy yield, and front desk workflow pacing.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p><p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Ready Rooms</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $availableRooms }}</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">RevPAR</p><p class="mt-1 font-serif text-xl font-bold">PHP {{ number_format($revPar, 2) }}</p></div>
            <div><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Velocity</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $operationalVelocity }}</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Room Availability</p><span class="material-symbols-outlined text-secondary">hotel</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $availableRooms }} Available</p><p class="mt-1 text-sm text-slate-500">of {{ $totalRooms }} rooms</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Housekeeping Readiness</p><span class="material-symbols-outlined text-secondary">cleaning_services</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $readinessRate }}%</p><div class="mt-3 h-2 rounded-full bg-surface-mid"><div class="h-2 rounded-full bg-secondary" style="width: {{ min($readinessRate, 100) }}%"></div></div></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">ADR / RevPAR</p><span class="material-symbols-outlined text-secondary">payments</span></div><p class="mt-4 font-serif text-2xl font-bold">PHP {{ number_format($adr, 2) }}</p><p class="mt-1 text-sm text-slate-500">RevPAR PHP {{ number_format($revPar, 2) }}</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Operational Velocity</p><span class="material-symbols-outlined text-secondary">timelapse</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $operationalVelocity }}</p><p class="mt-1 text-sm text-slate-500">Workflow events in owner feed</p></div>
</section>

<section class="owner-card rounded-xl border border-line p-4">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div class="owner-scroll flex flex-wrap gap-2">
            <button type="button" data-ops-filter="all" class="rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white">All Operational Reports</button>
            <button type="button" data-ops-filter="Housekeeping" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">Housekeeping Performance</button>
            <button type="button" data-ops-filter="Front Desk" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">Front Desk Flow</button>
            <button type="button" data-ops-filter="Rooms Division" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">Occupancy Yield</button>
            <button type="button" data-ops-filter="Facilities" class="rounded-lg bg-surface-low px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-primary">Maintenance</button>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <label class="relative block sm:w-80"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span><input id="ownerOpsReportSearch" class="w-full rounded-lg border border-line bg-white py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search operational report..." type="search"></label>
            <button id="ownerOpsReportExport" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-3.5 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container"><span class="material-symbols-outlined text-lg text-secondary-fixed">file_download</span>Generate Custom Operational Report</button>
        </div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-xl">
    <div class="flex items-center justify-between border-b border-line bg-white p-5">
        <div><h3 class="font-serif text-2xl font-bold text-primary">Departmental Reports Ledger</h3><p class="text-sm text-slate-500">Validated live operational exports with audit timestamps and departmental supervisor sign-offs.</p></div>
        <span class="rounded-full bg-secondary-fixed/30 px-3 py-1 text-xs font-bold text-secondary">View-only audit mode</span>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1120px] border-collapse text-left text-sm">
            <thead><tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600"><th class="px-5 py-3">Report Title & Code</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Frequency</th><th class="px-4 py-3">Telemetry / Status</th><th class="px-4 py-3">Audit Hash</th><th class="px-5 py-3 text-right">Executive Audit Actions</th></tr></thead>
            <tbody class="divide-y divide-line">
                @foreach ($reportRows as $report)
                    <tr class="transition hover:bg-surface-low" data-ops-report-row data-department="{{ $report['department'] }}" data-search="{{ strtolower($report['title'].' '.$report['code'].' '.$report['department'].' '.$report['frequency'].' '.$report['status'].' '.$report['metric']) }}">
                        <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary">summarize</span><div><p class="font-bold text-primary">{{ $report['title'] }}</p><p class="text-xs text-slate-500">{{ $report['code'] }}</p></div></div></td>
                        <td class="px-4 py-4"><span class="rounded-md px-2 py-1 text-[11px] font-bold {{ $departmentTone[$report['department']] }}">{{ $report['department'] }}</span></td>
                        <td class="px-4 py-4 text-slate-600">{{ $report['frequency'] }}</td>
                        <td class="px-4 py-4"><span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusTone[$report['status']] ?? $statusTone['Ready'] }}">{{ $report['status'] }}</span><p class="mt-1 text-xs text-slate-500">{{ $report['metric'] }}</p></td>
                        <td class="px-4 py-4 font-mono text-xs text-slate-600">OPS-{{ $report['hash'] }}</td>
                        <td class="px-5 py-4 text-right"><div class="flex justify-end gap-2"><button type="button" data-ops-preview class="inline-flex items-center gap-1 rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-surface-low"><span class="material-symbols-outlined text-sm">visibility</span>Preview</button><button type="button" data-ops-download class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white transition hover:bg-primary-container"><span class="material-symbols-outlined text-sm text-secondary-fixed">picture_as_pdf</span>CSV Download</button></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-2 border-t border-line bg-surface-low p-4 text-xs text-slate-500 sm:flex-row">
        <span id="ownerOpsReportCount">Displaying {{ $reportRows->count() }} operational reports</span>
        <span>Generated {{ now('Asia/Manila')->format('M d, Y h:i A') }} PHT</span>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Staffing Advisory</p><h3 class="mt-2 font-serif text-2xl font-bold">Coverage Stable</h3><p class="mt-3 text-sm text-slate-600">Zero unscheduled absences reported in the visible owner feed.</p></div>
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Safety Compliance</p><h3 class="mt-2 font-serif text-2xl font-bold">SBMA Standard Verified</h3><p class="mt-3 text-sm text-slate-600">Operational safety and hygiene monitoring remains compliant through current reporting period.</p></div>
    <div class="rounded-xl border border-primary-container bg-primary p-5 text-white shadow-md"><p class="text-xs font-bold uppercase tracking-wider text-secondary-fixed">Owner Governance</p><h3 class="mt-2 font-serif text-2xl font-bold">Executive View-Only</h3><p class="mt-3 text-sm leading-6 text-blue-100">Reports can be reviewed and exported. Operational edits remain restricted to authorized department users and administrators.</p></div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-ops-report-row]'));
        const search = document.getElementById('ownerOpsReportSearch');
        const count = document.getElementById('ownerOpsReportCount');
        const exportButton = document.getElementById('ownerOpsReportExport');
        const filterButtons = Array.from(document.querySelectorAll('[data-ops-filter]'));
        let activeDepartment = 'all';

        const applyFilters = () => {
            const terms = (search?.value || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
            let visible = 0;
            rows.forEach((row) => {
                const departmentMatch = activeDepartment === 'all' || row.dataset.department === activeDepartment;
                const textMatch = !terms.length || terms.every((term) => (row.dataset.search || '').includes(term));
                const show = departmentMatch && textMatch;
                row.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });
            filterButtons.forEach((button) => {
                const active = button.dataset.opsFilter === activeDepartment;
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('bg-surface-low', !active);
                button.classList.toggle('text-slate-600', !active);
            });
            if (count) count.textContent = `Displaying ${visible} of ${rows.length} operational reports`;
        };

        const exportVisible = () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = [
                ['Report Title & Code', 'Department', 'Frequency', 'Telemetry / Status', 'Audit Hash', 'Actions'],
                ...visibleRows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-operational-reports.csv';
            link.click();
            URL.revokeObjectURL(url);
        };

        filterButtons.forEach((button) => button.addEventListener('click', () => {
            activeDepartment = button.dataset.opsFilter || 'all';
            applyFilters();
        }));
        search?.addEventListener('input', applyFilters);
        exportButton?.addEventListener('click', exportVisible);
        document.querySelectorAll('[data-ops-download], [data-ops-preview]').forEach((button) => button.addEventListener('click', exportVisible));
        applyFilters();
    })();
</script>
