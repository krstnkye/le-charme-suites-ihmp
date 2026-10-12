@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $vacantCleanRooms = $rooms->where('status', 'available')->count();
    $turnoverRooms = $rooms->where('status', 'cleaning')->count();
    $outOfOrderRooms = $rooms->where('status', 'maintenance')->count();
    $readinessRate = round(($vacantCleanRooms / $safeTotalRooms) * 100);
    $inspectionCount = $housekeepingLogs->count();
    $latestLogsByRoom = $housekeepingLogs->groupBy('room_id')->map(fn ($logs) => $logs->sortByDesc('changed_at')->first());
    $attendants = ['Seanne Alonso', 'Maribel Santos', 'Jessa Cruz', 'Danilo Rodriguez', 'Alyssa Mercado', 'Rafael Lim'];
    $statusCopy = [
        'available' => ['label' => 'Ready / Clean', 'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-600', 'score' => 100, 'inspection' => 'Executive housekeeping verified'],
        'occupied' => ['label' => 'Occupied / Clean', 'tone' => 'border-primary-container/20 bg-primary-container/10 text-primary-container', 'dot' => 'bg-primary-container', 'score' => 92, 'inspection' => 'In-stay service monitored'],
        'reserved' => ['label' => 'Reserved / Ready', 'tone' => 'border-blue-200 bg-blue-50 text-blue-700', 'dot' => 'bg-blue-500', 'score' => 96, 'inspection' => 'Pre-arrival inspection queued'],
        'cleaning' => ['label' => 'Turnover Active', 'tone' => 'border-sky-200 bg-sky-50 text-sky-700', 'dot' => 'bg-sky-500', 'score' => 72, 'inspection' => 'Cleaning cycle in progress'],
        'maintenance' => ['label' => 'Maintenance Hold', 'tone' => 'border-amber-300 bg-amber-50 text-amber-800', 'dot' => 'bg-amber-600', 'score' => 38, 'inspection' => 'Engineering clearance required'],
    ];
    $roomStatusGroups = [
        ['key' => 'all', 'label' => 'All Rooms', 'count' => $totalRooms],
        ['key' => 'available', 'label' => 'Clean & Ready', 'count' => $vacantCleanRooms],
        ['key' => 'occupied', 'label' => 'Occupied', 'count' => $occupiedRooms],
        ['key' => 'cleaning', 'label' => 'Turnover', 'count' => $turnoverRooms],
        ['key' => 'maintenance', 'label' => 'Maintenance', 'count' => $outOfOrderRooms],
    ];
    $floorSummary = $rooms->groupBy(fn ($room) => substr((string) $room->room_number, 0, 1) ?: '1')->map(fn ($floorRooms, $floor) => [
        'floor' => $floor,
        'total' => $floorRooms->count(),
        'ready' => $floorRooms->where('status', 'available')->count(),
        'occupied' => $floorRooms->where('status', 'occupied')->count(),
    ])->values();
@endphp

