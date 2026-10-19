@php
    $recordDate = now('Asia/Manila');
    $guestArchive = $guests->map(fn ($guest) => [
        'id' => 'GST-'.str_pad((string) $guest->id, 5, '0', STR_PAD_LEFT),
        'subject' => $guest->full_name ?? 'Guest profile',
        'entity' => $guest->email ?? 'Guest registry',
        'classification' => 'Guest Record',
        'details' => ($guest->reservations_count ?? 0).' reservation(s) linked',
        'timestamp' => $guest->created_at ?? $recordDate,
        'status' => 'Identity Indexed',
        'amount' => (float) ($guest->transactions_sum_amount ?? 0),
        'icon' => 'badge',
    ]);
    $stayArchive = $checkIns->map(fn ($checkIn) => [
        'id' => 'CIN-'.str_pad((string) $checkIn->id, 5, '0', STR_PAD_LEFT),
        'subject' => $checkIn->guest?->full_name ?? 'Checked-in guest',
        'entity' => $checkIn->room?->room_number ? 'Room '.$checkIn->room->room_number : 'No room assigned',
        'classification' => 'Check-In Record',
        'details' => 'Arrival logged through front desk workflow',
        'timestamp' => $checkIn->checked_in_at ?? $recordDate,
        'status' => 'Archived / Active Stay',
        'amount' => 0,
        'icon' => 'login',
    ]);
    $departureArchive = $checkOuts->map(fn ($checkOut) => [
        'id' => 'COT-'.str_pad((string) $checkOut->id, 5, '0', STR_PAD_LEFT),
        'subject' => $checkOut->guest?->full_name ?? 'Departed guest',
        'entity' => $checkOut->room?->room_number ? 'Room '.$checkOut->room->room_number : 'No room assigned',
        'classification' => 'Check-Out Record',
        'details' => 'Settlement PHP '.number_format((float) $checkOut->amount_paid, 2),
        'timestamp' => $checkOut->checked_out_at ?? $recordDate,
        'status' => 'Departure Archived',
        'amount' => (float) $checkOut->amount_paid,
        'icon' => 'logout',
    ]);
    $billingArchive = $transactions->map(fn ($transaction) => [
        'id' => 'BIL-'.str_pad((string) $transaction->id, 5, '0', STR_PAD_LEFT),
        'subject' => $transaction->guest?->full_name ?? 'Billing entry',
        'entity' => $transaction->room?->room_number ? 'Room '.$transaction->room->room_number : ($transaction->transaction_type ?: 'Ledger account'),
        'classification' => 'Billing Record',
        'details' => str($transaction->transaction_type ?: 'Manual ledger')->replace('_', ' ')->title().' / PHP '.number_format((float) $transaction->amount, 2),
        'timestamp' => $transaction->transaction_date ?? $transaction->created_at ?? $recordDate,
        'status' => in_array(strtolower((string) $transaction->status), ['paid', 'settled', 'completed', 'recorded', 'cleared'], true) ? 'Closed & Audited' : 'Financial Review',
        'amount' => (float) $transaction->amount,
        'icon' => 'receipt_long',
    ]);
    $roomArchive = $rooms->map(fn ($room) => [
        'id' => 'ROM-'.str_pad((string) $room->id, 5, '0', STR_PAD_LEFT),
        'subject' => 'Room '.$room->room_number.' '.$room->type,
        'entity' => ucfirst((string) $room->status).' / Capacity '.$room->capacity,
        'classification' => 'Room Record',
        'details' => 'Rate PHP '.number_format((float) $room->rate, 2),
        'timestamp' => $room->updated_at ?? $room->created_at ?? $recordDate,
        'status' => 'Asset Indexed',
        'amount' => (float) $room->rate,
        'icon' => 'hotel',
    ]);
    $housekeepingArchive = $housekeepingLogs->map(fn ($log) => [
        'id' => 'HKS-'.str_pad((string) $log->id, 5, '0', STR_PAD_LEFT),
        'subject' => $log->room?->room_number ? 'Room '.$log->room->room_number : 'Housekeeping log',
        'entity' => $log->notes ?: 'Room status update',
        'classification' => 'Housekeeping Log',
        'details' => 'Status changed to '.str($log->status ?? 'recorded')->replace('_', ' ')->title(),
        'timestamp' => $log->changed_at ?? $log->created_at ?? $recordDate,
        'status' => 'QA Logged',
        'amount' => 0,
        'icon' => 'cleaning_services',
    ]);
    $archiveRows = $guestArchive
        ->concat($stayArchive)
        ->concat($departureArchive)
        ->concat($billingArchive)
        ->concat($roomArchive)
        ->concat($housekeepingArchive)
        ->sortByDesc('timestamp')
        ->values();
    $visibleRows = $archiveRows->take(12);
    $totalArchived = $archiveRows->count();
    $closedFinancial = $archiveRows->whereIn('status', ['Closed & Audited', 'Departure Archived'])->count();
    $historicalVolume = (float) $archiveRows->sum('amount');
    $archiveHash = substr(strtoupper(hash('sha256', 'owner-records-'.$totalArchived.'-'.$historicalVolume)), 0, 4)
        .'-'.substr(strtoupper(hash('sha256', 'guests-'.$guests->count())), 0, 4)
        .'-'.substr(strtoupper(hash('sha256', 'rooms-'.$rooms->count())), 0, 4);
    $classificationTone = [
        'Guest Record' => 'bg-blue-50 text-blue-700',
        'Check-In Record' => 'bg-primary/10 text-primary',
        'Check-Out Record' => 'bg-emerald-50 text-emerald-700',
        'Billing Record' => 'bg-secondary/10 text-secondary',
        'Room Record' => 'bg-surface-mid text-slate-700',
        'Housekeeping Log' => 'bg-amber-50 text-amber-800',
    ];
    $statusTone = [
        'Identity Indexed' => 'bg-blue-50 text-blue-700',
        'Archived / Active Stay' => 'bg-secondary/10 text-secondary',
        'Departure Archived' => 'bg-emerald-50 text-emerald-700',
        'Closed & Audited' => 'bg-emerald-50 text-emerald-700',
        'Financial Review' => 'bg-amber-50 text-amber-700',
        'Asset Indexed' => 'bg-surface-mid text-slate-700',
        'QA Logged' => 'bg-secondary-fixed/40 text-secondary',
    ];
