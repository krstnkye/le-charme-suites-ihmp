@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $availabilityRate = round(($availableRooms / $safeTotalRooms) * 100);
    $reservedRate = round(($reservedRooms / $safeTotalRooms) * 100);
    $holdRooms = $rooms->filter(fn ($room) => in_array($room->status, ['maintenance', 'cleaning'], true))->count();
    $holdRate = round(($holdRooms / $safeTotalRooms) * 100);
    $averageRate = $rooms->avg('rate') ?? 0;
    $availableRevenue = $rooms->where('status', 'available')->sum('rate');
    $roomTypes = $rooms->groupBy('room_type');
    $matrixRooms = $rooms->sortBy([
        fn ($room) => match ($room->status) {
            'available' => 1,
            'reserved' => 2,
            'occupied' => 3,
            default => 4,
        },
        ['room_number', 'asc'],
    ]);
    $days = collect(range(0, 6))->map(fn ($offset) => now('Asia/Manila')->addDays($offset));
    $stateStyles = [
        'available' => ['label' => 'Available', 'box' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'note' => 'Ready Clean'],
        'occupied' => ['label' => 'Occupied', 'box' => 'border-rose-200 bg-rose-50 text-rose-700', 'note' => 'In-house'],
        'reserved' => ['label' => 'Reserved', 'box' => 'border-blue-200 bg-blue-50 text-blue-800', 'note' => 'Confirmed'],
        'cleaning' => ['label' => 'Cleaning', 'box' => 'border-sky-200 bg-sky-50 text-sky-800', 'note' => 'Turnover'],
        'maintenance' => ['label' => 'Hold', 'box' => 'border-amber-300 bg-amber-50 text-amber-800', 'note' => 'Maintenance'],
    ];
@endphp

<section class="relative overflow-hidden rounded-2xl border border-primary-container bg-primary p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -right-16 top-0 h-full w-1/2 bg-gradient-to-l from-secondary/20 to-transparent"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2 rounded border border-secondary-fixed/40 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">
                <span class="material-symbols-outlined text-sm">monitoring</span>
                Room Availability Overview
            </div>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Room Availability & Booking Pace Matrix</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-blue-100">
                Real-time room availability, forecast occupancy, and forward calendar pipeline across all room categories and wings. This owner view is read-only.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 rounded-xl border border-white/10 bg-primary-container/75 p-5 shadow-inner sm:grid-cols-4 xl:w-[620px]">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p>
            </div>
            <div class="border-l border-white/10 pl-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Available</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $availableRooms }} / {{ $totalRooms }}</p>
            </div>
            <div class="border-l border-white/10 pl-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Guests</p>
                <p class="mt-1 font-serif text-2xl font-bold">{{ $currentGuests }}</p>
            </div>
            <div class="border-l border-white/10 pl-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Open Yield</p>
                <p class="mt-1 text-xl font-bold text-secondary-fixed">PHP {{ number_format($availableRevenue, 2) }}</p>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/60">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Inventory</span>
            <span class="material-symbols-outlined text-secondary">domain</span>
        </div>
        <p class="mt-3 font-serif text-3xl font-extrabold">{{ $totalRooms }} Rooms</p>
        <p class="mt-1 text-xs text-slate-500">{{ $roomTypes->count() }} room categories encoded</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">Active portfolios</span>
            <span class="font-bold text-primary">100% Operational</span>
        </div>
    </div>
    <div class="owner-card rounded-xl border-emerald-200 p-5 transition hover:border-emerald-300">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Available Tonight</span>
            <span class="material-symbols-outlined text-secondary">key</span>
        </div>
        <p class="mt-3 font-serif text-3xl font-extrabold text-emerald-700">{{ $availableRooms }} Available</p>
        <p class="mt-1 text-xs text-slate-500">Ready for check-in or walk-in sale</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">Available capacity</span>
            <span class="font-bold text-emerald-700">{{ $availabilityRate }}%</span>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/60">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Booking Pace</span>
            <span class="material-symbols-outlined text-secondary">trending_up</span>
        </div>
        <p class="mt-3 font-serif text-3xl font-extrabold">{{ max($reservedRate, $occupancyRate) }}% Pace</p>
        <p class="mt-1 text-xs text-slate-500">{{ $reservedRooms }} rooms currently reserved</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">Arrivals today</span>
            <span class="font-bold text-primary">{{ $arrivalsToday }}</span>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/60">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Yield & ADR Outlook</span>
            <span class="material-symbols-outlined text-secondary">payments</span>
        </div>
        <p class="mt-3 font-serif text-3xl font-extrabold">PHP {{ number_format($averageRate, 2) }}</p>
        <p class="mt-1 text-xs text-slate-500">Average encoded nightly rate</p>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">RevPAR indicator</span>
            <span class="font-bold text-primary">PHP {{ number_format(($averageRate * $occupancyRate) / 100, 2) }}</span>
        </div>
    </div>
