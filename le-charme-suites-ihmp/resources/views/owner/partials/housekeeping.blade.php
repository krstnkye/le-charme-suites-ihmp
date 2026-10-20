@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $readinessRate = round(($availableRooms / $safeTotalRooms) * 100);
    $turnoverQueue = $rooms->whereIn('status', ['cleaning', 'reserved'])->count();
    $maintenanceQueue = $rooms->where('status', 'maintenance')->count();
    $latestLogsByRoom = $housekeepingLogs->groupBy('room_id')->map(fn ($logs) => $logs->sortByDesc('changed_at')->first());
    $openRequestCount = $housekeepingLogs->filter(fn ($log) => str_contains(strtolower($log->notes ?? ''), 'request') || str_contains(strtolower($log->notes ?? ''), 'maintenance'))->count();
    $slaBreaches = $housekeepingLogs->filter(fn ($log) => in_array(strtolower($log->status ?? ''), ['maintenance', 'cleaning'], true))->count();
    $attendants = ['Seanne Alonso', 'Maribel Santos', 'Jessa Cruz', 'Danilo Rodriguez', 'Alyssa Mercado', 'Rafael Lim'];
    $statusStyles = [
        'available' => ['label' => 'Ready / Inspected', 'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-600', 'event' => 'Room released for guest assignment'],
        'occupied' => ['label' => 'Occupied', 'badge' => 'border-primary-container/20 bg-primary-container/10 text-primary-container', 'dot' => 'bg-primary-container', 'event' => 'In-stay monitoring active'],
        'reserved' => ['label' => 'Reserved for Arrival', 'badge' => 'border-blue-200 bg-blue-50 text-blue-700', 'dot' => 'bg-blue-500', 'event' => 'Room preparation waiting'],
        'cleaning' => ['label' => 'Room Preparation Cleaning', 'badge' => 'border-sky-200 bg-sky-50 text-sky-700', 'dot' => 'bg-sky-500', 'event' => 'Cleaning cycle in progress'],
        'maintenance' => ['label' => 'Maintenance Hold', 'badge' => 'border-amber-300 bg-amber-50 text-amber-800', 'dot' => 'bg-amber-600', 'event' => 'Engineering inspection required'],
    ];
    $floorSummary = $rooms->groupBy(fn ($room) => substr((string) $room->room_number, 0, 1) ?: '1')->map(fn ($floorRooms, $floor) => [
        'floor' => $floor,
        'total' => $floorRooms->count(),
        'ready' => $floorRooms->where('status', 'available')->count(),
    ])->values();
@endphp

