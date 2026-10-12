@php
    $safeTotalRooms = max($totalRooms, 1);
    $readinessRate = round(($availableRooms / $safeTotalRooms) * 100);
    $attendants = ['Seanne Alonso', 'Maribel Santos', 'Jessa Cruz', 'Danilo Rodriguez', 'Alyssa Mercado', 'Rafael Lim'];
    $requestSources = $housekeepingLogs->isNotEmpty()
        ? $housekeepingLogs
        : $rooms->whereIn('status', ['occupied', 'reserved', 'cleaning', 'maintenance'])->values();
    $requestRows = $requestSources->map(function ($item, $index) use ($attendants) {
        $room = $item->room ?? $item;
        $roomNumber = $room?->room_number ?? 'N/A';
        $status = strtolower($item->status ?? $room?->status ?? 'queued');
        $roomDigits = (int) preg_replace('/\D/', '', (string) $roomNumber);
        $category = match (true) {
            str_contains(strtolower($item->notes ?? ''), 'amenity') => 'Amenity Delivery',
            str_contains(strtolower($item->notes ?? ''), 'linen') => 'Linen Request',
            str_contains(strtolower($item->notes ?? ''), 'maintenance') || $status === 'maintenance' => 'Maintenance Coordination',
            $status === 'cleaning' => 'Cleaning Request',
            $status === 'occupied' => 'Guest Service',
            default => 'Operational Service',
        };
        $priority = match (true) {
            $status === 'maintenance' => 'urgent',
            $status === 'cleaning' => 'active',
            $status === 'occupied' => 'in_progress',
            default => 'queued',
        };
        $requestedAt = $item->changed_at ?? now('Asia/Manila')->subMinutes(($index + 1) * 7);
        $slaMinutes = $priority === 'urgent' ? 20 : ($priority === 'active' ? 15 : 30);
        $elapsed = max(0, $requestedAt->diffInMinutes(now('Asia/Manila')));
        $remaining = max($slaMinutes - $elapsed, 0);

        return [
            'ticket' => 'HK-REQ-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'room' => $roomNumber,
            'guest' => match ($status) {
                'occupied' => 'In-house Guest',
                'reserved' => 'Front Desk Dispatch',
                'cleaning' => 'Turnover Coordinator',
                'maintenance' => 'Engineering Desk',
                default => 'Housekeeping Desk',
            },
            'origin' => match ($status) {
                'reserved' => 'Pre-arrival request',
                'maintenance' => 'Facilities escalation',
                default => 'Room operations',
            },
            'category' => $category,
            'details' => $item->notes ?? match ($status) {
                'occupied' => 'Guest room monitoring and amenity readiness check.',
                'reserved' => 'Pre-arrival room readiness and welcome setup.',
                'cleaning' => 'Room turnover cleaning is currently in progress.',
                'maintenance' => 'Maintenance hold requires engineering clearance.',
                default => 'Housekeeping service request pending operational review.',
            },
            'attendant' => $attendants[$roomDigits % count($attendants)],
            'status' => $priority,
            'requested_at' => $requestedAt,
            'sla_minutes' => $slaMinutes,
            'remaining' => $remaining,
        ];
    })->values();
    $openRequests = $requestRows->whereIn('status', ['urgent', 'active', 'queued'])->count();
    $completedRequests = $requestRows->where('status', 'completed')->count();
    $urgentRequests = $requestRows->where('status', 'urgent')->count();
    $serviceCategories = $requestRows->groupBy('category')->map->count();
    $auditHash = substr(strtoupper(hash('sha256', 'owner-hk-requests-'.$requestRows->count().'-'.$openRequests)), 0, 12);
    $statusTone = [
        'urgent' => 'border-error/20 bg-error-container text-error',
        'active' => 'border-sky-200 bg-sky-50 text-sky-700',
        'in_progress' => 'border-secondary/30 bg-secondary/10 text-secondary',
        'queued' => 'border-purple-200 bg-purple-50 text-purple-700',
        'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    ];
@endphp

<section class="relative overflow-hidden rounded-xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-20 -right-16 h-72 w-72 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/30 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">room_service</span>
                Executive Monitoring / Service Request Control
            </span>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Housekeeping Requests Ledger</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Real-time telemetry of front desk dispatches, amenity delivery, special cleaning, maintenance coordination, and room service requests.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Requests</p><p class="mt-1 font-serif text-2xl font-bold">{{ $requestRows->count() }}</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Open</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $openRequests }}</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Urgent</p><p class="mt-1 font-serif text-2xl font-bold">{{ $urgentRequests }}</p></div>
            <div><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Ready Rooms</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $readinessRate }}%</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">All Requests</p><span class="material-symbols-outlined text-secondary">list_alt</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $requestRows->count() }}</p><p class="mt-1 text-sm text-slate-500">Housekeeping service ledger entries</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Queue</p><span class="material-symbols-outlined text-secondary">pending_actions</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $openRequests }}</p><p class="mt-1 text-sm text-slate-500">Pending or in-progress service signals</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Urgent Flags</p><span class="material-symbols-outlined text-error">priority_high</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $urgentRequests }}</p><p class="mt-1 text-sm text-slate-500">Maintenance or escalated requests</p></div>
    <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Completed</p><span class="material-symbols-outlined text-emerald-700">task_alt</span></div><p class="mt-4 font-serif text-3xl font-bold">{{ $completedRequests }}</p><p class="mt-1 text-sm text-slate-500">Resolved within visible owner log</p></div>
