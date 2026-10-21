@extends('layouts.housekeeping')

@section('title', $page['title'] . ' - Housekeeping Workspace')

@php
    $active = $page['active'];
    $statusTone = [
        'available' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'reserved' => 'border-amber-200 bg-amber-50 text-amber-800',
        'occupied' => 'border-sky-200 bg-sky-50 text-sky-800',
        'cleaning' => 'border-orange-200 bg-orange-50 text-orange-800',
        'maintenance' => 'border-red-200 bg-red-50 text-red-800',
    ];
    $pageRooms = match ($pageKey) {
        'assigned-rooms' => $assignedRooms,
        'room-inspection' => $inspectionRooms,
        'cleaning-tasks' => $cleaningRooms,
        'priority-rooms' => $priorityRooms,
        'guest-requests' => $guestRequestRooms,
        'special-cleaning', 'maintenance-requests', 'report-issue', 'reported-issues' => $maintenanceQueue,
        default => collect(),
    };
    $inspectionTarget = $inspectionRooms->first() ?? $assignedRooms->first();
    $inspectionScore = $inspectionTarget?->status === 'maintenance' ? 72 : ($inspectionTarget ? 94 : 0);
    $activeAssignedRoom = $assignedRooms->first();
    $queuedAssignedRooms = $assignedRooms->slice(1)->values();
    $completedAssignedRooms = $completedTasks->filter(fn ($log) => $log->room)->take(3)->values();
@endphp

