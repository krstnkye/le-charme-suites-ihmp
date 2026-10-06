@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $vacantCleanRooms = $rooms->where('status', 'available')->count();
    $turnoverRooms = $rooms->where('status', 'cleaning')->count();
    $outOfOrderRooms = $rooms->where('status', 'maintenance')->count();
    $readinessRate = round(($vacantCleanRooms / $safeTotalRooms) * 100, 1);
    $dailyPotential = $rooms->sum('rate');
    $statusCopy = [
        'available' => ['label' => 'Vacant Clean', 'badge' => 'border-secondary/30 bg-secondary/15 text-secondary', 'housekeeping' => 'Inspected & Sanitized', 'key' => 'Vault Assigned', 'keyIcon' => 'key_off'],
        'occupied' => ['label' => 'Occupied', 'badge' => 'border-primary-container/20 bg-primary-container/10 text-primary-container', 'housekeeping' => 'In-Stay Clean Completed', 'key' => 'Keycards Active', 'keyIcon' => 'key'],
        'reserved' => ['label' => 'Reserved', 'badge' => 'border-blue-200 bg-blue-50 text-blue-700', 'housekeeping' => 'Pre-arrival Release Ready', 'key' => 'Pending Issue', 'keyIcon' => 'vpn_key_alert'],
        'cleaning' => ['label' => 'Turnover', 'badge' => 'border-sky-200 bg-sky-50 text-sky-700', 'housekeeping' => 'Cleaning In Progress', 'key' => 'Key Vault Hold', 'keyIcon' => 'cleaning_services'],
        'maintenance' => ['label' => 'Maintenance Hold', 'badge' => 'border-amber-300 bg-amber-50 text-amber-800', 'housekeeping' => 'Engineering Review', 'key' => 'Locked Out', 'keyIcon' => 'lock_clock'],
    ];
    $statusDot = [
        'available' => 'bg-secondary',
        'occupied' => 'bg-primary-container',
        'reserved' => 'bg-blue-500',
        'cleaning' => 'bg-sky-500',
        'maintenance' => 'bg-amber-600',
    ];
    $roomStatusGroups = [
        ['label' => 'Vacant Clean', 'count' => $vacantCleanRooms, 'class' => 'bg-secondary'],
        ['label' => 'Occupied', 'count' => $occupiedRooms, 'class' => 'bg-primary'],
        ['label' => 'Reserved', 'count' => $reservedRooms, 'class' => 'bg-blue-500'],
        ['label' => 'Turnover', 'count' => $turnoverRooms, 'class' => 'bg-sky-500'],
        ['label' => 'Maintenance', 'count' => $outOfOrderRooms, 'class' => 'bg-amber-500'],
    ];
@endphp