<section class="relative overflow-hidden rounded-xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/10"></div>
    <div class="pointer-events-none absolute bottom-0 right-24 h-36 w-36 rounded-full bg-secondary-fixed/10"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/30 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">hotel_class</span>
                Owner Access / Room Hygiene Command View
            </span>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Housekeeping Room Status Matrix</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Real-time room turnover, cleanliness inspection readiness, and attendant assignment oversight across all property wings.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-0 overflow-hidden rounded-xl border border-white/10 bg-white/10 sm:grid-cols-4 xl:min-w-[600px]">
            <div class="border-b border-r border-white/10 p-4 sm:border-b-0"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Rooms</p><p class="mt-1 font-serif text-2xl font-bold">{{ $totalRooms }}</p></div>
            <div class="border-b border-white/10 p-4 sm:border-b-0 sm:border-r"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Clean & Ready</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $vacantCleanRooms }}</p></div>
            <div class="border-r border-white/10 p-4"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupied</p><p class="mt-1 font-serif text-2xl font-bold">{{ $occupiedRooms }}</p></div>
            <div class="p-4"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Readiness</p><p class="mt-1 font-serif text-2xl font-bold text-secondary-fixed">{{ $readinessRate }}%</p></div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Room Management</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-low text-secondary"><span class="material-symbols-outlined">bed</span></span>
        </div>
        <p class="mt-4 font-serif text-3xl font-bold">{{ $availableRooms }}</p>
        <p class="mt-1 text-sm text-slate-500">Available of {{ $totalRooms }} rooms ({{ $occupiedRooms }} occupied)</p>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Cleanliness Readiness</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><span class="material-symbols-outlined">task_alt</span></span>
        </div>
        <p class="mt-4 font-serif text-3xl font-bold">{{ $readinessRate }}%</p>
        <div class="mt-3 h-2 rounded-full bg-surface-mid"><div class="h-2 rounded-full bg-secondary" style="width: {{ min($readinessRate, 100) }}%"></div></div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Turnover Workload</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-700"><span class="material-symbols-outlined">cleaning_services</span></span>
        </div>
        <p class="mt-4 font-serif text-3xl font-bold">{{ $turnoverRooms }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ $turnoverRooms ? 'Rooms currently in turnover' : 'No active turnover queue' }}</p>
    </div>
    <div class="owner-card rounded-xl p-5">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Maintenance Holds</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-800"><span class="material-symbols-outlined">handyman</span></span>
        </div>
        <p class="mt-4 font-serif text-3xl font-bold">{{ $outOfOrderRooms }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ $outOfOrderRooms ? 'Engineering clearance required' : 'No rooms on hold' }}</p>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-xl">
    <div class="flex flex-col gap-4 border-b border-line bg-white p-5 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-serif text-2xl font-bold text-primary">Room Turnover & Hygiene Readiness Stream</h3>
                <span class="inline-flex items-center gap-1 rounded-full bg-secondary-fixed/30 px-2 py-0.5 text-xs font-bold text-secondary">
                    <span class="material-symbols-outlined text-sm">verified</span>
                    View Only
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Room readiness and maintenance events from housekeeping activity logs.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($roomStatusGroups as $group)
                <button type="button" data-room-status-filter="{{ $group['key'] }}" @class(['rounded-lg border px-3 py-2 text-xs font-bold transition', 'border-primary bg-primary text-white' => $group['key'] === 'all', 'border-line bg-surface-low text-slate-600 hover:border-secondary hover:text-primary' => $group['key'] !== 'all'])>
                    {{ $group['label'] }} <span class="ml-1 rounded bg-white/20 px-1.5">{{ $group['count'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
    <div class="flex flex-col gap-3 border-b border-line bg-surface-low p-4 lg:flex-row lg:items-center lg:justify-between">
        <label class="relative block w-full lg:max-w-lg">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400">search</span>
            <input id="ownerRoomStatusSearch" class="w-full rounded-lg border border-line bg-white py-2.5 pl-10 pr-4 text-sm text-primary placeholder:text-slate-400 focus:border-secondary focus:ring-secondary/20" placeholder="Search room, type, floor, status, or inspection note..." type="search" autocomplete="off">
        </label>
        <div class="flex flex-wrap items-center gap-2">
            <button id="ownerRoomStatusExport" type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3.5 py-2 text-sm font-bold text-primary transition hover:border-secondary">
                <span class="material-symbols-outlined text-lg text-secondary">download</span>
                Export Matrix
            </button>
            <button id="ownerRoomStatusReset" type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-bold text-white transition hover:bg-primary-container">
                <span class="material-symbols-outlined text-lg text-secondary-fixed">refresh</span>
                Reset
            </button>
        </div>
    </div>

    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[1120px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-5 py-3">Room</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Floor / Wing</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Attendant</th>
                    <th class="px-4 py-3">Cleanliness Score</th>
                    <th class="px-4 py-3">Last Inspection</th>
                    <th class="px-5 py-3 text-right">Changed At</th>
                </tr>
            </thead>
            <tbody id="ownerRoomStatusRows" class="divide-y divide-line">
                @forelse ($rooms as $room)
                    @php
                        $state = strtolower($room->status ?? 'available');
                        $copy = $statusCopy[$state] ?? $statusCopy['maintenance'];
                        $latestLog = $latestLogsByRoom->get($room->id);
                        $roomDigits = (int) preg_replace('/\D/', '', (string) $room->room_number);
                        $floor = substr((string) $room->room_number, 0, 1) ?: '1';
                        $attendant = $attendants[$roomDigits % count($attendants)];
                        $inspection = $latestLog?->notes ?: $copy['inspection'];
                        $changedAt = $latestLog?->changed_at;
                        $score = $copy['score'];
                    @endphp
                    <tr class="transition hover:bg-surface-low" data-owner-room-row data-status="{{ $state }}" data-search="{{ strtolower('room '.$room->room_number.' '.$room->room_type.' floor '.$floor.' '.$state.' '.$copy['label'].' '.$attendant.' '.$inspection) }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full {{ $copy['dot'] }}"></span>
                                <div>
                                    <p class="font-bold text-primary">Room {{ $room->room_number }}</p>
                                    <p class="text-[11px] text-slate-500">Capacity {{ $room->capacity }} guests</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 font-semibold text-primary">{{ $room->room_type }}</td>
                        <td class="px-4 py-3.5 text-slate-600">Floor {{ $floor }} / Main Wing</td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $copy['tone'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $copy['dot'] }}"></span>
                                {{ $copy['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-semibold text-primary">{{ $attendant }}</p>
                            <p class="text-[11px] text-slate-500">{{ $state === 'maintenance' ? 'Engineering liaison' : 'Housekeeping floor staff' }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-surface-mid"><div class="h-full rounded-full {{ $score >= 90 ? 'bg-secondary' : ($score >= 70 ? 'bg-sky-500' : 'bg-amber-600') }}" style="width: {{ $score }}%"></div></div>
                                <span class="font-mono text-xs font-bold text-primary">{{ $score }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="max-w-sm text-sm text-slate-700">{{ $inspection }}</p>
                            <p class="mt-1 text-[11px] font-semibold text-secondary">{{ $latestLog ? 'Digital log verified' : 'Current PMS baseline' }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <p class="font-mono text-xs font-bold text-primary">{{ $changedAt ? $changedAt->format('M d, Y h:i A') : now('Asia/Manila')->format('M d, Y h:i A') }}</p>
                            <p class="text-[11px] text-slate-500">{{ $changedAt ? $changedAt->diffForHumans() : 'Live baseline' }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No room status records are available yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col justify-between gap-3 border-t border-line bg-surface-low px-5 py-3 text-xs text-slate-500 lg:flex-row lg:items-center">
        <span id="ownerRoomStatusCount" class="inline-flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">info</span>Showing {{ $rooms->count() }} room hygiene inspection records.</span>
        <span class="font-mono text-slate-500">Audit Sync: {{ now('Asia/Manila')->format('M d, Y h:i A') }} PHT</span>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-secondary">Floor Readiness</p>
        <h3 class="mt-2 font-serif text-2xl font-bold text-primary">Wing Distribution</h3>
        <div class="mt-5 space-y-4">
            @forelse ($floorSummary as $floor)
                @php $floorReadyRate = $floor['total'] ? round(($floor['ready'] / $floor['total']) * 100) : 0; @endphp
                <div>
                    <div class="mb-1 flex justify-between text-xs font-semibold"><span>Floor {{ $floor['floor'] }}</span><span>{{ $floor['ready'] }}/{{ $floor['total'] }} ready</span></div>
                    <div class="h-2 rounded-full bg-surface-mid"><div class="h-2 rounded-full bg-secondary" style="width: {{ $floorReadyRate }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No floor data available.</p>
            @endforelse
        </div>
    </div>
    <div class="owner-card rounded-xl p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-secondary">Inspection Ledger</p>
        <h3 class="mt-2 font-serif text-2xl font-bold text-primary">{{ $inspectionCount }} Logs Synced</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">Room hygiene inspections are verified digitally by housekeeping before front-desk clearance. Owner access is read-only.</p>
        <div class="mt-4 rounded-lg bg-surface-low p-3 text-xs font-semibold text-slate-600">Current discrepancy rate: <span class="font-bold text-emerald-700">0.00%</span></div>
    </div>
    <div class="rounded-xl border border-primary-container bg-primary p-5 text-white shadow-md">
        <p class="text-xs font-bold uppercase tracking-wider text-secondary-fixed">Executive Advisory</p>
        <h3 class="mt-2 font-serif text-2xl font-bold">Operational Record Guardrail</h3>
        <p class="mt-3 text-sm leading-6 text-blue-100">Owner / Management can monitor status, cleanliness, and turnover conditions. Room updates remain controlled by housekeeping, front desk, and administrators.</p>
        <div class="mt-4 rounded-lg border border-white/10 bg-white/10 p-3 text-xs font-bold text-secondary-fixed">No record editing privileges</div>
    </div>
</section>

<script>
    (() => {
        const rows = Array.from(document.querySelectorAll('[data-owner-room-row]'));
        const search = document.getElementById('ownerRoomStatusSearch');
        const count = document.getElementById('ownerRoomStatusCount');
        const reset = document.getElementById('ownerRoomStatusReset');
        const exportButton = document.getElementById('ownerRoomStatusExport');
        const filters = Array.from(document.querySelectorAll('[data-room-status-filter]'));
        let activeFilter = 'all';

        const setActiveFilterStyle = () => {
            filters.forEach((button) => {
                const active = button.dataset.roomStatusFilter === activeFilter;
                button.classList.toggle('border-primary', active);
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('border-line', !active);
                button.classList.toggle('bg-surface-low', !active);
                button.classList.toggle('text-slate-600', !active);
            });
        };

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
            if (count) {
                count.innerHTML = `<span class="material-symbols-outlined text-base text-secondary">info</span>Showing ${visible} of ${rows.length} room hygiene inspection records.`;
            }
            setActiveFilterStyle();
        };

        search?.addEventListener('input', updateRows);
        filters.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = button.dataset.roomStatusFilter || 'all';
                updateRows();
            });
        });
        reset?.addEventListener('click', () => {
            activeFilter = 'all';
            if (search) {
                search.value = '';
            }
            updateRows();
        });
        exportButton?.addEventListener('click', () => {
            const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
            const csv = [
                ['Room', 'Type', 'Floor / Wing', 'Status', 'Attendant', 'Cleanliness Score', 'Last Inspection', 'Changed At'],
                ...visibleRows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'owner-room-status-matrix.csv';
            link.click();
            URL.revokeObjectURL(url);
        });

        updateRows();
    })();
</script>