@section('content')
    <section class="rounded-lg bg-primary p-6 text-white shadow-lg">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#ffdeaa]">{{ $page['group'] }}</p>
                <h2 class="mt-2 flex items-center gap-3 font-serif text-3xl font-bold">
                    <span class="material-symbols-outlined text-[#ffdeaa]">{{ $page['icon'] }}</span>
                    {{ $page['title'] }}
                </h2>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-200">{{ $page['subtitle'] }}</p>
            </div>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#ffdeaa]/40 bg-white/10 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/15">
                <span class="material-symbols-outlined text-base">bed</span>
                Open Room Status
            </a>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Property Rooms', 'value' => $publishedRoomCount, 'icon' => 'domain', 'caption' => 'Hotel total'],
            ['label' => 'Available', 'value' => $availableRooms, 'icon' => 'check_circle', 'caption' => 'Added inventory'],
            ['label' => 'Occupied', 'value' => $occupiedRooms, 'icon' => 'king_bed', 'caption' => 'Added inventory'],
            ['label' => 'Cleaning', 'value' => $cleaningRooms->count(), 'icon' => 'mop', 'caption' => 'Added inventory'],
            ['label' => 'Maintenance', 'value' => $maintenanceQueue->count(), 'icon' => 'construction', 'caption' => 'Added inventory'],
        ] as $stat)
            <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                    <span class="material-symbols-outlined text-secondary">{{ $stat['icon'] }}</span>
                </div>
                <p class="mt-3 font-serif text-3xl font-bold">{{ $stat['value'] }}</p>
                <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $stat['caption'] }}</p>
            </div>
        @endforeach
    </section>

    @if ($pageKey === 'assigned-rooms')
        <section class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-line bg-[#f6f3ed] p-5 sm:flex-row sm:items-center">
                    <div><p class="text-[11px] font-bold uppercase tracking-[0.18em] text-secondary">Shared Database Workflow</p><h3 class="mt-1 font-serif text-2xl font-bold">Live Housekeeping Work Orders</h3><p class="mt-1 text-sm text-slate-500">Assignments, progress, due times, and completion history are visible to authorized departments.</p></div>
                    <span class="rounded bg-primary px-3 py-1.5 text-xs font-bold text-white">{{ $housekeepingTasks->whereNotIn('status', ['completed', 'cancelled'])->count() }} Active</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="bg-[#fdfcfa] text-[11px] font-bold uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Room / Task</th><th class="px-4 py-3">Assigned to</th><th class="px-4 py-3">Priority</th><th class="px-4 py-3">Due</th><th class="px-4 py-3">Status & Action</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($housekeepingTasks as $task)
                                <tr>
                                    <td class="px-4 py-3"><p class="font-bold text-primary">Room {{ $task->room->room_number }} · {{ $task->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $task->instructions ?: ucfirst(str_replace('_', ' ', $task->task_type)) }}</p></td>
                                    <td class="px-4 py-3 font-semibold">{{ $task->assignee?->name ?? 'Unassigned queue' }}</td>
                                    <td class="px-4 py-3"><span @class(['rounded px-2 py-1 text-[11px] font-bold uppercase', 'bg-red-100 text-red-800' => in_array($task->priority, ['urgent', 'high'], true), 'bg-slate-100 text-slate-700' => in_array($task->priority, ['normal', 'low'], true)])>{{ $task->priority }}</span></td>
                                    <td class="px-4 py-3 text-xs">{{ $task->due_at?->format('M d, h:i A') ?? 'No deadline' }}</td>
                                    <td class="px-4 py-3">
                                        @if (! in_array($task->status, ['completed', 'cancelled'], true))
                                            <form method="POST" action="{{ route('housekeeping-tasks.update', $task) }}" class="flex flex-wrap items-center gap-2">
                                                @csrf @method('PATCH')
                                                <select name="assigned_to" aria-label="Housekeeper assigned to Room {{ $task->room->room_number }}" class="max-w-44 rounded border-line py-1.5 text-xs font-bold">
                                                    <option value="">Unassigned</option>
                                                    @if ($task?->assignee && $task->assignee->role !== 'housekeeping')<option value="{{ $task->assignee->id }}" selected>{{ $task->assignee->name }} ({{ ucfirst($task->assignee->role) }})</option>@endif
                                                    @foreach ($housekeepingStaff as $staff)<option value="{{ $staff->id }}" @selected($task->assigned_to === $staff->id)>{{ $staff->name }}</option>@endforeach
                                                </select>
                                                <select name="status" class="rounded border-line py-1.5 text-xs font-bold">
                                                    @foreach (['queued' => 'Waiting', 'in_progress' => 'Start / In Progress', 'inspection' => 'Ready for Inspection', 'completed' => 'Complete'] as $value => $label)<option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>@endforeach
                                                </select>
                                                <button class="rounded bg-primary px-2.5 py-1.5 text-xs font-bold text-white">Save</button>
                                            </form>
                                        @else
                                            <span class="font-bold text-emerald-700">{{ ucfirst($task->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No housekeeping work orders yet. Create one using the assignment form.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <form method="POST" action="{{ route('housekeeping-tasks.store') }}" class="h-fit space-y-4 rounded-lg border border-line bg-white p-5 shadow-sm">
                @csrf
                <div><p class="text-[11px] font-bold uppercase tracking-[0.18em] text-secondary">Task Assignment</p><h3 class="mt-1 font-serif text-xl font-bold">Create Work Order</h3></div>
                <label class="block text-xs font-bold">Room<select name="room_id" required class="mt-1 w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm"><option value="">Select room</option>@foreach ($allRooms as $room)<option value="{{ $room->id }}">Room {{ $room->room_number }} · {{ $room->status }}</option>@endforeach</select></label>
                <label class="block text-xs font-bold">Assign housekeeper<select name="assigned_to" required class="mt-1 w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm"><option value="">Select housekeeping staff</option>@foreach ($housekeepingStaff as $staff)<option value="{{ $staff->id }}" @selected(auth()->id() === $staff->id)>{{ $staff->name }}</option>@endforeach</select></label>
                <label class="block text-xs font-bold">Task type<select name="task_type" required class="mt-1 w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm">@foreach (['turnover' => 'Departure Room Preparation', 'stayover' => 'Occupied Room Service', 'inspection' => 'Room Inspection', 'guest_request' => 'Guest Request', 'deep_cleaning' => 'Deep Cleaning', 'maintenance_follow_up' => 'Maintenance Follow-up'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                <label class="block text-xs font-bold">Task title<input name="title" required maxlength="255" class="mt-1 w-full rounded-lg border-line px-3 py-2.5 text-sm" placeholder="Example: Prepare room for arrival"></label>
                <label class="block text-xs font-bold">Instructions<textarea name="instructions" rows="3" class="mt-1 w-full rounded-lg border-line px-3 py-2.5 text-sm"></textarea></label>
                <div class="grid grid-cols-2 gap-3"><label class="text-xs font-bold">Priority<select name="priority" class="mt-1 w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm"><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option><option value="low">Low</option></select></label><label class="text-xs font-bold">Due by<input name="due_at" type="datetime-local" class="mt-1 w-full rounded-lg border-line px-3 py-2.5 text-sm"></label></div>
                <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white"><span class="material-symbols-outlined text-lg">assignment_add</span>Create Assignment</button>
            </form>
        </section>
    @endif

    @if ($pageKey === 'assigned-rooms')
        <section class="rounded-lg border border-line bg-white shadow-sm">
            <div class="border-b border-line bg-[#f6f3ed] p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-secondary">Housekeeping Assignment Deck</p>
                        <h3 class="mt-1 font-serif text-3xl font-bold">My Assigned Rooms</h3>
                        <p class="mt-2 max-w-3xl text-sm text-slate-500">Personal room workload with cleaning priority, current cleaning stage, and action shortcuts.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('housekeeping.page', 'report-issue') }}" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100">
                            <span class="material-symbols-outlined text-base">flag</span>
                            Report Discrepancy
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Assigned Today', $assignedRooms->count(), 'assignment_ind', 'text-primary'],
                    ['Cleaning List', $cleaningRooms->count(), 'cleaning_services', 'text-orange-700'],
                    ['Priority Rooms', $priorityRooms->count(), 'priority_high', 'text-red-700'],
                    ['Completed Logs', $completedTasks->count(), 'done_all', 'text-emerald-700'],
                ] as [$label, $value, $icon, $color])
                    <div class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                            <span class="material-symbols-outlined {{ $color }}">{{ $icon }}</span>
                        </div>
                        <p class="mt-3 font-serif text-3xl font-bold {{ $color }}">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-12">
            <div class="space-y-5 xl:col-span-8">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2">
                        <h3 class="font-serif text-2xl font-bold">Room Preparation List</h3>
                        <span class="rounded bg-[#f6f3ed] px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $assignedRooms->count() }} Rooms Allocated</span>
                    </div>
                    <p class="text-xs text-slate-500">Sorted by: <strong class="text-primary">Priority & Response Time Urgency</strong></p>
                </div>

                @if ($activeAssignedRoom)
                    <article class="overflow-hidden rounded-lg border-2 border-secondary/60 bg-white shadow-md">
                        <div class="h-1.5 bg-gradient-to-r from-[#c5a36a] to-[#f6f3ed]"></div>
                        <div class="p-5">
                            <div class="flex flex-col justify-between gap-4 border-b border-line pb-4 lg:flex-row lg:items-start">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg border border-secondary/30 bg-secondary/10 text-primary">
                                        <span class="text-[10px] font-bold uppercase text-secondary">Room</span>
                                        <span class="text-xl font-extrabold leading-none">{{ $activeAssignedRoom->room_number }}</span>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-lg font-bold">{{ $activeAssignedRoom->room_type }}</h4>
                                            <span class="rounded border border-amber-300 bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-900">
                                                {{ $activeAssignedRoom->status === 'maintenance' ? 'PRIORITY HOLD' : 'ACTIVE WORKING ROOM' }}
                                            </span>
                                            <span class="rounded border border-sky-200 bg-sky-100 px-2 py-0.5 text-[11px] font-bold text-sky-900">IN PROGRESS</span>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500">{{ $activeAssignedRoom->description ?: 'Room Preparation checklist ready for housekeeping completion.' }}</p>
                                    </div>
                                </div>
                                <div class="text-left lg:text-right">
                                    <p class="flex items-center gap-1.5 text-sm font-bold text-red-700 lg:justify-end">
                                        <span class="material-symbols-outlined text-lg">timer</span>
                                        Live Response Time Tracking
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500">Target: next IHMP readiness release</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <span>Sanitization & Room Preparation Milestones</span>
                                    <span class="text-secondary">2 of 5 Completed</span>
                                </div>
                                <div class="grid gap-2 md:grid-cols-2">
                                    @foreach ([
                                        [true, 'Strip bed & fresh linen setup'],
                                        [true, 'Bathroom deep clean & fixtures'],
                                        [false, 'Glass, mirror, and balcony polish'],
                                        [false, 'Amenity and supply replenishment'],
                                        [false, 'Final scent, lights, and IHMP notes'],
                                    ] as [$checked, $label])
                                        <label class="{{ $loop->last ? 'md:col-span-2' : '' }} flex cursor-pointer items-center gap-2.5 rounded-lg border {{ $checked ? 'border-line bg-[#f6f3ed]' : 'border-secondary/40 bg-white shadow-sm' }} p-2.5 text-sm hover:bg-[#f6f3ed]">
                                            <input type="checkbox" @checked($checked) class="h-4 w-4 rounded border-line text-secondary focus:ring-secondary">
                                            <span class="{{ $checked ? 'text-slate-500 line-through' : 'font-semibold text-primary' }}">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col justify-between gap-3 border-t border-line pt-4 sm:flex-row sm:items-center">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('housekeeping.page', 'notifications') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#f6f3ed] px-3 py-2 text-xs font-bold text-primary hover:bg-line">
                                        <span class="material-symbols-outlined text-base">call</span>
                                        Call Supervisor
                                    </a>
                                </div>
                                <a href="{{ route('rooms.edit', $activeAssignedRoom) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-[#5b4313]">
                                    <span class="material-symbols-outlined text-lg">verified</span>
                                    Complete & Request Inspection
                                </a>
                            </div>
                        </div>
                    </article>
                @endif

                <div class="space-y-3">
                    @forelse ($queuedAssignedRooms as $room)
                        <article class="rounded-lg border border-line bg-white p-4 shadow-sm transition hover:border-secondary/40">
                            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-[#f6f3ed] text-primary">
                                        <span class="text-[10px] font-bold uppercase text-slate-500">Rm</span>
                                        <span class="text-base font-extrabold leading-none">{{ $room->room_number }}</span>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-bold">{{ $room->room_type }}</h4>
                                            <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $statusTone[$room->status] ?? 'bg-slate-100 text-slate-700' }}">{{ strtoupper($room->status) }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">{{ $room->description ?: 'Assigned room awaiting housekeeping action.' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('rooms.show', $room) }}" class="rounded border border-line px-3 py-1.5 text-xs font-bold text-primary hover:bg-[#f6f3ed]">View</a>
                                    <a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-xs font-bold text-white hover:bg-primary/90">
                                        <span class="material-symbols-outlined text-base">play_arrow</span>
                                        Start / Update
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        @unless ($activeAssignedRoom)
                            <div class="rounded-lg border border-dashed border-line bg-white p-8 text-center text-sm text-slate-500">No assigned rooms are waiting right now.</div>
                        @endunless
                    @endforelse

                    @foreach ($completedAssignedRooms as $log)
                        <article class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-4 shadow-sm">
                            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-emerald-100 text-emerald-800">
                                        <span class="text-[10px] font-bold uppercase">Rm</span>
                                        <span class="text-base font-extrabold leading-none">{{ $log->room?->room_number ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-primary">Completed Status Update</h4>
                                        <p class="mt-1 text-xs text-slate-500">Released at {{ $log->changed_at?->format('h:i A') }} - {{ $log->notes ?: 'IHMP synced.' }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800"><span class="material-symbols-outlined text-base">verified_user</span>IHMP Synced</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="space-y-5 xl:col-span-4">
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-line pb-3">
                        <h3 class="flex items-center gap-2 text-sm font-bold">
                            <span class="material-symbols-outlined text-secondary">cleaning_bucket</span>
                            Cart Supplies & Par
                        </h3>
                        <span class="rounded bg-[#ffdeaa]/50 px-2 py-0.5 text-[10px] font-bold text-secondary">Housekeeping Cart</span>
                    </div>
                    <div class="mt-4 space-y-4">
                        @foreach ([
                            ['Microfiber Cloths', '8 Clean / 4 Soiled', 66, 'bg-emerald-600'],
                            ['Sanitizing Chemical Spray', '80% Level', 80, 'bg-secondary'],
                            ['Bath Sheet Sets', '6 Sets Remaining', 50, 'bg-primary'],
                            ['Guest Amenity Pairs', '4 Pairs', 40, 'bg-primary'],
                        ] as [$label, $value, $width, $color])
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="font-medium">{{ $label }}</span>
                                    <span class="font-bold text-primary">{{ $value }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-line">
                                    <div class="h-full rounded-full {{ $color }}" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border border-amber-300/60 bg-amber-50/40 p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-amber-200 pb-3">
                        <h3 class="flex items-center gap-2 text-sm font-bold">
                            <span class="material-symbols-outlined text-amber-700">room_service</span>
                            Floor Guest Deliveries
                        </h3>
                        <span class="rounded-full bg-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-900">{{ $guestRequestRooms->count() }} Active</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($guestRequestRooms->take(2) as $room)
                            <div class="rounded-lg border border-amber-200 bg-white p-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[11px] font-bold text-amber-800">Room {{ $room->room_number }}</span>
                                        <p class="mt-2 text-xs font-bold text-primary">Amenity / linen follow-up</p>
                                        <p class="mt-1 text-[11px] text-slate-500">{{ $room->room_type }}</p>
                                    </div>
                                    <span class="text-[11px] font-bold text-amber-900">Open</span>
                                </div>
                                <a href="{{ route('rooms.show', $room) }}" class="mt-3 inline-flex w-full justify-center rounded bg-secondary px-2.5 py-1.5 text-[11px] font-bold text-white hover:bg-[#5b4313]">Mark Delivered</a>
                            </div>
                        @empty
                            <p class="rounded-lg bg-white px-3 py-3 text-sm text-slate-500">No guest delivery requests on the floor.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 p-5">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-700 text-white"><span class="material-symbols-outlined">sos</span></span>
                        <div>
                            <h4 class="text-sm font-bold text-red-800">Staff Duress & Medical Alert</h4>
                            <p class="text-xs text-slate-600">Immediate notification to the supervisor.</p>
                        </div>
                    </div>
                    <a href="{{ route('housekeeping.page', 'notifications') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-700 px-3 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-red-800">
                        <span class="material-symbols-outlined text-base">notifications_active</span>
                        Immediate Assistance
                    </a>
                </div>
            </aside>
        </section>

    @elseif ($pageKey === 'room-inspection')
        @include('housekeeping.partials.room-inspection')
    @elseif ($pageKey === 'priority-rooms')
        @include('housekeeping.partials.task-board', ['boardTitle' => 'Priority Rooms & Fast Cleaning', 'boardSubtitle' => 'Open high and urgent tasks, most urgent first.', 'boardPriorities' => ['urgent', 'high']])
    @elseif ($pageKey === 'cleaning-tasks')
        @include('housekeeping.partials.task-board', ['boardTitle' => 'Unit Room Preparation & Cleaning Tasks List', 'boardSubtitle' => 'Room cleaning after check-out and room inspections waiting to be done.', 'boardTypes' => ['turnover', 'inspection']])
    @elseif ($pageKey === 'guest-requests')
        @include('housekeeping.partials.task-board', ['boardTitle' => 'Live Request Assignment Board', 'boardSubtitle' => 'Guest requests logged by the front desk or housekeeping, such as extra towels or amenities.', 'boardTypes' => ['guest_request']])
    @elseif ($pageKey === 'special-cleaning')
        @include('housekeeping.partials.task-board', ['boardTitle' => 'Special Cleaning & Room Sanitation', 'boardSubtitle' => 'Deep cleaning and cleaning after maintenance work.', 'boardTypes' => ['deep_cleaning', 'maintenance_follow_up']])
    @elseif ($pageKey === 'maintenance-requests')
        @include('housekeeping.partials.maintenance-requests')
    @elseif ($pageKey === 'report-issue')
        @include('housekeeping.partials.report-issue')
    @elseif ($pageKey === 'reported-issues')
        @include('housekeeping.partials.reported-issues')
    @elseif ($pageKey === 'reports')
        @include('housekeeping.partials.reports')
    @elseif ($pageKey === 'tasks')
        @include('housekeeping.partials.task-board', ['boardTitle' => 'Housekeeping Tasks & Operational Assignment', 'boardSubtitle' => 'Every open housekeeping task across the hotel.'])
    @elseif ($pageKey === 'service-requests')
        @include('housekeeping.partials.task-board', ['boardTitle' => 'Service Requests Hub & Guest Amenities Delivery', 'boardSubtitle' => 'Guest requests and occupied-room service for in-house guests.', 'boardTypes' => ['guest_request', 'stayover']])
    @elseif ($pageKey === 'completed-tasks')
        @include('housekeeping.partials.task-board', ['boardTitle' => 'Completed Tasks & Shift Cleaning History', 'boardSubtitle' => 'Tasks marked completed, newest first.', 'boardShowCompleted' => true])
    @elseif ($pageKey === 'lost-found')
        @include('housekeeping.partials.lost-found')
    @elseif ($pageKey === 'notifications')
        @include('housekeeping.partials.notifications')
    @endif
@endsection