<section class="relative overflow-hidden rounded-xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-white/10 to-transparent"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/30 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">cleaning_services</span>
                Executive Monitoring / Housekeeping Oversight
            </span>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Housekeeping Department Overview</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Consolidated management information for floor operations, rooms ready for guests, attendant assignments, guest amenity requests, and maintenance coordination.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:min-w-[620px]">
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p><p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Property Rooms</p><p class="mt-1 font-serif text-2xl font-bold">{{ $publishedRoomCount }}</p><p class="text-[10px] text-blue-100/70">{{ $totalRooms }} rooms recorded</p></div>
            <div class="border-r border-white/10 pr-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Attendants</p><p class="mt-1 font-serif text-2xl font-bold">{{ min(count($attendants), max($totalRooms, 1)) }}</p></div>
            <div><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">List</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $turnoverQueue + $maintenanceQueue }}</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Ready Status Score</p>
        <div class="mt-3 flex items-end justify-between"><span class="font-serif text-3xl font-bold">{{ $readinessRate }}%</span><span class="material-symbols-outlined text-secondary">verified</span></div>
        <div class="mt-4 h-2 rounded-full bg-surface-mid"><div class="h-2 rounded-full bg-secondary" style="width: {{ min($readinessRate, 100) }}%"></div></div>
        <p class="mt-3 text-xs text-slate-500">{{ $availableRooms }} rooms ready for assignment</p>
    </div>
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Room Preparation List</p>
        <div class="mt-3 flex items-end justify-between"><span class="font-serif text-3xl font-bold">{{ $turnoverQueue }}</span><span class="material-symbols-outlined text-secondary">cycle</span></div>
        <p class="mt-4 border-t border-line pt-3 text-xs font-semibold text-secondary">{{ $turnoverQueue ? 'Rooms are awaiting release' : 'No active room cleaning list' }}</p>
    </div>
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Open Requests</p>
        <div class="mt-3 flex items-end justify-between"><span class="font-serif text-3xl font-bold">{{ $openRequestCount }}</span><span class="material-symbols-outlined text-secondary">room_service</span></div>
        <p class="mt-4 border-t border-line pt-3 text-xs text-slate-500">Detected from housekeeping and maintenance notes</p>
    </div>
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Live Sync</p>
        <div class="mt-3 flex items-end justify-between"><span class="font-serif text-3xl font-bold">{{ $housekeepingLogs->count() }}</span><span class="material-symbols-outlined text-emerald-700">sync</span></div>
        <p class="mt-4 border-t border-line pt-3 text-xs font-semibold text-emerald-700">Latest activity logs online</p>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="owner-card rounded-xl p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-secondary">Room Status & Hygiene Table</p>
                <h3 class="mt-2 font-serif text-2xl font-bold">Ready Status Control Board</h3>
                <p class="mt-1 text-sm text-slate-500">Owner view of clean rooms, occupied rooms, room cleaning workload, and maintenance holds.</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-lg border border-line bg-surface-low text-secondary"><span class="material-symbols-outlined">table_chart</span></span>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-lg bg-surface-low p-3"><p class="text-xs font-bold uppercase text-slate-500">Ready</p><p class="font-serif text-2xl font-bold">{{ $availableRooms }}</p></div>
            <div class="rounded-lg bg-surface-low p-3"><p class="text-xs font-bold uppercase text-slate-500">Occupied</p><p class="font-serif text-2xl font-bold">{{ $occupiedRooms }}</p></div>
            <div class="rounded-lg bg-surface-low p-3"><p class="text-xs font-bold uppercase text-slate-500">Room Preparation</p><p class="font-serif text-2xl font-bold">{{ $turnoverQueue }}</p></div>
            <div class="rounded-lg bg-surface-low p-3"><p class="text-xs font-bold uppercase text-slate-500">Maintenance</p><p class="font-serif text-2xl font-bold">{{ $maintenanceQueue }}</p></div>
        </div>
        <button type="button" data-hk-scroll="#housekeeping-room-status" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container">
            <span class="material-symbols-outlined text-lg text-secondary-fixed">visibility</span>
            View Room Status Table
        </button>
    </div>

    <div class="owner-card rounded-xl p-6" id="housekeeping-requests">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-secondary">Guest Service & Amenity Requests</p>
                <h3 class="mt-2 font-serif text-2xl font-bold">Request Oversight</h3>
                <p class="mt-1 text-sm text-slate-500">Monitoring of request-driven housekeeping notes, inspection flags, and service recovery visibility.</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-lg border border-line bg-surface-low text-secondary"><span class="material-symbols-outlined">support_agent</span></span>
        </div>
        <div class="mt-5 grid grid-cols-3 gap-3 text-sm">
            <div class="rounded-lg bg-surface-low p-3"><p class="text-xs font-bold uppercase text-slate-500">Requests</p><p class="font-serif text-2xl font-bold">{{ $openRequestCount }}</p></div>
            <div class="rounded-lg bg-surface-low p-3"><p class="text-xs font-bold uppercase text-slate-500">Response Time Flags</p><p class="font-serif text-2xl font-bold">{{ $slaBreaches }}</p></div>
            <div class="rounded-lg bg-surface-low p-3"><p class="text-xs font-bold uppercase text-slate-500">Logs</p><p class="font-serif text-2xl font-bold">{{ $housekeepingLogs->count() }}</p></div>
        </div>
        <button type="button" id="hkShowRequests" class="mt-5 inline-flex items-center gap-2 rounded-lg border border-secondary bg-white px-4 py-2.5 text-sm font-bold text-secondary transition hover:bg-secondary-fixed/30">
            <span class="material-symbols-outlined text-lg">filter_alt</span>
            View Requests Records
        </button>
    </div>