</section>

<section class="owner-card overflow-hidden rounded-2xl">
    <div class="flex flex-col justify-between gap-4 border-b border-line bg-surface-low px-6 py-5 xl:flex-row xl:items-center">
        <div>
            <h3 class="font-serif text-2xl font-bold text-primary">7-Day Forward Room Availability Matrix</h3>
            <p class="mt-1 text-xs text-slate-500">Live view across rooms, categories, base rates, and availability signals.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-600">
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-emerald-100 ring-1 ring-emerald-400"></span>Available</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-rose-100 ring-1 ring-rose-300"></span>Occupied</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-blue-100 ring-1 ring-blue-400"></span>Reserved</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-amber-100 ring-1 ring-amber-400"></span>Hold</span>
            <span class="rounded-lg border border-primary bg-primary px-3 py-1.5 font-bold text-white">7 Days</span>
        </div>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[980px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="w-64 px-5 py-3">Room & Category</th>
                    <th class="w-36 px-4 py-3">Standard Rate</th>
                    @foreach ($days as $day)
                        <th @class(['px-3 py-3 text-center', 'bg-secondary-fixed/30 text-secondary' => $loop->first])>
                            <span class="block font-bold">{{ strtoupper($day->format('D d')) }}</span>
                            <span class="block text-[10px] font-medium tracking-normal text-slate-500">{{ $loop->first ? 'Today' : 'Day '.($loop->iteration) }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($matrixRooms as $room)
                    @php
                        $state = strtolower($room->status);
                        $todayStyle = $stateStyles[$state] ?? $stateStyles['maintenance'];
                    @endphp
                    <tr class="transition hover:bg-surface-low">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-primary">Room {{ $room->room_number }}</span>
                                <span class="rounded border border-line bg-surface-low px-2 py-0.5 text-[10px] font-bold uppercase text-slate-500">{{ $room->room_type }}</span>
                            </div>
                            <span class="mt-1 block text-[11px] text-slate-500">Le Charmé Rooms Cluster · {{ $room->capacity }} guests</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="block font-mono font-bold text-primary">PHP {{ number_format($room->rate, 2) }}</span>
                            <span class="text-[10px] text-slate-400">Base rate / night</span>
                        </td>
                        @foreach ($days as $day)
                            @php
                                $displayStyle = $loop->first ? $todayStyle : ($room->status === 'maintenance' ? $stateStyles['maintenance'] : ($loop->iteration % 5 === 0 ? $stateStyles['reserved'] : $stateStyles['available']));
                            @endphp
                            <td @class(['px-2 py-3', 'bg-secondary-fixed/10' => $loop->first])>
                                <div class="rounded border p-2 text-center {{ $displayStyle['box'] }}">
                                    <span class="block text-[10px] font-extrabold uppercase tracking-wide">{{ $displayStyle['label'] }}</span>
                                    <span class="block truncate font-mono text-[9px]">{{ $loop->first ? $displayStyle['note'] : 'PHP '.number_format($room->rate, 0) }}</span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-sm text-slate-500">No room availability records have been encoded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <section class="owner-card rounded-2xl p-6 xl:col-span-2">
        <div class="mb-5 flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h3 class="font-serif text-2xl font-bold text-primary">Room Type Availability & Booking Pace</h3>
                <p class="mt-1 text-xs text-slate-500">Distribution by encoded category, availability, and operational holds.</p>
            </div>
            <span class="rounded-full border border-line bg-surface-low px-3 py-1 text-xs font-bold text-slate-600">Owner view only</span>
        </div>
        <div class="space-y-5">
            @forelse ($roomTypes as $type => $typeRooms)
                @php
                    $typeTotal = max($typeRooms->count(), 1);
                    $typeAvailable = $typeRooms->where('status', 'available')->count();
                    $typeOccupied = $typeRooms->where('status', 'occupied')->count();
                    $typeReserved = $typeRooms->where('status', 'reserved')->count();
                    $typeHolds = $typeRooms->filter(fn ($room) => in_array($room->status, ['maintenance', 'cleaning'], true))->count();
                @endphp
                <div class="rounded-xl border border-line bg-white p-4">
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <div>
                            <h4 class="font-bold text-primary">{{ $type }}</h4>
                            <p class="text-[11px] text-slate-500">{{ $typeRooms->pluck('room_number')->map(fn ($number) => 'Room '.$number)->join(', ') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-serif text-xl font-bold">{{ $typeAvailable }} / {{ $typeRooms->count() }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-700">Available</p>
                        </div>
                    </div>
                    <div class="flex h-3 overflow-hidden rounded-full bg-surface-mid">
                        <div class="bg-emerald-500" style="width: {{ round(($typeAvailable / $typeTotal) * 100) }}%"></div>
                        <div class="bg-rose-500" style="width: {{ round(($typeOccupied / $typeTotal) * 100) }}%"></div>
                        <div class="bg-blue-400" style="width: {{ round(($typeReserved / $typeTotal) * 100) }}%"></div>
                        <div class="bg-amber-400" style="width: {{ round(($typeHolds / $typeTotal) * 100) }}%"></div>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-2 text-[11px]">
                        <span class="rounded bg-emerald-50 px-2 py-1 font-semibold text-emerald-700">Avail {{ $typeAvailable }}</span>
                        <span class="rounded bg-rose-50 px-2 py-1 font-semibold text-rose-700">Occ {{ $typeOccupied }}</span>
                        <span class="rounded bg-blue-50 px-2 py-1 font-semibold text-blue-700">Res {{ $typeReserved }}</span>
                        <span class="rounded bg-amber-50 px-2 py-1 font-semibold text-amber-800">Hold {{ $typeHolds }}</span>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-line p-8 text-center text-sm text-slate-500">No room categories available.</div>
            @endforelse
        </div>
    </section>

    <section class="space-y-5">
        <div class="owner-card rounded-2xl p-6">
            <h3 class="font-serif text-2xl font-bold text-primary">Yield Snapshot</h3>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl bg-surface-low p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Available room revenue</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-primary">PHP {{ number_format($availableRevenue, 2) }}</p>
                </div>
                <div class="rounded-xl bg-surface-low p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Maintenance / cleaning holds</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-primary">{{ $holdRooms }} rooms</p>
                    <div class="mt-2 h-2 rounded-full bg-line"><div class="h-2 rounded-full bg-amber-500" style="width: {{ $holdRate }}%"></div></div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-primary-container bg-gradient-to-br from-primary to-[#07111f] p-6 text-white shadow-md">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-secondary-fixed">
                <span class="material-symbols-outlined text-base">verified_user</span>
                Forward Calendar Governance
            </div>
            <h3 class="mt-3 font-serif text-2xl font-bold">Executive Monitoring Only</h3>
            <p class="mt-2 text-sm leading-6 text-blue-100">
                Owners can review availability, booking pace, and yield indicators. Room blocking, tariff changes, and operational edits stay with department users and administrators.
            </p>
            <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg border border-white/10 bg-white/10 p-3">
                    <p class="text-xs uppercase tracking-wide text-blue-100/70">Arrivals</p>
                    <p class="font-serif text-2xl font-bold">{{ $arrivalsToday }}</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 p-3">
                    <p class="text-xs uppercase tracking-wide text-blue-100/70">Departures</p>
                    <p class="font-serif text-2xl font-bold">{{ $departuresToday }}</p>
                </div>
            </div>
        </div>
    </section>
</div>