@endphp

<section class="relative overflow-hidden rounded-xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-secondary-fixed/5">
        <span class="material-symbols-outlined text-[220px]">inventory_2</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/40 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">policy</span>
                Executive Monitoring
            </span>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Master Operational & Historical Records Archive</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Central owner-view registry of guest profiles, stays, billing records, room assets, housekeeping logs, and immutable system archives.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:min-w-[650px]">
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Total Archived</p><p class="mt-1 font-serif text-2xl font-bold">{{ $totalArchived }}</p><p class="text-[11px] text-secondary-fixed">Records</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Closed Financial</p><p class="mt-1 font-serif text-2xl font-bold">{{ $closedFinancial }}</p><p class="text-[11px] text-secondary-fixed">Dossiers</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Retention</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">7 Yrs</p><p class="text-[11px] text-blue-100/70">SBMA / BIR</p></div>
            <div><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Integrity</p><p class="mt-1 font-serif text-xl font-bold">100% Sealed</p><p class="text-[11px] text-secondary-fixed">{{ $archiveHash }}</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Registered Guest Records</p><span class="material-symbols-outlined text-secondary">badge</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $guests->count() }}</p><p class="mt-1 text-sm text-slate-500">Owner-visible guest profiles</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Stay Movement Records</p><span class="material-symbols-outlined text-secondary">sync_alt</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $checkIns->count() + $checkOuts->count() }}</p><p class="mt-1 text-sm text-slate-500">Check-in and check-out archive</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Room & QA Logs</p><span class="material-symbols-outlined text-secondary">hotel</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $rooms->count() + $housekeepingLogs->count() }}</p><p class="mt-1 text-sm text-slate-500">Room assets and housekeeping events</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Historical Volume</p><span class="material-symbols-outlined text-secondary">payments</span></div><p class="mt-4 font-serif text-2xl font-bold">PHP {{ number_format($historicalVolume, 2) }}</p><p class="mt-1 text-sm text-slate-500">Record-linked financial value</p></div>
</section>

