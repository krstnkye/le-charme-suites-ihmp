@php
    $safeTotalRooms = max($totalRooms, 1);
    $occupancyRate = round(($occupiedRooms / $safeTotalRooms) * 100);
    $availableRate = round(($availableRooms / $safeTotalRooms) * 100);
    $reservedRate = round(($reservedRooms / $safeTotalRooms) * 100);
    $maintenanceRate = round(($maintenanceRooms / $safeTotalRooms) * 100);
    $averageRate = $rooms->avg('rate') ?? 0;
    $highestCapacity = $rooms->max('capacity') ?? 0;
    $roomTypes = $rooms->groupBy('room_type');
    $roomPageCopy = [
        'rooms' => ['Room Management Overview', 'Room inventory, occupancy, availability, and maintenance readiness visibility across all Le Charmé Suites rooms.'],
        'room-inventory' => ['Room Inventory Overview', 'Detailed read-only room records with nightly rates, capacity, and operational owner record status.'],
        'room-availability' => ['Room Availability Overview', 'Executive availability monitoring across room types, capacity, and booking readiness segments.'],
        'room-status' => ['Room Status Overview', 'Read-only occupancy, reserved, cleaning, and maintenance status monitoring for owner review.'],
    ];
    [$roomHeroTitle, $roomHeroSubtitle] = $roomPageCopy[$activePage] ?? $roomPageCopy['rooms'];
    $statusBadge = [
        'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'occupied' => 'bg-rose-50 text-rose-700 border-rose-200',
        'reserved' => 'bg-amber-50 text-amber-800 border-amber-300',
        'cleaning' => 'bg-sky-50 text-sky-700 border-sky-200',
        'maintenance' => 'bg-slate-100 text-slate-700 border-slate-300',
    ];
    $statusDot = [
        'available' => 'bg-emerald-500',
        'occupied' => 'bg-rose-500',
        'reserved' => 'bg-amber-500',
        'cleaning' => 'bg-sky-500',
        'maintenance' => 'bg-slate-400',
    ];
@endphp

<section class="relative overflow-hidden rounded-2xl border border-primary-container bg-primary p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute right-0 top-0 flex h-full w-1/3 items-center justify-center opacity-[0.06]">
        <span class="material-symbols-outlined text-[220px]">layers</span>
    </div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-3xl space-y-3">
            <div class="inline-flex items-center gap-2 rounded border border-secondary/40 bg-secondary/20 px-2.5 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-fixed">
                <span class="h-1.5 w-1.5 rounded-full bg-secondary-fixed"></span>
                Executive Monitoring
            </div>
            <h2 class="font-serif text-4xl font-extrabold leading-tight">{{ $roomHeroTitle }}</h2>
            <p class="text-sm leading-6 text-slate-300">
                {{ $roomHeroSubtitle }} Owner access remains read-only.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-5 rounded-xl border border-white/10 bg-primary-container/70 p-5 shadow-inner sm:grid-cols-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Occupancy</p>
                <p class="mt-1 text-2xl font-extrabold">{{ $occupancyRate }}<span class="text-lg text-secondary-fixed">%</span></p>
            </div>
            <div class="border-l border-white/10 pl-5">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rooms</p>
                <p class="mt-1 text-2xl font-extrabold">{{ $publishedRoomCount }} <span class="text-xs font-normal text-slate-400">Property Total</span></p>
                <p class="mt-1 text-[11px] text-slate-400">{{ $totalRooms }} operational records added</p>
            </div>
            <div class="border-l border-white/10 pl-5">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Guests</p>
                <p class="mt-1 text-2xl font-extrabold">{{ $currentGuests }}</p>
            </div>
            <div class="border-l border-white/10 pl-5">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Revenue</p>
                <p class="mt-1 text-xl font-bold text-secondary-fixed">PHP {{ number_format($recordedRevenue, 2) }}</p>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/60">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Guest Management</p>
                <h3 class="mt-2 font-serif text-3xl font-extrabold text-primary">{{ $totalGuests }}</h3>
                <p class="mt-1 text-xs text-slate-500">Registered guest profiles</p>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary/20 bg-secondary-fixed/20 text-secondary"><span class="material-symbols-outlined">person</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">Directory status</span>
            <span class="font-semibold text-emerald-600">Live synced</span>
        </div>
    </div>
    <div class="owner-card rounded-xl border-secondary/40 p-5 ring-1 ring-secondary/20">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">Room Management</p>
                <h3 class="mt-2 font-serif text-3xl font-extrabold text-primary">{{ $availableRooms }}</h3>
                <p class="mt-1 text-xs font-medium text-slate-600">Available of {{ $totalRooms }} rooms</p>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary/30 bg-secondary-fixed/40 text-secondary"><span class="material-symbols-outlined">home</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">Ready for booking</span>
            <span class="font-bold text-primary">{{ $availableRate }}% capacity</span>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/60">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Reservations</p>
                <h3 class="mt-2 font-serif text-3xl font-extrabold text-primary">{{ $activeReservations }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ $arrivalsToday }} arrivals today</p>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary/20 bg-secondary-fixed/20 text-secondary"><span class="material-symbols-outlined">calendar_month</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">Scheduled departures</span>
            <span class="font-medium text-slate-700">{{ $departuresToday }} departures</span>
        </div>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/60">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Billing & Transactions</p>
                <h3 class="mt-2.5 font-serif text-2xl font-extrabold text-primary">PHP {{ number_format($collectedToday, 2) }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ $settledToday }} paid today</p>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary/20 bg-secondary-fixed/20 text-secondary"><span class="material-symbols-outlined">payments</span></span>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-[11px]">
            <span class="text-slate-400">Payment summary</span>
            <span class="font-semibold text-emerald-700">Balanced & cleared</span>
        </div>
    </div>