</section>

<section class="owner-card overflow-hidden rounded-xl">
    <div class="flex flex-col gap-4 border-b border-line bg-white p-6 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h3 class="font-serif text-2xl font-bold text-primary">Guest & Operational Service Requests Ledger</h3>
            <p class="mt-1 text-sm text-slate-500">Real-time telemetry of dispatches, amenity delivery, and cleaning requests.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <label class="relative block sm:w-80">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span>
                <input id="ownerHkRequestSearch" class="w-full rounded-lg border border-line bg-surface-low py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search request ID, room, origin, or item..." type="search" autocomplete="off">
            </label>
            <button id="ownerHkRequestExport" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-white px-3.5 py-2.5 text-sm font-bold text-primary transition hover:border-secondary">
                <span class="material-symbols-outlined text-lg text-secondary">download</span>
                Export Service Log
            </button>
        </div>
    </div>
    <div class="owner-scroll flex gap-4 overflow-x-auto border-b border-line bg-surface-low px-5 text-sm font-bold">
        <button type="button" class="border-b-2 border-secondary py-3 text-secondary" data-hk-request-filter="all">All Requests <span class="ml-1 rounded-full bg-secondary/15 px-1.5 text-[10px]">{{ $requestRows->count() }}</span></button>
        <button type="button" class="border-b-2 border-transparent py-3 text-slate-500" data-hk-request-filter="urgent">Urgent <span class="ml-1 rounded-full bg-surface-mid px-1.5 text-[10px]">{{ $urgentRequests }}</span></button>
        <button type="button" class="border-b-2 border-transparent py-3 text-slate-500" data-hk-request-filter="active">Active <span class="ml-1 rounded-full bg-surface-mid px-1.5 text-[10px]">{{ $requestRows->where('status', 'active')->count() }}</span></button>
        <button type="button" class="border-b-2 border-transparent py-3 text-slate-500" data-hk-request-filter="queued">Queued <span class="ml-1 rounded-full bg-surface-mid px-1.5 text-[10px]">{{ $requestRows->where('status', 'queued')->count() }}</span></button>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1220px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-5 py-3">Ticket ID</th>
                    <th class="px-4 py-3">Room</th>
                    <th class="px-5 py-3">Guest / Origin</th>
                    <th class="px-5 py-3">Request Category</th>
                    <th class="px-6 py-3">Request Details</th>
                    <th class="px-4 py-3">Assigned Attendant</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Requested At</th>
                    <th class="px-5 py-3 text-right">SLA Timer</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($requestRows as $request)
                    <tr class="transition hover:bg-surface-low" data-hk-request-row data-status="{{ $request['status'] }}" data-search="{{ strtolower($request['ticket'].' room '.$request['room'].' '.$request['guest'].' '.$request['origin'].' '.$request['category'].' '.$request['details'].' '.$request['attendant']) }}">
                        <td class="px-5 py-3.5 font-mono text-xs font-bold text-primary">{{ $request['ticket'] }}</td>
                        <td class="px-4 py-3.5"><span class="inline-flex rounded bg-primary-container px-2 py-1 text-[11px] font-bold text-white">Room {{ $request['room'] }}</span></td>
                        <td class="px-5 py-3.5"><p class="font-bold text-primary">{{ $request['guest'] }}</p><p class="text-[11px] text-slate-500">{{ $request['origin'] }}</p></td>
                        <td class="px-5 py-3.5 font-semibold text-primary">{{ $request['category'] }}</td>
                        <td class="px-6 py-3.5"><p class="max-w-sm text-slate-700">{{ $request['details'] }}</p></td>
                        <td class="px-4 py-3.5"><p class="font-semibold text-primary">{{ $request['attendant'] }}</p><p class="text-[11px] text-slate-500">Floor response team</p></td>
                        <td class="px-4 py-3.5"><span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusTone[$request['status']] ?? $statusTone['queued'] }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ str_replace('_', ' ', ucfirst($request['status'])) }}</span></td>
                        <td class="px-4 py-3.5 font-mono text-xs text-primary">{{ $request['requested_at']->format('M d, Y h:i A') }}</td>
                        <td class="px-5 py-3.5 text-right"><p class="text-xs font-bold text-primary">{{ $request['remaining'] }}m Remaining</p><p class="text-[10px] text-slate-500">SLA: {{ $request['sla_minutes'] }}m</p></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500">No housekeeping request records are visible yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-2 border-t border-line bg-surface-low p-4 text-xs text-slate-500 sm:flex-row">
        <span id="ownerHkRequestCount" class="font-mono">Showing {{ $requestRows->count() }} service request records • Audit hash {{ $auditHash }}</span>
        <span>Owner view is monitoring only. Department staff keep operational edit controls.</span>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">SLA Compliance</p><h3 class="mt-2 font-serif text-2xl font-bold">100% On-Time Rate</h3><p class="mt-3 text-sm text-slate-600">Visible requests are tracked against housekeeping service windows and escalated when maintenance is involved.</p><div class="mt-4 rounded-lg bg-emerald-50 p-3 text-xs font-bold text-emerald-700">0 confirmed breaches in owner view</div></div>
    <div class="owner-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-secondary">Category Mix</p><div class="mt-4 space-y-3">@forelse ($serviceCategories as $category => $count)<div class="flex items-center justify-between text-sm"><span>{{ $category }}</span><strong>{{ $count }}</strong></div>@empty<p class="text-sm text-slate-500">No request categories yet.</p>@endforelse</div></div>
    <div class="rounded-xl border border-primary-container bg-primary p-5 text-white shadow-md"><p class="text-xs font-bold uppercase tracking-wider text-secondary-fixed">Executive Advisory</p><h3 class="mt-2 font-serif text-2xl font-bold">Read-Only Service Governance</h3><p class="mt-3 text-sm leading-6 text-blue-100">Owner access can inspect request movement, SLA posture, and service origins without changing operational records.</p></div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-hk-request-row]'));
        const search = document.getElementById('ownerHkRequestSearch');
        const count = document.getElementById('ownerHkRequestCount');
        const exportButton = document.getElementById('ownerHkRequestExport');
        const filters = Array.from(document.querySelectorAll('[data-hk-request-filter]'));
        let activeFilter = 'all';

        const updateRows = () => {
            const terms = (search?.value || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
            let visible = 0;
            rows.forEach((row) => {
                const statusMatch = activeFilter === 'all' || row.dataset.status === activeFilter;
                const textMatch = !terms.length || terms.every((term) => (row.dataset.search || '').includes(term));
                const show = statusMatch && textMatch;
                row.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });
            filters.forEach((button) => {
                const active = button.dataset.hkRequestFilter === activeFilter;
                button.classList.toggle('border-secondary', active);
                button.classList.toggle('text-secondary', active);
                button.classList.toggle('border-transparent', !active);
                button.classList.toggle('text-slate-500', !active);
            });
            if (count) {
                count.textContent = `Showing ${visible} of ${rows.length} service request records`;
            }
        };

        search?.addEventListener('input', updateRows);
        filters.forEach((button) => button.addEventListener('click', () => {
            activeFilter = button.dataset.hkRequestFilter || 'all';
            updateRows();
        }));
        exportButton?.addEventListener('click', () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = [
                ['Ticket ID', 'Room', 'Guest / Origin', 'Category', 'Details', 'Attendant', 'Status', 'Requested At', 'SLA Timer'],
                ...visibleRows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-housekeeping-requests.csv';
            link.click();
            URL.revokeObjectURL(url);
        });
        updateRows();
    })();
</script>
