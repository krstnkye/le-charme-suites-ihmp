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
                        primary: '#161a20',
                        secondary: '#5b6069',
                        cream: '#f5f5f2',
                        line: '#e4e4df',
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        serif: ['Manrope', 'sans-serif'],
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
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-line bg-cream px-6">
            <div>
                <h1 class="text-base font-semibold">Housekeeping Workspace</h1>
                <p class="text-xs text-[#5b6069]">Le Charmé Suites · Rooms &amp; facilities</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden text-xs tabular-nums text-[#5b6069] sm:inline">{{ $today->format('M d, Y - h:i A') }}</span>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-1.5 py-1 hover:bg-[#e9e9e5]">
                    <span class="lc-avatar">{{ strtoupper(substr($currentUser?->name ?? 'H', 0, 1)) }}</span>
                    <span class="hidden text-left sm:block"><span class="block text-sm font-semibold leading-tight">{{ $currentUser?->name }}</span><span class="block text-xs text-[#5b6069]">Housekeeping access</span></span>
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 px-6 py-8">
            <section class="lc-page-head">
                <div>
                    <h2 class="text-[26px] font-semibold leading-8 tracking-tight">Daily Rooms Readiness & Turnover Control</h2>
                    <p>Housekeeping Command Center · cleaning queues, inspections, maintenance and guest requests.</p>
                </div>
                <a href="{{ route('rooms.index') }}" class="lc-btn lc-btn-primary"><span class="material-symbols-outlined" aria-hidden="true">bed</span>Open Room Status</a>
            </section>

            <section class="rounded-[10px] border border-line bg-white p-5">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-lg font-semibold">{{ $availableRooms }} of {{ $totalRooms }} rooms ready · {{ $readyRate }}%</h3>
                    <p class="text-sm text-[#5b6069]">Operational records · {{ $publishedRoomCount }} rooms in the property</p>
                </div>
                @php($hkBase = max($totalRooms, 1))
                <div class="mt-4 flex h-3 w-full gap-[3px] overflow-hidden rounded-full bg-[#f0f0ec]" role="img" aria-label="Room readiness breakdown">
                    @if ($availableRooms > 0)<span class="h-full bg-[#17707f]" style="width: {{ round($availableRooms / $hkBase * 100, 2) }}%"></span>@endif
                    @if ($occupiedRooms > 0)<span class="h-full bg-[#3f444c]" style="width: {{ round($occupiedRooms / $hkBase * 100, 2) }}%"></span>@endif
                    @if ($cleaningCount > 0)<span class="h-full" style="width: {{ round($cleaningCount / $hkBase * 100, 2) }}%; background: repeating-linear-gradient(135deg,#a9aba5 0 2px,#e4e4df 2px 5px)"></span>@endif
                    @if ($maintenanceCount > 0)<span class="h-full bg-[#c4242a]" style="width: {{ round($maintenanceCount / $hkBase * 100, 2) }}%"></span>@endif
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-y-4 sm:grid-cols-5">
                    @foreach ([
                        ['label' => 'Property Rooms', 'value' => $publishedRoomCount, 'swatch' => null],
                        ['label' => 'Ready / Available', 'value' => $availableRooms, 'swatch' => 'background:#17707f'],
                        ['label' => 'Occupied Rooms', 'value' => $occupiedRooms, 'swatch' => 'background:#3f444c'],
                        ['label' => 'Cleaning Queue', 'value' => $cleaningCount, 'swatch' => 'background:repeating-linear-gradient(135deg,#a9aba5 0 2px,#e4e4df 2px 5px)'],
                        ['label' => 'Maintenance Issues', 'value' => $maintenanceCount, 'swatch' => 'background:#c4242a'],
                    ] as $stat)
                        <div @class(['sm:border-l sm:border-[#eeeeea] sm:pl-5' => ! $loop->first])>
                            <dt class="flex items-center gap-2 text-sm font-medium text-[#5b6069]">@if ($stat['swatch'])<span class="h-2.5 w-2.5 rounded-sm" style="{{ $stat['swatch'] }}" aria-hidden="true"></span>@endif{{ $stat['label'] }}</dt>
                            <dd @class(['mt-1 text-[26px] font-semibold leading-8 tracking-tight tabular-nums', 'text-[#c4242a]' => $stat['label'] === 'Maintenance Issues' && $stat['value'] > 0])>{{ $stat['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,0.65fr)]">
                <div class="space-y-6">
                    <section id="assigned-rooms" class="rounded-xl border border-line bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-line p-5">
                            <div>
                                <h3 class="font-serif text-2xl font-bold">My Assigned Rooms</h3>
                                <p class="mt-1 text-sm text-slate-500">Rooms needing cleaning, inspection, maintenance follow-up, or occupied-room service.</p>
                            </div>
                            <a href="{{ route('rooms.index') }}" class="lc-btn lc-btn-quiet">Room status</a>
                        </div>
                        <div class="grid gap-3 p-5 md:grid-cols-2">
                            @forelse ($assignedRooms as $room)
                                <article class="rounded-lg border border-line bg-[#fafaf8] p-4">
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
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line p-5">
                            <div>
                                <h3 class="font-serif text-2xl font-bold">Housekeeping Task Assignments</h3>
                                <p class="mt-1 text-sm text-slate-500">Assign rooms to housekeeping staff and monitor the work status.</p>
                            </div>
                            <a href="{{ route('housekeeping.page', 'cleaning-tasks') }}" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary hover:bg-secondary/10">Open All Tasks</a>
                        </div>
                        <div class="border-b border-line bg-stone-50 p-5">
                            <form method="POST" action="{{ route('housekeeping-tasks.store') }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_0.8fr_auto] lg:items-end">
                                @csrf
                                <input type="hidden" name="task_type" value="turnover">
                                <input type="hidden" name="title" value="Room cleaning and preparation">
                                <input type="hidden" name="instructions" value="Clean, restock, and prepare the assigned room for inspection.">
                                <label class="text-xs font-bold text-slate-600">Room
                                    <select name="room_id" required class="mt-1 block w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm text-primary focus:border-secondary focus:ring-secondary">
                                        <option value="">Select room</option>
                                        @foreach ($taskRooms as $room)
                                            <option value="{{ $room->id }}">Room {{ $room->room_number }} - {{ $room->room_type }} ({{ ucfirst($room->status) }})</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="text-xs font-bold text-slate-600">Housekeeper
                                    <select name="assigned_to" required class="mt-1 block w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm text-primary focus:border-secondary focus:ring-secondary">
                                        <option value="">Select staff</option>
                                        @foreach ($housekeepingStaff as $staff)
                                            <option value="{{ $staff->id }}" @selected($staff->id === auth()->id())>{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="text-xs font-bold text-slate-600">Priority
                                    <select name="priority" required class="mt-1 block w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm text-primary focus:border-secondary focus:ring-secondary">
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="low">Low</option>
                                    </select>
                                </label>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-900">
                                    <span class="material-symbols-outlined text-base text-[#e8e8e4]">assignment_add</span>
                                    Assign Task
                                </button>
                            </form>
                        </div>
                        <div class="space-y-3 border-b border-line p-5">
                            <h4 class="font-bold">Active Assignments</h4>
                            @forelse ($housekeepingTasks as $task)
                                <form method="POST" action="{{ route('housekeeping-tasks.update', $task) }}" class="grid gap-3 rounded-lg border border-line bg-white p-3 md:grid-cols-[1fr_1fr_0.8fr_auto] md:items-center">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <p class="font-bold">Room {{ $task->room?->room_number ?? 'N/A' }} - {{ $task->title }}</p>
                                        <p class="text-xs text-slate-500">Priority: {{ ucfirst($task->priority) }}</p>
                                    </div>
                                    <select name="assigned_to" class="rounded-lg border-line bg-white px-3 py-2 text-sm focus:border-secondary focus:ring-secondary">
                                        <option value="">Unassigned</option>
                                        @foreach ($housekeepingStaff as $staff)
                                            <option value="{{ $staff->id }}" @selected($task->assigned_to === $staff->id)>{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="status" required class="rounded-lg border-line bg-white px-3 py-2 text-sm focus:border-secondary focus:ring-secondary">
                                        @foreach (['queued' => 'Waiting', 'in_progress' => 'In Progress', 'inspection' => 'For Inspection', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                            <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary hover:bg-secondary hover:text-white">Save</button>
                                </form>
                            @empty
                                <p class="rounded-lg border border-dashed border-line p-5 text-center text-sm text-slate-500">No active task assignments yet.</p>
                            @endforelse
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
                        <div id="report-issue" class="mt-4 rounded-lg border border-dashed border-secondary/40 bg-[#f7f7f4] p-4 text-sm text-slate-600">To report an issue, open the room status editor and set the room to maintenance with notes.</div>
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