</section>

<section id="room-inventory" class="owner-card overflow-hidden rounded-2xl">
    <div class="flex flex-col justify-between gap-4 border-b border-line bg-surface-low px-6 py-5 md:flex-row md:items-center">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-serif text-2xl font-bold text-primary">Room Inventory Preview</h3>
                <span class="rounded-full border border-emerald-200 bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-800">Live Synced</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Live room status monitoring without update controls · Subic Bay Central inventory cluster</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-lg border border-line bg-surface-mid p-1 text-xs">
                <span class="rounded bg-white px-3 py-1 font-bold text-primary shadow-sm">All ({{ $totalRooms }})</span>
                <span class="px-3 py-1 font-medium text-slate-600">Available ({{ $availableRooms }})</span>
                <span class="px-3 py-1 font-medium text-slate-600">Occupied ({{ $occupiedRooms }})</span>
                <span class="px-3 py-1 font-medium text-slate-600">Reserved ({{ $reservedRooms }})</span>
                <span class="px-3 py-1 font-medium text-slate-600">Maintenance ({{ $maintenanceRooms }})</span>
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-2.5 top-2 text-sm text-slate-400">search</span>
                <input class="w-52 rounded-lg border border-line bg-white py-1.5 pl-8 pr-3 text-xs placeholder-slate-400 focus:border-secondary focus:ring-secondary/30" placeholder="Search room # or wing..." type="text" readonly>
            </div>
        </div>
    </div>
    <div class="owner-scroll overflow-x-auto">
        <table class="w-full min-w-[880px] border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <th class="px-6 py-3.5">Room</th>
                    <th class="px-6 py-3.5">Type & Wing</th>
                    <th class="px-6 py-3.5">Capacity</th>
                    <th class="px-6 py-3.5">Rate</th>
                    <th class="px-6 py-3.5 text-center">Status</th>
                    <th class="px-6 py-3.5 text-right">Owner Records</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($rooms as $room)
                    @php
                        $status = strtolower($room->status);
                        $statusLabel = str_replace('_', ' ', $room->status);
                    @endphp
                    <tr @class(['transition hover:bg-surface-low', 'bg-secondary-fixed/20' => $status === 'reserved'])>
                        <td class="flex items-center gap-2 px-6 py-3.5 font-semibold text-primary">
                            <span class="h-2 w-2 rounded-full {{ $statusDot[$status] ?? 'bg-slate-400' }}"></span>
                            <span>Room {{ $room->room_number }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-600">
                            <span class="block font-semibold text-primary">{{ $room->room_type }}</span>
                            <span class="block text-xs text-slate-400">Le Charmé Room System</span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-600">
                            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-slate-400">person</span>{{ $room->capacity }} guests</span>
                        </td>
                        <td class="px-6 py-3.5 font-mono font-semibold text-primary">PHP {{ number_format($room->rate, 2) }}</td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="inline-flex rounded-md border px-3 py-1 text-xs font-semibold capitalize {{ $statusBadge[$status] ?? 'bg-surface-mid text-slate-700 border-line' }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400">
                                {{ $status === 'available' ? 'Housekeeping Passed' : 'View-only status' }}
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No rooms have been added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col items-center justify-between gap-3 border-t border-line bg-surface-low px-6 py-4 text-xs text-slate-500 sm:flex-row">
        <span>Showing <strong class="text-primary">{{ $rooms->count() }}</strong> of <strong class="text-primary">{{ $totalRooms }}</strong> verified rooms</span>
        <span class="rounded border border-secondary bg-secondary-fixed/30 px-3 py-1.5 font-bold text-secondary">Page 1</span>
    </div>
</section>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <section id="room-availability" class="owner-card rounded-xl p-5 lg:col-span-2">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h4 class="font-serif text-xl font-bold text-primary">Wing Distribution & Architectural Breakdown</h4>
                <p class="text-xs text-slate-500">Inventory allocation by room type using live added room types.</p>
            </div>
            <span class="rounded border border-secondary/20 bg-secondary-fixed/30 px-2 py-1 text-xs font-semibold text-secondary">Property Total: {{ $publishedRoomCount }} Rooms · {{ $totalRooms }} Added</span>
        </div>
        <div class="space-y-4 text-xs">
            @forelse ($roomTypes as $type => $typeRooms)
                @php
                    $typeTotal = max($typeRooms->count(), 1);
                    $typeOccupied = $typeRooms->where('status', 'occupied')->count();
                    $typeAvailable = $typeRooms->where('status', 'available')->count();
                    $typeReserved = $typeRooms->where('status', 'reserved')->count();
                    $typeMaintenance = $typeRooms->filter(fn ($room) => in_array($room->status, ['maintenance', 'cleaning'], true))->count();
                @endphp
                <div>
                    <div class="mb-1 flex justify-between font-medium text-primary">
                        <span>{{ $type }}</span>
                        <span class="font-bold">{{ $typeRooms->count() }} Rooms · {{ round(($typeAvailable / $typeTotal) * 100) }}% Available</span>
                    </div>
                    <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-surface-mid">
                        <div class="bg-rose-500" style="width: {{ round(($typeOccupied / $typeTotal) * 100) }}%" title="Occupied"></div>
                        <div class="bg-emerald-500" style="width: {{ round(($typeAvailable / $typeTotal) * 100) }}%" title="Available"></div>
                        <div class="bg-amber-400" style="width: {{ round(($typeReserved / $typeTotal) * 100) }}%" title="Reserved"></div>
                        <div class="bg-slate-400" style="width: {{ round(($typeMaintenance / $typeTotal) * 100) }}%" title="Maintenance"></div>
                    </div>
                    <div class="mt-1 flex justify-between text-[10px] text-slate-400">
                        <span>{{ $typeRooms->pluck('room_number')->map(fn ($number) => 'Room '.$number)->join(', ') }}</span>
                        <span>Avg Rate: PHP {{ number_format($typeRooms->avg('rate') ?? 0, 2) }}</span>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-line bg-white p-6 text-center text-sm text-slate-500">No room type distribution available.</div>
            @endforelse
        </div>
    </section>

    <section id="room-status" class="rounded-xl border border-primary-container bg-gradient-to-br from-primary to-[#07111f] p-5 text-white shadow-md">
        <div>
            <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-secondary-fixed">
                <span class="material-symbols-outlined text-base">verified_user</span>
                Owner Access Rules
            </div>
            <h4 class="mb-2 font-serif text-xl font-bold">Executive Sandboxing Notice</h4>
            <p class="text-xs leading-6 text-slate-300">
                Owners can view this page, but room status changes, housekeeping assignments, and room rate changes must be handled by authorized staff.
            </p>
            <div class="mt-4 rounded-lg border border-secondary/20 bg-primary-container/80 p-3 text-[11px] text-secondary-fixed/90">
                Front desk and housekeeping system update remains operational through the Subic Central IHMP cluster.
            </div>
        </div>
        <div class="mt-6 flex items-center justify-between border-t border-white/10 pt-4 text-[11px] text-slate-400">
            <span>Average Rate: <strong class="text-white">PHP {{ number_format($averageRate, 2) }}</strong></span>
            <span class="font-medium text-emerald-400">Highest Capacity: {{ $highestCapacity }} guests</span>
        </div>
    </section>
</div>