<section class="relative overflow-hidden rounded-2xl border border-primary-container bg-primary-container p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -right-10 -top-10 h-44 w-44 rounded-full bg-white/10"></div>
    <div class="pointer-events-none absolute bottom-0 right-16 h-32 w-32 rounded-full bg-secondary/10"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-end">
        <div class="max-w-4xl space-y-3">
            <div class="inline-flex items-center gap-2 rounded border border-secondary-fixed/30 bg-secondary/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1;">insights</span>
                Executive Monitoring / Real-Time Housekeeping & Maintenance
            </div>
            <h2 class="font-serif text-4xl font-extrabold leading-tight">Room Status & Operational Readiness</h2>
            <p class="max-w-3xl text-sm leading-6 text-blue-100">
                Room Status Overview for live physical room status, housekeeping turnover stages, maintenance holds, and front-desk release status across Le Charmé Suites.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-primary/60 p-4 sm:grid-cols-4 xl:min-w-[520px]">
            <div class="border-r border-white/10 px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</span>
                <span class="mt-1 block font-serif text-2xl font-bold">{{ $occupancyRate }}%</span>
            </div>
            <div class="border-r border-white/10 px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Clean & Inspected</span>
                <span class="mt-1 block font-serif text-2xl font-bold text-secondary-fixed">{{ $vacantCleanRooms }}</span>
            </div>
            <div class="border-r border-white/10 px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Turnover</span>
                <span class="mt-1 block font-serif text-2xl font-bold">{{ $turnoverRooms }}</span>
            </div>
            <div class="px-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-100/70">In-House</span>
                <span class="mt-1 block font-serif text-2xl font-bold text-secondary-fixed">{{ $occupiedRooms }}</span>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5 hover:border-secondary/50">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Vacant Clean / Inspected</p>
                <h3 class="mt-1 font-serif text-3xl font-bold text-primary">{{ $vacantCleanRooms }}</h3>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-line bg-surface-low text-secondary"><span class="material-symbols-outlined">verified</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs">
            <span class="font-medium text-secondary">Ready for immediate check-in</span>
            <span class="font-bold text-primary">{{ $readinessRate }}%</span>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5 hover:border-secondary/50">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Occupied / In-House</p>
                <h3 class="mt-1 font-serif text-3xl font-bold text-primary">{{ $occupiedRooms }}</h3>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-primary-container/20 bg-primary-container/10 text-primary-container"><span class="material-symbols-outlined">person_check</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs">
            <span class="text-slate-500">{{ $currentGuests }} current guests</span>
            <span class="font-bold text-primary">Live stay</span>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5 hover:border-secondary/50">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Turnover / Cleaning</p>
                <h3 class="mt-1 font-serif text-3xl font-bold text-primary">{{ $turnoverRooms }}</h3>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-sky-100 bg-sky-50 text-sky-700"><span class="material-symbols-outlined">cleaning_services</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs">
            <span class="text-slate-500">{{ $turnoverRooms ? 'Turnover queue active' : 'All turnovers completed' }}</span>
            <span class="font-semibold text-secondary">{{ $turnoverRooms ? 'Active Cycle' : 'Idle Cycle' }}</span>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:-translate-y-0.5 hover:border-secondary/50">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Maintenance / Out-of-Order</p>
                <h3 class="mt-1 font-serif text-3xl font-bold text-secondary">{{ $outOfOrderRooms }}</h3>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary-container bg-secondary-container/40 text-secondary"><span class="material-symbols-outlined">handyman</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs">
            <span class="font-medium text-secondary">{{ $outOfOrderRooms ? 'Engineering review required' : 'No active holds' }}</span>
            <span class="font-semibold text-error">{{ $outOfOrderRooms ? 'Hold' : 'Clear' }}</span>
        </div>
    </div>
</section>

