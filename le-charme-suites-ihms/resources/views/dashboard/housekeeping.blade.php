@php
    $currentUser = auth()->user();
    $today = now(config('app.timezone'));
    $cleaningCount = $cleaningRooms->count();
    $maintenanceCount = $maintenanceQueue->count();
    $readyRate = $totalRooms > 0 ? round(($availableRooms / $totalRooms) * 100) : 0;
    $statusTone = [
        'available' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'reserved' => 'border-amber-200 bg-amber-50 text-amber-800',
        'occupied' => 'border-sky-200 bg-sky-50 text-sky-800',
        'cleaning' => 'border-orange-200 bg-orange-50 text-orange-800',
        'maintenance' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Housekeeping Dashboard - Le Charme Suites IHMP</title>
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
        .material-symbols-outlined {
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-cream font-sans text-primary antialiased">
    @include('partials.housekeeping-sidebar', ['active' => 'dashboard'])

    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-line bg-cream/95 px-6 shadow-sm backdrop-blur">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">cleaning_services</span>
                <div>
                    <h1 class="font-serif text-xl font-bold">Le Charme Suites IHMP - Housekeeping Workspace</h1>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-secondary">Rooms & Facilities Operations</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden rounded-full bg-white px-3 py-1 text-xs font-bold text-secondary shadow-sm sm:inline-flex">{{ $today->format('M d, Y - h:i A') }}</span>
                <div class="text-right">
                    <p class="text-xs font-bold">{{ $currentUser?->name }}</p>
                    <p class="text-[11px] text-slate-500">Housekeeping Access</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#e6c185] bg-primary text-sm font-bold text-white">{{ strtoupper(substr($currentUser?->name ?? 'H', 0, 1)) }}</a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 px-6 py-8">
            <section class="rounded-xl bg-primary p-7 text-white shadow-lg">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_480px] lg:items-center">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#ffdeaa]">Housekeeping Command Center</p>
                        <h2 class="mt-3 font-serif text-4xl font-bold leading-tight">Daily Rooms Readiness & Turnover Control</h2>
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-200">Monitor cleaning queues, room inspection readiness, maintenance reports, guest service requests, and supply records from one role-specific workspace.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-white/10 bg-white/10 p-4">
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-300">Ready Rooms</p>
                            <p class="mt-1 text-2xl font-bold">{{ $availableRooms }} / {{ $totalRooms }}</p>
                            <p class="mt-1 text-[11px] text-slate-300">Encoded inventory · {{ $publishedRoomCount }}-room property</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-300">Readiness</p>
                            <p class="mt-1 text-2xl font-bold">{{ $readyRate }}%</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-300">Cleaning Tasks</p>
                            <p class="mt-1 text-2xl font-bold">{{ $cleaningCount }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-300">Maintenance</p>
                            <p class="mt-1 text-2xl font-bold">{{ $maintenanceCount }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    ['label' => 'Rooms Encoded', 'value' => $totalRooms.' / '.$publishedRoomCount, 'icon' => 'domain'],
                    ['label' => 'Ready / Available', 'value' => $availableRooms, 'icon' => 'check_circle'],
                    ['label' => 'Occupied Rooms', 'value' => $occupiedRooms, 'icon' => 'king_bed'],
                    ['label' => 'Cleaning Queue', 'value' => $cleaningCount, 'icon' => 'mop'],
                    ['label' => 'Maintenance Issues', 'value' => $maintenanceCount, 'icon' => 'construction'],
                ] as $stat)
                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                            <span class="material-symbols-outlined text-secondary">{{ $stat['icon'] }}</span>
                        </div>
                        <p class="mt-3 font-serif text-3xl font-bold">{{ $stat['value'] }}</p>
                        <div class="mt-3 h-1.5 rounded-full bg-slate-100"><div class="h-1.5 rounded-full bg-secondary" style="width: {{ $totalRooms > 0 ? min(100, round(((int) $stat['value'] / max($totalRooms, 1)) * 100)) : 0 }}%"></div></div>
                    </div>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,0.65fr)]">
                <div class="space-y-6">
                    <section id="assigned-rooms" class="rounded-xl border border-line bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-line p-5">
                            <div>
                                <h3 class="font-serif text-2xl font-bold">My Assigned Rooms</h3>
                                <p class="mt-1 text-sm text-slate-500">Rooms needing cleaning, inspection, maintenance follow-up, or occupied-room service.</p>
                            </div>
                            <a href="{{ route('rooms.index') }}" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary hover:bg-secondary/10">Open Room Status</a>
                        </div>
                        <div class="grid gap-3 p-5 md:grid-cols-2">
                            @forelse ($assignedRooms as $room)
                                <article class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-secondary">Room {{ $room->room_number }}</p>
                                            <h4 class="mt-1 text-lg font-bold">{{ $room->room_type }}</h4>
                                        </div>
                                        <span class="rounded-full border px-2 py-1 text-[11px] font-bold {{ $statusTone[$room->status] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">{{ ucfirst($room->status) }}</span>
                                    </div>
                                    <p class="mt-3 text-sm text-slate-600">{{ $room->description ?: 'No room notes recorded.' }}</p>
                                    <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500">
                                        <span>Capacity: {{ $room->capacity }} guests</span>
                                        <a href="{{ route('rooms.edit', $room) }}" class="font-bold text-secondary hover:underline">Update status</a>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-lg border border-dashed border-line p-8 text-center text-sm text-slate-500 md:col-span-2">No assigned rooms are waiting right now.</div>
                            @endforelse
                        </div>
                    </section>

                    <section id="cleaning-tasks" class="rounded-xl border border-line bg-white shadow-sm">
                        <div class="border-b border-line p-5">
                            <h3 class="font-serif text-2xl font-bold">Housekeeping Tasks</h3>
                            <p class="mt-1 text-sm text-slate-500">Cleaning queue, priorities, and completed room-status work.</p>
                        </div>
                        <div class="grid gap-4 p-5 lg:grid-cols-3">
                            <div>
                                <h4 class="mb-3 flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-secondary">mop</span>Cleaning Tasks</h4>
                                <div class="space-y-2">
                                    @forelse ($cleaningRooms as $room)
                                        <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2 text-sm text-orange-900 hover:bg-orange-100"><span>Room {{ $room->room_number }}</span><span class="font-bold">Clean</span></a>
                                    @empty
                                        <p class="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-500">No rooms are marked for cleaning.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div id="priority-rooms">
                                <h4 class="mb-3 flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-secondary">priority_high</span>Priority Rooms</h4>
                                <div class="space-y-2">
                                    @forelse ($priorityRooms as $room)
                                        <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-between rounded-lg bg-red-50 px-3 py-2 text-sm text-red-900 hover:bg-red-100"><span>Room {{ $room->room_number }}</span><span class="font-bold">{{ ucfirst($room->status) }}</span></a>
                                    @empty
                                        <p class="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-500">No urgent rooms at the moment.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div id="completed-tasks">
                                <h4 class="mb-3 flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-secondary">done_all</span>Completed Tasks</h4>
                                <div class="space-y-2">
                                    @forelse ($completedTasks as $log)
                                        <div class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-900">
                                            <p class="font-bold">Room {{ $log->room?->room_number ?? 'N/A' }} - {{ ucfirst($log->status) }}</p>
                                            <p class="text-xs">{{ $log->changed_at?->format('h:i A') }} · {{ $log->notes }}</p>
                                        </div>
                                    @empty
                                        <p class="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-500">No completed room-status logs yet today.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="service-requests" class="grid gap-6 lg:grid-cols-2">
                        <div id="guest-requests" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                            <h3 class="font-serif text-2xl font-bold">Guest Requests</h3>
                            <p class="mt-1 text-sm text-slate-500">Occupied rooms that may receive amenity, linen, or service follow-up.</p>
                            <div class="mt-4 space-y-2">
                                @forelse ($guestRequestRooms as $room)
                                    <a href="{{ route('rooms.show', $room) }}" class="flex items-center justify-between rounded-lg border border-line px-3 py-2 text-sm hover:bg-slate-50"><span>Room {{ $room->room_number }}</span><span class="font-bold">{{ $room->room_type }}</span></a>
                                @empty
                                    <p class="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-500">No occupied rooms currently need guest service tracking.</p>
                                @endforelse
                            </div>
                        </div>
                        <div id="special-cleaning" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                            <h3 class="font-serif text-2xl font-bold">Special Cleaning</h3>
                            <p class="mt-1 text-sm text-slate-500">Maintenance and cleaning rooms are treated as special attention items.</p>
                            <p class="mt-5 font-serif text-4xl font-bold">{{ $maintenanceCount + $cleaningCount }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $maintenanceCount }} maintenance, {{ $cleaningCount }} cleaning</p>
                        </div>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section id="room-inspection" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Room Inspection</h3>
                        <p class="mt-1 text-sm text-slate-500">Rooms requiring readiness inspection before returning to inventory.</p>
                        <div class="mt-4 space-y-2">
                            @forelse ($inspectionRooms as $room)
                                <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-between rounded-lg border border-line px-3 py-2 text-sm hover:bg-slate-50"><span>Room {{ $room->room_number }}</span><span class="font-bold">{{ ucfirst($room->status) }}</span></a>
                            @empty
                                <p class="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-500">No inspection queue right now.</p>
                            @endforelse
                        </div>
                    </section>

                    <section id="maintenance-requests" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Maintenance Requests</h3>
                        <div id="report-issue" class="mt-4 rounded-lg border border-dashed border-secondary/40 bg-[#fff8ec] p-4 text-sm text-slate-600">To report an issue, open the room status editor and set the room to maintenance with notes.</div>
                        <div id="reported-issues" class="mt-4 space-y-2">
                            @forelse ($maintenanceQueue as $room)
                                <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-between rounded-lg bg-red-50 px-3 py-2 text-sm text-red-900 hover:bg-red-100"><span>Room {{ $room->room_number }}</span><span>Review</span></a>
                            @empty
                                <p class="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-500">No maintenance issues reported.</p>
                            @endforelse
                        </div>
                    </section>

                    <section id="lost-found" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Lost &amp; Found</h3>
                        <p class="mt-3 text-sm text-slate-600">No lost-and-found entries are stored yet. Use room status notes to record recovered items until a separate lost-and-found record page is added.</p>
                    </section>

                    <section id="linen-supplies" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Linen &amp; Supplies</h3>
                        <div id="linen-records" class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-slate-50 p-3"><p class="text-xs font-bold uppercase text-slate-500">Linen Records</p><p class="mt-1 text-2xl font-bold">{{ $cleaningCount }}</p></div>
                            <div id="housekeeping-supplies" class="rounded-lg bg-slate-50 p-3"><p class="text-xs font-bold uppercase text-slate-500">Supplies Queue</p><p class="mt-1 text-2xl font-bold">{{ $occupiedRooms }}</p></div>
                        </div>
                    </section>

                    <section id="reports" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Reports</h3>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span>Ready rooms</span><strong>{{ $availableRooms }}</strong></div>
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span>Turnover queue</span><strong>{{ $cleaningCount }}</strong></div>
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span>Maintenance queue</span><strong>{{ $maintenanceCount }}</strong></div>
                        </div>
                    </section>

                    <section id="notifications" class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Notifications</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($recentRoomLogs as $log)
                                <div class="rounded-lg border border-line px-3 py-2 text-sm">
                                    <p class="font-bold">Room {{ $log->room?->room_number ?? 'N/A' }} changed to {{ ucfirst($log->status) }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->changed_at?->diffForHumans() }}</p>
                                </div>
                            @empty
                                <p class="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-500">No room notifications yet.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </section>
        </main>
    </div>
</body>
</html>