<section class="owner-card rounded-xl p-5">
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <label class="relative block flex-1">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input id="ownerRecordsSearch" class="w-full rounded-lg border border-line bg-white py-2.5 pl-11 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search by record ID, guest, room, invoice, status, or classification..." type="search" autocomplete="off">
            </label>
            <button id="ownerRecordsFocus" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface-low px-4 py-2.5 text-sm font-bold text-primary transition hover:border-secondary"><span class="material-symbols-outlined text-lg text-secondary">tune</span>Filter Records</button>
            <button id="ownerRecordsExport" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container"><span class="material-symbols-outlined text-lg text-secondary-fixed">archive</span>Export Selection CSV</button>
        </div>
        <div class="owner-scroll flex gap-2 overflow-x-auto border-t border-line pt-4" data-owner-record-filters>
            <button type="button" data-records-filter="all" class="whitespace-nowrap rounded-full bg-primary px-3.5 py-1.5 text-xs font-bold text-white">All Records <span class="ml-1 rounded-full bg-secondary-fixed px-1.5 py-0.5 text-[10px] text-primary">{{ $totalArchived }}</span></button>
            <button type="button" data-records-filter="Check-In Record" class="whitespace-nowrap rounded-full bg-surface-mid px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-primary">Completed Check-ins <span class="ml-1 rounded bg-white px-1.5 py-0.5 text-[10px]">{{ $checkIns->count() }}</span></button>
            <button type="button" data-records-filter="Billing Record" class="whitespace-nowrap rounded-full bg-surface-mid px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-primary">Billing Records <span class="ml-1 rounded bg-white px-1.5 py-0.5 text-[10px]">{{ $transactions->count() }}</span></button>
            <button type="button" data-records-filter="Housekeeping Log" class="whitespace-nowrap rounded-full bg-surface-mid px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-primary">Housekeeping QA <span class="ml-1 rounded bg-white px-1.5 py-0.5 text-[10px]">{{ $housekeepingLogs->count() }}</span></button>
            <button type="button" data-records-filter="Room Record" class="whitespace-nowrap rounded-full bg-surface-mid px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-primary">Room Assets <span class="ml-1 rounded bg-white px-1.5 py-0.5 text-[10px]">{{ $rooms->count() }}</span></button>
        </div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-xl">
    <div class="flex flex-col gap-3 border-b border-line bg-surface-low p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-serif text-2xl font-bold text-primary">Institutional Ledger & Document Register</h3>
            <p class="mt-1 text-sm text-slate-500">Archived records signed by the IHMP owner-view audit ledger.</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-500"><span id="ownerRecordsPageLabel">Showing {{ $visibleRows->count() }} of {{ $totalArchived }}</span><span class="rounded border border-line bg-white px-2 py-1 font-mono">Node PH-SBFZ-01</span></div>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1200px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-5 py-3">Record Identifier</th>
                    <th class="px-5 py-3">Subject / Entity</th>
                    <th class="px-5 py-3">Classification & Details</th>
                    <th class="px-5 py-3">Archive Timestamp</th>
                    <th class="px-5 py-3">Retention Status</th>
                    <th class="px-5 py-3 text-right">Owner Verification</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line bg-white">
                @forelse ($visibleRows as $record)
                    @php
                        $timestamp = $record['timestamp'];
                        $recordHash = substr(strtoupper(hash('sha256', $record['id'].$record['subject'].$record['classification'].$record['status'])), 0, 4).'..'.substr(strtoupper(hash('sha256', $record['details'].$record['entity'])), 0, 4);
                    @endphp
                    <tr class="transition hover:bg-surface-low" data-owner-record-row data-classification="{{ $record['classification'] }}" data-search="{{ e(strtolower($record['id'].' '.$record['subject'].' '.$record['entity'].' '.$record['classification'].' '.$record['details'].' '.$record['status'])) }}">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2"><span class="font-mono text-xs font-bold text-primary">{{ $record['id'] }}</span><span class="material-symbols-outlined text-xs text-secondary">lock</span></div>
                            <span class="font-mono text-[10px] text-slate-400">HASH: {{ $recordHash }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-primary">{{ $record['subject'] }}</div>
                            <div class="mt-0.5 flex items-center gap-1 text-xs text-slate-500"><span class="material-symbols-outlined text-sm text-slate-400">{{ $record['icon'] }}</span>{{ $record['entity'] }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold {{ $classificationTone[$record['classification']] ?? 'bg-surface-mid text-slate-700' }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $record['classification'] }}</span>
                            <p class="mt-1 text-xs text-slate-500">{{ $record['details'] }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-primary">{{ $timestamp?->timezone('Asia/Manila')->format('M d, Y') ?? 'No date' }}</div>
                            <span class="font-mono text-xs text-slate-500">{{ $timestamp?->timezone('Asia/Manila')->format('h:i A') ?? '--:--' }} PHT</span>
                        </td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusTone[$record['status']] ?? 'bg-surface-mid text-slate-700' }}">{{ $record['status'] }}</span></td>
                        <td class="px-5 py-4 text-right">
                            <button type="button" data-owner-record-dossier class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface-low px-3 py-1.5 text-xs font-bold text-secondary transition hover:border-secondary"><span class="material-symbols-outlined text-sm">visibility</span>View Dossier</button>
                        </td>
                    </tr>
                @empty
                    <tr data-owner-record-empty><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">No owner-visible records are available yet.</td></tr>
                @endforelse
                <tr class="hidden" data-owner-record-no-results><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">No records match the current search or filter.</td></tr>
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-2 border-t border-line bg-surface-low p-4 text-xs text-slate-500 sm:flex-row">
        <span><span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span> Ledger block synchronized with owner archive • <strong class="text-primary">{{ $archiveHash }}</strong></span>
        <span>Owner access is view-only. Direct archival edits remain restricted.</span>
    </div>
