@php
    $user = auth()->user();
    $role = $user?->role ?? 'staff';
    $canManageRooms = in_array($role, ['admin', 'housekeeping'], true);
    $statusLabels = [
        'available' => 'Available',
        'reserved' => 'Reserved',
        'occupied' => 'Occupied',
        'cleaning' => 'Cleaning',
        'maintenance' => 'Maintenance',
    ];
    $statusBadgeClasses = [
        'available' => 'bg-emerald-50 border-emerald-300 text-emerald-800',
        'reserved' => 'bg-secondary-container/40 border-secondary/40 text-secondary',
        'occupied' => 'bg-primary/10 border-primary/20 text-primary',
        'cleaning' => 'bg-error-container/40 border-error/30 text-error',
        'maintenance' => 'bg-error-container/40 border-error/30 text-error',
    ];
    $statusTileClasses = [
        'available' => 'bg-emerald-100 text-emerald-900 border border-emerald-300',
        'reserved' => 'bg-secondary-container text-secondary',
        'occupied' => 'bg-primary text-white',
        'cleaning' => 'bg-error-container text-error',
        'maintenance' => 'bg-error-container text-error',
    ];
    $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
    $availableRate = $totalRooms > 0 ? round(($availableRooms / $totalRooms) * 100, 1) : 0;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Status - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        'on-primary-container': '#7f93ae',
                        secondary: '#755a28',
                        'secondary-container': '#fdd79a',
                        'secondary-fixed': '#ffdeaa',
                        surface: '#fcf9f3',
                        background: '#fcf9f3',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f6f3ed',
                        'surface-container': '#f0eee8',
                        'surface-container-high': '#ebe8e2',
                        'surface-variant': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        'on-surface': '#1c1c18',
                        'on-surface-variant': '#44474d',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6',
                    },
                    fontFamily: {
                        body: ['Manrope', 'sans-serif'],
                        headline: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-body text-on-surface antialiased">
    @if ($role === 'housekeeping')
        @include('partials.housekeeping-sidebar', ['active' => 'room-status'])
    @else
        @include('partials.front-desk-sidebar', ['active' => 'room-status'])
    @endif

    <div class="{{ $role === 'housekeeping' ? 'lg:pl-72' : 'lg:ml-64' }}">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-variant bg-surface px-4 shadow-sm lg:px-7">
            <form method="GET" action="{{ route('rooms.index') }}" class="relative hidden w-80 md:block">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-xl text-primary-container">search</span>
                <input name="search" value="{{ $search }}" class="w-full rounded-lg border-outline-variant bg-white py-2 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Search room #, type, or wing...">
                @if ($status !== '')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                @if ($type !== '')
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
            </form>

            <div class="ml-auto flex items-center gap-5">
                <div class="hidden text-right sm:block">
                    <div class="text-xs font-bold uppercase tracking-wider text-primary">Role: {{ ucwords(str_replace('_', ' ', $role)) }}</div>
                    <div id="room-clock" class="text-xs text-outline" data-timezone="{{ config('app.timezone') }}">{{ now(config('app.timezone'))->format('M d, Y - h:i:s A') }}</div>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full border border-secondary/40 bg-secondary-container font-bold text-secondary">
                    {{ strtoupper(substr($user?->name ?? 'S', 0, 1)) }}
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 p-4 lg:p-7">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif
            <div class="flex flex-col justify-between gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 sm:flex-row sm:items-center">
                <span><strong>{{ $totalRooms }} of {{ $publishedRoomCount }}</strong> published hotel rooms are encoded in this shared inventory.</span>
                <span class="text-xs font-bold uppercase tracking-wider">{{ $officialRoomTypeCount }} official room types</span>
            </div>

            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <div class="mb-1 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-secondary">
                        <span>IPT101 Integrated Hospitality Courseware</span>
                        <span>•</span>
                        <span>Room Records Updated</span>
                    </div>
                    <h2 class="font-headline text-3xl font-semibold tracking-tight text-primary">Room Management & Availability Grid</h2>
                    <p class="text-sm text-on-surface-variant">Real-time room occupancy states, quick room assignments, and inventory booking matrix.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2 rounded-xl border border-surface-variant bg-white p-2.5 shadow-sm">
                    <span class="px-1.5 text-[11px] font-bold uppercase text-outline">Legend:</span>
                    @foreach ([
                        ['Available', 'bg-emerald-600', 'text-emerald-800 bg-emerald-50 border-emerald-200'],
                        ['Reserved', 'bg-secondary', 'text-secondary bg-secondary-container/30 border-secondary/30'],
                        ['Occupied', 'bg-primary', 'text-primary bg-primary/10 border-primary/20'],
                        ['Maintenance', 'bg-error', 'text-error bg-error-container/40 border-error/30'],
                    ] as [$label, $dot, $classes])
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $classes }}">
                            <span class="h-2 w-2 rounded-full {{ $dot }}"></span>
                            {{ strtoupper($label) }}
                        </span>
                    @endforeach
                </div>
            </div>

            <section class="relative overflow-hidden rounded-xl border border-surface-variant bg-white p-5 shadow-sm">
                <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-secondary via-secondary-container to-secondary"></div>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary text-2xl">tune</span>
                        <h3 class="text-lg font-bold text-primary">Availability Range Search</h3>
                    </div>
                    <span class="rounded border border-secondary/30 bg-secondary-container/20 px-2 py-0.5 text-[11px] font-bold text-secondary">Room Filter</span>
                </div>
                <form method="GET" action="{{ route('rooms.index') }}" class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                    <div class="space-y-1.5 md:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Check-In Date</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-xl text-primary-container">calendar_today</span>
                            <input class="w-full rounded-lg border-outline-variant bg-surface py-2 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" type="text" value="{{ now(config('app.timezone'))->format('M d, Y') }}" readonly>
                        </div>
                    </div>
                    <div class="space-y-1.5 md:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Check-Out Date</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-xl text-primary-container">event</span>
                            <input class="w-full rounded-lg border-outline-variant bg-surface py-2 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" type="text" value="{{ now(config('app.timezone'))->addDays(3)->format('M d, Y') }}" readonly>
                        </div>
                    </div>
                    <div class="space-y-1.5 md:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Room Category / Type</label>
                        <select name="type" class="w-full rounded-lg border-outline-variant bg-surface py-2 text-sm focus:border-secondary focus:ring-secondary">
                            <option value="">All Types</option>
                            @foreach ($roomTypes as $roomType)
                                <option value="{{ $roomType }}" @selected($type === $roomType)>{{ $roomType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Status</label>
                        <select name="status" class="w-full rounded-lg border-outline-variant bg-surface py-2 text-sm focus:border-secondary focus:ring-secondary">
                            <option value="">All Statuses</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container md:col-span-1">
                        <span class="material-symbols-outlined text-lg">manage_search</span>
                        Check
                    </button>
                </form>
            </section>

            <section class="grid grid-cols-2 gap-3 md:grid-cols-5">
                @foreach ([
                    ['Property Rooms', $publishedRoomCount, 'apartment', 'text-primary', 'Hotel total'],
                    ['Available', $availableRooms, 'check_circle', 'text-emerald-800', 'Encoded inventory'],
                    ['Reserved', $reservedRooms, 'event_available', 'text-secondary', 'Encoded inventory'],
                    ['Occupied', $occupiedRooms, 'king_bed', 'text-primary', 'Encoded inventory'],
                    ['Maintenance', $maintenanceRooms, 'build', 'text-error', 'Encoded inventory'],
                ] as [$label, $value, $icon, $color, $caption])
                    <article class="rounded-xl border border-surface-variant bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-outline">{{ $label }}</span>
                            <span class="material-symbols-outlined {{ $color }} text-lg">{{ $icon }}</span>
                        </div>
                        <p class="mt-2 font-headline text-3xl font-semibold {{ $color }}">{{ $value }}</p>
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-outline">{{ $caption }}</p>
                    </article>
                @endforeach
            </section>

            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="flex items-center gap-2 text-lg font-bold text-primary">
                            <span class="material-symbols-outlined text-secondary">grid_view</span>
                            Live Availability Selection Matrix
                        </h3>
                        <p class="text-xs text-on-surface-variant">Recommended available rooms for {{ now(config('app.timezone'))->format('M d') }} to {{ now(config('app.timezone'))->addDays(3)->format('M d, Y') }}</p>
                    </div>
                    <span class="text-xs font-semibold text-outline">Showing {{ $availableRecommendations->count() }} options</span>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    @forelse ($availableRecommendations as $room)
                        <article class="relative flex flex-col overflow-hidden rounded-xl border-2 border-emerald-500/40 bg-white shadow-sm transition hover:shadow-md">
                            @if ($loop->first)
                                <span class="absolute right-4 top-3 z-10 rounded-full bg-secondary px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">Ready</span>
                            @endif
                            @if ($room->imagePath())
                                <img src="{{ asset($room->imagePath()) }}" alt="{{ $room->room_type }} room reference" class="aspect-[3/2] w-full object-cover" loading="lazy">
                            @else
                                <div class="flex aspect-[3/2] items-center justify-center bg-surface-container text-on-surface-variant"><span class="material-symbols-outlined text-5xl">bedroom_parent</span><span class="sr-only">Room photo unavailable</span></div>
                            @endif
                            <div class="flex flex-1 flex-col justify-between p-5">
                                <div>
                                <span class="text-xs font-bold uppercase text-secondary">Floor {{ substr($room->room_number, 0, 1) }}</span>
                                <h4 class="mt-1 text-lg font-bold text-primary">Room {{ $room->room_number }} - {{ $room->room_type }}</h4>
                                <p class="mt-2 min-h-10 text-sm text-on-surface-variant">{{ $room->description ?: 'Clean room inventory available for guest assignment.' }}</p>
                                <div class="my-4 border-b border-surface-variant pb-4">
                                    <span class="font-headline text-2xl font-bold text-primary">PHP {{ number_format($room->rate, 2) }}</span>
                                    <span class="text-xs text-outline">/ night</span>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-md border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    AVAILABLE
                                </span>
                                </div>
                                @if (in_array($role, ['admin', 'receptionist'], true))
                                    <a href="{{ route('reservations.create', ['room_id' => $room->id]) }}" class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container">
                                        <span class="material-symbols-outlined text-lg">add_circle</span>
                                        Select for Reservation
                                    </a>
                                @else
                                    <a href="{{ route('rooms.show', $room) }}" class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                        View Room
                                    </a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-surface-variant bg-white p-6 text-sm font-semibold text-on-surface-variant md:col-span-3">
                            No available rooms match the current filters.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-surface-variant bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-surface-variant bg-surface-container-low/50 p-5 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="flex items-center gap-2 text-lg font-bold text-primary">
                            <span class="material-symbols-outlined text-secondary">table_chart</span>
                            Master Room Inventory & Status Roster
                        </h3>
                        <p class="text-sm text-on-surface-variant">Complete inventory breakdown across all encoded rooms.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($canManageRooms)
                            <button class="flex items-center gap-1.5 rounded-lg border border-secondary bg-white px-3 py-1.5 text-sm font-bold text-secondary transition hover:bg-secondary-container/20" onclick="document.getElementById('statusModal').classList.remove('hidden')">
                                <span class="material-symbols-outlined text-lg">sync_alt</span>
                                Update Room Status
                            </button>
                        @endif
                        @if ($role === 'admin')
                            <a href="{{ route('rooms.create') }}" class="flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-sm font-bold text-white transition hover:bg-primary-container">
                                <span class="material-symbols-outlined text-lg">add</span>
                                Add Room
                            </a>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[880px] text-left text-sm">
                        <thead class="border-b border-surface-variant bg-surface-container text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th class="px-4 py-3">Room Number</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Floor</th>
                                <th class="px-4 py-3">Capacity</th>
                                <th class="px-4 py-3">Nightly Rate</th>
                                <th class="px-4 py-3">Current Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-variant">
                            @forelse ($rooms as $room)
                                <tr class="transition hover:bg-surface-container-low">
                                    <td class="px-4 py-3.5 text-base font-bold text-primary">Room {{ $room->room_number }}</td>
                                    <td class="px-4 py-3.5">{{ $room->room_type }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-on-surface-variant">Floor {{ substr($room->room_number, 0, 1) }}</td>
                                    <td class="px-4 py-3.5">{{ $room->capacity }} pax</td>
                                    <td class="px-4 py-3.5 font-bold text-primary">PHP {{ number_format($room->rate, 2) }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-bold {{ $statusBadgeClasses[$room->status] ?? 'bg-surface-container border-outline-variant text-primary' }}">
                                            {{ strtoupper($statusLabels[$room->status] ?? $room->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            @if ($room->status === 'available' && in_array($role, ['admin', 'receptionist'], true))
                                                <a href="{{ route('reservations.create', ['room_id' => $room->id]) }}" class="rounded bg-secondary px-2.5 py-1 text-xs font-bold text-white transition hover:brightness-110">Reserve</a>
                                            @endif
                                            <a href="{{ route('rooms.show', $room) }}" class="rounded border border-outline-variant bg-surface-container px-2.5 py-1 text-xs font-bold text-primary transition hover:bg-surface-variant">View Room</a>
                                            @if ($canManageRooms)
                                                <a href="{{ route('rooms.edit', $room) }}" class="rounded border border-secondary/40 bg-white px-2.5 py-1 text-xs font-bold text-secondary transition hover:bg-secondary-container/20">Edit Status</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-8 text-center font-semibold text-on-surface-variant" colspan="7">No rooms found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-surface-variant bg-surface-container p-4 text-sm text-outline sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <span>Live Inventory: <strong class="text-on-surface">{{ $totalRooms }} of {{ $publishedRoomCount }} Property Rooms Encoded</strong></span>
                        <span>Available Rate: <strong class="text-emerald-700">{{ $availableRate }}%</strong></span>
                        <span>Occupancy Rate: <strong class="text-primary">{{ $occupancyRate }}%</strong></span>
                    </div>
                    <div>{{ $rooms->links() }}</div>
                </div>
            </section>

            <section class="rounded-xl border border-surface-variant bg-white p-5 shadow-sm">
                <h3 class="mb-4 font-headline text-xl font-medium text-primary">Floor Room Matrix</h3>
                <div class="grid gap-3 md:grid-cols-3">
                    @forelse ($roomMatrix as $floor => $floorRooms)
                        <div class="rounded-lg border border-surface-variant bg-surface-container-low p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="font-bold text-primary">Floor {{ $floor }}</span>
                                <span class="text-xs text-on-surface-variant">{{ $floorRooms->count() }} rooms</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1.5">
                                @foreach ($floorRooms as $room)
                                    <a href="{{ route('rooms.show', $room) }}" class="{{ $statusTileClasses[$room->status] ?? 'bg-surface-container text-primary' }} rounded p-2 text-center text-xs font-bold" title="{{ $room->room_number }} - {{ ucfirst($room->status) }}">
                                        {{ $room->room_number }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-on-surface-variant">No room matrix available.</p>
                    @endforelse
                </div>
            </section>
        </main>
    </div>

    @if ($canManageRooms)
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 p-4 backdrop-blur-sm" id="statusModal">
            <div class="w-full max-w-lg overflow-hidden rounded-xl border border-secondary/30 bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-primary px-6 py-4 text-white">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-secondary-fixed text-2xl">edit_attributes</span>
                        <div>
                            <h4 class="text-lg font-bold">Update Room Status</h4>
                            <p class="text-xs text-on-primary-container">Select a room from the table and use Edit Status for saved updates.</p>
                        </div>
                    </div>
                    <button class="rounded-lg p-1 text-on-primary-container transition hover:bg-primary-container hover:text-white" onclick="document.getElementById('statusModal').classList.add('hidden')">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="space-y-4 p-6">
                    <p class="text-sm text-on-surface-variant">For accurate audit logs, open a specific room and save the new status from its edit page.</p>
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach ($statusLabels as $value => $label)
                            <div class="rounded-xl border border-surface-variant bg-surface p-3">
                                <div class="text-xs font-bold uppercase {{ $value === 'available' ? 'text-emerald-800' : ($value === 'reserved' ? 'text-secondary' : ($value === 'occupied' ? 'text-primary' : 'text-error')) }}">{{ $label }}</div>
                                <div class="text-[11px] text-outline">{{ $statusCounts[$value] ?? 0 }} room(s)</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end">
                        <button class="rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white transition hover:bg-primary-container" onclick="document.getElementById('statusModal').classList.add('hidden')">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const clock = document.getElementById('room-clock');

            if (!clock) {
                return;
            }

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