</section>

<section class="owner-card scroll-mt-24 overflow-hidden rounded-xl" id="housekeeping-room-status">
    <div class="space-y-4 border-b border-line bg-white p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="font-serif text-2xl font-bold text-primary">Housekeeping Activity & Room Preparation Monitor</h3>
                <p class="mt-1 text-sm text-slate-500">Real-time rooms ready for guests, sanitization events, and attendant logs across all resort units.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="hkExport" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface-low px-3.5 py-2 text-sm font-bold text-primary transition hover:border-secondary">
                    <span class="material-symbols-outlined text-lg text-secondary">download</span>
                    Export Statement
                </button>
                <button type="button" id="hkReset" class="inline-flex items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-bold text-white transition hover:bg-primary-container">
                    <span class="material-symbols-outlined text-lg text-secondary-fixed">refresh</span>
                    Reset View
                </button>
            </div>
        </div>
        <label class="relative block w-full lg:max-w-xl">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span>
            <input id="hkSearch" class="w-full rounded-lg border border-line bg-white py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search room, type, attendant, status, or notes..." type="search" autocomplete="off">
        </label>
    </div>

    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1180px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-low text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-4 py-3">Room</th>
                    <th class="px-4 py-3">Type & Floor</th>
                    <th class="px-4 py-3">Live Status</th>
                    <th class="px-4 py-3">Assigned Attendant</th>
                    <th class="px-4 py-3">Notes & Operational Event</th>
                    <th class="px-4 py-3">Changed / Inspection</th>
                    <th class="px-4 py-3 text-right">System Status</th>
                </tr>
            </thead>
            <tbody id="hkRows" class="divide-y divide-line">
                @forelse ($rooms as $room)
                    @php
                        $state = strtolower($room->status ?? 'available');
                        $style = $statusStyles[$state] ?? $statusStyles['maintenance'];
                        $latestLog = $latestLogsByRoom->get($room->id);
                        $roomDigits = (int) preg_replace('/\D/', '', (string) $room->room_number);
                        $attendant = $attendants[$roomDigits % count($attendants)];
                        $floor = substr((string) $room->room_number, 0, 1) ?: '1';
                        $notes = $latestLog?->notes ?: $style['event'];
                        $changedAt = $latestLog?->changed_at;
                        $telemetry = substr(strtoupper(hash('crc32b', 'hk-'.$room->id.'-'.$state.'-'.$notes)), 0, 8);
                    @endphp
                    <tr class="transition hover:bg-surface-low" data-hk-row data-hk-search="{{ strtolower('room '.$room->room_number.' '.$room->room_type.' floor '.$floor.' '.$state.' '.$style['label'].' '.$attendant.' '.$notes) }}">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary/30 bg-secondary/10 font-serif text-lg font-bold text-secondary">{{ $room->room_number }}</span>
                                <div>
                                    <p class="font-bold text-primary">Room {{ $room->room_number }}</p>
                                    <p class="text-[11px] text-slate-500">Le Charmé Suites Wing</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-semibold text-primary">{{ $room->room_type }}</p>
                            <p class="text-[11px] text-slate-500">Floor {{ $floor }} • {{ $room->capacity }} guests</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $style['badge'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $style['dot'] }}"></span>
                                {{ $style['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-semibold text-primary">{{ $attendant }}</p>
                            <p class="text-[11px] text-slate-500">{{ $state === 'maintenance' ? 'Engineering liaison' : 'Floor care assignment' }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="max-w-md text-sm text-slate-700">{{ $notes }}</p>
                            <p class="mt-1 text-[11px] font-semibold text-secondary">{{ $latestLog ? 'Activity log synced' : 'Derived from current room status' }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-mono text-xs font-bold text-primary">{{ $changedAt ? $changedAt->format('M d, Y h:i A') : now('Asia/Manila')->format('M d, Y h:i A') }}</p>
                            <p class="text-[11px] text-slate-500">{{ $changedAt ? $changedAt->diffForHumans() : 'Current shift baseline' }}</p>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="inline-flex rounded border border-line bg-white px-2 py-1 font-mono text-[11px] font-bold text-slate-500">HK-{{ $telemetry }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">No rooms are currently added for housekeeping monitoring.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col justify-between gap-3 border-t border-line bg-surface-low px-5 py-4 text-xs text-slate-500 sm:flex-row">
        <span id="hkCount">Showing {{ $rooms->count() }} active operational keys</span>
        <span class="font-semibold">Owner access remains view-only. Operational updates stay with Housekeeping and Admin.</span>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-secondary">Room Preparation Velocity</p>
        <h3 class="mt-2 font-serif text-2xl font-bold">Floor Ready Status</h3>
        <div class="mt-4 space-y-3">
            @forelse ($floorSummary as $floor)
                @php $floorReadyRate = $floor['total'] ? round(($floor['ready'] / $floor['total']) * 100) : 0; @endphp
                <div>
                    <div class="mb-1 flex justify-between text-xs font-semibold"><span>Floor {{ $floor['floor'] }}</span><span>{{ $floor['ready'] }}/{{ $floor['total'] }} ready</span></div>
                    <div class="h-2 rounded-full bg-surface-mid"><div class="h-2 rounded-full bg-secondary" style="width: {{ $floorReadyRate }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No floor data available yet.</p>
            @endforelse
        </div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-secondary">Response Time / Service Requests</p>
        <h3 class="mt-2 font-serif text-2xl font-bold">{{ $openRequestCount }} Open Signals</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">Guest service notes, maintenance mentions, and room-readiness changes are surfaced here for management visibility without editing access.</p>
        <div class="mt-4 rounded-lg bg-surface-low p-3 text-xs font-semibold text-slate-600">Response Time flags detected: <span class="font-bold text-primary">{{ $slaBreaches }}</span></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-secondary">Maintenance Risk / Management</p>
        <h3 class="mt-2 font-serif text-2xl font-bold">{{ $maintenanceQueue }} Rooms On Hold</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">Maintenance holds remain visible to ownership, while issue resolution and record edits stay with authorized departments.</p>
        <div class="mt-4 rounded-lg border border-line bg-white p-3 text-xs font-bold text-primary">Read-only view-only mode active</div>
    </div>
</section>

<script>
    (() => {
        const search = document.getElementById('hkSearch');
        const rows = Array.from(document.querySelectorAll('[data-hk-row]'));
        const count = document.getElementById('hkCount');
        const reset = document.getElementById('hkReset');
        const requestButton = document.getElementById('hkShowRequests');
        const exportButton = document.getElementById('hkExport');

        const updateRows = () => {
            const terms = (search?.value || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
            let visible = 0;
            rows.forEach((row) => {
                const haystack = row.dataset.hkSearch || '';
                const match = !terms.length || terms.every((term) => haystack.includes(term));
                row.classList.toggle('hidden', !match);
                if (match) visible += 1;
            });
            if (count) {
                count.textContent = `Showing ${visible} of ${rows.length} active operational keys`;
            }
        };

        search?.addEventListener('input', updateRows);
        reset?.addEventListener('click', () => {
            if (search) {
                search.value = '';
            }
            updateRows();
            document.getElementById('housekeeping-room-status')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        requestButton?.addEventListener('click', () => {
            if (search) {
                search.value = 'request maintenance';
                search.focus();
            }
            updateRows();
            document.getElementById('housekeeping-room-status')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        document.querySelectorAll('[data-hk-scroll]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelector(button.dataset.hkScroll)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
        exportButton?.addEventListener('click', () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = [
                ['Room', 'Type', 'Status', 'Attendant', 'Notes', 'Changed At', 'System Status'],
                ...visibleRows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-housekeeping-monitor.csv';
            link.click();
            URL.revokeObjectURL(url);
        });
    })();
</script>
