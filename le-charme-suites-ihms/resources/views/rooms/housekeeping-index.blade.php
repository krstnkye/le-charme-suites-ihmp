@php
    $user = auth()->user();
    $statusLabels = [
        'available' => 'Available',
        'reserved' => 'Reserved',
        'occupied' => 'Occupied',
        'cleaning' => 'Cleaning',
        'maintenance' => 'Maintenance',
    ];
    $statusStyles = [
        'available' => 'border-emerald-300 bg-emerald-50 text-emerald-800',
        'reserved' => 'border-amber-300 bg-amber-50 text-amber-800',
        'occupied' => 'border-sky-300 bg-sky-50 text-sky-800',
        'cleaning' => 'border-orange-300 bg-orange-50 text-orange-800',
        'maintenance' => 'border-red-300 bg-red-50 text-red-800',
    ];
    $statusIcons = [
        'available' => 'check_circle',
        'reserved' => 'event_available',
        'occupied' => 'lock',
        'cleaning' => 'mop',
        'maintenance' => 'construction',
    ];
    $currentRooms = collect($rooms->items());
    $priorityRoom = $currentRooms->firstWhere('status', 'maintenance')
        ?? $currentRooms->firstWhere('status', 'cleaning')
        ?? $currentRooms->first()
        ?? $availableRecommendations->first();
    $readyRate = $totalRooms > 0 ? round(($availableRooms / $totalRooms) * 100) : 0;
    $turnoverCount = ($statusCounts['cleaning'] ?? 0) + ($statusCounts['maintenance'] ?? 0);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Status & Live Floor Matrix - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        secondary: '#755a28',
                        cream: '#fcf9f3',
                        line: '#e5e2dc',
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        serif: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined{display:inline-block;vertical-align:middle;line-height:1;font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
        .custom-scrollbar::-webkit-scrollbar{width:6px;height:6px}
        .custom-scrollbar::-webkit-scrollbar-track{background:#f0eee8}
        .custom-scrollbar::-webkit-scrollbar-thumb{background:#c4c6cd;border-radius:999px}
    </style>
    @include('partials.favicon')
</head>
<body class="h-screen overflow-hidden bg-cream font-sans text-primary antialiased">
    @include('partials.housekeeping-sidebar', ['active' => 'room-status'])

    <div class="flex h-screen flex-col lg:pl-72">
        <header class="z-30 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-line bg-white px-5 shadow-sm">
            <form method="GET" action="{{ route('rooms.index') }}" class="relative hidden w-full max-w-sm md:block">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-lg text-slate-400">search</span>
                <input name="search" value="{{ $search }}" class="w-full rounded-lg border-line bg-[#f6f3ed] py-2 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Quick search rooms, tasks, guests...">
                @if ($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
                @if ($type !== '')<input type="hidden" name="type" value="{{ $type }}">@endif
            </form>

            <div class="hidden items-center gap-1 rounded-lg border border-line bg-[#f6f3ed] p-1 xl:flex">
                @foreach (['Main Wing' => $totalRooms, 'Ready' => $availableRooms, 'Turnover' => $turnoverCount] as $label => $count)
                    <span class="{{ $loop->first ? 'bg-white text-primary shadow-sm' : 'text-slate-500' }} rounded-md px-3 py-1 text-xs font-bold">
                        {{ $label }} <span class="ml-1 text-secondary">{{ $count }}</span>
                    </span>
                @endforeach
            </div>

            <div class="ml-auto flex items-center gap-3">
                <a href="{{ route('housekeeping.page', 'report-issue') }}" class="hidden items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 sm:inline-flex">
                    <span class="material-symbols-outlined text-base">report_problem</span>
                    Report Issue
                </a>
                <a href="{{ route('housekeeping.page', 'tasks') }}" class="hidden items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white hover:bg-[#172b42] sm:inline-flex">
                    <span class="material-symbols-outlined text-base">add_task</span>
                    New Task
                </a>
                <div class="hidden border-l border-line pl-3 text-right sm:block">
                    <p class="text-[11px] font-bold uppercase tracking-wider">Role: Housekeeping</p>
                    <p id="room-clock" class="text-[11px] text-slate-500" data-timezone="{{ config('app.timezone') }}">{{ now(config('app.timezone'))->format('M d, Y - h:i:s A') }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-[#e6c185] bg-[#ffdeaa] text-sm font-bold text-secondary">
                    {{ strtoupper(substr($user?->name ?? 'H', 0, 1)) }}
                </a>
            </div>
        </header>

        <div class="flex min-h-0 flex-1 overflow-hidden">
            <main class="custom-scrollbar min-w-0 flex-1 overflow-y-auto p-5 lg:p-7">
                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
                @endif
                <div class="mb-5 flex flex-col justify-between gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 sm:flex-row sm:items-center">
                    <span><strong>{{ $totalRooms }} of {{ $publishedRoomCount }}</strong> published hotel rooms are encoded in this shared inventory.</span>
                    <span class="text-xs font-bold uppercase tracking-wider">{{ $officialRoomTypeCount }} official room types</span>
                </div>

                <section class="mb-6 flex flex-col justify-between gap-4 border-b border-line pb-5 xl:flex-row xl:items-end">
                    <div>
                        <div class="mb-2 flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary">
                            <span>Housekeeping Command</span>
                            <span>•</span>
                            <span>Live Room Status</span>
                        </div>
                        <h2 class="font-serif text-3xl font-bold leading-tight text-primary lg:text-4xl">Room Status & Live Floor Matrix</h2>
                        <p class="mt-2 max-w-3xl text-sm text-slate-600">Room Management for housekeeping personnel with readiness, turnover, inspection, and maintenance visibility.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 rounded-lg border border-line bg-white p-2 shadow-sm">
                        <span class="px-1 text-[11px] font-bold uppercase text-slate-500">Legend:</span>
                        @foreach (['available' => 'Available', 'reserved' => 'Reserved', 'occupied' => 'Occupied', 'maintenance' => 'Maintenance'] as $value => $label)
                            <a href="{{ route('rooms.index', ['status' => $value]) }}" class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusStyles[$value] }}">
                                <span class="h-2 w-2 rounded-full {{ $value === 'available' ? 'bg-emerald-600' : ($value === 'reserved' ? 'bg-amber-600' : ($value === 'occupied' ? 'bg-primary' : 'bg-red-600')) }}"></span>
                                {{ strtoupper($label) }}
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="mb-6 rounded-lg border border-line bg-white shadow-sm">
                    <div class="border-t-4 border-secondary p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="flex items-center gap-2 text-lg font-bold">
                                <span class="material-symbols-outlined text-secondary">tune</span>
                                Availability Range Search
                            </h3>
                            <span class="rounded border border-secondary/30 px-2 py-0.5 text-[11px] font-bold text-secondary">Room Filter</span>
                        </div>
                        <form method="GET" action="{{ route('rooms.index') }}" class="grid grid-cols-1 items-end gap-4 xl:grid-cols-12">
                            <div class="xl:col-span-3">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Check-In Date</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-lg text-primary">calendar_today</span>
                                    <input class="w-full rounded-lg border-line bg-cream py-2 pl-10 pr-3 text-sm" value="{{ now(config('app.timezone'))->format('M d, Y') }}" readonly>
                                </div>
                            </div>
                            <div class="xl:col-span-3">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Check-Out Date</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-lg text-primary">event</span>
                                    <input class="w-full rounded-lg border-line bg-cream py-2 pl-10 pr-3 text-sm" value="{{ now(config('app.timezone'))->addDays(3)->format('M d, Y') }}" readonly>
                                </div>
                            </div>
                            <div class="xl:col-span-3">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Room Category / Type</label>
                                <select name="type" class="w-full rounded-lg border-line bg-cream py-2 text-sm focus:border-secondary focus:ring-secondary">
                                    <option value="">All Types</option>
                                    @foreach ($roomTypes as $roomType)
                                        <option value="{{ $roomType }}" @selected($type === $roomType)>{{ $roomType }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="xl:col-span-2">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Status</label>
                                <select name="status" class="w-full rounded-lg border-line bg-cream py-2 text-sm focus:border-secondary focus:ring-secondary">
                                    <option value="">All Statuses</option>
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-[#172b42] xl:col-span-1">
                                <span class="material-symbols-outlined text-lg">manage_search</span>
                                Check
                            </button>
                        </form>
                    </div>
                </section>

                <section class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
                    @foreach ([
                        ['Total Rooms', $totalRooms, 'domain', 'text-primary'],
                        ['Available', $availableRooms, 'check_circle', 'text-emerald-700'],
                        ['Reserved', $reservedRooms, 'event_available', 'text-secondary'],
                        ['Occupied', $occupiedRooms, 'king_bed', 'text-primary'],
                        ['Maintenance', $maintenanceRooms, 'construction', 'text-red-700'],
                    ] as [$label, $value, $icon, $color])
                        <article class="rounded-lg border border-line bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $label }}</span>
                                <span class="material-symbols-outlined {{ $color }} text-lg">{{ $icon }}</span>
                            </div>
                            <p class="mt-3 font-serif text-3xl font-bold {{ $color }}">{{ $value }}</p>
                        </article>
                    @endforeach
                </section>

                <section class="mb-7 space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="flex items-center gap-2 text-lg font-bold">
                                <span class="material-symbols-outlined text-secondary">grid_view</span>
                                Live Availability Selection Matrix
                            </h3>
                            <p class="text-xs text-slate-500">Showing operational room cards from the housekeeping room status page.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">{{ $rooms->total() }} room records</span>
                    </div>

                    @forelse ($roomMatrix as $floor => $floorRooms)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-line pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-sm bg-secondary"></span>
                                    <h4 class="font-bold">Floor {{ $floor }} • Housekeeping Matrix</h4>
                                    <span class="text-xs text-slate-500">• {{ $floorRooms->count() }} total rooms</span>
                                </div>
                                <span class="text-xs font-bold text-secondary">{{ $floorRooms->where('status', 'available')->count() }} Ready • {{ $floorRooms->where('status', 'cleaning')->count() }} Cleaning</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 2xl:grid-cols-3">
                                @foreach ($floorRooms as $room)
                                    <article class="overflow-hidden rounded-lg border bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $room->status === 'maintenance' ? 'border-red-300' : 'border-line' }}">
                                        @if ($room->imagePath())
                                            <img src="{{ asset($room->imagePath()) }}" alt="{{ $room->room_type }} room reference" class="aspect-[3/1] w-full object-cover" loading="lazy">
                                        @endif
                                        <div class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-lg font-extrabold">Room {{ $room->room_number }}</span>
                                                    <span class="material-symbols-outlined text-base text-secondary">{{ $statusIcons[$room->status] ?? 'meeting_room' }}</span>
                                                </div>
                                                <p class="text-sm text-slate-500">{{ $room->room_type }}</p>
                                            </div>
                                            <span class="rounded-full border px-2 py-0.5 text-[11px] font-bold {{ $statusStyles[$room->status] ?? 'border-line bg-slate-50 text-slate-700' }}">
                                                {{ $statusLabels[$room->status] ?? ucfirst($room->status) }}
                                            </span>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                            <span>{{ $room->description ?: 'Housekeeping status ready for review.' }}</span>
                                            <span class="font-mono">P{{ number_format($room->rate, 0) }}</span>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between border-t border-line pt-3">
                                            <span class="text-[11px] text-slate-500">Capacity {{ $room->capacity }} pax</span>
                                            <div class="flex gap-2">
                                                <a href="{{ route('rooms.show', $room) }}" class="rounded bg-primary px-2.5 py-1 text-xs font-bold text-white hover:bg-[#172b42]">View</a>
                                                <a href="{{ route('rooms.edit', $room) }}" class="rounded border border-secondary/40 px-2.5 py-1 text-xs font-bold text-secondary hover:bg-[#fff8ec]">Update</a>
                                            </div>
                                        </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-line bg-white p-8 text-center text-sm text-slate-500">No room matrix available.</div>
                    @endforelse
                </section>

                <section class="rounded-lg border border-line bg-white shadow-sm">
                    <div class="flex flex-col justify-between gap-3 border-b border-line p-5 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="flex items-center gap-2 text-lg font-bold">
                                <span class="material-symbols-outlined text-secondary">table_chart</span>
                                Master Room Inventory & Status Roster
                            </h3>
                            <p class="text-sm text-slate-500">Complete housekeeping room inventory breakdown.</p>
                        </div>
                        <button class="inline-flex items-center gap-2 rounded-lg border border-secondary px-3 py-2 text-sm font-bold text-secondary hover:bg-[#fff8ec]" onclick="document.getElementById('statusModal').classList.remove('hidden')">
                            <span class="material-symbols-outlined text-lg">sync_alt</span>
                            Update Room Status
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[860px] text-left text-sm">
                            <thead class="border-b border-line bg-[#f6f3ed] text-xs font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">Room</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Floor</th>
                                    <th class="px-4 py-3">Capacity</th>
                                    <th class="px-4 py-3">Rate</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($rooms as $room)
                                    <tr class="hover:bg-[#f6f3ed]">
                                        <td class="px-4 py-3 font-bold">Room {{ $room->room_number }}</td>
                                        <td class="px-4 py-3">{{ $room->room_type }}</td>
                                        <td class="px-4 py-3">Floor {{ substr($room->room_number, 0, 1) }}</td>
                                        <td class="px-4 py-3">{{ $room->capacity }} pax</td>
                                        <td class="px-4 py-3 font-bold">PHP {{ number_format($room->rate, 2) }}</td>
                                        <td class="px-4 py-3"><span class="rounded-full border px-2 py-0.5 text-xs font-bold {{ $statusStyles[$room->status] ?? 'border-line bg-slate-50 text-slate-700' }}">{{ strtoupper($statusLabels[$room->status] ?? $room->status) }}</span></td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('rooms.show', $room) }}" class="rounded border border-line bg-white px-2.5 py-1 text-xs font-bold text-primary hover:bg-[#f6f3ed]">View Room</a>
                                            <a href="{{ route('rooms.edit', $room) }}" class="rounded border border-secondary/40 bg-white px-2.5 py-1 text-xs font-bold text-secondary hover:bg-[#fff8ec]">Edit Status</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="px-4 py-8 text-center text-slate-500" colspan="7">No rooms found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="flex flex-col gap-3 border-t border-line bg-[#f6f3ed] p-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                        <span>Ready rate: <strong class="text-emerald-700">{{ $readyRate }}%</strong></span>
                        <div>{{ $rooms->links() }}</div>
                    </div>
                </section>
            </main>

            <aside class="custom-scrollbar hidden w-96 shrink-0 overflow-y-auto border-l border-line bg-white shadow-lg xl:block">
                <div class="border-b border-line bg-[#f6f3ed] p-4">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-red-700">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                            Priority Telemetry
                        </span>
                        <a href="{{ route('housekeeping.page', 'notifications') }}" class="rounded p-1 text-slate-500 hover:bg-white hover:text-primary" title="Notifications">
                            <span class="material-symbols-outlined text-lg">notifications</span>
                        </a>
                    </div>
                </div>

                <div class="space-y-5 p-5">
                    @if ($priorityRoom)
                        <section class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="mb-2 inline-flex items-center gap-1 rounded bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
                                        <span class="material-symbols-outlined text-xs">bolt</span>
                                        Priority Room
                                    </span>
                                    <h3 class="font-serif text-2xl font-bold">Room {{ $priorityRoom->room_number }}</h3>
                                    <p class="text-sm text-slate-500">{{ $priorityRoom->room_type }} • Floor {{ substr($priorityRoom->room_number, 0, 1) }}</p>
                                </div>
                                <span class="rounded-full border px-2 py-0.5 text-[11px] font-bold {{ $statusStyles[$priorityRoom->status] ?? 'border-line bg-slate-50 text-slate-700' }}">{{ ucfirst($priorityRoom->status) }}</span>
                            </div>
                            <div class="mt-4 rounded-lg border border-line bg-white p-3 text-sm">
                                <div class="flex justify-between"><span class="text-slate-500">Readiness target</span><strong>{{ $priorityRoom->status === 'maintenance' ? 'Engineering Hold' : 'Release Review' }}</strong></div>
                                <div class="mt-2 flex justify-between"><span class="text-slate-500">Rate</span><strong>PHP {{ number_format($priorityRoom->rate, 2) }}</strong></div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('rooms.edit', $priorityRoom) }}" class="flex-1 rounded-lg bg-secondary px-3 py-2 text-center text-sm font-bold text-white hover:bg-[#5b4313]">Update Status</a>
                                <a href="{{ route('rooms.show', $priorityRoom) }}" class="rounded-lg border border-line px-3 py-2 text-sm font-bold hover:bg-[#f6f3ed]">View</a>
                            </div>
                        </section>
                    @endif

                    <section class="rounded-lg border border-line p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-wider">Cleaning Progression</h4>
                            <span class="text-sm font-bold text-secondary">{{ $readyRate }}% Ready</span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-line">
                            <div class="h-full rounded-full bg-secondary" style="width: {{ $readyRate }}%"></div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between"><span>Available rooms</span><strong>{{ $availableRooms }}</strong></div>
                            <div class="flex justify-between"><span>Cleaning queue</span><strong>{{ $statusCounts['cleaning'] ?? 0 }}</strong></div>
                            <div class="flex justify-between"><span>Maintenance queue</span><strong>{{ $statusCounts['maintenance'] ?? 0 }}</strong></div>
                        </div>
                    </section>

                    <section class="rounded-lg border border-line p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider">Quick Housekeeping Actions</h4>
                        <div class="mt-3 grid gap-2">
                            <a href="{{ route('housekeeping.page', 'room-inspection') }}" class="rounded-lg border border-line px-3 py-2 text-sm font-bold hover:bg-[#f6f3ed]">Open Inspection Queue</a>
                            <a href="{{ route('housekeeping.page', 'maintenance-requests') }}" class="rounded-lg border border-line px-3 py-2 text-sm font-bold hover:bg-[#f6f3ed]">Review Maintenance</a>
                            <a href="{{ route('housekeeping.page', 'linen-supplies') }}" class="rounded-lg border border-line px-3 py-2 text-sm font-bold hover:bg-[#f6f3ed]">Linen & Supplies</a>
                        </div>
                    </section>
                </div>
            </aside>
        </div>

        <footer class="hidden h-8 shrink-0 items-center justify-between border-t border-line bg-[#ebe8e2] px-5 text-[11px] text-slate-500 lg:flex">
            <span><strong class="text-primary">Le Charme Suites Subic</strong> • IHMP Housekeeping Module</span>
            <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>PMS Database Connected</span>
        </footer>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 p-4 backdrop-blur-sm" id="statusModal">
        <div class="w-full max-w-lg overflow-hidden rounded-lg border border-secondary/30 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-primary px-6 py-4 text-white">
                <div>
                    <h4 class="text-lg font-bold">Update Room Status</h4>
                    <p class="text-xs text-slate-300">Choose a room from the roster and use Edit Status to save audited updates.</p>
                </div>
                <button class="rounded-lg p-1 text-slate-300 hover:bg-white/10 hover:text-white" onclick="document.getElementById('statusModal').classList.add('hidden')">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <div class="space-y-4 p-6">
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($statusLabels as $value => $label)
                        <a href="{{ route('rooms.index', ['status' => $value]) }}" class="rounded-lg border p-3 {{ $statusStyles[$value] ?? 'border-line bg-slate-50 text-slate-700' }}">
                            <p class="text-xs font-bold uppercase">{{ $label }}</p>
                            <p class="mt-1 text-sm">{{ $statusCounts[$value] ?? 0 }} room(s)</p>
                        </a>
                    @endforeach
                </div>
                <button class="w-full rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white hover:bg-[#172b42]" onclick="document.getElementById('statusModal').classList.add('hidden')">Close</button>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const clock = document.getElementById('room-clock');
            if (!clock) return;
            const formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: clock.dataset.timezone || 'Asia/Manila',
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });
            const refreshClock = () => {
                clock.textContent = formatter.format(new Date()).replace(',', '').replace(',', ' -').toUpperCase();
            };
            refreshClock();
            setInterval(refreshClock, 1000);
        });
    </script>
</body>
</html>
