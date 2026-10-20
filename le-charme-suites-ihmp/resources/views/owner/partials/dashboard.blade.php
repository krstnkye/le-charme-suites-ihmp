@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupiedPercent = round(($occupiedRooms / $safeTotalRooms) * 100);
    $availablePercent = round(($availableRooms / $safeTotalRooms) * 100);
    $reservedPercent = round(($reservedRooms / $safeTotalRooms) * 100);
    $maintenancePercent = round(($maintenanceRooms / $safeTotalRooms) * 100);
    $averageRoomRate = $rooms->avg('rate') ?? 0;
    $roomRevenue = $recordedRevenue > 0 ? $recordedRevenue : $collectedToday;
    $forecastRevenue = $roomRevenue > 0 ? $roomRevenue * 1.25 : 0;
    $operationalHealth = min(100, max(0, 80 + ($availablePercent / 5) - ($maintenancePercent / 2)));
    $roomRows = $rooms->take(4);
    $latestAuditRows = $transactions->take(4);
@endphp

<section class="relative overflow-hidden rounded-xl border border-secondary/20 bg-gradient-to-r from-primary via-primary-container to-[#1b344f] p-7 text-white shadow-lg">
    <div class="pointer-events-none absolute -bottom-20 -right-16 opacity-[0.06]">
        <span class="material-symbols-outlined text-[220px]">shield</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="space-y-3">
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="rounded border border-secondary-container bg-secondary-fixed px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-primary">Board Level View</span>
                <span class="font-mono text-xs text-secondary-fixed">Node: SBMA-LCS-01</span>
            </div>
            <h2 class="max-w-4xl font-serif text-4xl font-semibold leading-tight">Hotel Ownership & Executive Performance Portal</h2>
            <p class="max-w-4xl text-sm leading-6 text-blue-100">
                Executive read-only command view with <strong class="text-white">{{ number_format($operationalHealth, 1) }}% Operational Health Index</strong>,
                a <strong class="text-secondary-fixed">{{ $publishedRoomCount }}-room property</strong> with {{ $totalRooms }} rooms currently encoded for live occupancy, readiness, and revenue visibility.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('owner.page', ['page' => 'reports']) }}" class="inline-flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/20">
                <span class="material-symbols-outlined text-secondary-fixed">bolt</span>
                Executive Flash Briefing
            </a>
            <a href="{{ route('owner.page', ['page' => 'reports']) }}#records" class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-secondary/90">
                <span class="material-symbols-outlined">picture_as_pdf</span>
                Export Board Deck
            </a>
            <a href="{{ route('owner.page', ['page' => 'reservations']) }}" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-primary-container px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary">
                <span class="material-symbols-outlined">calendar_add_on</span>
                Schedule GM Sync
            </a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
    <div class="owner-card relative overflow-hidden rounded-xl p-5">
        <div class="absolute left-0 top-0 h-1 w-full bg-secondary"></div>
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Revenue Today</span><span class="rounded-lg bg-surface-mid p-2 text-secondary"><span class="material-symbols-outlined">payments</span></span></div>
        <p class="mt-3 font-serif text-2xl font-bold">PHP {{ number_format($roomRevenue, 2) }}</p>
        <p class="mt-2 text-xs text-slate-500">Forecasted run rate: <strong>PHP {{ number_format($forecastRevenue, 2) }}</strong></p>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl p-5">
        <div class="absolute left-0 top-0 h-1 w-full bg-primary"></div>
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Property Occupancy</span><span class="rounded-lg bg-surface-mid p-2 text-primary"><span class="material-symbols-outlined">meeting_room</span></span></div>
        <p class="mt-3 font-serif text-2xl font-bold">{{ $occupiedPercent }}%</p>
        <p class="mt-2 text-xs text-slate-500">Occupied: <strong>{{ $occupiedRooms }} / {{ $totalRooms }}</strong> encoded rooms · {{ $publishedRoomCount }} property total</p>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl p-5">
        <div class="absolute left-0 top-0 h-1 w-full bg-secondary"></div>
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Average Room Rate</span><span class="rounded-lg bg-surface-mid p-2 text-secondary"><span class="material-symbols-outlined">query_stats</span></span></div>
        <p class="mt-3 font-serif text-2xl font-bold">PHP {{ number_format($averageRoomRate, 2) }}</p>
        <p class="mt-2 text-xs text-slate-500">Calculated from encoded room inventory</p>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl p-5">
        <div class="absolute left-0 top-0 h-1 w-full bg-primary"></div>
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Reservations</span><span class="rounded-lg bg-surface-mid p-2 text-primary"><span class="material-symbols-outlined">calendar_month</span></span></div>
        <p class="mt-3 font-serif text-2xl font-bold">{{ $activeReservations }}</p>
        <p class="mt-2 text-xs text-slate-500">{{ $arrivalsToday }} arrivals, {{ $departuresToday }} departures today</p>
    </div>
    <div class="owner-card relative overflow-hidden rounded-xl p-5">
        <div class="absolute left-0 top-0 h-1 w-full bg-emerald-600"></div>
        <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Cash & Bill Clearing</span><span class="rounded-lg bg-emerald-50 p-2 text-emerald-700"><span class="material-symbols-outlined">verified</span></span></div>
        <p class="mt-3 font-serif text-2xl font-bold">{{ $settledToday }} Settled</p>
        <p class="mt-2 text-xs text-slate-500">Today collection: <strong>PHP {{ number_format($collectedToday, 2) }}</strong></p>
    </div>