<div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">
    <section class="owner-card overflow-hidden rounded-2xl xl:col-span-8">
        <div class="flex flex-col justify-between gap-4 border-b border-line bg-white px-6 py-5 md:flex-row md:items-center">
            <div>
                <h3 class="font-serif text-2xl font-bold text-primary">Comprehensive Room Status Ledger</h3>
                <p class="mt-1 text-xs text-slate-500">Real-time housekeeping turnover states, inspection stamps, and electronic key distribution.</p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 rounded-lg border border-line bg-surface-mid p-1 text-xs font-bold">
                <span class="rounded bg-primary px-3 py-1 text-white">All ({{ $totalRooms }})</span>
                <span class="px-3 py-1 text-slate-600">Vacant ({{ $vacantCleanRooms }})</span>
                <span class="px-3 py-1 text-slate-600">Occupied ({{ $occupiedRooms }})</span>
                <span class="px-3 py-1 text-slate-600">Maint ({{ $outOfOrderRooms }})</span>
            </div>
        </div>
        <div class="owner-scroll overflow-x-auto">
            <table class="w-full min-w-[960px] border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <th class="px-4 py-3">Room & Wing</th>
                        <th class="px-4 py-3">Category & Cap</th>
                        <th class="px-4 py-3">Nightly Rate</th>
                        <th class="px-4 py-3">Physical Status</th>
                        <th class="px-4 py-3">Housekeeping Status</th>
                        <th class="px-4 py-3">Key Status</th>
                        <th class="px-4 py-3 text-right">Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($rooms as $room)
                        @php
                            $state = strtolower($room->status);
                            $copy = $statusCopy[$state] ?? $statusCopy['maintenance'];
                            $recentLog = $housekeepingLogs->firstWhere('room_id', $room->id);
                        @endphp
                        <tr @class(['transition hover:bg-surface-low', 'bg-secondary-container/10' => $state === 'maintenance'])>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <span @class(['flex h-8 w-8 items-center justify-center rounded border text-xs font-bold', 'border-secondary/30 bg-secondary/10 text-secondary' => $state === 'maintenance', 'border-primary/20 bg-primary-container/10 text-primary' => $state !== 'maintenance'])>{{ $room->room_number }}</span>
                                    <div>
                                        <div class="font-bold text-primary">Room {{ $room->room_number }}</div>
                                        <span class="text-[11px] text-slate-500">Le Charmé Rooms Cluster</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-primary">{{ $room->room_type }}</div>
                                <div class="flex items-center gap-1 text-[11px] text-slate-500"><span class="material-symbols-outlined text-sm">group</span>{{ $room->capacity }} guests</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-semibold text-primary">PHP {{ number_format($room->rate, 2) }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $copy['badge'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDot[$state] ?? 'bg-slate-400' }}"></span>
                                    {{ $copy['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-xs font-semibold text-primary">{{ $recentLog?->notes ?: $copy['housekeeping'] }}</div>
                                <div class="text-[11px] text-slate-500">{{ $recentLog?->changed_at ? 'Logged '.$recentLog->changed_at->format('M d, h:i A') : 'Latest inspection synced' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $state === 'maintenance' ? 'text-error' : 'text-secondary' }}">
                                    <span class="material-symbols-outlined text-sm">{{ $copy['keyIcon'] }}</span>
                                    {{ $copy['key'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="inline-flex rounded border border-line bg-white px-2 py-1 text-[11px] font-bold text-slate-500">View Only</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">No room status records are available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col items-center justify-between gap-3 border-t border-line bg-surface-low px-5 py-4 text-xs text-slate-500 sm:flex-row">
            <span class="inline-flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">database</span>Displaying {{ $totalRooms }} registered active rooms under Owner Room Status Overview</span>
            <span class="font-semibold">Total Daily Potential: <strong class="font-mono text-primary">PHP {{ number_format($dailyPotential, 2) }}</strong></span>
        </div>
    </section>

    <aside class="space-y-6 xl:col-span-4">
        <section class="owner-card rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Operational Readiness</p>
                    <h3 class="mt-1 font-serif text-2xl font-bold text-primary">Status Distribution</h3>
                </div>
                <span class="material-symbols-outlined text-secondary">donut_large</span>
            </div>
            <div class="mt-5 space-y-4">
                @foreach ($roomStatusGroups as $group)
                    @php $width = round(($group['count'] / $safeTotalRooms) * 100); @endphp
                    <div>
                        <div class="mb-1 flex justify-between text-xs font-semibold">
                            <span>{{ $group['label'] }}</span>
                            <span>{{ $group['count'] }} rooms</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-surface-mid">
                            <div class="h-full {{ $group['class'] }}" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-primary-container bg-gradient-to-br from-primary to-[#07111f] p-6 text-white shadow-md">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-secondary-fixed">
                <span class="material-symbols-outlined text-base">security</span>
                Owner Access Guardrail
            </div>
            <h3 class="mt-3 font-serif text-2xl font-bold">Read-Only Operational Window</h3>
            <p class="mt-2 text-sm leading-6 text-blue-100">
                This page is for owner monitoring only. Room status changes, cleaning assignments, maintenance releases, and key vault edits remain with department users and administrators.
            </p>
            <div class="mt-5 rounded-xl border border-white/10 bg-white/10 p-4 text-sm">
                <div class="flex items-center justify-between">
                    <span>Housekeeping logs visible</span>
                    <strong>{{ $housekeepingLogs->count() }}</strong>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-white/10 pt-3">
                    <span>System lock</span>
                    <strong class="text-secondary-fixed">No editing privileges</strong>
                </div>
            </div>
        </section>
    </aside>
</div>