</section>

<section class="owner-card rounded-xl p-5">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-surface-mid text-secondary"><span class="material-symbols-outlined">gavel</span></span>
            <div>
                <h4 class="font-serif text-xl font-bold text-primary">Subic Bay Freeport Legal Retention Compliance</h4>
                <p class="mt-1 max-w-4xl text-sm leading-6 text-slate-600">Guest identity records, billing accounts, stay logs, room status records, and tax-related documents are retained for executive review and statutory audit support.</p>
            </div>
        </div>
        <div class="rounded-lg border border-line bg-white px-3 py-2 text-right">
            <p class="text-[10px] font-bold uppercase tracking-wider text-secondary">Cryptographic Archive Seal</p>
            <p class="mt-1 font-mono text-xs text-primary">SHA256: {{ $archiveHash }}</p>
        </div>
    </div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-owner-record-row]'));
        const search = document.getElementById('ownerRecordsSearch');
        const focus = document.getElementById('ownerRecordsFocus');
        const exportButton = document.getElementById('ownerRecordsExport');
        const count = document.getElementById('ownerRecordsPageLabel');
        const noResults = document.querySelector('[data-owner-record-no-results]');
        const filterButtons = Array.from(document.querySelectorAll('[data-records-filter]'));
        let activeClass = 'all';

        const applyFilters = () => {
            const terms = (search?.value || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
            let visible = 0;
            rows.forEach((row) => {
                const classMatch = activeClass === 'all' || row.dataset.classification === activeClass;
                const textMatch = !terms.length || terms.every((term) => (row.dataset.search || '').includes(term));
                const show = classMatch && textMatch;
                row.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });
            filterButtons.forEach((button) => {
                const active = button.dataset.recordsFilter === activeClass;
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('bg-surface-mid', !active);
                button.classList.toggle('text-slate-600', !active);
            });
            if (count) count.textContent = `Showing ${visible} of ${rows.length}`;
            noResults?.classList.toggle('hidden', visible !== 0);
        };

        const exportVisible = () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = [
                ['Record Identifier', 'Subject / Entity', 'Classification & Details', 'Archive Timestamp', 'Retention Status', 'Owner Verification'],
                ...visibleRows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `owner-records-archive-${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        };

        filterButtons.forEach((button) => button.addEventListener('click', () => {
            activeClass = button.dataset.recordsFilter || 'all';
            applyFilters();
        }));
        search?.addEventListener('input', applyFilters);
        focus?.addEventListener('click', () => {
            search?.focus();
            search?.select();
        });
        exportButton?.addEventListener('click', exportVisible);
        document.querySelectorAll('[data-owner-record-dossier]').forEach((button) => button.addEventListener('click', exportVisible));
        applyFilters();
    })();
</script>