</section>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <section class="owner-card rounded-xl p-6 xl:col-span-7">
        <div class="flex items-start justify-between gap-4 border-b border-line pb-4">
            <div>
                <h3 class="font-serif text-2xl font-bold">Departmental Operational Pulse & Integrity</h3>
                <p class="text-sm text-slate-500">Live accountability, status health, and executive monitoring across departments.</p>
            </div>
            <span class="rounded bg-surface-mid px-2.5 py-1 text-xs font-bold text-secondary">Live Sync</span>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div class="rounded-lg border border-line bg-surface-low p-4">
                <div class="flex items-center justify-between"><span class="font-bold">Front Desk</span><span class="rounded bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">Active</span></div>
                <dl class="mt-3 space-y-2 text-xs text-slate-600">
                    <div class="flex justify-between"><dt>Arrivals Today</dt><dd class="font-bold text-primary">{{ $arrivalsToday }}</dd></div>
                    <div class="flex justify-between"><dt>Departures Today</dt><dd class="font-bold text-primary">{{ $departuresToday }}</dd></div>
                    <div class="flex justify-between"><dt>In-house Guests</dt><dd class="font-bold text-primary">{{ $currentGuests }}</dd></div>
                </dl>
            </div>
            <div class="rounded-lg border border-line bg-surface-low p-4">
                <div class="flex items-center justify-between"><span class="font-bold">Billing</span><span class="rounded bg-secondary-fixed px-2 py-0.5 text-[11px] font-bold text-primary">View Only</span></div>
                <dl class="mt-3 space-y-2 text-xs text-slate-600">
                    <div class="flex justify-between"><dt>Settled Today</dt><dd class="font-bold text-primary">{{ $settledToday }}</dd></div>
                    <div class="flex justify-between"><dt>Recorded Revenue</dt><dd class="font-bold text-primary">PHP {{ number_format($recordedRevenue, 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Latest Transactions</dt><dd class="font-bold text-primary">{{ $transactions->count() }}</dd></div>
                </dl>
            </div>
            <div class="rounded-lg border border-line bg-surface-low p-4">
                <div class="flex items-center justify-between"><span class="font-bold">Housekeeping</span><span class="rounded bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800">{{ $maintenanceRooms }} Queue</span></div>
                <dl class="mt-3 space-y-2 text-xs text-slate-600">
                    <div class="flex justify-between"><dt>Available Rooms</dt><dd class="font-bold text-primary">{{ $availableRooms }}</dd></div>
                    <div class="flex justify-between"><dt>Cleaning / Maintenance</dt><dd class="font-bold text-primary">{{ $maintenanceRooms }}</dd></div>
                    <div class="flex justify-between"><dt>Readiness</dt><dd class="font-bold text-emerald-700">{{ $availablePercent }}%</dd></div>
                </dl>
            </div>
            <div class="rounded-lg border border-line bg-surface-low p-4">
                <div class="flex items-center justify-between"><span class="font-bold">Owner Controls</span><span class="rounded bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">No access - admin only</span></div>
                <p class="mt-3 text-xs leading-5 text-slate-600">User Management and System Settings are admin-only. Operational record changes remain with departments and administrators.</p>
            </div>
        </div>
        <div class="mt-5 rounded-lg border border-line bg-surface-mid p-3 text-xs font-semibold text-slate-700">
            Overall enterprise SLA compliance estimate: <strong class="text-primary">{{ number_format($operationalHealth, 1) }}%</strong>
        </div>
    </section>

    <section class="owner-card rounded-xl p-6 xl:col-span-5">
        <div class="flex items-start justify-between gap-4 border-b border-line pb-4">
            <div>
                <h3 class="font-serif text-2xl font-bold">Room Inventory & Cohort Distribution</h3>
                <p class="text-sm text-slate-500">Live room breakdown based on encoded inventory.</p>
            </div>
            <span class="rounded bg-primary px-2 py-1 font-mono text-xs font-bold text-secondary-fixed">{{ $occupiedRooms }} / {{ $totalRooms }} Active</span>
        </div>
        <div class="mt-4 flex flex-wrap gap-3 text-xs">
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-primary"></span> Occupied {{ $occupiedRooms }}</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-secondary"></span> Available {{ $availableRooms }}</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-amber-500"></span> Reserved {{ $reservedRooms }}</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-error"></span> Maintenance {{ $maintenanceRooms }}</span>
        </div>
        <div class="mt-5 rounded-lg border border-line bg-surface-low p-4">
            <div class="flex justify-between text-xs font-bold text-primary"><span>All Managed Rooms</span><span>{{ $occupiedPercent }}% occupied</span></div>
            <div class="mt-2 flex h-4 overflow-hidden rounded-full bg-surface-mid">
                <div class="bg-primary" style="width: {{ $occupiedPercent }}%"></div>
                <div class="bg-secondary" style="width: {{ $availablePercent }}%"></div>
                <div class="bg-amber-500" style="width: {{ $reservedPercent }}%"></div>
                <div class="bg-error" style="width: {{ $maintenancePercent }}%"></div>
            </div>
        </div>
        <div class="mt-4 space-y-3">
            @forelse ($roomRows as $room)
                <div class="rounded-lg border border-line bg-white p-3 text-sm">
                    <div class="flex items-center justify-between">
                        <strong>Room {{ $room->room_number }} · {{ $room->room_type }}</strong>
                        <span class="rounded bg-surface-mid px-2 py-1 text-xs font-bold capitalize">{{ $room->status }}</span>
                    </div>
                    <div class="mt-2 flex justify-between text-xs text-slate-500">
                        <span>{{ $room->capacity }} guests</span>
                        <span>PHP {{ number_format($room->rate, 2) }}</span>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-line bg-white p-5 text-sm text-slate-500">No room inventory records yet.</div>
            @endforelse
        </div>
        <a href="{{ route('owner.page', ['page' => 'rooms']) }}" class="mt-5 inline-flex items-center gap-1 text-sm font-bold text-secondary hover:underline">
            Open Full Interactive Grid <span class="material-symbols-outlined text-base">open_in_new</span>
        </a>
    </section>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <section class="owner-card rounded-xl p-6 xl:col-span-5">
        <div class="flex items-center justify-between border-b border-line pb-4">
            <div>
                <h3 class="font-serif text-2xl font-bold">Revenue Streams & Contribution</h3>
                <p class="text-sm text-slate-500">Owner-level revenue visibility from current hotel records.</p>
            </div>
            <span class="rounded bg-surface-mid p-2 text-secondary"><span class="material-symbols-outlined">pie_chart</span></span>
        </div>
        <div class="mt-5 space-y-4">
            @foreach ([
                ['label' => 'Room & Stay Charges', 'value' => $roomRevenue, 'percent' => 68, 'color' => 'bg-primary'],
                ['label' => 'Billing Collections', 'value' => $collectedToday, 'percent' => 22, 'color' => 'bg-secondary'],
                ['label' => 'Reservations Pipeline', 'value' => $activeReservations * max($averageRoomRate, 0), 'percent' => 10, 'color' => 'bg-slate-500'],
            ] as $stream)
                <div>
                    <div class="mb-1 flex justify-between text-xs">
                        <span class="font-bold text-primary">{{ $stream['label'] }}</span>
                        <span class="font-mono font-bold">PHP {{ number_format($stream['value'], 2) }}</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-surface-mid">
                        <div class="{{ $stream['color'] }} h-full rounded-full" style="width: {{ $stream['percent'] }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6 rounded-lg border border-line bg-surface-mid p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Net Operating Visibility</p>
            <p class="mt-1 font-serif text-2xl font-bold">PHP {{ number_format($roomRevenue + $collectedToday, 2) }}</p>
        </div>
    </section>

    <section class="owner-card rounded-xl p-6 xl:col-span-7">
        <div class="flex items-center justify-between border-b border-line pb-4">
            <div>
                <h3 class="font-serif text-2xl font-bold">Privileged Executive Audit & GM Shift Feed</h3>
                <p class="text-sm text-slate-500">Timestamped high-value activity visible to ownership.</p>
            </div>
            <a href="{{ route('owner.page', ['page' => 'reports']) }}" class="rounded border border-line bg-surface-low px-3 py-1 text-xs font-bold text-primary hover:border-secondary">Open Records</a>
        </div>
        <div class="mt-3 divide-y divide-line">
            @forelse ($latestAuditRows as $transaction)
                <div class="flex items-start gap-4 py-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-secondary/30 bg-secondary-fixed/50 text-secondary"><span class="material-symbols-outlined text-lg">approval</span></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <strong>{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }} Recorded</strong>
                            <span class="font-mono text-xs text-slate-500">{{ $transaction->transaction_date?->format('M d, h:i A') }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $transaction->guest?->full_name ?? 'Guest account' }} · Room {{ $transaction->room?->room_number ?? 'N/A' }} ·
                            <strong class="text-primary">PHP {{ number_format($transaction->amount, 2) }}</strong>
                        </p>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-line bg-white p-6 text-center text-sm text-slate-500">No executive audit activity has been recorded yet.</div>
            @endforelse
        </div>
    </section>
</div>
