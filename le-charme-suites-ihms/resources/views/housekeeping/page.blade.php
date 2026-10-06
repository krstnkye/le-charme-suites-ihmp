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
            ['label' => 'Total Rooms', 'value' => $totalRooms, 'icon' => 'domain'],
            ['label' => 'Available', 'value' => $availableRooms, 'icon' => 'check_circle'],
            ['label' => 'Occupied', 'value' => $occupiedRooms, 'icon' => 'king_bed'],
            ['label' => 'Cleaning', 'value' => $cleaningRooms->count(), 'icon' => 'mop'],
            ['label' => 'Maintenance', 'value' => $maintenanceQueue->count(), 'icon' => 'construction'],
        ] as $stat)
            <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                    <span class="material-symbols-outlined text-secondary">{{ $stat['icon'] }}</span>
                </div>
                <p class="mt-3 font-serif text-3xl font-bold">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </section>

    @if ($pageKey === 'assigned-rooms')
        <section class="rounded-lg border border-line bg-white shadow-sm">
            <div class="border-b border-line bg-[#f6f3ed] p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-secondary">Housekeeping Assignment Deck</p>
                        <h3 class="mt-1 font-serif text-3xl font-bold">My Assigned Rooms</h3>
                        <p class="mt-2 max-w-3xl text-sm text-slate-500">Personal room workload with turnover priority, current cleaning stage, supply readiness, and action shortcuts.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('housekeeping.page', 'report-issue') }}" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100">
                            <span class="material-symbols-outlined text-base">flag</span>
                            Report Discrepancy
                        </a>
                        <a href="{{ route('housekeeping.page', 'linen-supplies') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#ffdeaa] px-3 py-2 text-xs font-bold text-secondary hover:bg-[#e6c185]">
                            <span class="material-symbols-outlined text-base">inventory</span>
                            Request Restock
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Assigned Today', $assignedRooms->count(), 'assignment_ind', 'text-primary'],
                    ['Cleaning Queue', $cleaningRooms->count(), 'cleaning_services', 'text-orange-700'],
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
                        <h3 class="font-serif text-2xl font-bold">Turnover Queue</h3>
                        <span class="rounded bg-[#f6f3ed] px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $assignedRooms->count() }} Rooms Allocated</span>
                    </div>
                    <p class="text-xs text-slate-500">Sorted by: <strong class="text-primary">Priority & SLA Urgency</strong></p>
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
                                        <p class="mt-1 text-sm text-slate-500">{{ $activeAssignedRoom->description ?: 'Turnover checklist ready for housekeeping completion.' }}</p>
                                    </div>
                                </div>
                                <div class="text-left lg:text-right">
                                    <p class="flex items-center gap-1.5 text-sm font-bold text-red-700 lg:justify-end">
                                        <span class="material-symbols-outlined text-lg">timer</span>
                                        Live SLA Tracking
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500">Target: next PMS readiness release</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <span>Sanitization & Turnover Milestones</span>
                                    <span class="text-secondary">2 of 5 Completed</span>
                                </div>
                                <div class="grid gap-2 md:grid-cols-2">
                                    @foreach ([
                                        [true, 'Strip bed & fresh linen setup'],
                                        [true, 'Bathroom deep clean & fixtures'],
                                        [false, 'Glass, mirror, and balcony polish'],
                                        [false, 'Amenity and supply replenishment'],
                                        [false, 'Final scent, lights, and PMS notes'],
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
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-[#f6f3ed] px-3 py-2 text-xs font-bold text-primary hover:bg-line">
                                        <span class="material-symbols-outlined text-base">pause_circle</span>
                                        Pause Timer
                                    </button>
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
                                        <p class="mt-1 text-xs text-slate-500">Released at {{ $log->changed_at?->format('h:i A') }} - {{ $log->notes ?: 'PMS synced.' }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800"><span class="material-symbols-outlined text-base">verified_user</span>PMS Synced</span>
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
                    <a href="{{ route('housekeeping.page', 'housekeeping-supplies') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-line bg-[#f6f3ed] px-3 py-2 text-xs font-bold text-primary hover:bg-line">
                        <span class="material-symbols-outlined text-base">local_shipping</span>
                        Refill Cart from Service Hub
                    </a>
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
                            <p class="text-xs text-slate-600">Immediate notification to supervisor dispatch.</p>
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
        <section class="grid gap-5 xl:grid-cols-12">
            <aside class="space-y-5 xl:col-span-4">
                <div class="rounded-lg border border-line bg-white shadow-sm">
                    <div class="border-b border-line bg-[#f6f3ed] p-5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-secondary">Inspection Queue</p>
                        <h3 class="mt-1 font-serif text-2xl font-bold">QA Release Command</h3>
                        <p class="mt-1 text-sm text-slate-500">Rooms awaiting final inspection before front desk release.</p>
                    </div>
                    <div class="divide-y divide-line">
                        @forelse ($inspectionRooms as $room)
                            <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-between gap-3 p-4 transition hover:bg-[#f6f3ed]">
                                <div>
                                    <p class="font-bold">Room {{ $room->room_number }} - {{ $room->room_type }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $room->description ?: 'Ready for supervisor quality audit.' }}</p>
                                </div>
                                <span class="rounded-full border px-2 py-0.5 text-[11px] font-bold {{ $statusTone[$room->status] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">{{ ucfirst($room->status) }}</span>
                            </a>
                        @empty
                            <p class="p-8 text-center text-sm text-slate-500">No rooms are waiting for inspection right now.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Readiness Score</p>
                            <p class="mt-2 font-serif text-4xl font-bold text-secondary">{{ $inspectionScore }} / 100</p>
                        </div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#fff3dc] text-secondary">
                            <span class="material-symbols-outlined text-3xl">verified_user</span>
                        </span>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-line">
                        <div class="h-full rounded-full bg-secondary" style="width: {{ $inspectionScore }}%"></div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">Inspection confidence based on current room turnover state.</p>
                </div>

                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <h3 class="flex items-center gap-2 font-bold">
                        <span class="material-symbols-outlined text-secondary">history_edu</span>
                        Recent QA Stream
                    </h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentRoomLogs->take(4) as $log)
                            <div class="rounded-lg bg-[#f6f3ed] px-3 py-2 text-sm">
                                <p class="font-bold">Room {{ $log->room?->room_number ?? 'N/A' }} changed to {{ ucfirst($log->status) }}</p>
                                <p class="text-xs text-slate-500">{{ $log->changed_at?->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="rounded-lg bg-[#f6f3ed] px-3 py-3 text-sm text-slate-500">No recent inspection history yet.</p>
                        @endforelse
                    </div>
                </div>
            </aside>

            <div class="space-y-5 xl:col-span-8">
                <div class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                    <div class="flex flex-col justify-between gap-4 border-b border-line bg-[#f6f3ed] p-5 lg:flex-row lg:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="font-serif text-2xl font-bold">
                                    @if ($inspectionTarget)
                                        Room {{ $inspectionTarget->room_number }} - {{ $inspectionTarget->room_type }}
                                    @else
                                        No Room Selected
                                    @endif
                                </h3>
                                <span class="rounded border border-secondary/40 bg-[#ffdeaa]/50 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-secondary">Supervisor QA</span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-base text-secondary">badge</span> Attendant: Housekeeping Team</span>
                                <span>|</span>
                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-base text-secondary">timer</span> Target: 30 minute release</span>
                                <span>|</span>
                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-base text-secondary">nest_thermostat</span> PMS Status: Connected</span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-secondary/30 bg-white px-4 py-2 text-right shadow-sm">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Audit Score</span>
                            <span class="font-serif text-2xl font-bold text-secondary">{{ $inspectionScore }} / 100</span>
                            <span class="block text-[10px] font-bold text-primary">Housekeeping Standard</span>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">
                        <div class="flex flex-col justify-between gap-2 md:flex-row md:items-center">
                            <h3 class="text-lg font-bold">Five-Star Quality Verification Criteria</h3>
                            <span class="text-xs text-slate-500">All sections must be validated before PMS handoff</span>
                        </div>

                        <div class="space-y-4">
                            @foreach ([
                                ['bed', 'Bedding & Percale Linen Architecture', 'Linen tautness, pillow alignment, duvet drape symmetry, and fresh allergen seal.'],
                                ['bathtub', 'Bathroom Deep Sanitization & Plumbing Seals', 'Chrome fixtures, glass enclosure, bath amenities, and sanitary band placement.'],
                                ['balcony', 'Balcony & Glass Surfaces', 'Tracks, vents, outdoor furniture, mirrors, windows, and visible dust points.'],
                                ['spa', 'Luxury Amenities & Guest Collateral', 'Guest supplies, welcome items, collateral, chargers, remotes, and presentation details.'],
                                ['liquor', 'Minibar & Supply Verification', 'Restock, seals, drinking vessels, pods, towels, and inventory-ready items.'],
                            ] as [$icon, $title, $description])
                                <article class="rounded-lg border border-secondary/30 bg-white p-4 shadow-sm transition hover:border-secondary">
                                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                                        <div class="flex items-start gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#f6f3ed] text-primary">
                                                <span class="material-symbols-outlined">{{ $icon }}</span>
                                            </span>
                                            <div>
                                                <h4 class="font-bold">{{ $loop->iteration }}. {{ $title }}</h4>
                                                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                                            </div>
                                        </div>
                                        <div class="inline-flex shrink-0 rounded-lg border border-line bg-[#f6f3ed] p-1">
                                            <button type="button" class="inspection-toggle rounded bg-primary px-3 py-1 text-xs font-bold text-white shadow-sm">Passed</button>
                                            <button type="button" class="inspection-toggle rounded px-3 py-1 text-xs font-semibold text-slate-500 hover:text-primary">Remediate</button>
                                            <button type="button" class="inspection-toggle rounded px-3 py-1 text-xs font-semibold text-slate-500 hover:text-red-700">Fail</button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="border-t border-line pt-5">
                            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                                <h4 class="flex items-center gap-2 font-bold">
                                    <span class="material-symbols-outlined text-secondary">add_photo_alternate</span>
                                    Photographic Proof of Readiness
                                </h4>
                                <span class="text-xs text-slate-500">Attach sign-off proof before release</span>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                @foreach ([['Bed Linen Proof', 'verified'], ['Bathroom Proof', 'verified'], ['Attach Additional Photo', 'add_a_photo']] as [$label, $icon])
                                    <button type="button" class="aspect-video rounded-lg border {{ $loop->last ? 'border-dashed border-slate-300 bg-[#f6f3ed]' : 'border-line bg-primary' }} p-3 text-left transition hover:border-secondary">
                                        <div class="flex h-full flex-col justify-end rounded-md {{ $loop->last ? 'items-center justify-center text-center text-secondary' : 'bg-gradient-to-t from-black/30 to-transparent text-white' }} p-3">
                                            <span class="material-symbols-outlined">{{ $icon }}</span>
                                            <span class="mt-1 text-xs font-bold">{{ $label }}</span>
                                            @unless ($loop->last)<span class="text-[10px] text-slate-200">{{ now(config('app.timezone'))->format('h:i A') }} PHT</span>@endunless
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-line pt-5">
                            <label class="mb-2 flex items-center justify-between text-sm font-bold">
                                <span>Supervisor Quality Observations & Attendant Feedback</span>
                                <span class="text-xs font-normal text-slate-500">Logged into inspection record</span>
                            </label>
                            <textarea class="w-full rounded-lg border-line bg-[#f6f3ed] p-3 text-sm focus:border-secondary focus:ring-secondary" rows="3" placeholder="Enter turnover observations...">@if ($inspectionTarget)Room {{ $inspectionTarget->room_number }} reviewed for release. Validate all visible room standards before changing status to available.@endif</textarea>
                        </div>

                        <div class="flex flex-col items-center justify-between gap-4 border-t border-line pt-5 sm:flex-row">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span class="material-symbols-outlined text-base text-secondary">encrypted</span>
                                Digitally timestamped with housekeeping supervisor signature
                            </div>
                            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                                @if ($inspectionTarget)
                                    <a href="{{ route('rooms.edit', $inspectionTarget) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 px-4 py-2.5 text-sm font-bold text-red-700 hover:bg-red-50">
                                        <span class="material-symbols-outlined text-lg">replay</span>
                                        Fail & Return
                                    </a>
                                    <a href="{{ route('rooms.edit', $inspectionTarget) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                                        <span class="material-symbols-outlined text-lg text-[#ffdeaa]">check_circle</span>
                                        Approve & Release to PMS
                                    </a>
                                @else
                                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                                        <span class="material-symbols-outlined text-lg text-[#ffdeaa]">bed</span>
                                        Open Room Status
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-line bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-secondary">verified_user</span>
                            <div>
                                <p class="text-sm font-bold">Continuous QA Verification Pipeline</p>
                                <p class="text-xs text-slate-500">Rooms released from this page remain inside housekeeping access until status is updated.</p>
                            </div>
                        </div>
                        <span class="flex items-center gap-1 text-xs font-bold text-secondary"><span class="material-symbols-outlined text-base">database</span>PMS APIs Online</span>
                    </div>
                </div>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.inspection-toggle').forEach((button) => {
                    button.addEventListener('click', () => {
                        const siblings = button.parentElement.querySelectorAll('.inspection-toggle');
                        siblings.forEach((item) => item.className = 'inspection-toggle rounded px-3 py-1 text-xs font-semibold text-slate-500 hover:text-primary');
                        if (button.textContent.trim() === 'Fail') {
                            button.className = 'inspection-toggle rounded bg-red-700 px-3 py-1 text-xs font-bold text-white shadow-sm';
                        } else if (button.textContent.trim() === 'Remediate') {
                            button.className = 'inspection-toggle rounded bg-[#ffdeaa] px-3 py-1 text-xs font-bold text-secondary shadow-sm';
                        } else {
                            button.className = 'inspection-toggle rounded bg-primary px-3 py-1 text-xs font-bold text-white shadow-sm';
                        }
                    });
                });
            });
        </script>
    @elseif ($pageKey === 'priority-rooms')
        @php
            $priorityQueue = $priorityRooms->values();
            $maintenanceHoldCount = $maintenanceQueue->count();
            $rushTurnoverCount = $cleaningRooms->count();
            $primaryPriorityRoom = $priorityQueue->first();
            $priorityCompletion = $primaryPriorityRoom
                ? ($primaryPriorityRoom->status === 'maintenance' ? 35 : 72)
                : 0;
            $dispatchTeams = [
                ['name' => 'Lead Attendant', 'initials' => 'LA', 'tone' => 'bg-secondary'],
                ['name' => 'Linen Runner', 'initials' => 'LR', 'tone' => 'bg-primary'],
                ['name' => 'QA Inspector', 'initials' => 'QA', 'tone' => 'bg-emerald-800'],
            ];
            $intercomNotes = [
                ['source' => 'Front Desk Station', 'body' => $primaryPriorityRoom ? 'Expedite Room '.$primaryPriorityRoom->room_number.' for priority release.' : 'No priority release requests currently open.', 'time' => now()->subMinutes(7)->format('h:i A')],
                ['source' => 'Housekeeping Lead', 'body' => $maintenanceHoldCount > 0 ? $maintenanceHoldCount.' maintenance hold '.Str::plural('room', $maintenanceHoldCount).' awaiting clearance.' : 'Maintenance queue is clear.', 'time' => now()->subMinutes(14)->format('h:i A')],
                ['source' => 'Linen Depot', 'body' => $rushTurnoverCount > 0 ? 'Rush cart prepared for '.$rushTurnoverCount.' active turnover '.Str::plural('unit', $rushTurnoverCount).'.' : 'Rush cart on standby.', 'time' => now()->subMinutes(22)->format('h:i A')],
            ];
        @endphp

        <section class="space-y-5">
            <div class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                <div class="grid gap-5 border-b border-line bg-primary p-5 text-white xl:grid-cols-[1fr_auto] xl:items-center">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.22em] text-[#ffdeaa]">
                            <span class="material-symbols-outlined text-base">priority_high</span>
                            Housekeeping Rush Readiness Command
                        </div>
                        <h3 class="font-serif text-3xl font-bold">Priority Rooms &amp; Expedited Turnovers</h3>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-200">Fast-track cleaning, maintenance release, and supervisor QA for rooms that need immediate housekeeping attention.</p>
                    </div>
                    <div class="rounded-lg border border-white/15 bg-white/10 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Priority Queue</p>
                        <p class="mt-1 font-serif text-4xl font-bold text-[#ffdeaa]">{{ $priorityQueue->count() }}</p>
                        <p class="text-xs text-slate-300">live {{ Str::plural('room', $priorityQueue->count()) }}</p>
                    </div>
                </div>

                <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Critical Holds', 'value' => $maintenanceHoldCount, 'icon' => 'engineering', 'tone' => 'text-red-700', 'bar' => min(100, $maintenanceHoldCount * 25)],
                        ['label' => 'Rush Turnovers', 'value' => $rushTurnoverCount, 'icon' => 'cleaning_services', 'tone' => 'text-orange-700', 'bar' => min(100, $rushTurnoverCount * 20)],
                        ['label' => 'Ready Rooms', 'value' => $availableRooms, 'icon' => 'verified', 'tone' => 'text-emerald-700', 'bar' => $totalRooms ? round(($availableRooms / max($totalRooms, 1)) * 100) : 0],
                        ['label' => 'Primary Progress', 'value' => $priorityCompletion.'%', 'icon' => 'speed', 'tone' => 'text-secondary', 'bar' => $priorityCompletion],
                    ] as $metric)
                        <div class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                                <span class="material-symbols-outlined {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                            </div>
                            <p class="mt-3 font-serif text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-line">
                                <div class="h-full rounded-full bg-secondary" style="width: {{ $metric['bar'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex gap-3">
                        <span class="material-symbols-outlined text-amber-700">emergency_home</span>
                        <div>
                            <p class="font-bold text-primary">Rush readiness alert</p>
                            <p class="text-sm text-slate-600">{{ $priorityQueue->count() }} {{ Str::plural('room', $priorityQueue->count()) }} currently require fast housekeeping action before they can return to front desk availability.</p>
                        </div>
                    </div>
                    <a href="{{ route('housekeeping.page', 'cleaning-tasks') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary/90">
                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">bolt</span>
                        Dispatch Backup Attendants
                    </a>
                </div>
            </div>

            <section class="grid gap-6 xl:grid-cols-12">
                <div class="space-y-5 xl:col-span-8">
                    <div class="rounded-lg border border-line bg-white shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-line p-5 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="font-serif text-2xl font-bold">Expedited Room Release Board</h3>
                                <p class="mt-1 text-sm text-slate-500">Rooms are pulled from live room statuses marked cleaning or maintenance.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('rooms.index', ['status' => 'maintenance']) }}" class="rounded-lg border border-line bg-[#f6f3ed] px-3 py-2 text-xs font-bold text-primary hover:bg-line">Maintenance Holds</a>
                                <a href="{{ route('rooms.index', ['status' => 'cleaning']) }}" class="rounded-lg border border-line bg-[#f6f3ed] px-3 py-2 text-xs font-bold text-primary hover:bg-line">Cleaning Queue</a>
                            </div>
                        </div>

                        <div class="space-y-4 p-5">
                            @forelse ($priorityQueue as $room)
                                @php
                                    $isMaintenance = $room->status === 'maintenance';
                                    $statusLabel = $isMaintenance ? 'Maintenance Hold' : 'Expedited Turnover';
                                    $statusClass = $isMaintenance ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-300 bg-amber-100 text-amber-900';
                                    $progress = $isMaintenance ? min(65, 20 + ($loop->iteration * 8)) : min(95, 55 + ($loop->iteration * 9));
                                    $team = $dispatchTeams[($loop->iteration - 1) % count($dispatchTeams)];
                                    $releaseWindow = now()->addMinutes($isMaintenance ? 45 + ($loop->iteration * 10) : 20 + ($loop->iteration * 7))->format('h:i A');
                                @endphp
                                <article class="rounded-lg border {{ $isMaintenance ? 'border-red-200' : 'border-amber-300' }} bg-[#fdfcfa] p-4 shadow-sm">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="min-w-0">
                                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                                <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider {{ $statusClass }}">{{ $statusLabel }}</span>
                                                @if ($loop->first)
                                                    <span class="rounded-full bg-primary px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#ffdeaa]">Top Priority</span>
                                                @endif
                                            </div>
                                            <h4 class="font-serif text-2xl font-bold text-primary">Room {{ $room->room_number }} - {{ $room->room_type }}</h4>
                                            <p class="mt-1 text-sm text-slate-600">{{ $room->description ?: ($isMaintenance ? 'Resolve maintenance clearance before front desk assignment.' : 'Turnover clean requested for immediate release.') }}</p>
                                        </div>
                                        <div class="rounded-lg border border-line bg-white px-4 py-3 text-right">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Target Release</p>
                                            <p class="mt-1 font-serif text-2xl font-bold text-secondary">{{ $releaseWindow }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                                        <div class="rounded-lg border border-line bg-white p-3">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Assigned Response</p>
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="{{ $team['tone'] }} flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold text-white">{{ $team['initials'] }}</span>
                                                <span class="text-sm font-bold text-primary">{{ $team['name'] }}</span>
                                            </div>
                                        </div>
                                        <div class="rounded-lg border border-line bg-white p-3">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Room Capacity</p>
                                            <p class="mt-2 font-bold text-primary">{{ $room->capacity }} {{ Str::plural('guest', $room->capacity) }}</p>
                                        </div>
                                        <div class="rounded-lg border border-line bg-white p-3">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Nightly Rate</p>
                                            <p class="mt-2 font-bold text-primary">PHP {{ number_format((float) $room->rate, 2) }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 rounded-lg border border-line bg-white p-3">
                                        <div class="mb-2 flex items-center justify-between text-xs">
                                            <span class="font-bold uppercase tracking-wider text-slate-500">Readiness Progress</span>
                                            <span class="font-bold text-secondary">{{ $progress }}%</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full {{ $isMaintenance ? 'bg-red-600' : 'bg-secondary' }}" style="width: {{ $progress }}%"></div></div>
                                        <div class="mt-3 grid gap-2 text-xs md:grid-cols-3">
                                            <span class="inline-flex items-center gap-1 rounded border border-line bg-[#f6f3ed] px-2 py-1"><span class="material-symbols-outlined text-sm text-secondary">task_alt</span>Linen cart staged</span>
                                            <span class="inline-flex items-center gap-1 rounded border border-line bg-[#f6f3ed] px-2 py-1"><span class="material-symbols-outlined text-sm text-secondary">task_alt</span>Supervisor QA required</span>
                                            <span class="inline-flex items-center gap-1 rounded border border-line bg-[#f6f3ed] px-2 py-1"><span class="material-symbols-outlined text-sm text-secondary">task_alt</span>Front desk handoff pending</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('housekeeping.page', 'linen-supplies') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-secondary px-3 py-2 text-xs font-bold text-secondary hover:bg-[#ffdeaa]/30">
                                            <span class="material-symbols-outlined text-base">local_laundry_service</span>
                                            Expedite Linen Cart
                                        </a>
                                        <a href="{{ route('housekeeping.page', 'notifications') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line px-3 py-2 text-xs font-bold text-primary hover:bg-[#f6f3ed]">
                                            <span class="material-symbols-outlined text-base">radio</span>
                                            Radio Lead
                                        </a>
                                        <a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white hover:bg-primary/90">
                                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">fact_check</span>
                                            Update Room Status
                                        </a>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-lg border border-dashed border-line bg-[#fdfcfa] px-5 py-14 text-center">
                                    <span class="material-symbols-outlined text-5xl text-slate-300">task_alt</span>
                                    <h4 class="mt-3 font-serif text-2xl font-bold text-primary">No priority rooms right now</h4>
                                    <p class="mt-1 text-sm text-slate-500">Rooms marked cleaning or maintenance will appear here automatically.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <aside class="space-y-5 xl:col-span-4">
                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-3">
                            <h3 class="flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-secondary">forum</span>Front Desk Intercom Stream</h3>
                            <span class="rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">Live</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach ($intercomNotes as $note)
                                <div class="rounded-lg border border-line bg-[#fdfcfa] p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-bold text-primary">{{ $note['source'] }}</p>
                                        <span class="text-[11px] text-slate-500">{{ $note['time'] }}</span>
                                    </div>
                                    <p class="mt-1 text-sm leading-5 text-slate-600">{{ $note['body'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-secondary">room_service</span>Expedite Depot Carts</h3>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between rounded-lg border border-line bg-[#f6f3ed] px-3 py-2"><span>Rush Linen Cart</span><strong class="text-emerald-700">{{ $rushTurnoverCount > 0 ? 'Ready' : 'Standby' }}</strong></div>
                            <div class="flex justify-between rounded-lg border border-line bg-[#f6f3ed] px-3 py-2"><span>Maintenance Kit</span><strong class="{{ $maintenanceHoldCount > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $maintenanceHoldCount > 0 ? 'Dispatch' : 'Clear' }}</strong></div>
                            <div class="flex justify-between rounded-lg border border-line bg-[#f6f3ed] px-3 py-2"><span>QA Tablet</span><strong class="text-secondary">Synced</strong></div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary p-5 text-white shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]"><span class="material-symbols-outlined">assignment_late</span>Priority SLA Summary</h3>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[11px] uppercase tracking-wider text-slate-300">Open</p>
                                <p class="font-serif text-3xl font-bold">{{ $priorityQueue->count() }}</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[11px] uppercase tracking-wider text-slate-300">Cleared Today</p>
                                <p class="font-serif text-3xl font-bold">{{ $completedTasks->count() }}</p>
                            </div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'room-inspection') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-3 py-2 text-xs font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">rule</span>
                            Open Supervisor Inspection
                        </a>
                    </div>
                </aside>
            </section>
        </section>
    @elseif ($pageKey === 'cleaning-tasks')
        @php
            $cleaningQueue = $cleaningRooms->values();
            $rushCount = $priorityRooms->where('status', 'maintenance')->count();
            $activeCleaningCount = $cleaningQueue->count();
            $qaQueue = $completedTasks->filter(fn ($log) => in_array($log->status, ['cleaning', 'available'], true))->count();
            $shiftTotal = max($activeCleaningCount + $completedTasks->count(), 1);
            $completionRate = min(100, round(($completedTasks->count() / $shiftTotal) * 100));
            $attendants = [
                ['name' => 'Ronald Chen', 'initials' => 'RC', 'room' => $cleaningQueue->get(0)?->room_number, 'done' => min(5, $completedTasks->count() + 1), 'quota' => 6, 'tone' => 'bg-secondary'],
                ['name' => 'Elena Bautista', 'initials' => 'EB', 'room' => $cleaningQueue->get(1)?->room_number, 'done' => min(4, $completedTasks->count()), 'quota' => 6, 'tone' => 'bg-slate-700'],
                ['name' => 'Mateo Gutierrez', 'initials' => 'MG', 'room' => $cleaningQueue->get(2)?->room_number, 'done' => min(6, $completedTasks->count() + $availableRooms), 'quota' => 6, 'tone' => 'bg-emerald-800'],
            ];
        @endphp

        <section class="space-y-5">
            <div class="rounded-lg border border-line bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-line bg-[#f6f3ed] p-5 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.22em] text-secondary">
                            <span class="h-2.5 w-2.5 rounded-full bg-secondary"></span>
                            Subic Bay Operational Desk
                        </div>
                        <h3 class="font-serif text-3xl font-bold text-primary">Unit Turnover &amp; Cleaning Tasks Queue</h3>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Real-time room cleaning roster, turnover stages, housekeeping assignments, and sanitization progress from live room inventory.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ([['Main Wing', $totalRooms], ['Ocean Wing', $occupiedRooms], ['Executive Villas', $maintenanceQueue->count()]] as [$wing, $count])
                            <button type="button" class="{{ $loop->first ? 'bg-primary text-white' : 'bg-white text-slate-600 hover:bg-line' }} inline-flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-xs font-bold">
                                {{ $wing }}
                                <span class="{{ $loop->first ? 'bg-[#ffdeaa] text-primary' : 'bg-[#f0eee8] text-slate-600' }} rounded px-1.5 py-0.5 text-[10px]">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Active Cleaning Units', 'value' => $activeCleaningCount, 'icon' => 'cleaning_services', 'tone' => 'text-orange-700'],
                        ['label' => 'Rush / Priority Rooms', 'value' => $rushCount, 'icon' => 'notification_important', 'tone' => 'text-red-700'],
                        ['label' => 'Pending QA Review', 'value' => $qaQueue, 'icon' => 'fact_check', 'tone' => 'text-secondary'],
                        ['label' => 'Shift Completion', 'value' => $completionRate . '%', 'icon' => 'speed', 'tone' => 'text-emerald-700'],
                    ] as $metric)
                        <div class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                                <span class="material-symbols-outlined {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                            </div>
                            <p class="mt-3 font-serif text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <section class="grid gap-6 xl:grid-cols-12">
                <div class="space-y-5 xl:col-span-8">
                    <div class="rounded-lg border border-line bg-white shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-line p-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="font-serif text-2xl font-bold">Live Cleaning Task Board</h3>
                                <p class="mt-1 text-sm text-slate-500">Showing {{ $activeCleaningCount }} active room turnover {{ Str::plural('task', $activeCleaningCount) }} from the rooms table.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('rooms.index', ['status' => 'cleaning']) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-[#f6f3ed] px-3 py-2 text-xs font-bold text-primary hover:bg-line">
                                    <span class="material-symbols-outlined text-base">filter_alt</span>
                                    Cleaning Only
                                </a>
                                <a href="{{ route('housekeeping.page', 'priority-rooms') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white hover:bg-primary/90">
                                    <span class="material-symbols-outlined text-base text-[#ffdeaa]">priority_high</span>
                                    Priority Rooms
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[920px] text-left text-sm">
                                <thead class="bg-[#f0eee8] text-xs font-bold uppercase tracking-wider text-slate-600">
                                    <tr>
                                        <th class="px-4 py-3">Room</th>
                                        <th class="px-3 py-3">Room Type</th>
                                        <th class="px-3 py-3">Task Type</th>
                                        <th class="px-3 py-3">Assigned To</th>
                                        <th class="px-3 py-3">Stage</th>
                                        <th class="px-3 py-3">Progress</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @forelse ($cleaningQueue as $room)
                                        @php
                                            $progress = min(95, 25 + ($loop->iteration * 15));
                                            $stage = $loop->first ? 'In Progress' : ($loop->iteration % 3 === 0 ? 'Pending Inspection' : 'Queued');
                                            $stageClass = $stage === 'In Progress'
                                                ? 'border-amber-300 bg-amber-100 text-amber-900'
                                                : ($stage === 'Pending Inspection' ? 'border-purple-200 bg-purple-50 text-purple-900' : 'border-line bg-[#f6f3ed] text-slate-600');
                                            $attendant = $attendants[$loop->index % count($attendants)];
                                        @endphp
                                        <tr class="bg-white/80 transition hover:bg-[#f6f3ed]">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-8 w-1.5 rounded-full {{ $loop->first ? 'bg-orange-500' : 'bg-secondary' }}"></div>
                                                    <div>
                                                        <p class="font-bold text-primary">Room {{ $room->room_number }}</p>
                                                        <p class="text-xs text-slate-500">Floor {{ substr($room->room_number, 0, 1) ?: 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">
                                                <p class="font-semibold text-primary">{{ $room->room_type }}</p>
                                                <p class="text-xs text-slate-500">{{ $room->description ?: 'Vacant dirty turnover' }}</p>
                                            </td>
                                            <td class="px-3 py-3">
                                                <span class="inline-flex items-center gap-1 font-bold text-primary">
                                                    <span class="material-symbols-outlined text-sm text-secondary">sanitizer</span>
                                                    Full Turnover
                                                </span>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="{{ $attendant['tone'] }} flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold text-white">{{ $attendant['initials'] }}</span>
                                                    <div>
                                                        <p class="font-semibold leading-none text-primary">{{ $attendant['name'] }}</p>
                                                        <p class="mt-1 text-[10px] text-slate-500">{{ $attendant['room'] ? 'Room ' . $attendant['room'] . ' queue' : 'Available / next' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $stageClass }}">{{ $stage }}</span>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="w-28">
                                                    <div class="mb-1 flex justify-between text-[11px] text-slate-500"><span>{{ $progress }}%</span><span>SLA</span></div>
                                                    <div class="h-1.5 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full bg-secondary" style="width: {{ $progress }}%"></div></div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('rooms.show', $room) }}" class="rounded p-1.5 text-slate-500 hover:bg-[#f6f3ed] hover:text-primary" title="View checklist"><span class="material-symbols-outlined text-base">checklist</span></a>
                                                    <a href="{{ route('rooms.edit', $room) }}" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white hover:bg-primary/90">Update</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">No rooms are currently marked for cleaning. Rooms checked out by front desk will appear here automatically.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-line bg-[#f6f3ed] px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                            <span>Showing <strong class="text-primary">{{ $activeCleaningCount }}</strong> active cleaning {{ Str::plural('unit', $activeCleaningCount) }}</span>
                            <span>Completed today: <strong class="text-secondary">{{ $completedTasks->count() }}</strong></span>
                        </div>
                    </div>
                </div>

                <aside class="space-y-5 xl:col-span-4">
                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-3">
                            <h3 class="flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-secondary">badge</span>Attendant Workload</h3>
                            <span class="rounded bg-[#ffdeaa]/60 px-2 py-0.5 text-[11px] font-bold text-secondary">{{ count($attendants) }} on Duty</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach ($attendants as $attendant)
                                @php
                                    $workload = min(100, round(($attendant['done'] / max($attendant['quota'], 1)) * 100));
                                @endphp
                                <div class="rounded-lg border border-line bg-[#fdfcfa] p-3">
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="{{ $attendant['tone'] }} flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-bold text-white">{{ $attendant['initials'] }}</span>
                                            <span class="text-xs font-bold text-primary">{{ $attendant['name'] }}</span>
                                        </div>
                                        <span class="rounded bg-[#f6f3ed] px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $attendant['room'] ? 'Room ' . $attendant['room'] : 'Available' }}</span>
                                    </div>
                                    <div class="mb-1 flex justify-between text-[11px] text-slate-500"><span>Quota: {{ $attendant['done'] }} of {{ $attendant['quota'] }}</span><span>{{ $workload }}%</span></div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full bg-secondary" style="width: {{ $workload }}%"></div></div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2.5 text-xs font-bold text-white hover:bg-primary/90">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">shuffle</span>
                            Auto-Balance Attendant Loads
                        </button>
                    </div>

                    <div class="rounded-lg border border-amber-300 bg-amber-50/50 p-5 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-bold text-primary">Linen &amp; Stock Alert</h3>
                            <span class="material-symbols-outlined text-amber-700">warning</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between rounded border border-line bg-white px-3 py-2"><span>Ocean Wing Duvet Covers</span><strong class="text-red-700">{{ max(0, 8 - $activeCleaningCount) }} sets left</strong></div>
                            <div class="flex justify-between rounded border border-line bg-white px-3 py-2"><span>Bath Towels</span><strong class="text-emerald-700">{{ 80 + $availableRooms }} sets</strong></div>
                            <div class="flex justify-between rounded border border-line bg-white px-3 py-2"><span>Amenity Kits</span><strong class="text-secondary">Restock by 13:00</strong></div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'linen-supplies') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-secondary px-3 py-2 text-xs font-bold text-secondary hover:bg-[#ffdeaa]/30">
                            <span class="material-symbols-outlined text-base">inventory_2</span>
                            Open Linen & Supplies
                        </a>
                    </div>

                    <div class="rounded-lg bg-primary p-4 text-white shadow-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]"><span class="material-symbols-outlined">radio</span>Housekeeping Channel 3</h3>
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-xs leading-5 text-slate-300">Broadcast coordination notes across all active attendants on Shift 1.</p>
                        <a href="{{ route('housekeeping.page', 'notifications') }}" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-3 py-2 text-xs font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">campaign</span>
                            Open Notifications
                        </a>
                    </div>
                </aside>
            </section>
        </section>
    @elseif ($pageKey === 'guest-requests')
        @php
            $requestQueue = $guestRequestRooms->values();
            $attendants = [
                ['name' => 'Janice Alcantara', 'initials' => 'JA', 'tone' => 'bg-secondary'],
                ['name' => 'Carlos Reyes', 'initials' => 'CR', 'tone' => 'bg-primary'],
                ['name' => 'Elena Dimaculangan', 'initials' => 'ED', 'tone' => 'bg-emerald-800'],
            ];
            $requestTemplates = [
                ['priority' => 'Critical Priority', 'class' => 'border-red-200 bg-red-50 text-red-800', 'accent' => 'bg-red-600', 'sla' => '10 mins', 'remaining' => '05:40', 'item' => 'Extra Percale Duvet', 'detail' => 'King size linen set with hypoallergenic pillows', 'icon' => 'bed', 'source' => 'Front Desk Concierge'],
                ['priority' => 'Moderate Priority', 'class' => 'border-amber-300 bg-amber-100 text-amber-900', 'accent' => 'bg-secondary', 'sla' => '15 mins', 'remaining' => '10:15', 'item' => 'Bathrobes & Premium Towels', 'detail' => 'Two robe sets with fresh bath towel bundle', 'icon' => 'dry_cleaning', 'source' => 'Guest Mobile Request'],
                ['priority' => 'Standard Request', 'class' => 'border-line bg-[#f6f3ed] text-slate-700', 'accent' => 'bg-slate-400', 'sla' => '25 mins', 'remaining' => '18:30', 'item' => 'Toiletry Amenity Pack', 'detail' => 'Dental kits, shaving kit, slippers, and bottled water', 'icon' => 'styler', 'source' => 'In-Room Tablet'],
            ];
            $activeRequests = $requestQueue->count();
            $runnerCount = min($activeRequests, count($attendants));
        @endphp

        <section class="space-y-6">
            <div class="flex flex-col gap-4 border-b border-line pb-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-[0.22em] text-secondary">Housekeeping Command Terminal</span>
                        <span class="h-1 w-1 rounded-full bg-line"></span>
                        <span class="rounded-full border border-amber-300 bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-800">Live SLA Active</span>
                    </div>
                    <h3 class="font-serif text-3xl font-bold text-primary">Guest Service Requests &amp; In-Room Amenities Delivery</h3>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Live dispatch terminal for guest-requested linens, toiletries, extra duvets, and amenity deliveries. Occupied rooms are shown as eligible request channels.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['All', 'Urgent', 'Moderate', 'Standard'] as $filter)
                        <button type="button" class="guest-request-filter rounded-lg border border-line px-3 py-2 text-xs font-bold {{ $loop->first ? 'bg-primary text-white' : 'bg-white text-slate-600 hover:bg-[#f6f3ed]' }}" data-filter="{{ strtolower($filter) }}">
                            {{ $filter }}
                        </button>
                    @endforeach
                    <a href="{{ route('housekeeping.page', 'notifications') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-primary/90">
                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">add</span>
                        Log Inbound Guest Request
                    </a>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Active Requests', 'value' => $activeRequests, 'caption' => 'Queued from occupied rooms', 'icon' => 'pending_actions', 'tone' => 'text-primary', 'bar' => 'bg-primary'],
                    ['label' => 'SLA Breach Risk', 'value' => $maintenanceQueue->count(), 'caption' => 'maintenance-linked risk', 'icon' => 'timer', 'tone' => 'text-secondary', 'bar' => 'bg-secondary'],
                    ['label' => 'Most Requested Item', 'value' => 'Linen', 'caption' => 'Extra duvets and towels', 'icon' => 'bed', 'tone' => 'text-slate-700', 'bar' => 'bg-slate-500'],
                    ['label' => 'Attendants on Run', 'value' => $runnerCount, 'caption' => 'dedicated runners active', 'icon' => 'directions_walk', 'tone' => 'text-emerald-700', 'bar' => 'bg-emerald-600'],
                ] as $metric)
                    <div class="relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="absolute left-0 right-0 top-0 h-1 {{ $metric['bar'] }}"></div>
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                            <span class="material-symbols-outlined {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                        </div>
                        <p class="mt-3 font-serif text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $metric['caption'] }}</p>
                    </div>
                @endforeach
            </section>

            <section class="space-y-4">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-serif text-2xl font-bold text-primary">Live Request Dispatch Board</h3>
                        <span class="rounded-full bg-primary px-2.5 py-1 text-xs font-bold text-white">{{ $activeRequests }} In Transit / Queued</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <span class="material-symbols-outlined text-base text-emerald-600">sensors</span>
                        Subic Bay IoT Dispatch Relay Connected
                    </div>
                </div>

                @forelse ($requestQueue as $room)
                    @php
                        $template = $requestTemplates[($loop->iteration - 1) % count($requestTemplates)];
                        $attendant = $attendants[($loop->iteration - 1) % count($attendants)];
                        $ticketNumber = 'GR-' . str_pad((string) (4090 + $loop->iteration), 4, '0', STR_PAD_LEFT);
                        $filterTag = $loop->first ? 'urgent' : ($loop->iteration % 2 === 0 ? 'moderate' : 'standard');
                    @endphp
                    <article class="guest-request-card relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm transition hover:shadow-md" data-filter="{{ $filterTag }}">
                        <div class="absolute left-0 right-0 top-0 h-1 {{ $template['accent'] }}"></div>
                        <div class="grid gap-5 lg:grid-cols-12 lg:items-center">
                            <div class="border-b border-line pb-4 lg:col-span-3 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider {{ $template['class'] }}">{{ $template['priority'] }}</span>
                                    <span class="text-xs font-semibold text-slate-500">#{{ $ticketNumber }}</span>
                                </div>
                                <h4 class="mt-3 font-serif text-2xl font-bold text-primary">Room {{ $room->room_number }}</h4>
                                <p class="text-xs font-bold text-secondary">{{ $room->room_type }} • {{ $room->capacity }} {{ Str::plural('guest', $room->capacity) }}</p>
                                <div class="mt-3 flex items-center gap-2 text-sm text-primary">
                                    <span class="material-symbols-outlined text-base text-slate-500">person</span>
                                    <span class="font-semibold">In-house guest</span>
                                    @if ($loop->first)
                                        <span class="rounded bg-[#ffdeaa]/70 px-1.5 py-0.5 text-[10px] font-bold text-secondary">VIP</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-[11px] italic text-slate-500">Requested via {{ $template['source'] }}</p>
                            </div>

                            <div class="border-b border-line pb-4 lg:col-span-4 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Amenity Manifest</p>
                                <div class="mt-3 flex items-start gap-2">
                                    <span class="material-symbols-outlined mt-0.5 text-secondary">{{ $template['icon'] }}</span>
                                    <div>
                                        <p class="font-bold text-primary">{{ $template['item'] }}</p>
                                        <p class="text-sm text-slate-600">{{ $template['detail'] }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 rounded-lg border border-line bg-[#f6f3ed] p-2 text-xs text-slate-600">
                                    <span class="font-bold text-primary">Guest note:</span> Please deliver quietly and confirm completion with front desk.
                                </div>
                            </div>

                            <div class="border-b border-line pb-4 lg:col-span-2 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-4">
                                <p class="flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    <span class="material-symbols-outlined text-sm text-secondary">timer</span>
                                    SLA Target: {{ $template['sla'] }}
                                </p>
                                <div class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1 text-amber-900">
                                    <span class="font-serif text-xl font-bold">{{ $template['remaining'] }}</span>
                                    <span class="text-[10px] font-bold uppercase">remaining</span>
                                </div>
                                <div class="mt-3">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Current Status</p>
                                    <p class="mt-1 flex items-center gap-1.5 text-xs font-bold text-primary">
                                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                        Dispatched from Linen Depot
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500">Assigned: <strong class="text-primary">{{ $attendant['name'] }}</strong></p>
                                </div>
                            </div>

                            <div class="lg:col-span-3">
                                <button type="button" class="guest-request-deliver inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                                    <span class="material-symbols-outlined text-base text-[#ffdeaa]">task_alt</span>
                                    Mark Delivered
                                </button>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <a href="{{ route('housekeeping.page', 'notifications') }}" class="inline-flex items-center justify-center gap-1 rounded-lg border border-secondary px-2 py-1.5 text-xs font-bold text-secondary hover:bg-[#ffdeaa]/30">
                                        <span class="material-symbols-outlined text-base">phone_in_talk</span>
                                        Call Runner
                                    </a>
                                    <a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center justify-center gap-1 rounded-lg border border-line px-2 py-1.5 text-xs font-bold text-slate-600 hover:bg-[#f6f3ed] hover:text-primary">
                                        <span class="material-symbols-outlined text-base">swap_horiz</span>
                                        Reassign
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-line bg-white px-5 py-14 text-center shadow-sm">
                        <span class="material-symbols-outlined text-5xl text-slate-300">room_service</span>
                        <h4 class="mt-3 font-serif text-2xl font-bold text-primary">No active guest requests</h4>
                        <p class="mt-1 text-sm text-slate-500">Occupied rooms will appear here as request channels for in-room amenities and service follow-up.</p>
                    </div>
                @endforelse
            </section>

            <section class="rounded-lg border border-line bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-line pb-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="font-serif text-2xl font-bold text-primary">Completed Requests Log &amp; Confirmation Signatures</h3>
                        <p class="mt-1 text-sm text-slate-500">Recent room updates are used as the housekeeping confirmation ledger until request records are separated into their own table.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="rounded-lg border border-line px-3 py-2 text-xs font-bold text-primary hover:bg-[#f6f3ed]">Export Folio CSV</button>
                        <button type="button" class="rounded-lg border border-line px-3 py-2 text-xs font-bold text-primary hover:bg-[#f6f3ed]">Columns</button>
                    </div>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="bg-[#f0eee8] text-xs font-bold uppercase tracking-wider text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Ticket / Room</th>
                                <th class="px-3 py-3">Delivered Amenities</th>
                                <th class="px-3 py-3">Runner</th>
                                <th class="px-3 py-3">Delivered At</th>
                                <th class="px-4 py-3 text-right">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($recentRoomLogs->take(4) as $log)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-primary">Room {{ $log->room?->room_number ?? 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">#GR-C{{ str_pad((string) $loop->iteration, 3, '0', STR_PAD_LEFT) }}</p>
                                    </td>
                                    <td class="px-3 py-3">Housekeeping status confirmation</td>
                                    <td class="px-3 py-3">{{ $attendants[($loop->iteration - 1) % count($attendants)]['name'] }}</td>
                                    <td class="px-3 py-3">{{ optional($log->changed_at)->format('M d, h:i A') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ $log->room ? route('rooms.show', $log->room) : route('housekeeping.page', 'notifications') }}" class="rounded-lg border border-line px-3 py-1.5 text-xs font-bold text-primary hover:bg-[#f6f3ed]">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">No completed guest request confirmations have been recorded today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const filterButtons = document.querySelectorAll('.guest-request-filter');
                    const cards = document.querySelectorAll('.guest-request-card');

                    filterButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            const filter = button.dataset.filter;
                            filterButtons.forEach((item) => {
                                item.className = 'guest-request-filter rounded-lg border border-line px-3 py-2 text-xs font-bold bg-white text-slate-600 hover:bg-[#f6f3ed]';
                            });
                            button.className = 'guest-request-filter rounded-lg border border-line px-3 py-2 text-xs font-bold bg-primary text-white';
                            cards.forEach((card) => {
                                card.classList.toggle('hidden', filter !== 'all' && card.dataset.filter !== filter);
                            });
                        });
                    });

                    document.querySelectorAll('.guest-request-deliver').forEach((button) => {
                        button.addEventListener('click', () => {
                            button.innerHTML = '<span class="material-symbols-outlined text-base text-emerald-200">verified</span> Delivered';
                            button.className = 'guest-request-deliver inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3 py-2.5 text-sm font-bold text-white shadow-sm';
                            button.disabled = true;
                        });
                    });
                });
            </script>
        </section>
    @elseif ($pageKey === 'special-cleaning')
        @php
            $specialCleaningRooms = $priorityRooms->values();
            $inTreatmentCount = $maintenanceQueue->count();
            $scheduledTreatmentCount = $specialCleaningRooms->count();
            $completedThisWeek = $completedTasks->count();
            $protocols = [
                ['label' => 'White Glove Ozone Deodorization', 'apparatus' => 'BioZone Pro UV/Ozone Generator', 'trigger' => 'Post-occupancy odor remediation', 'status' => 'Room Sealed - Safety Lock Active', 'class' => 'border-red-200 bg-red-50 text-red-800', 'accent' => 'bg-gradient-to-r from-red-600 via-secondary to-primary', 'progress' => 63],
                ['label' => 'Carpet & Upholstery Hot-Water Extraction', 'apparatus' => 'Deep Extractor + Citrus pH Cleaner', 'trigger' => 'Scheduled deep-clean cycle', 'status' => 'Drying - Air Movers Active', 'class' => 'border-amber-300 bg-amber-100 text-amber-900', 'accent' => 'bg-secondary', 'progress' => 48],
                ['label' => 'Post-Maintenance Allergen Shield Misting', 'apparatus' => 'BioCide Hypoallergenic Mist', 'trigger' => 'Engineering handoff sanitization', 'status' => 'Ready for Final Air Quality Inspection', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'accent' => 'bg-emerald-700', 'progress' => 86],
            ];
            $specialists = ['Danilo Ramos', 'Teresa Mendoza', 'Manuel Quezon', 'Elena Dimaculangan'];
        @endphp

        <section class="space-y-6">
            <div class="flex flex-col gap-4 border-b border-line pb-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary">
                        <span>Housekeeping Oversight</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="text-slate-500">Service Requests</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="text-primary">Special Cleaning &amp; Sanitization</span>
                    </div>
                    <h3 class="font-serif text-3xl font-bold text-primary">Specialized Cleaning &amp; Environmental Sanitization Protocol</h3>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Management of ozone treatments, upholstery extraction, hypoallergenic misting, post-maintenance deep scrubs, and room release certification.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('housekeeping.page', 'linen-supplies') }}" class="inline-flex items-center gap-2 rounded-lg border border-secondary bg-white px-4 py-2.5 text-xs font-bold text-primary shadow-sm hover:bg-[#f6f3ed]">
                        <span class="material-symbols-outlined text-base text-secondary">verified</span>
                        Chemical Protocol Standards
                    </a>
                    <a href="{{ route('rooms.index', ['status' => 'maintenance']) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-primary/90">
                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">add_circle</span>
                        Schedule Specialized Treatment
                    </a>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Scheduled Treatments', 'value' => $scheduledTreatmentCount, 'caption' => 'rooms in cleaning or maintenance queue', 'icon' => 'event_note', 'tone' => 'text-primary', 'bar' => 'bg-primary'],
                    ['label' => 'In-Treatment Locked', 'value' => $inTreatmentCount, 'caption' => 'off-market maintenance holds', 'icon' => 'lock_clock', 'tone' => 'text-red-700', 'bar' => 'bg-red-600'],
                    ['label' => 'Completed Today', 'value' => $completedThisWeek, 'caption' => 'room status confirmations', 'icon' => 'task_alt', 'tone' => 'text-secondary', 'bar' => 'bg-secondary'],
                    ['label' => 'Air Quality Index', 'value' => $scheduledTreatmentCount > 0 ? '99.8%' : 'Clear', 'caption' => 'Subic CleanStandard sensor sync', 'icon' => 'air', 'tone' => 'text-emerald-700', 'bar' => 'bg-emerald-700'],
                ] as $metric)
                    <div class="relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="absolute left-0 right-0 top-0 h-1 {{ $metric['bar'] }}"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</span>
                            <span class="material-symbols-outlined {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                        </div>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="font-serif text-4xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</span>
                        </div>
                        <p class="mt-3 border-t border-line pt-3 text-xs text-slate-500">{{ $metric['caption'] }}</p>
                    </div>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <div class="space-y-5 xl:col-span-8">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-serif text-2xl font-bold text-primary">Active Specialized Cleanings</h3>
                            <span class="rounded-full bg-primary px-2.5 py-1 text-xs font-bold text-[#ffdeaa]">Live Chamber Feeds</span>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            @foreach (['All Protocols', 'Ozone Only', 'Bio-Extract'] as $filter)
                                <button type="button" class="{{ $loop->first ? 'bg-white text-primary' : 'text-slate-500 hover:text-primary' }} rounded border {{ $loop->first ? 'border-line' : 'border-transparent' }} px-3 py-1.5 font-bold">{{ $filter }}</button>
                            @endforeach
                        </div>
                    </div>

                    @forelse ($specialCleaningRooms as $room)
                        @php
                            $protocol = $protocols[($loop->iteration - 1) % count($protocols)];
                            $specialist = $specialists[($loop->iteration - 1) % count($specialists)];
                            $releaseTime = now()->addMinutes(40 + ($loop->iteration * 18))->format('h:i A');
                        @endphp
                        <article class="relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                            <div class="absolute left-0 right-0 top-0 h-1 {{ $protocol['accent'] }}"></div>
                            <div class="flex flex-col gap-4 border-b border-line pb-4 md:flex-row md:items-start md:justify-between">
                                <div class="flex gap-3">
                                    <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-primary text-[#ffdeaa] shadow-inner">
                                        <span class="text-[10px] font-bold uppercase">{{ $room->room_type === 'Suite' ? 'Suite' : 'Room' }}</span>
                                        <span class="font-bold leading-none">{{ $room->room_number }}</span>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-serif text-2xl font-bold text-primary">Room {{ $room->room_number }} - {{ $room->room_type }}</h4>
                                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase {{ $protocol['class'] }}">{{ ucfirst($room->status) }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-600"><strong class="text-primary">Protocol:</strong> {{ $protocol['label'] }}</p>
                                    </div>
                                </div>
                                <div class="text-left md:text-right">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold {{ $protocol['class'] }}">
                                        <span class="material-symbols-outlined text-sm">lock</span>
                                        {{ $protocol['status'] }}
                                    </span>
                                    <p class="mt-1 text-[11px] text-slate-500">Expected release: {{ $releaseTime }} PHT</p>
                                </div>
                            </div>

                            <div class="my-4 grid gap-3 md:grid-cols-3">
                                <div class="rounded-lg border border-line bg-[#f6f3ed] p-3">
                                    <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500"><span class="material-symbols-outlined text-sm text-secondary">assignment_late</span>Remediation Trigger</p>
                                    <p class="mt-2 text-sm font-bold text-primary">{{ $protocol['trigger'] }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500">Live room status: {{ ucfirst($room->status) }}</p>
                                </div>
                                <div class="rounded-lg border border-line bg-[#f6f3ed] p-3">
                                    <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500"><span class="material-symbols-outlined text-sm text-secondary">precision_manufacturing</span>Hardware Apparatus</p>
                                    <p class="mt-2 text-sm font-bold text-primary">{{ $protocol['apparatus'] }}</p>
                                    <p class="mt-1 text-[11px] text-emerald-700">Telemetry: output optimal</p>
                                </div>
                                <div class="rounded-lg border border-line bg-[#f6f3ed] p-3">
                                    <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500"><span class="material-symbols-outlined text-sm text-secondary">badge</span>Assigned Specialist</p>
                                    <p class="mt-2 text-sm font-bold text-primary">{{ $specialist }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500">Certified deep-clean technician</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5 text-slate-600"><span class="material-symbols-outlined text-sm text-secondary">timelapse</span>Chamber cycle progress</span>
                                    <span class="font-bold text-secondary">{{ $protocol['progress'] }}% Complete</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-line">
                                    <div class="h-full rounded-full bg-secondary" style="width: {{ $protocol['progress'] }}%"></div>
                                </div>
                                <div class="flex flex-col gap-2 pt-1 text-[11px] text-slate-500 md:flex-row md:items-center md:justify-between">
                                    <span>Automatic flush target: <strong class="text-primary">{{ $releaseTime }} PHT</strong></span>
                                    <span>Target particulate threshold: <strong class="text-primary">&lt; 0.04 ppm</strong></span>
                                    <a href="{{ route('rooms.edit', $room) }}" class="font-bold text-secondary hover:underline">Update Room Status</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-line bg-white px-5 py-14 text-center shadow-sm">
                            <span class="material-symbols-outlined text-5xl text-slate-300">cleaning_services</span>
                            <h4 class="mt-3 font-serif text-2xl font-bold text-primary">No special cleaning treatments scheduled</h4>
                            <p class="mt-1 text-sm text-slate-500">Rooms marked cleaning or maintenance will appear here for specialized sanitization review.</p>
                        </div>
                    @endforelse
                </div>

                <aside class="space-y-5 xl:col-span-4">
                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-3">
                            <h3 class="flex items-center gap-2 font-bold text-primary"><span class="material-symbols-outlined text-secondary">health_and_safety</span>Safety &amp; Compliance</h3>
                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">SBMA Verified</span>
                        </div>
                        <div class="mt-4 space-y-2">
                            @foreach ([
                                ['name' => 'Ozone Gas In-Situ', 'meta' => 'Threshold: 0.10 ppm', 'icon' => 'science'],
                                ['name' => 'BioCide AllergenShield IV', 'meta' => 'Hypoallergenic misting agent', 'icon' => 'sanitizer'],
                                ['name' => 'EcoGentle Upholstery Extract', 'meta' => 'pH 6.8 citrus formulation', 'icon' => 'water_drop'],
                            ] as $binder)
                                <div class="flex items-center justify-between rounded-lg border border-line bg-[#f6f3ed] p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-secondary">{{ $binder['icon'] }}</span>
                                        <div>
                                            <p class="text-sm font-bold text-primary">{{ $binder['name'] }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $binder['meta'] }}</p>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-400">picture_as_pdf</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-primary"><span class="material-symbols-outlined text-secondary">fact_check</span>Shift PPE Inspection Log</h3>
                        <div class="mt-4 space-y-3">
                            @foreach ([
                                ['item' => 'N95 respirators sealed', 'state' => 'Passed'],
                                ['item' => 'Chemical gloves available', 'state' => 'Passed'],
                                ['item' => 'Air quality meter calibrated', 'state' => 'Due at shift end'],
                            ] as $ppe)
                                <div class="flex items-center justify-between rounded-lg border border-line bg-[#fdfcfa] px-3 py-2 text-sm">
                                    <span>{{ $ppe['item'] }}</span>
                                    <strong class="{{ $ppe['state'] === 'Passed' ? 'text-emerald-700' : 'text-secondary' }}">{{ $ppe['state'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary p-5 text-white shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]"><span class="material-symbols-outlined">sensors</span>Environmental Telemetry</h3>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[11px] uppercase tracking-wider text-slate-300">Humidity</p>
                                <p class="font-serif text-3xl font-bold">42%</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[11px] uppercase tracking-wider text-slate-300">PM2.5</p>
                                <p class="font-serif text-3xl font-bold">0.04</p>
                            </div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'room-inspection') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-3 py-2 text-xs font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">verified</span>
                            Open Final Inspection
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'maintenance-requests')
        @php
            $maintenanceWorkOrders = $maintenanceQueue->isNotEmpty()
                ? $maintenanceQueue->take(3)->values()
                : $allRooms->take(3)->values();
            $activeTickets = $maintenanceQueue->count();
            $visibleTickets = $maintenanceWorkOrders->count();
            $criticalRooms = $maintenanceQueue->filter(fn ($room) => $room->status === 'maintenance')->count();
            $occupiedRoom = $allRooms->firstWhere('status', 'occupied');
            $availableCount = (int) $availableRooms;
            $occupiedCount = (int) $occupiedRooms;
            $issueCatalog = [
                ['title' => 'HVAC Freon Compressor Leak / Thermostat Drift', 'icon' => 'ac_unit', 'category' => 'HVAC & Climate', 'note' => 'Ambient room temperature and air-conditioning readings need engineering verification before the room can be released.'],
                ['title' => 'IoT Deadbolt Latch Mismatch', 'icon' => 'lock_clock', 'category' => 'IoT Lock & Keycard', 'note' => 'Door lock, RFID encoder, or latch telemetry requires a maintenance inspection and housekeeping clearance.'],
                ['title' => 'Balcony Sliding Glass Track Jammed', 'icon' => 'door_sliding', 'category' => 'Furniture & Balcony', 'note' => 'Track, handle, or balcony hardware needs facilities follow-up before assigning the room to arriving guests.'],
            ];
            $engineers = ['Engr. Danilo Ramos', 'Engr. Patricia Velasco', 'Engr. Carlo Reyes'];
        @endphp
        <section class="space-y-6">
            <div class="relative overflow-hidden rounded-lg border border-line bg-primary p-6 text-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-secondary"></div>
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-secondary px-3 py-1 text-[11px] font-bold uppercase tracking-[0.28em] text-white">Maintenance Requests</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-200">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Real-time Facilities Synced
                            </span>
                        </div>
                        <h2 class="font-serif text-4xl font-bold leading-tight text-white">Maintenance Requests &amp; Engineering Dispatch Queue</h2>
                        <p class="text-sm leading-6 text-slate-200">Defects, hardware anomalies, and out-of-order rooms reported for facilities and engineering follow-up.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('housekeeping.page', 'room-status') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/15">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">grid_view</span>
                            Open Room Status Matrix
                        </a>
                        <a href="{{ route('housekeeping.page', 'report-issue') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">assignment_add</span>
                            Report Defect / Work Order
                        </a>
                        <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                            <span class="material-symbols-outlined text-base">bolt</span>
                            Emergency Dispatch
                        </a>
                    </div>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    ['label' => 'Total Rooms', 'value' => $totalRooms, 'caption' => 'Housekeeping cohort', 'icon' => 'hotel', 'tone' => 'text-primary'],
                    ['label' => 'Available', 'value' => $availableCount, 'caption' => $totalRooms ? round(($availableCount / max($totalRooms, 1)) * 100, 1) . '% fleet ready' : 'No rooms encoded', 'icon' => 'check_circle', 'tone' => 'text-emerald-700'],
                    ['label' => 'Occupied', 'value' => $occupiedCount, 'caption' => $occupiedRoom ? 'Stayover: Room ' . $occupiedRoom->room_number : 'No active stayovers', 'icon' => 'key', 'tone' => 'text-primary'],
                    ['label' => 'Cleaning', 'value' => $cleaningRooms->count(), 'caption' => $cleaningRooms->count() ? 'Active turns' : 'All turns complete', 'icon' => 'mop', 'tone' => 'text-secondary'],
                    ['label' => 'Active Tickets', 'value' => $activeTickets, 'caption' => $criticalRooms ? $criticalRooms . ' maintenance room' . ($criticalRooms === 1 ? '' : 's') : 'No frozen rooms', 'icon' => 'engineering', 'tone' => $activeTickets ? 'text-red-700' : 'text-emerald-700'],
                    ['label' => 'Avg SLA Time', 'value' => $activeTickets ? '14m' : '0m', 'caption' => $activeTickets ? 'within target SLA' : 'no open queue', 'icon' => 'timer', 'tone' => 'text-primary'],
                ] as $metric)
                    <div class="rounded-lg border {{ $metric['label'] === 'Active Tickets' ? 'border-secondary/50' : 'border-line' }} bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</span>
                            <span class="material-symbols-outlined {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                        </div>
                        <div class="mt-3 font-serif text-4xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</div>
                        <p class="mt-2 text-[11px] font-semibold text-slate-500">{{ $metric['caption'] }}</p>
                    </div>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="space-y-4 xl:col-span-8">
                    <div class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-line bg-[#f6f3ed] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-serif text-2xl font-bold text-primary">Active Work Orders &amp; Defects</h3>
                                    <span class="rounded-full bg-primary px-2.5 py-1 text-xs font-bold text-white">{{ $visibleTickets }} Recorded</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">Real-time engineering sync with Subic Facilities Command.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-sm text-secondary">search</span>
                                    <input type="text" class="w-56 rounded-md border border-line bg-white py-2 pl-8 pr-3 text-xs text-primary focus:border-secondary focus:ring-secondary" placeholder="Filter room or WO#">
                                </div>
                                <a href="{{ route('housekeeping.page', 'maintenance-requests') }}" class="rounded-md border border-line bg-white p-2 text-slate-500 hover:text-primary" title="Reload Queue">
                                    <span class="material-symbols-outlined text-sm">sync</span>
                                </a>
                            </div>
                        </div>

                        <div class="divide-y divide-line">
                            @forelse ($maintenanceWorkOrders as $room)
                                @php
                                    $issue = $issueCatalog[($loop->iteration - 1) % count($issueCatalog)];
                                    $engineer = $engineers[($loop->iteration - 1) % count($engineers)];
                                    $isMaintenance = $room->status === 'maintenance';
                                    $isOccupied = $room->status === 'occupied';
                                    $severityClass = $isMaintenance ? 'bg-red-700 text-white' : ($isOccupied ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-[#f6f3ed] text-secondary border border-line');
                                    $severityText = $isMaintenance ? 'Urgent / OOO Frozen' : ($isOccupied ? 'High Priority Stayover' : 'Scheduled Maintenance');
                                    $accentClass = $isMaintenance ? 'bg-red-700' : ($isOccupied ? 'bg-amber-500' : 'bg-secondary');
                                    $ticketNumber = 'MNT-' . str_pad((string) (880 + $loop->iteration + (int) $room->id), 3, '0', STR_PAD_LEFT);
                                @endphp
                                <article class="relative p-5 transition hover:bg-[#fdfbf7]">
                                    <div class="absolute bottom-0 left-0 top-0 w-1 {{ $accentClass }}"></div>
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="space-y-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded bg-secondary px-2 py-0.5 text-xs font-bold text-white">#{{ $ticketNumber }}</span>
                                                <h4 class="text-lg font-bold text-primary">Room {{ $room->room_number }} - {{ $room->room_type }}</h4>
                                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $severityClass }}">{{ $severityText }}</span>
                                                <span class="text-xs text-slate-500">Capacity {{ $room->capacity }} guests</span>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined mt-0.5 {{ $isMaintenance ? 'text-red-700' : 'text-secondary' }}">{{ $issue['icon'] }}</span>
                                                <div>
                                                    <p class="font-bold text-primary">{{ $issue['title'] }}</p>
                                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $issue['note'] }}</p>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap gap-4 text-sm">
                                                <span class="inline-flex items-center gap-1.5 text-slate-600">
                                                    <span class="material-symbols-outlined text-sm text-secondary">engineering</span>
                                                    Assigned: <strong class="text-primary">{{ $engineer }}</strong>
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 text-slate-600">
                                                    <span class="h-2 w-2 rounded-full {{ $isMaintenance ? 'bg-red-600' : 'bg-amber-500' }}"></span>
                                                    Status: <strong class="text-secondary">{{ ucfirst($room->status) }}</strong>
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 text-slate-500">
                                                    <span class="material-symbols-outlined text-sm">payments</span>
                                                    Base rate PHP {{ number_format((float) $room->rate, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 flex-wrap gap-2 lg:flex-col">
                                            <a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-secondary px-3.5 py-2 text-xs font-bold text-white hover:bg-[#5b4313]">
                                                <span class="material-symbols-outlined text-sm">verified</span>
                                                Verify &amp; Clear Room
                                            </a>
                                            <a href="{{ route('housekeeping.page', 'report-issue') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-line bg-white px-3.5 py-2 text-xs font-bold text-primary hover:bg-[#f6f3ed]">
                                                <span class="material-symbols-outlined text-sm text-secondary">sensors</span>
                                                Add Engineering Note
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="px-6 py-16 text-center">
                                    <span class="material-symbols-outlined text-5xl text-slate-300">engineering</span>
                                    <h4 class="mt-3 font-serif text-2xl font-bold text-primary">No maintenance work orders waiting</h4>
                                    <p class="mt-1 text-sm text-slate-500">New reports from housekeeping will appear in this queue.</p>
                                    <a href="{{ route('housekeeping.page', 'report-issue') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0d2238]">
                                        <span class="material-symbols-outlined text-base">assignment_add</span>
                                        Report First Issue
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <aside class="space-y-5 xl:col-span-4">
                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">Express Incident Console</p>
                                <h3 class="font-serif text-2xl font-bold text-primary">Dispatch Work Order</h3>
                            </div>
                            <span class="material-symbols-outlined text-secondary">construction</span>
                        </div>
                        <form action="{{ route('housekeeping.page', 'report-issue') }}" method="GET" class="mt-4 space-y-4">
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Room</span>
                                <select class="mt-1 w-full rounded-lg border-line text-sm text-primary focus:border-secondary focus:ring-secondary">
                                    @forelse ($allRooms as $room)
                                        <option>Room {{ $room->room_number }} - {{ $room->room_type }} ({{ ucfirst($room->status) }})</option>
                                    @empty
                                        <option>No rooms encoded</option>
                                    @endforelse
                                </select>
                            </label>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Defect Category</span>
                                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                    @foreach (['HVAC', 'Plumbing', 'Door Lock', 'Electrical'] as $category)
                                        <label class="flex items-center gap-2 rounded-lg border border-line bg-[#f6f3ed] px-3 py-2 text-sm font-semibold text-primary">
                                            <input type="radio" name="category" class="border-line text-secondary focus:ring-secondary" @checked($loop->first)>
                                            {{ $category }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Defect Telemetry / Staff Note</span>
                                <textarea rows="4" class="mt-1 w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary" placeholder="Describe issue, affected fixture, guest impact, and urgency..."></textarea>
                            </label>
                            <label class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-primary">
                                Immediate PMS Room Freeze
                                <input type="checkbox" class="rounded border-line text-secondary focus:ring-secondary" checked>
                            </label>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0d2238]">
                                <span class="material-symbols-outlined text-base">send</span>
                                Open Full Report Form
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-primary">Duty Engineer On-Call</h3>
                            <span class="rounded bg-[#f6f3ed] px-2 py-1 text-[11px] font-bold text-secondary">07:00 - 15:30</span>
                        </div>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-sm font-bold text-[#ffdeaa]">DR</div>
                            <div>
                                <p class="font-bold text-primary">Engr. Danilo Ramos</p>
                                <p class="text-xs text-slate-500">Chief Plant Operations &amp; HVAC</p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                            <span class="rounded bg-amber-100 px-2 py-1 font-bold text-secondary">Radio Ch. 2</span>
                            <span class="rounded bg-[#f6f3ed] px-2 py-1 font-bold text-primary">Ext 4088</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs font-bold">
                            <a href="{{ route('housekeeping.page', 'report-issue') }}" class="text-secondary hover:underline">Instant Radio Ping</a>
                            <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="text-primary hover:underline">View Reported Issues</a>
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary p-5 text-white shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]"><span class="material-symbols-outlined">query_stats</span>Facilities Snapshot</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-white/10 pb-2"><span>Total rooms</span><strong>{{ $totalRooms }}</strong></div>
                            <div class="flex justify-between border-b border-white/10 pb-2"><span>Maintenance queue</span><strong>{{ $maintenanceQueue->count() }}</strong></div>
                            <div class="flex justify-between border-b border-white/10 pb-2"><span>Cleaning queue</span><strong>{{ $cleaningRooms->count() }}</strong></div>
                            <div class="flex justify-between"><span>Priority rooms</span><strong>{{ $priorityRooms->count() }}</strong></div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">handyman</span>
                            View Reported Issues
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'report-issue')
        @php
            $reportRooms = $allRooms->values();
            $selectedReportRoom = $reportRooms->first();
            $reportAction = $selectedReportRoom ? route('rooms.update', $selectedReportRoom) : route('rooms.index');
            $categories = [
                ['label' => 'HVAC & Climate', 'hint' => 'Aircon, thermostat, air', 'icon' => 'mode_fan', 'active' => true],
                ['label' => 'Plumbing & Water', 'hint' => 'Pressure, drains, leaks', 'icon' => 'faucet', 'active' => false],
                ['label' => 'IoT Lock & Keycard', 'hint' => 'Deadbolts, RFID, latch', 'icon' => 'lock_clock', 'active' => false],
                ['label' => 'Electrical & Lights', 'hint' => 'Fixtures, outlets, panels', 'icon' => 'lightbulb', 'active' => false],
                ['label' => 'Furniture & Balcony', 'hint' => 'Joinery, glass sliders', 'icon' => 'deck', 'active' => false],
                ['label' => 'Smart TV & Media', 'hint' => 'IPTV, casting, remote', 'icon' => 'tv', 'active' => false],
            ];
            $recentDispatchRooms = $maintenanceQueue->take(3)->values();
        @endphp

        <section class="space-y-6">
            <div class="flex flex-col gap-4 border-b border-line pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <div class="mb-2 inline-flex items-center gap-2 rounded bg-secondary/15 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary">
                        <span class="material-symbols-outlined text-sm">build</span>
                        Engineering Dispatch Terminal
                    </div>
                    <h3 class="font-serif text-3xl font-bold text-primary">Report Maintenance Defect or Room Discrepancy</h3>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Direct incident logging and dispatch terminal to Engineering &amp; Facilities for room hardware, plumbing, HVAC, or smart-lock failures.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-slate-600">
                    <span class="material-symbols-outlined text-base text-secondary">schedule</span>
                    Avg. Dispatch Response: 14 Mins
                </span>
            </div>

            <div class="grid gap-6 xl:grid-cols-12">
                <div class="space-y-6 xl:col-span-8">
                    <form id="reportIssueForm" method="POST" action="{{ $reportAction }}" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <input id="reportRoomNumber" type="hidden" name="room_number" value="{{ $selectedReportRoom?->room_number }}">
                        <input id="reportRoomType" type="hidden" name="room_type" value="{{ $selectedReportRoom?->room_type }}">
                        <input id="reportRate" type="hidden" name="rate" value="{{ $selectedReportRoom?->rate }}">
                        <input id="reportCapacity" type="hidden" name="capacity" value="{{ $selectedReportRoom?->capacity }}">
                        <input type="hidden" name="status" value="maintenance">

                        <section class="relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm">
                            <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-secondary to-primary"></div>
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="flex items-center gap-2 font-bold text-primary"><span class="material-symbols-outlined text-secondary">room_preferences</span>Room Location &amp; Defect Classification</h3>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Step 1 of 3</span>
                            </div>

                            <div class="mb-5">
                                <label class="mb-1.5 block text-sm font-bold text-primary">Target Room or Facility Location <span class="text-red-700">*</span></label>
                                <div class="relative">
                                    <select id="reportRoomSelect" class="w-full rounded-lg border-line bg-white py-2.5 pl-10 pr-10 text-sm focus:border-secondary focus:ring-secondary">
                                        @forelse ($reportRooms as $room)
                                            <option value="{{ $room->id }}"
                                                data-action="{{ route('rooms.update', $room) }}"
                                                data-room-number="{{ $room->room_number }}"
                                                data-room-type="{{ $room->room_type }}"
                                                data-rate="{{ $room->rate }}"
                                                data-capacity="{{ $room->capacity }}"
                                                data-status="{{ $room->status }}"
                                                data-description="{{ $room->description }}">
                                                Room {{ $room->room_number }} - {{ $room->room_type }} ({{ ucfirst($room->status) }})
                                            </option>
                                        @empty
                                            <option>No rooms available</option>
                                        @endforelse
                                    </select>
                                    <span class="material-symbols-outlined absolute left-3 top-3 text-lg text-secondary">meeting_room</span>
                                </div>
                                <div class="mt-1.5 flex flex-col gap-1 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                                    <span>Selected status: <strong id="reportSelectedStatus" class="font-semibold text-secondary">{{ $selectedReportRoom ? ucfirst($selectedReportRoom->status) : 'N/A' }}</strong></span>
                                    <span>Submitting will move the room to maintenance queue.</span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-primary">Defect Primary Category <span class="text-red-700">*</span></label>
                                <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($categories as $category)
                                        <button type="button" class="issue-category group flex items-center gap-2.5 rounded-lg border p-3 text-left transition {{ $category['active'] ? 'border-secondary bg-[#ffdeaa]/25 shadow-sm' : 'border-line bg-[#f6f3ed] hover:border-secondary/60' }}">
                                            <span class="material-symbols-outlined {{ $category['active'] ? 'text-secondary' : 'text-slate-500 group-hover:text-secondary' }}">{{ $category['icon'] }}</span>
                                            <span>
                                                <span class="block text-sm font-bold text-primary">{{ $category['label'] }}</span>
                                                <span class="block text-[11px] leading-tight text-slate-500">{{ $category['hint'] }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        <section class="rounded-lg border border-line bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between border-b border-line pb-3">
                                <h3 class="flex items-center gap-2 font-bold text-primary"><span class="material-symbols-outlined text-secondary">speed</span>Severity Matrix &amp; Incident Scope</h3>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Step 2 of 3</span>
                            </div>

                            <div>
                                <label class="mb-2.5 block text-sm font-bold text-primary">Dispatch Urgency Level <span class="text-red-700">*</span></label>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ([
                                        ['value' => 'low', 'title' => 'Low Priority', 'tag' => 'Routine PM', 'copy' => 'Non-intrusive cosmetic or scheduled preventative maintenance.', 'class' => 'border-line bg-white'],
                                        ['value' => 'medium', 'title' => 'Medium Priority', 'tag' => 'Turnover Window', 'copy' => 'Rectify during room turnover before next guest arrival.', 'class' => 'border-line bg-white'],
                                        ['value' => 'high', 'title' => 'High Priority', 'tag' => 'Guest In-House', 'copy' => 'Guest comfort impaired. Requires rapid dispatch.', 'class' => 'border-secondary bg-[#ffdeaa]/20'],
                                        ['value' => 'critical', 'title' => 'Critical Emergency', 'tag' => 'Stop Sales', 'copy' => 'Water flood, fire safety, or major structural failure.', 'class' => 'border-red-300 bg-red-50'],
                                    ] as $severity)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-3.5 {{ $severity['class'] }}">
                                            <input class="mt-1 border-line text-secondary focus:ring-secondary" name="severity" type="radio" value="{{ $severity['value'] }}" {{ $severity['value'] === 'high' ? 'checked' : '' }}>
                                            <span>
                                                <span class="flex items-center gap-2">
                                                    <span class="font-bold {{ $severity['value'] === 'critical' ? 'text-red-700' : 'text-primary' }}">{{ $severity['title'] }}</span>
                                                    <span class="rounded bg-[#f0eee8] px-2 py-0.5 text-[10px] font-bold text-secondary">{{ $severity['tag'] }}</span>
                                                </span>
                                                <span class="mt-0.5 block text-xs text-slate-500">{{ $severity['copy'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <label class="text-sm font-bold text-primary">Detailed Defect Description <span class="text-red-700">*</span></label>
                                    <span class="text-[11px] text-slate-500">Tap chip to insert preset descriptor</span>
                                </div>
                                <div class="mb-2.5 flex flex-wrap gap-1.5">
                                    @foreach ([
                                        'Aircon blowing warm air despite low setpoint',
                                        'Deadbolt sensor jammed and unable to lock',
                                        'Balcony sliding glass door stuck off track',
                                        'Bathroom vanity sink slow drain',
                                        'Keycard RFID reader blinking red',
                                    ] as $preset)
                                        <button type="button" class="issue-preset rounded-full border border-line bg-[#f6f3ed] px-2.5 py-1 text-xs text-slate-600 hover:border-secondary hover:text-secondary" data-preset="{{ $preset }}">+ {{ $preset }}</button>
                                    @endforeach
                                </div>
                                <textarea id="defectDescription" name="description" rows="4" required class="w-full rounded-lg border-line bg-white p-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Specify observations, readings, sounds, error codes, and guest interaction notes...">Maintenance issue reported by housekeeping: HVAC/climate defect affecting guest comfort. Please dispatch Engineering for inspection.</textarea>
                            </div>

                            <div class="mt-5">
                                <label class="mb-1.5 block text-sm font-bold text-primary">Photo &amp; Evidence Documentation</label>
                                <div class="rounded-lg border-2 border-dashed border-line bg-[#f6f3ed]/60 p-5 text-center">
                                    <span class="material-symbols-outlined text-4xl text-secondary">add_photo_alternate</span>
                                    <p class="mt-2 font-bold text-primary">Drag inspection photos here, or browse local terminal</p>
                                    <p class="mt-1 text-xs text-slate-500">Evidence capture is represented here; image upload storage can be connected when file persistence is added.</p>
                                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                                        <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-primary"><span class="material-symbols-outlined text-base text-secondary">photo_camera</span>Capture with Tablet Camera</button>
                                        <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-bold text-primary"><span class="material-symbols-outlined text-base">link</span>Add IoT Diagnostic Dump</button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-lg border border-line bg-white p-5 shadow-sm">
                            <h3 class="flex items-center gap-2 font-bold text-primary"><span class="material-symbols-outlined text-secondary">domain_verification</span>PMS Inventory Impact &amp; Protocol Enforcement</h3>
                            <div class="mt-4 space-y-3 rounded-lg border border-line bg-[#f6f3ed]/70 p-4">
                                @foreach ([
                                    ['label' => 'Take Room Out of Order immediately in Front Desk PMS', 'copy' => 'Freezes room inventory to prevent new arrivals until cleared.', 'checked' => true],
                                    ['label' => 'Require Supervisor Re-inspection before release', 'copy' => 'Engineering sign-off alone will not return room to inspected clean.', 'checked' => true],
                                    ['label' => 'Notify Front Desk Concierge for guest recovery', 'copy' => 'Alerts Duty Manager if relocation or service recovery is needed.', 'checked' => false],
                                ] as $impact)
                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input class="mt-1 rounded border-line text-secondary focus:ring-secondary" type="checkbox" {{ $impact['checked'] ? 'checked' : '' }}>
                                        <span>
                                            <span class="font-bold text-primary">{{ $impact['label'] }}</span>
                                            <span class="block text-xs text-slate-500">{{ $impact['copy'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-5 flex flex-col gap-3 border-t border-line pt-4 sm:flex-row sm:items-center sm:justify-between">
                                <a href="{{ route('housekeeping.page', 'maintenance-requests') }}" class="inline-flex justify-center rounded-lg border border-line px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-[#f6f3ed]">Cancel</a>
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-secondary px-5 py-2.5 text-sm font-bold text-secondary hover:bg-[#ffdeaa]/20">
                                        <span class="material-symbols-outlined text-base">save</span>
                                        Save Draft Incident
                                    </button>
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary/90" {{ $selectedReportRoom ? '' : 'disabled' }}>
                                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">send</span>
                                        Submit Work Order to Engineering
                                    </button>
                                </div>
                            </div>
                        </section>
                    </form>
                </div>

                <aside class="space-y-5 xl:col-span-4">
                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-secondary"><span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>Lead Engineer On Duty</span>
                            <span class="rounded bg-[#f6f3ed] px-2 py-0.5 text-[11px] font-mono text-slate-500">07:00 - 15:30</span>
                        </div>
                        <div class="flex items-center gap-3 border-b border-line pb-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-sm font-bold text-[#ffdeaa]">DR</div>
                            <div>
                                <h4 class="font-bold text-primary">Engr. Danilo Rodriguez</h4>
                                <p class="text-xs text-slate-500">Chief of Plant Operations &amp; HVAC</p>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span class="rounded bg-[#ffdeaa]/60 px-2 py-0.5 text-[11px] font-bold text-secondary">Radio Ch. 2</span>
                                    <span class="rounded bg-[#f6f3ed] px-2 py-0.5 text-[11px] font-mono text-primary">Ext 402</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <button type="button" class="text-xs font-bold text-secondary hover:underline">Instant Radio Ping</button>
                            <button type="button" class="text-xs font-bold text-primary hover:text-secondary">Direct Terminal Message</button>
                        </div>
                    </div>

                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-primary"><span class="material-symbols-outlined text-secondary">history</span>Maintenance History</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($maintenanceQueue->take(4) as $room)
                                <a href="{{ route('rooms.show', $room) }}" class="block rounded-lg border border-line bg-[#fdfcfa] p-3 hover:bg-[#f6f3ed]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-primary">Room {{ $room->room_number }}</span>
                                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $statusTone[$room->status] ?? 'border-line' }}">{{ ucfirst($room->status) }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $room->description ?: 'Maintenance queue item awaiting engineering clearance.' }}</p>
                                </a>
                            @empty
                                <p class="rounded-lg border border-line bg-[#fdfcfa] p-4 text-sm text-slate-500">No active maintenance rooms yet. New work orders submitted here will appear in this queue.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary p-5 text-white shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]"><span class="material-symbols-outlined">analytics</span>Floor Maintenance Snapshot</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-white/10 pb-2"><span class="text-slate-300">Total rooms</span><strong>{{ $totalRooms }}</strong></div>
                            <div class="flex justify-between border-b border-white/10 pb-2"><span class="text-slate-300">Maintenance queue</span><strong>{{ $maintenanceQueue->count() }}</strong></div>
                            <div class="flex justify-between"><span class="text-slate-300">Cleaning queue</span><strong>{{ $cleaningRooms->count() }}</strong></div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-3 py-2 text-xs font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">construction</span>
                            View Reported Issues
                        </a>
                    </div>
                </aside>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const roomSelect = document.getElementById('reportRoomSelect');
                    const form = document.getElementById('reportIssueForm');
                    const fields = {
                        room_number: document.getElementById('reportRoomNumber'),
                        room_type: document.getElementById('reportRoomType'),
                        rate: document.getElementById('reportRate'),
                        capacity: document.getElementById('reportCapacity'),
                        statusText: document.getElementById('reportSelectedStatus'),
                    };

                    const syncRoom = () => {
                        const option = roomSelect?.selectedOptions?.[0];
                        if (!option || !form) return;
                        form.action = option.dataset.action || form.action;
                        fields.room_number.value = option.dataset.roomNumber || '';
                        fields.room_type.value = option.dataset.roomType || '';
                        fields.rate.value = option.dataset.rate || '';
                        fields.capacity.value = option.dataset.capacity || '';
                        fields.statusText.textContent = (option.dataset.status || 'N/A').replace(/^\w/, (char) => char.toUpperCase());
                    };

                    roomSelect?.addEventListener('change', syncRoom);
                    syncRoom();

                    document.querySelectorAll('.issue-category').forEach((button) => {
                        button.addEventListener('click', () => {
                            document.querySelectorAll('.issue-category').forEach((item) => {
                                item.className = 'issue-category group flex items-center gap-2.5 rounded-lg border border-line bg-[#f6f3ed] p-3 text-left transition hover:border-secondary/60';
                            });
                            button.className = 'issue-category group flex items-center gap-2.5 rounded-lg border border-secondary bg-[#ffdeaa]/25 p-3 text-left transition shadow-sm';
                        });
                    });

                    document.querySelectorAll('.issue-preset').forEach((button) => {
                        button.addEventListener('click', () => {
                            const textarea = document.getElementById('defectDescription');
                            textarea.value = `${textarea.value.trim()}\n${button.dataset.preset}`.trim();
                        });
                    });
                });
            </script>
        </section>
    @elseif ($pageKey === 'reported-issues')
        @php
            $reportedIssueRooms = $maintenanceQueue->values();
            $selectedIssueRoom = $reportedIssueRooms->first();
            $ticketStates = [
                ['label' => 'Repaired - Pending HK Signoff', 'class' => 'bg-emerald-100 text-emerald-800', 'icon' => 'check_circle', 'priority' => 'Urgent Priority', 'priorityClass' => 'bg-red-100 text-red-800'],
                ['label' => 'Technician Dispatched', 'class' => 'bg-blue-100 text-blue-900', 'icon' => 'directions_walk', 'priority' => 'High Priority', 'priorityClass' => 'bg-amber-100 text-amber-900'],
                ['label' => 'Awaiting Replacement Part', 'class' => 'bg-[#ffdeaa] text-secondary', 'icon' => 'inventory_2', 'priority' => 'Standard Priority', 'priorityClass' => 'bg-stone-200 text-stone-800'],
                ['label' => 'Work Order Accepted', 'class' => 'bg-sky-100 text-sky-900', 'icon' => 'receipt', 'priority' => 'High Priority', 'priorityClass' => 'bg-amber-100 text-amber-900'],
            ];
            $defects = [
                ['icon' => 'hvac', 'title' => 'HVAC Freon Compressor Leak', 'copy' => 'Guest-reported ambient temperature variance. Engineering diagnostics and air misting follow-up required.'],
                ['icon' => 'lock_clock', 'title' => 'IoT Lock Sensor Mismatch', 'copy' => 'Gateway sensor state is inconsistent with the physical deadbolt. RFID sync verification required.'],
                ['icon' => 'window', 'title' => 'Balcony Sliding Track Jammed', 'copy' => 'Saline corrosion and heavy resistance on lower roller carriage. Weather seal inspection required.'],
                ['icon' => 'hot_tub', 'title' => 'Jacuzzi Jet Pressure Calibration', 'copy' => 'Pressure sensor trips circuit breaker during continuous cycle. Engineering reset required.'],
            ];
        @endphp

        <section class="space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="mb-1 flex items-center gap-2 text-xs text-slate-500">
                        <span>Housekeeping Oversight</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="font-bold text-secondary">Maintenance Requests</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="font-bold text-primary">Reported Issues</span>
                    </div>
                    <h3 class="font-serif text-3xl font-bold text-primary">Reported Issues &amp; Facilities Work Orders Log</h3>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Tracking status, engineering progress, technician dispatch, and return-to-service sign-offs for rooms currently marked for maintenance.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary hover:bg-[#f6f3ed]">
                        <span class="material-symbols-outlined text-base">file_download</span>
                        Export Maintenance CSV
                    </button>
                    <a href="{{ route('housekeeping.page', 'report-issue') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-primary/90">
                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">add</span>
                        New Maintenance Ticket
                    </a>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Open Housekeeping Tickets', 'value' => $reportedIssueRooms->count(), 'caption' => 'active in queue', 'icon' => 'report_problem', 'tone' => 'text-primary', 'badge' => 'Live'],
                    ['label' => 'Out of Order Units', 'value' => $reportedIssueRooms->count(), 'caption' => 'rooms blocked', 'icon' => 'do_not_disturb_on', 'tone' => 'text-red-700', 'badge' => 'Inventory Impact'],
                    ['label' => 'Average Time to Repair', 'value' => $reportedIssueRooms->count() ? '1h 42m' : '0m', 'caption' => 'rolling operational estimate', 'icon' => 'timelapse', 'tone' => 'text-secondary', 'badge' => 'Rolling 30D'],
                    ['label' => 'Engineering Response SLA', 'value' => $reportedIssueRooms->count() ? '98%' : '100%', 'caption' => 'first response under target', 'icon' => 'verified', 'tone' => 'text-emerald-700', 'badge' => 'Compliant'],
                ] as $metric)
                    <div class="relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm">
                        <span class="material-symbols-outlined absolute -bottom-3 -right-3 text-7xl text-slate-100">{{ $metric['icon'] }}</span>
                        <div class="relative flex items-center justify-between">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                            <span class="rounded-full bg-[#f6f3ed] px-2 py-0.5 text-[10px] font-bold text-secondary">{{ $metric['badge'] }}</span>
                        </div>
                        <div class="relative mt-3 flex items-baseline gap-2">
                            <span class="font-serif text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</span>
                            <span class="text-xs font-bold text-slate-500">{{ $metric['caption'] }}</span>
                        </div>
                    </div>
                @endforeach
            </section>

            <section class="flex flex-col gap-3 rounded-lg border border-line bg-white p-4 shadow-sm xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="mr-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Filter Status:</span>
                    @foreach ([
                        ['All Open', $reportedIssueRooms->count()],
                        ['In Progress', min(2, $reportedIssueRooms->count())],
                        ['Awaiting Parts', $reportedIssueRooms->count() > 0 ? 1 : 0],
                        ['Ready for Verification', $reportedIssueRooms->count() > 0 ? 1 : 0],
                    ] as [$label, $count])
                        <button type="button" class="{{ $loop->first ? 'bg-primary text-white' : 'bg-[#f6f3ed] text-primary hover:bg-line' }} inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-bold">
                            {{ $label }}
                            <span class="{{ $loop->first ? 'bg-[#ffdeaa] text-primary' : 'bg-white text-slate-600' }} rounded px-1.5 py-0.5 text-[10px]">{{ $count }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-2">
                    <select class="rounded-lg border-line bg-[#f6f3ed] text-xs font-bold text-primary focus:border-secondary focus:ring-secondary">
                        <option>All Resort Wings</option>
                        <option>Main Wing</option>
                        <option>Ocean Wing</option>
                    </select>
                    <select class="rounded-lg border-line bg-[#f6f3ed] text-xs font-bold text-primary focus:border-secondary focus:ring-secondary">
                        <option>Severity: All Priorities</option>
                        <option>Urgent / Critical</option>
                        <option>High Priority</option>
                    </select>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <div class="space-y-4 xl:col-span-7">
                    @forelse ($reportedIssueRooms as $room)
                        @php
                            $state = $ticketStates[($loop->iteration - 1) % count($ticketStates)];
                            $defect = $defects[($loop->iteration - 1) % count($defects)];
                            $ticket = 'MNT-' . str_pad((string) (880 + $loop->iteration), 3, '0', STR_PAD_LEFT);
                            $wing = ((int) $room->room_number >= 300) ? 'Ocean Wing' : 'Main Wing';
                        @endphp
                        <article class="{{ $loop->first ? 'border-2 border-secondary shadow-md' : 'border border-line shadow-sm hover:border-secondary/60' }} relative rounded-lg bg-white p-5 transition">
                            @if ($loop->first)
                                <span class="absolute right-0 top-0 rounded-bl-lg bg-secondary px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Inspection Selected</span>
                            @endif
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="font-serif text-2xl font-bold text-primary">Room {{ $room->room_number }} {{ $wing }}</h4>
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $state['priorityClass'] }}">{{ $state['priority'] }}</span>
                                        <span class="rounded bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-700">OOO Active</span>
                                    </div>
                                    <p class="mt-1 text-xs font-bold text-secondary">Ticket #{{ $ticket }} • {{ $wing }} Facilities Queue</p>
                                </div>
                                <span class="text-xs text-slate-500">Reported {{ $loop->iteration * 18 }}m ago</span>
                            </div>

                            <div class="mt-4 rounded-lg border border-line bg-[#f6f3ed] p-3">
                                <div class="mb-1 flex items-center gap-2 text-xs font-bold text-primary">
                                    <span class="material-symbols-outlined text-base text-secondary">{{ $defect['icon'] }}</span>
                                    <span>Reported Defect: {{ $defect['title'] }}</span>
                                </div>
                                <p class="text-xs leading-5 text-slate-600">{{ $room->description ?: $defect['copy'] }}</p>
                            </div>

                            <div class="mt-4 grid gap-3 border-y border-line py-3 text-xs sm:grid-cols-3">
                                <div>
                                    <span class="block text-[11px] font-bold uppercase text-slate-500">Current State</span>
                                    <span class="mt-1 inline-flex items-center gap-1 rounded px-2 py-0.5 font-bold {{ $state['class'] }}">
                                        <span class="material-symbols-outlined text-xs">{{ $state['icon'] }}</span>
                                        {{ $state['label'] }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-bold uppercase text-slate-500">Assigned Technician</span>
                                    <span class="mt-1 flex items-center gap-1 font-bold text-primary"><span class="material-symbols-outlined text-sm text-secondary">engineering</span>Eng. Danilo Ramos</span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-bold uppercase text-slate-500">Downtime</span>
                                    <span class="mt-1 block font-bold text-primary">{{ $loop->iteration }}h {{ 12 + $loop->iteration }}m logged</span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('rooms.update', $room) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                        <input type="hidden" name="room_type" value="{{ $room->room_type }}">
                                        <input type="hidden" name="rate" value="{{ $room->rate }}">
                                        <input type="hidden" name="capacity" value="{{ $room->capacity }}">
                                        <input type="hidden" name="description" value="{{ $room->description }}">
                                        <input type="hidden" name="status" value="available">
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-800 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-900">
                                            <span class="material-symbols-outlined text-base">verified_user</span>
                                            Verify &amp; Clear Room
                                        </button>
                                    </form>
                                    <a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-[#f6f3ed] px-3.5 py-1.5 text-xs font-bold text-primary hover:bg-line">
                                        <span class="material-symbols-outlined text-base text-secondary">visibility</span>
                                        View Details
                                    </a>
                                </div>
                                <span class="text-[11px] italic text-slate-500">HK verification and sanitization pending</span>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-line bg-white px-5 py-14 text-center shadow-sm">
                            <span class="material-symbols-outlined text-5xl text-slate-300">construction</span>
                            <h4 class="mt-3 font-serif text-2xl font-bold text-primary">No reported issues open</h4>
                            <p class="mt-1 text-sm text-slate-500">Rooms submitted from Report Issue will appear here once marked for maintenance.</p>
                            <a href="{{ route('housekeeping.page', 'report-issue') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary/90">
                                <span class="material-symbols-outlined text-base text-[#ffdeaa]">add</span>
                                New Maintenance Ticket
                            </a>
                        </div>
                    @endforelse
                </div>

                <aside class="space-y-5 xl:col-span-5">
                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between border-b border-line pb-4">
                            <div>
                                <span class="text-xs font-mono text-slate-500">Ticket #{{ $selectedIssueRoom ? 'MNT-881' : 'MNT---' }}</span>
                                <h3 class="mt-1 font-serif text-2xl font-bold text-primary">Facilities Work Order &amp; Return-to-Service Dossier</h3>
                                <p class="mt-1 text-sm text-slate-500">Room {{ $selectedIssueRoom?->room_number ?? 'N/A' }} engineering and housekeeping clearance timeline.</p>
                            </div>
                            <span class="material-symbols-outlined text-secondary">assignment</span>
                        </div>

                        <div class="mt-5">
                            <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Engineering Resolution Flow</p>
                            <div class="grid grid-cols-4 gap-2 text-center text-[10px] font-bold text-slate-500">
                                @foreach ([['Reported', true], ['Dispatched', true], ['Repaired', $selectedIssueRoom !== null], ['Verified', false]] as [$step, $done])
                                    <div>
                                        <div class="{{ $done ? 'bg-secondary text-white' : 'bg-[#f0eee8] text-slate-400' }} mx-auto flex h-9 w-9 items-center justify-center rounded-full">
                                            <span class="material-symbols-outlined text-base">{{ $done ? 'done' : 'radio_button_unchecked' }}</span>
                                        </div>
                                        <span class="mt-1 block">{{ $step }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-5 rounded-lg border border-line bg-[#f6f3ed] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Dispatch &amp; Repair Timeline</p>
                            <div class="mt-3 space-y-3">
                                @foreach ([
                                    ['time' => '09:12', 'title' => 'Issue Flagged During Room Refresh', 'copy' => 'Housekeeping moved room into maintenance review.'],
                                    ['time' => '09:24', 'title' => 'Engineering Dispatch Accepted', 'copy' => 'Facilities team assigned technician and opened work order.'],
                                    ['time' => '10:18', 'title' => 'Housekeeping Verification Pending', 'copy' => 'Room remains out of order until final room inspection.'],
                                ] as $event)
                                    <div class="flex gap-3">
                                        <span class="w-12 shrink-0 font-mono text-xs font-bold text-secondary">{{ $event['time'] }}</span>
                                        <div>
                                            <p class="text-sm font-bold text-primary">{{ $event['title'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $event['copy'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary p-5 text-white shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]"><span class="material-symbols-outlined">bolt</span>Engineering Response SLA</h3>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[11px] uppercase tracking-wider text-slate-300">Open</p>
                                <p class="font-serif text-3xl font-bold">{{ $reportedIssueRooms->count() }}</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[11px] uppercase tracking-wider text-slate-300">Blocked</p>
                                <p class="font-serif text-3xl font-bold">{{ $reportedIssueRooms->count() }}</p>
                            </div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'room-inspection') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-3 py-2 text-xs font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">fact_check</span>
                            Open HK Verification Queue
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'linen-records')
        @php
            $linenDemand = max(1, ($cleaningRooms->count() * 18) + ($occupiedRooms * 8) + ($reservedRooms * 6));
            $circulatingPar = $totalRooms > 0 ? min(99, max(82, round((($availableRooms + $reservedRooms + $occupiedRooms) / max($totalRooms, 1)) * 93.3, 1))) : 93.3;
            $cleanDepotStock = max(120, ($availableRooms * 34) + ($reservedRooms * 18) + 64);
            $offsiteLaundry = max(40, ($cleaningRooms->count() * 24) + ($maintenanceQueue->count() * 12));
            $soiledPending = max(12, ($occupiedRooms * 10) + ($priorityRooms->count() * 6));
            $damagedMonthToDate = max(1, $priorityRooms->count() + $maintenanceQueue->count());
            $lossRate = number_format(min(0.8, max(0.2, ($damagedMonthToDate / max($cleanDepotStock + $offsiteLaundry + $soiledPending, 1)) * 100)), 1);
            $linenRows = [
                ['sku' => 'LIN-BED-KNG-001', 'name' => 'Premium King Sheet Set - 400TC Percale', 'category' => 'Bedding Linens', 'par' => max(60, $totalRooms * 10), 'floor' => $cleanDepotStock, 'laundry' => $offsiteLaundry, 'reserve' => 34, 'status' => 'Optimal Par', 'tone' => 'emerald', 'icon' => 'bed'],
                ['sku' => 'LIN-BED-DUV-002', 'name' => 'Hypoallergenic Duvet Insert - Queen/King', 'category' => 'Bedding Linens', 'par' => max(35, $totalRooms * 6), 'floor' => max(28, $availableRooms * 8), 'laundry' => max(18, $cleaningRooms->count() * 8), 'reserve' => 12, 'status' => 'Monitor', 'tone' => 'amber', 'icon' => 'bedroom_parent'],
                ['sku' => 'LIN-BATH-SHT-003', 'name' => 'Egyptian Cotton Bath Sheet 900gsm', 'category' => 'Bath Linens', 'par' => max(70, $totalRooms * 14), 'floor' => max(54, $occupiedRooms * 12 + 32), 'laundry' => max(24, $cleaningRooms->count() * 10), 'reserve' => 18, 'status' => 'Optimal Par', 'tone' => 'emerald', 'icon' => 'dry_cleaning'],
                ['sku' => 'LIN-BATH-TWL-004', 'name' => 'Face Towel & Hand Towel Bundle', 'category' => 'Bath Linens', 'par' => max(90, $totalRooms * 18), 'floor' => max(62, $availableRooms * 15), 'laundry' => max(30, $soiledPending), 'reserve' => 20, 'status' => 'Low Stock Watch', 'tone' => 'red', 'icon' => 'laundry'],
                ['sku' => 'LIN-FB-NAP-005', 'name' => 'Restaurant Napkin Set - Ivory Cotton', 'category' => 'Food & Beverage Linens', 'par' => 120, 'floor' => 94, 'laundry' => 44, 'reserve' => 16, 'status' => 'Optimal Par', 'tone' => 'emerald', 'icon' => 'room_service'],
                ['sku' => 'LIN-POOL-ROB-006', 'name' => 'Spa Robe & Pool Towel Rotation Pack', 'category' => 'Pool & Spa Linens', 'par' => 48, 'floor' => 39, 'laundry' => 12, 'reserve' => 8, 'status' => 'Par Safe', 'tone' => 'emerald', 'icon' => 'hot_tub'],
            ];
            $linenTone = [
                'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'amber' => 'border-amber-200 bg-amber-50 text-amber-800',
                'red' => 'border-red-200 bg-red-50 text-red-800',
            ];
        @endphp

        <section class="space-y-6">
            <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
                <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                    <span class="material-symbols-outlined text-base text-secondary">home</span>
                    <span>Subic IHMS</span>
                    <span>/</span>
                    <span>Housekeeping Oversight</span>
                    <span>/</span>
                    <span class="text-secondary">Linen Records</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-xs text-slate-600 shadow-sm">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span>
                    <span>RFID Gateways: <strong class="font-mono text-primary">All 8 Online</strong></span>
                    <span class="text-line">|</span>
                    <span>Last Cycle Sync: <strong class="font-mono text-primary">{{ now()->format('h:i:s A') }} PHT</strong></span>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-line bg-white p-6 shadow-sm">
                <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-secondary via-[#ffdeaa] to-secondary"></div>
                <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-start">
                    <div>
                        <p class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary">
                            <span>Housekeeping Oversight</span>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span>Linen &amp; Supplies</span>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span>Linen Records</span>
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <h3 class="font-serif text-3xl font-bold text-primary">Linen Inventory, Par Levels &amp; Laundry Cycles</h3>
                            <span class="rounded-full border border-secondary/20 bg-secondary/10 px-3 py-1 text-xs font-bold text-secondary">Par Status: {{ $circulatingPar }}% Optimal</span>
                        </div>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Real-time property-wide par stock management, circulating linen balances, central commercial laundry deliveries, and damaged item scrap logs.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-[#f6f3ed] px-4 py-2.5 text-sm font-bold text-primary shadow-sm hover:bg-line">
                            <span class="material-symbols-outlined text-base text-secondary">inventory_2</span>
                            Audit Par Stock
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-secondary bg-secondary/10 px-4 py-2.5 text-sm font-bold text-secondary shadow-sm hover:bg-secondary/20">
                            <span class="material-symbols-outlined text-base">local_shipping</span>
                            Record Laundry Dispatch
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">archive</span>
                            Receive Laundry Delivery
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Circulating Linen Par</p>
                        <span class="material-symbols-outlined rounded-lg bg-secondary/10 p-2 text-secondary">tune</span>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <p class="font-serif text-3xl font-bold text-primary">{{ $circulatingPar }}%</p>
                        <span class="text-xs font-bold text-slate-500">/ 3.0 Par</span>
                    </div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[#f0eee8]">
                        <div class="h-full rounded-full bg-secondary" style="width: {{ $circulatingPar }}%"></div>
                    </div>
                    <p class="mt-3 flex items-center gap-1 text-sm font-semibold text-secondary"><span class="material-symbols-outlined text-sm">check_circle</span>Optimal Buffer</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Clean in Floor Depots</p>
                        <span class="material-symbols-outlined rounded-lg bg-primary/10 p-2 text-primary">shelves</span>
                    </div>
                    <p class="mt-4 font-serif text-3xl font-bold text-primary">{{ $cleanDepotStock }}</p>
                    <p class="mt-2 text-sm text-slate-500">Ready pieces for room attendants.</p>
                    <p class="mt-2 text-[11px] font-bold text-primary">{{ max(1, floor($cleanDepotStock / 6)) }} beds complete</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">In Laundry Offsite</p>
                        <span class="material-symbols-outlined rounded-lg bg-[#ffdeaa]/40 p-2 text-secondary">local_laundry_service</span>
                    </div>
                    <p class="mt-4 font-serif text-3xl font-bold text-primary">{{ $offsiteLaundry }}</p>
                    <p class="mt-2 text-sm text-slate-500">Subic Central Wash Facility.</p>
                    <p class="mt-2 text-[11px] font-bold text-secondary">Due today @ {{ now()->addHours(2)->format('h:i A') }} PHT</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Soiled Pending Dispatch</p>
                        <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">swap_vert</span>
                    </div>
                    <p class="mt-4 font-serif text-3xl font-bold text-primary">{{ $soiledPending }}</p>
                    <p class="mt-2 text-sm text-slate-500">Awaiting laundry manifest handoff.</p>
                    <p class="mt-2 text-[11px] font-bold text-slate-500">Batch manifest #DSP-{{ now()->format('md') }}</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Damaged / Scrapped MTD</p>
                        <span class="material-symbols-outlined rounded-lg bg-red-50 p-2 text-red-700">delete_sweep</span>
                    </div>
                    <p class="mt-4 font-serif text-3xl font-bold text-red-700">{{ $damagedMonthToDate }}</p>
                    <p class="mt-2 text-sm text-slate-500">Thread tear / chemical wear.</p>
                    <p class="mt-2 text-[11px] font-bold text-secondary">{{ $lossRate }}% loss rate, benchmark under 0.8%</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
                <section class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-line bg-[#f6f3ed]/40 p-5">
                        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
                            <div>
                                <h3 class="font-serif text-2xl font-bold text-primary">Master Linen Stock Ledger</h3>
                                <p class="mt-1 text-sm text-slate-500">RFID-tracked linen par balances, floor depot stock, offsite laundry, reserve stock, and quick restock actions.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white shadow-sm"><span class="material-symbols-outlined text-base text-[#ffdeaa]">bed</span>Bedding Linens <span class="rounded-full bg-white/20 px-1.5">4 Items</span></button>
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-xs font-bold text-primary hover:bg-line"><span class="material-symbols-outlined text-base">bathtub</span>Bath Linens <span class="rounded-full bg-[#f6f3ed] px-1.5 text-slate-500">3 Items</span></button>
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-xs font-bold text-primary hover:bg-line"><span class="material-symbols-outlined text-base">restaurant</span>Food &amp; Beverage Linens</button>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <label class="relative md:w-80">
                                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-400">search</span>
                                <input class="w-full rounded-lg border-line py-2 pl-10 text-sm focus:border-secondary focus:ring-secondary" placeholder="Search linen batch, RFID SKU, floor depot..." type="search" />
                            </label>
                            <div class="flex gap-2">
                                <select class="rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary">
                                    <option>All Health States</option>
                                    <option>Low Stock Alert</option>
                                    <option>Optimal Par</option>
                                </select>
                                <button type="button" onclick="window.print()" class="rounded-lg border border-line px-3 py-2 text-xs font-bold text-primary hover:border-secondary">
                                    <span class="material-symbols-outlined text-base align-middle text-secondary">file_download</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-[#f0eee8] text-xs uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="px-5 py-3">Linen Item Description</th>
                                    <th class="px-3 py-3 text-center">Par Req.</th>
                                    <th class="px-3 py-3 text-center">Floor Stock</th>
                                    <th class="px-3 py-3 text-center">Offsite Laundry</th>
                                    <th class="px-3 py-3 text-center">Reserve / Scrap</th>
                                    <th class="px-3 py-3 text-center">Health Status</th>
                                    <th class="px-5 py-3 text-right">Quick Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($linenRows as $linen)
                                    <tr>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">{{ $linen['icon'] }}</span>
                                                <div>
                                                    <p class="font-bold text-primary">{{ $linen['name'] }}</p>
                                                    <p class="mt-1 font-mono text-xs text-slate-500">{{ $linen['sku'] }} - {{ $linen['category'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-center font-bold text-primary">{{ $linen['par'] }}</td>
                                        <td class="px-3 py-4 text-center text-slate-700">{{ $linen['floor'] }}</td>
                                        <td class="px-3 py-4 text-center text-slate-700">{{ $linen['laundry'] }}</td>
                                        <td class="px-3 py-4 text-center text-slate-700">{{ $linen['reserve'] }}</td>
                                        <td class="px-3 py-4 text-center">
                                            <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $linenTone[$linen['tone']] }}">{{ $linen['status'] }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button type="button" class="rounded-lg bg-[#f6f3ed] px-3 py-1.5 text-xs font-bold text-primary hover:bg-line">Restock</button>
                                                <button type="button" class="rounded-lg border border-line p-1.5 text-slate-500 hover:border-red-200 hover:bg-red-50 hover:text-red-700" title="Log Wash Discrepancy">
                                                    <span class="material-symbols-outlined text-base">report_problem</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex flex-col items-center justify-between gap-2 border-t border-line bg-[#f6f3ed]/40 p-4 text-xs text-slate-500 sm:flex-row">
                        <div>Showing {{ count($linenRows) }} of 28 Linen Standard SKUs - <span class="font-bold text-secondary">100% RFID UHF Verified</span></div>
                        <div class="flex gap-1">
                            <button type="button" class="rounded border border-line px-2.5 py-1 text-slate-400" disabled>Previous</button>
                            <button type="button" class="rounded bg-primary px-2.5 py-1 font-bold text-white">1</button>
                            <button type="button" class="rounded border border-line px-2.5 py-1 text-primary hover:bg-white">2</button>
                            <button type="button" class="rounded border border-line px-2.5 py-1 text-primary hover:bg-white">Next</button>
                        </div>
                    </div>
                </section>

                <aside class="space-y-5">
                    <div class="relative overflow-hidden rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-secondary via-[#ffdeaa] to-secondary"></div>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Subic Bay Logistics Link</p>
                                <h3 class="mt-1 font-serif text-2xl font-bold text-primary">Commercial Laundry Inbound</h3>
                            </div>
                            <span class="material-symbols-outlined rounded-lg bg-secondary/10 p-2 text-secondary">local_shipping</span>
                        </div>
                        <div class="mt-5 rounded-lg border border-line bg-[#f6f3ed] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs text-slate-500">Truck Arrival Schedule</p>
                                    <p class="mt-1 font-serif text-2xl font-bold text-primary">{{ now()->addHours(2)->format('h:i A') }} PHT</p>
                                    <span class="mt-1 inline-flex rounded bg-[#ffdeaa]/60 px-2 py-0.5 font-mono text-[10px] font-bold text-secondary">In transit - {{ max(10, $offsiteLaundry % 45) }} mins away</span>
                                </div>
                                <div class="text-right">
                                    <p class="font-mono text-[11px] font-bold text-slate-500">MANIFEST ID</p>
                                    <p class="font-mono text-lg font-bold text-primary">#LAU-{{ now()->format('md') }}</p>
                                    <p class="text-xs font-bold text-secondary">{{ $offsiteLaundry }} pcs clean</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-primary">Subic Laundry Central</p>
                                    <p class="mt-1 text-xs font-mono text-slate-500">{{ now()->format('h:i A') }} PHT - Driver: R. Navarro (Van #4)</p>
                                </div>
                                <p class="text-xs font-bold text-secondary">{{ $cleanDepotStock + $offsiteLaundry }} Pcs Clean</p>
                            </div>
                        </div>
                        <div class="mt-5 space-y-4">
                            @foreach ([
                                ['label' => 'Dispatched from Subic Laundry Central', 'time' => now()->subMinutes(55)->format('h:i A'), 'done' => true],
                                ['label' => 'Arrived at service gate', 'time' => now()->subMinutes(18)->format('h:i A'), 'done' => true],
                                ['label' => 'RFID count and quality receiving', 'time' => 'Pending housekeeping sign-off', 'done' => false],
                            ] as $step)
                                <div class="flex gap-3">
                                    <span class="material-symbols-outlined mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $step['done'] ? 'bg-emerald-50 text-emerald-700' : 'bg-[#f6f3ed] text-secondary' }} text-base">{{ $step['done'] ? 'check_circle' : 'pending' }}</span>
                                    <div>
                                        <p class="font-bold text-primary">{{ $step['label'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $step['time'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-line bg-[#f6f3ed] px-4 py-2.5 text-sm font-bold text-primary hover:bg-line">
                            <span class="material-symbols-outlined text-base text-secondary">receipt_long</span>
                            Inspect Manifest #LAU-{{ now()->format('md') }}
                        </button>
                    </div>

                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Life Cycle Audit</p>
                                <h3 class="mt-1 font-serif text-2xl font-bold text-primary">Linen Life Cycle &amp; Thread Degradation Log</h3>
                            </div>
                            <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">history_toggle_off</span>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-600">RFID wash-cycle telemetry tracking fabric strength, chemical wash degradation, and scheduled depreciation retirement.</p>
                        <div class="mt-5 space-y-4">
                            @foreach ([
                                ['name' => '400TC Percale Bedding Series', 'value' => 51, 'meta' => '62 / 120 wash cycles - Fabric integrity 94%'],
                                ['name' => 'Egyptian Cotton 900gsm Towels', 'value' => 88, 'meta' => '88 / 100 wash cycles - Re-order watch'],
                                ['name' => 'Velour Shawl Plush Bathrobes', 'value' => 42, 'meta' => '34 / 80 wash cycles - Softness 4.9/5.0'],
                            ] as $cycle)
                                <div>
                                    <div class="mb-1 flex justify-between gap-3 text-xs font-bold">
                                        <span class="text-primary">{{ $cycle['name'] }}</span>
                                        <span class="font-mono text-slate-600">{{ $cycle['value'] }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-[#f0eee8]">
                                        <div class="h-full rounded-full {{ $cycle['value'] > 80 ? 'bg-secondary' : 'bg-primary' }}" style="width: {{ $cycle['value'] }}%"></div>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">{{ $cycle['meta'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-5 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Items under 50 washes</span><strong class="text-primary">{{ max(18, $totalRooms * 6) }}</strong></div>
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Aging inspection queue</span><strong class="text-secondary">{{ max(4, $maintenanceQueue->count() + 3) }}</strong></div>
                            <div class="flex justify-between"><span class="text-slate-600">Damaged / scrap review</span><strong class="text-red-700">{{ max(1, $priorityRooms->count()) }}</strong></div>
                        </div>
                        <button type="button" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-secondary px-4 py-2.5 text-sm font-bold text-secondary hover:bg-secondary/10">
                            <span class="material-symbols-outlined text-base">fact_check</span>
                            Log Damaged Linen Inspection
                        </button>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'housekeeping-supplies')
        @php
            $supplyDemand = max(1, $cleaningRooms->count() + $occupiedRooms);
            $floorCarts = max(1, min(6, $assignedRooms->count() + $cleaningRooms->count()));
            $supplyParHealth = $totalRooms > 0 ? min(100, max(72, round(($availableRooms + $reservedRooms + $occupiedRooms) / max($totalRooms, 1) * 94))) : 94;
            $amenityStock = [
                ['sku' => 'AMN-BATH-001', 'name' => 'Luxury Bath Amenity Set', 'location' => 'Central stockroom A-02', 'current' => 120 + ($availableRooms * 4), 'par' => max(100, $totalRooms * 20), 'status' => 'Par Normal', 'tone' => 'emerald', 'icon' => 'soap'],
                ['sku' => 'AMN-COFF-014', 'name' => 'In-Room Coffee & Tea Replenishment Pack', 'location' => 'Guest pantry rack C-01', 'current' => 86 + ($reservedRooms * 6), 'par' => max(90, $totalRooms * 18), 'status' => 'Monitor', 'tone' => 'amber', 'icon' => 'local_cafe'],
                ['sku' => 'AMN-SLIP-006', 'name' => 'Disposable Slippers & Laundry Bag Kit', 'location' => 'Linen depot shelf B-04', 'current' => 75 + ($availableRooms * 3), 'par' => max(80, $totalRooms * 16), 'status' => 'Par Normal', 'tone' => 'emerald', 'icon' => 'dry_cleaning'],
            ];
            $chemicalStock = [
                ['sku' => 'CHM-QUAT-5L', 'name' => 'Hospital-Grade Quaternary Disinfectant (5L refill)', 'location' => 'Chemical cage batch QD-10', 'current' => max(2, 6 - $cleaningRooms->count()), 'par' => 8, 'status' => 'Critical Par', 'tone' => 'red', 'icon' => 'sanitizer'],
                ['sku' => 'CHM-GLASS-2L', 'name' => 'Streak-Free Glass Cleaner Concentrate', 'location' => 'Eco dilution rack G-02', 'current' => 18 + $availableRooms, 'par' => 12, 'status' => 'Well Stocked', 'tone' => 'emerald', 'icon' => 'water_drop'],
                ['sku' => 'CHM-STONE-1L', 'name' => 'Stone & Marble Neutral Cleaner', 'location' => 'Special care locker S-03', 'current' => 10 + $maintenanceQueue->count(), 'par' => 9, 'status' => 'Par Safe', 'tone' => 'emerald', 'icon' => 'cleaning_services'],
            ];
            $equipmentStock = [
                ['name' => 'Microfiber Color-Coded Cloth Bundle', 'current' => 45 + ($floorCarts * 3), 'par' => 30, 'caption' => 'Optimal buffer for floor attendants', 'icon' => 'laundry'],
                ['name' => 'Vacuum HEPA Replacement Filter', 'current' => 14, 'par' => 10, 'caption' => 'Weekly machine readiness stock', 'icon' => 'air'],
            ];
            $lowStockCount = collect($chemicalStock)->where('tone', 'red')->count();
            $supplyRows = collect($amenityStock)->merge($chemicalStock);
            $rowTone = [
                'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'amber' => 'border-amber-200 bg-amber-50 text-amber-800',
                'red' => 'border-red-200 bg-red-50 text-red-800',
            ];
        @endphp

        <section class="space-y-6">
            <div class="rounded-lg border border-line bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-start">
                    <div>
                        <p class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary">
                            <span>Housekeeping</span>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span>Linen &amp; Supplies</span>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span>Par Stock Hub</span>
                        </p>
                        <h3 class="mt-2 font-serif text-3xl font-bold text-primary">Housekeeping Supplies &amp; Chemical Par Stock Hub</h3>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Central stockroom inventory, guest bathroom toiletries, eco-cleaning chemical dilution par, and floor cart dispensing requisitions.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm hover:bg-slate-50">
                            <span class="material-symbols-outlined text-base text-secondary">print</span>
                            Print Inventory Audit Sheet
                        </button>
                        <a href="{{ route('housekeeping.page', 'linen-records') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">assignment_add</span>
                            Create Stock Requisition
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Supply Par Health</p>
                        <span class="material-symbols-outlined rounded-lg bg-[#ffdeaa]/40 p-2 text-secondary">verified</span>
                    </div>
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <p class="font-serif text-3xl font-bold text-primary">{{ $supplyParHealth }}%</p>
                        <span class="text-xs font-bold text-secondary">Stocked</span>
                    </div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[#f0eee8]">
                        <div class="h-full rounded-full bg-secondary" style="width: {{ $supplyParHealth }}%"></div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">Central baseline par maintained</p>
                </div>
                <div class="relative overflow-hidden rounded-lg border border-red-200 bg-white p-5 shadow-sm">
                    <div class="absolute right-0 top-0 h-16 w-16 rounded-bl-full bg-red-50"></div>
                    <div class="relative flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-red-700">Low Stock Alerts</p>
                        <span class="material-symbols-outlined rounded-lg bg-red-50 p-2 text-red-700">warning</span>
                    </div>
                    <p class="relative mt-4 font-serif text-3xl font-bold text-primary">{{ $lowStockCount }}</p>
                    <p class="relative mt-2 text-sm font-semibold text-red-700">Quat Sanitizer 5L refill</p>
                    <p class="relative mt-1 text-sm text-slate-500">Estimated {{ max(1, 3 - $cleaningRooms->count()) }} operational days remaining</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Floor Carts Provisioned</p>
                        <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">trolley</span>
                    </div>
                    <p class="mt-4 font-serif text-3xl font-bold text-primary">{{ $floorCarts }}</p>
                    <p class="mt-2 text-sm text-slate-500">Active attendant cart kits prepared</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Today&apos;s Room Demand</p>
                        <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">meeting_room</span>
                    </div>
                    <p class="mt-4 font-serif text-3xl font-bold text-primary">{{ $supplyDemand }}</p>
                    <p class="mt-2 text-sm text-slate-500">Occupied plus turnover rooms using supplies</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
                <div class="space-y-6">
                    <section class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                        <div class="flex flex-col justify-between gap-3 border-b border-line bg-[#f6f3ed] px-5 py-4 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined rounded-lg bg-white p-2 text-secondary">room_service</span>
                                <div>
                                    <h3 class="font-serif text-2xl font-bold text-primary">1. Luxury Guest Room Amenities &amp; Toiletries</h3>
                                    <p class="text-sm text-slate-500">Guest consumables, replenishment kits, and bathroom amenity controls.</p>
                                </div>
                            </div>
                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">{{ count($amenityStock) }} Active SKUs</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-[#f0eee8] text-xs uppercase tracking-wider text-slate-600">
                                    <tr>
                                        <th class="px-5 py-3">Supply Item</th>
                                        <th class="px-4 py-3">Current Stock</th>
                                        <th class="px-4 py-3">Par Level</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-5 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($amenityStock as $item)
                                        <tr>
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">{{ $item['icon'] }}</span>
                                                    <div>
                                                        <p class="font-bold text-primary">{{ $item['name'] }}</p>
                                                        <p class="mt-1 font-mono text-xs text-slate-500">{{ $item['sku'] }} - {{ $item['location'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 font-bold text-primary">{{ $item['current'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $item['par'] }}</td>
                                            <td class="px-4 py-4">
                                                <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $rowTone[$item['tone']] }}">{{ round(($item['current'] / max($item['par'], 1)) * 100) }}% {{ $item['status'] }}</span>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <button type="button" class="rounded-lg border border-line px-3 py-2 text-xs font-bold text-primary hover:border-secondary">Recount</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                        <div class="flex flex-col justify-between gap-3 border-b border-line bg-[#f6f3ed] px-5 py-4 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined rounded-lg bg-white p-2 text-secondary">science</span>
                                <div>
                                    <h3 class="font-serif text-2xl font-bold text-primary">2. Cleaning Chemicals &amp; Sanitizing Agents</h3>
                                    <p class="text-sm text-slate-500">Eco-dilution dispensers, compliant sanitizers, and specialized stone care.</p>
                                </div>
                            </div>
                            <span class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-bold text-red-800">{{ $lowStockCount }} Action Required</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-[#f0eee8] text-xs uppercase tracking-wider text-slate-600">
                                    <tr>
                                        <th class="px-5 py-3">Chemical / Formulation</th>
                                        <th class="px-4 py-3">Stock on Hand</th>
                                        <th class="px-4 py-3">Par Threshold</th>
                                        <th class="px-4 py-3">Condition</th>
                                        <th class="px-5 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($chemicalStock as $item)
                                        <tr>
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">{{ $item['icon'] }}</span>
                                                    <div>
                                                        <p class="font-bold {{ $item['tone'] === 'red' ? 'text-red-700' : 'text-primary' }}">{{ $item['name'] }}</p>
                                                        <p class="mt-1 font-mono text-xs text-slate-500">{{ $item['sku'] }} - {{ $item['location'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 font-bold text-primary">{{ $item['current'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $item['par'] }}</td>
                                            <td class="px-4 py-4">
                                                <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $rowTone[$item['tone']] }}">{{ $item['status'] }}</span>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <button type="button" class="rounded-lg {{ $item['tone'] === 'red' ? 'bg-primary text-white' : 'border border-line text-primary' }} px-3 py-2 text-xs font-bold hover:opacity-90">{{ $item['tone'] === 'red' ? 'Reorder' : 'Verify' }}</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3 border-b border-line pb-4">
                            <div>
                                <h3 class="font-serif text-2xl font-bold text-primary">3. Equipment Consumables &amp; Cart Gear</h3>
                                <p class="mt-1 text-sm text-slate-500">Floor machinery consumables, vacuum elements, and microfiber gear.</p>
                            </div>
                            <span class="rounded-full bg-[#f6f3ed] px-3 py-1 text-xs font-bold text-secondary">{{ count($equipmentStock) }} Active SKUs</span>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($equipmentStock as $item)
                                <div class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="material-symbols-outlined rounded-lg bg-white p-2 text-secondary">{{ $item['icon'] }}</span>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-800">{{ round(($item['current'] / max($item['par'], 1)) * 100) }}%</span>
                                    </div>
                                    <h4 class="mt-3 font-bold text-primary">{{ $item['name'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item['caption'] }}</p>
                                    <div class="mt-3 flex justify-between border-t border-line pt-3 text-sm">
                                        <span>Current: <strong>{{ $item['current'] }}</strong></span>
                                        <span>Minimum Par: <strong>{{ $item['par'] }}</strong></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <aside class="space-y-5">
                    <div class="rounded-lg border-2 border-secondary/30 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3 border-b border-line pb-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Dispensing Drawer</p>
                                <h3 class="mt-1 font-serif text-2xl font-bold text-primary">Cart Stocking Quick Form</h3>
                            </div>
                            <span class="material-symbols-outlined rounded-lg bg-[#ffdeaa]/40 p-2 text-secondary">trolley</span>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-600">Allocate pre-bundled floor supply kits or chemical refill batches directly to scheduled floor attendants.</p>

                        <form class="mt-5 space-y-4" onsubmit="event.preventDefault();">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Attendant Cart</label>
                                <select class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary">
                                    <option>Main Wing Cart A - Morning Shift</option>
                                    <option>Ocean Wing Cart B - Turnover Shift</option>
                                    <option>Executive Villa Cart - VIP Service</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Dispensing Pack</label>
                                <select class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary">
                                    <option>Standard Restock Pack (Toiletries + 2 Glass Cleaner Refills)</option>
                                    <option>Turn-Down Service Amenity Topper Pack</option>
                                    <option>Heavy Chemical &amp; Deep Clean Sanitizer Pack</option>
                                    <option>Custom Items Line-by-Line</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Rooms Served</label>
                                    <input class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary" type="number" min="1" value="{{ $supplyDemand }}" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Batch Count</label>
                                    <input class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary" type="number" min="1" value="{{ max(1, $floorCarts) }}" />
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Audit Notes / Dispenser Badge</label>
                                <input class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary" type="text" value="Dispensed by Lead Keeper E. Reyes" />
                            </div>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primary/90">
                                <span class="material-symbols-outlined text-base text-[#ffdeaa]">inventory_2</span>
                                Dispense &amp; Update Stock
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-line bg-[#f6f3ed] p-5">
                        <h3 class="flex items-center gap-2 font-bold text-primary">
                            <span class="material-symbols-outlined text-secondary">fact_check</span>
                            Stockkeeper Compliance Notes
                        </h3>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="flex gap-2"><span class="material-symbols-outlined text-base text-emerald-700">check_circle</span><span>Chemical batches must be recorded before cart release.</span></div>
                            <div class="flex gap-2"><span class="material-symbols-outlined text-base text-emerald-700">check_circle</span><span>Low par items require supervisor approval for emergency pull.</span></div>
                            <div class="flex gap-2"><span class="material-symbols-outlined text-base text-emerald-700">check_circle</span><span>Room demand is estimated from current occupied and turnover rooms.</span></div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'reports')
        @php
            $completedCount = $completedTasks->count();
            $releasedCount = $completedTasks->where('status', 'available')->count();
            $qaYield = $completedCount > 0 ? min(100, round(($releasedCount / max($completedCount, 1)) * 98.4, 1)) : 98.4;
            $rushSla = $priorityRooms->count() > 0 ? max(85, 100 - ($maintenanceQueue->count() * 4)) : 100;
            $turnaroundMinutes = max(28, 52 - ($availableRooms * 3) + ($maintenanceQueue->count() * 4));
            $attendantRows = [
                ['name' => 'Elena Reyes', 'zone' => 'Main Wing', 'rooms' => max(1, $cleaningRooms->count() + 2), 'score' => 98, 'result' => 'Passed first audit', 'callbacks' => $maintenanceQueue->count()],
                ['name' => 'Carlos Mendoza', 'zone' => 'Ocean Wing', 'rooms' => max(1, $availableRooms), 'score' => 96, 'result' => 'Ready release verified', 'callbacks' => 0],
                ['name' => 'Grace Macapagal', 'zone' => 'Executive Villas', 'rooms' => max(1, $occupiedRooms), 'score' => 94, 'result' => 'Guest request support', 'callbacks' => $guestRequestRooms->count()],
            ];
            $reportCards = [
                ['title' => 'Daily Housekeeper Performance & Pacing Ledger', 'badge' => 'Official Audit Ledger', 'body' => 'Full shift timestamp logs, individual room turnaround intervals, and discrepancy notes.', 'icon' => 'assignment', 'format' => 'PDF / XLSX', 'tone' => 'red'],
                ['title' => 'White Glove Quality Audit Compliance Digest', 'badge' => 'Quality Compliance', 'body' => '5-star housekeeping standards checklist, linen freshness benchmarks, and VIP turn logs.', 'icon' => 'verified', 'format' => 'PDF', 'tone' => 'red'],
                ['title' => 'Chemical Consumption & Par Stock Variance', 'badge' => 'Stock Analytics', 'body' => 'Cleaning chemical drawdowns, refill batches, and low-par consumable alerts.', 'icon' => 'science', 'format' => 'XLSX', 'tone' => 'amber'],
            ];
            $issueInsights = [
                ['label' => 'Engineering defects', 'value' => $maintenanceQueue->count(), 'caption' => 'Housekeeping-flagged unit defects for Engineering.', 'tone' => $maintenanceQueue->count() > 0 ? 'text-red-700' : 'text-emerald-700', 'icon' => 'build'],
                ['label' => 'Priority escalations', 'value' => $priorityRooms->count(), 'caption' => 'VIP, rush, or guest-impacting room readiness items.', 'tone' => 'text-secondary', 'icon' => 'priority_high'],
                ['label' => 'Room status updates', 'value' => $recentRoomLogs->count(), 'caption' => 'Latest room state changes available for reporting.', 'tone' => 'text-primary', 'icon' => 'history'],
            ];
        @endphp

        <section class="space-y-6">
            <div class="rounded-lg border border-line bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary">
                            <span class="material-symbols-outlined text-sm">monitoring</span>
                            Quality Compliance
                        </div>
                        <h3 class="font-serif text-3xl font-bold text-primary">Housekeeping Operational Reports &amp; SLA Analytics</h3>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Turnaround velocity analytics, attendant productivity metrics, quality audit compliance, and chemical consumption reports from live housekeeping activity.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm hover:bg-slate-50">
                            <span class="material-symbols-outlined text-base text-secondary">tune</span>
                            Generate Custom Report
                        </button>
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">download</span>
                            Export Departmental Dossier
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Quality Audit First-Pass Yield</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="font-serif text-3xl font-bold text-primary">{{ $qaYield }}%</span>
                        <span class="text-xs font-bold text-secondary">5-Star Standard</span>
                    </div>
                    <div class="mt-4 h-1.5 rounded-full bg-[#f0eee8]"><div class="h-full rounded-full bg-secondary" style="width: {{ $qaYield }}%"></div></div>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">VIP Rush SLA Compliance</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="font-serif text-3xl font-bold text-secondary">{{ $rushSla }}%</span>
                        <span class="text-xs font-bold text-primary">On-Time</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">{{ $priorityRooms->count() }} priority rooms tracked today</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Average Turnaround Velocity</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="font-serif text-3xl font-bold text-primary">{{ $turnaroundMinutes }}</span>
                        <span class="text-xs font-bold text-slate-500">minutes</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">{{ $cleaningRooms->count() }} active turnovers in queue</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Completed Shift Records</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="font-serif text-3xl font-bold text-primary">{{ $completedCount }}</span>
                        <span class="text-xs font-bold text-emerald-700">{{ $releasedCount }} released</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Certified status logs available</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
                <div class="space-y-6">
                    <section class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="flex flex-col justify-between gap-3 border-b border-line pb-4 md:flex-row md:items-center">
                            <div>
                                <h3 class="font-serif text-2xl font-bold text-primary">Operational Exception Intelligence</h3>
                                <p class="mt-1 text-sm text-slate-500">Housekeeping exceptions grouped for daily stand-up review.</p>
                            </div>
                            <span class="rounded-full bg-[#f6f3ed] px-3 py-1 text-xs font-bold text-secondary">{{ collect($issueInsights)->sum('value') }} report signals</span>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            @foreach ($issueInsights as $insight)
                                <div class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $insight['label'] }}</p>
                                            <p class="mt-2 font-serif text-3xl font-bold {{ $insight['tone'] }}">{{ $insight['value'] }}</p>
                                        </div>
                                        <span class="material-symbols-outlined rounded-lg bg-white p-2 text-secondary">{{ $insight['icon'] }}</span>
                                    </div>
                                    <p class="mt-3 text-sm leading-5 text-slate-600">{{ $insight['caption'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                        <div class="flex flex-col justify-between gap-3 border-b border-line p-5 md:flex-row md:items-center">
                            <div>
                                <h3 class="font-serif text-2xl font-bold text-primary">Attendant Productivity &amp; Quality Audit Ledger</h3>
                                <p class="mt-1 text-sm text-slate-500">Real-time floor performance, QA scoring index, and callback logging.</p>
                            </div>
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary hover:border-secondary">
                                <span class="material-symbols-outlined text-base text-secondary">file_download</span>
                                Export Ledger
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-[#f0eee8] text-xs uppercase tracking-wider text-slate-600">
                                    <tr>
                                        <th class="px-5 py-3">Attendant / Zone</th>
                                        <th class="px-5 py-3">Rooms Completed</th>
                                        <th class="px-5 py-3">QA Audit Score</th>
                                        <th class="px-5 py-3">Inspection Result</th>
                                        <th class="px-5 py-3 text-right">Defect Callbacks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($attendantRows as $row)
                                        <tr>
                                            <td class="px-5 py-4">
                                                <p class="font-bold text-primary">{{ $row['name'] }}</p>
                                                <p class="text-xs text-slate-500">{{ $row['zone'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 font-bold text-primary">{{ $row['rooms'] }}</td>
                                            <td class="px-5 py-4">
                                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800">{{ $row['score'] }}%</span>
                                            </td>
                                            <td class="px-5 py-4 text-slate-600">{{ $row['result'] }}</td>
                                            <td class="px-5 py-4 text-right font-bold {{ $row['callbacks'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $row['callbacks'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="grid gap-4 lg:grid-cols-3">
                        @foreach ($reportCards as $card)
                            <article class="rounded-lg border border-line bg-white p-5 shadow-sm transition hover:border-secondary">
                                <div class="flex items-start gap-4">
                                    <span class="material-symbols-outlined flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $card['tone'] === 'red' ? 'bg-red-50 text-red-700' : 'bg-[#ffdeaa]/40 text-secondary' }}">{{ $card['icon'] }}</span>
                                    <div>
                                        <span class="inline-block rounded bg-[#f6f3ed] px-2 py-0.5 text-xs font-bold text-slate-600">{{ $card['badge'] }}</span>
                                        <h4 class="mt-2 font-bold text-primary">{{ $card['title'] }}</h4>
                                        <p class="mt-2 text-sm leading-5 text-slate-600">{{ $card['body'] }}</p>
                                    </div>
                                </div>
                                <div class="mt-5 flex items-center justify-between border-t border-line pt-4">
                                    <span class="text-xs font-bold text-slate-500">{{ $card['format'] }}</span>
                                    <button type="button" class="rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white hover:bg-primary/90">Download</button>
                                </div>
                            </article>
                        @endforeach
                    </section>
                </div>

                <aside class="space-y-5">
                    <div class="rounded-lg border border-secondary/40 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Report Console</p>
                                <h3 class="mt-1 font-serif text-2xl font-bold text-primary">Custom Report Builder</h3>
                            </div>
                            <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">summarize</span>
                        </div>
                        <form class="mt-5 space-y-4" onsubmit="event.preventDefault();">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Report Type</label>
                                <select class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary">
                                    <option>Daily Housekeeping Performance</option>
                                    <option>Quality Audit Compliance</option>
                                    <option>Chemical Consumption</option>
                                    <option>Maintenance Callback Summary</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">From</label>
                                    <input class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary" type="date" value="{{ now()->toDateString() }}" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">To</label>
                                    <input class="w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary" type="date" value="{{ now()->toDateString() }}" />
                                </div>
                            </div>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primary/90">
                                <span class="material-symbols-outlined text-base text-[#ffdeaa]">analytics</span>
                                Generate Report Preview
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-line bg-[#f6f3ed] p-5">
                        <h3 class="flex items-center gap-2 font-bold text-primary">
                            <span class="material-symbols-outlined text-secondary">shield</span>
                            Compliance Snapshot
                        </h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Room audit records</span><strong>{{ $recentRoomLogs->count() }}</strong></div>
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Completed QA entries</span><strong>{{ $completedCount }}</strong></div>
                            <div class="flex justify-between"><span class="text-slate-600">Maintenance callbacks</span><strong>{{ $maintenanceQueue->count() }}</strong></div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'tasks')
        @php
            $activeTurnovers = max(1, $cleaningRooms->count());
            $vipRush = max(1, min(3, $priorityRooms->count()));
            $readyPercent = $totalRooms > 0 ? round(($availableRooms / max($totalRooms, 1)) * 100) : 0;
            $occupiedPercent = $totalRooms > 0 ? round(($occupiedRooms / max($totalRooms, 1)) * 100) : 0;
            $cleaningPercent = $totalRooms > 0 ? round(($cleaningRooms->count() / max($totalRooms, 1)) * 100) : 0;
            $maintenancePercent = $totalRooms > 0 ? round(($maintenanceQueue->count() / max($totalRooms, 1)) * 100) : 0;
            $taskRooms = $priorityRooms->merge($cleaningRooms)->unique('id')->take(3)->values();
            $attendants = [
                ['name' => 'Carlos Reyes', 'initials' => 'CR', 'zone' => 'Ocean Wing - Fl 3-4', 'quota' => '7 / 8 Keys', 'pace' => 88],
                ['name' => 'Elena Dimaculangan', 'initials' => 'ED', 'zone' => 'Main Wing - Fl 1-2', 'quota' => '6 / 8 Keys', 'pace' => 75],
                ['name' => 'Mark Tagalog', 'initials' => 'MT', 'zone' => 'Villa Sector', 'quota' => '5 / 6 Keys', 'pace' => 83],
            ];
        @endphp

        <section class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-[#172b42]/40 bg-gradient-to-r from-primary via-[#172b42] to-primary p-7 text-white shadow-xl">
                <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-[#ffdeaa]/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-[#ffdeaa]/40 bg-[#ffdeaa]/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[#ffdeaa]">
                            <span class="material-symbols-outlined text-sm">done_all</span>
                            Housekeeping Tasks &amp; Turnover Workspace
                        </div>
                        <h3 class="mt-4 font-serif text-4xl font-bold leading-tight">Housekeeping Tasks &amp; Operational Dispatch</h3>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200">
                            Daily cleaning roster, priority turnover acceleration, and verified inspection pipelines across {{ $totalRooms }} active hotel rooms.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <select class="rounded-lg border border-white/20 bg-[#172b42] px-3 py-2.5 text-xs font-bold text-white focus:border-[#ffdeaa] focus:ring-[#ffdeaa]">
                            <option>All Wings ({{ $totalRooms }} Units)</option>
                            <option>Main Wing</option>
                            <option>Ocean Wing</option>
                            <option>Villa Sector</option>
                        </select>
                        <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-xs font-bold text-white transition hover:border-[#ffdeaa]/60 hover:bg-white/15">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">dashboard</span>
                            Open Room Status
                        </a>
                        <a href="{{ route('housekeeping.page', 'priority-rooms') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#ffdeaa] px-4 py-2.5 text-xs font-extrabold text-primary shadow-lg transition hover:bg-[#e6c185]">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            Dispatch Urgent Turnover
                        </a>
                    </div>
                </div>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    ['label' => 'Total Rooms', 'value' => $totalRooms, 'caption' => 'Active inventory', 'icon' => 'apartment', 'tone' => 'text-primary', 'bar' => 'bg-primary', 'percent' => 100],
                    ['label' => 'Available & Ready', 'value' => $availableRooms, 'caption' => $readyPercent.'% of inventory', 'icon' => 'check_circle', 'tone' => 'text-emerald-700', 'bar' => 'bg-emerald-600', 'percent' => $readyPercent],
                    ['label' => 'Occupied', 'value' => $occupiedRooms, 'caption' => $occupiedPercent.'% in-house', 'icon' => 'bed', 'tone' => 'text-sky-700', 'bar' => 'bg-sky-600', 'percent' => $occupiedPercent],
                    ['label' => 'In Turnover', 'value' => $cleaningRooms->count(), 'caption' => $vipRush.' VIP rush', 'icon' => 'mop', 'tone' => 'text-amber-700', 'bar' => 'bg-amber-500', 'percent' => max(5, $cleaningPercent)],
                    ['label' => 'Maintenance Hold', 'value' => $maintenanceQueue->count(), 'caption' => $maintenanceQueue->count() ? 'Engineering queue' : 'No open holds', 'icon' => 'construction', 'tone' => 'text-red-700', 'bar' => 'bg-red-600', 'percent' => max(3, $maintenancePercent)],
                ] as $metric)
                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                        <div class="flex items-start justify-between">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                            <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                        </div>
                        <p class="mt-4 font-serif text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[#f0eee8]">
                            <div class="h-full rounded-full {{ $metric['bar'] }}" style="width: {{ min(100, $metric['percent']) }}%"></div>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-slate-500">{{ $metric['caption'] }}</p>
                    </div>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="space-y-4 xl:col-span-8">
                    <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-line bg-[#f6f3ed] p-1">
                                <a href="{{ route('housekeeping.page', 'cleaning-tasks') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm">
                                    <span class="material-symbols-outlined text-base text-[#ffdeaa]">local_fire_department</span>
                                    Active Cleaning Tasks
                                    <span class="rounded-full bg-[#ffdeaa] px-1.5 text-[10px] text-primary">{{ $cleaningRooms->count() }}</span>
                                </a>
                                <a href="{{ route('housekeeping.page', 'priority-rooms') }}" class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-xs font-bold text-slate-600 hover:bg-white">
                                    <span class="material-symbols-outlined text-base text-amber-600">stars</span>
                                    Priority Rooms
                                    <span class="rounded-full bg-white px-1.5 text-[10px]">{{ $priorityRooms->count() }}</span>
                                </a>
                                <a href="{{ route('housekeeping.page', 'completed-tasks') }}" class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-xs font-bold text-slate-600 hover:bg-white">
                                    <span class="material-symbols-outlined text-base text-emerald-700">done_all</span>
                                    Completed Tasks
                                    <span class="font-mono text-[11px] text-slate-400">({{ $completedTasks->count() }})</span>
                                </a>
                            </div>
                            <div class="flex gap-2">
                                <label class="relative">
                                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-400">search</span>
                                    <input type="search" placeholder="Search room, attendant..." class="w-56 rounded-lg border-line py-2 pl-9 text-xs focus:border-secondary focus:ring-secondary">
                                </label>
                                <button type="button" class="rounded-lg border border-line bg-[#f6f3ed] p-2 text-slate-600 hover:bg-line" title="Filter Options">
                                    <span class="material-symbols-outlined text-lg">tune</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        @forelse ($taskRooms as $room)
                            @php
                                $isPriority = in_array($room->status, ['maintenance', 'cleaning'], true);
                                $progress = $room->status === 'maintenance' ? 25 : ($room->status === 'cleaning' ? 62 : 88);
                                $tone = $room->status === 'maintenance' ? 'red' : ($isPriority ? 'amber' : 'sky');
                                $toneClasses = [
                                    'red' => 'border-l-red-600 bg-red-50 text-red-700',
                                    'amber' => 'border-l-amber-500 bg-amber-50 text-amber-800',
                                    'sky' => 'border-l-sky-600 bg-sky-50 text-sky-800',
                                ][$tone];
                                $barClass = ['red' => 'bg-red-600', 'amber' => 'bg-amber-500', 'sky' => 'bg-sky-600'][$tone];
                                $attendant = $attendants[$loop->index % count($attendants)];
                            @endphp
                            <article class="rounded-xl border border-line border-l-4 bg-white p-5 shadow-sm transition hover:shadow-md {{ $toneClasses }}">
                                <div class="flex flex-col justify-between gap-4 border-b border-line pb-4 md:flex-row md:items-center">
                                    <div class="flex items-center gap-3.5">
                                        <div class="flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-primary text-white shadow">
                                            <span class="text-[9px] uppercase tracking-wider text-slate-300">Room</span>
                                            <span class="font-serif text-base font-bold leading-none">{{ $room->room_number }}</span>
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-serif text-lg font-bold text-primary">Room {{ $room->room_number }} - {{ $room->room_type }}</h3>
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase {{ $toneClasses }}">
                                                    {{ $room->status === 'maintenance' ? 'Engineering Hold' : 'Turnover Active' }}
                                                </span>
                                            </div>
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $room->capacity }} guests capacity - PHP {{ number_format($room->rate, 2) }} room class</p>
                                        </div>
                                    </div>
                                    <div class="rounded-lg border border-line bg-[#f6f3ed] px-3 py-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">SLA Pacing</p>
                                        <p class="font-mono text-xs font-bold text-primary">{{ $room->status === 'maintenance' ? 'Engineering response' : '32m target turnover' }}</p>
                                    </div>
                                </div>
                                <div class="grid gap-4 pt-4 md:grid-cols-12 md:items-center">
                                    <div class="flex items-center gap-3 md:col-span-4">
                                        <div class="relative">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#f0eee8] text-xs font-bold text-primary ring-2 ring-line">{{ $attendant['initials'] }}</div>
                                            <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-primary">{{ $attendant['name'] }}</p>
                                            <p class="flex items-center gap-1 text-[11px] text-slate-500"><span class="material-symbols-outlined text-xs text-emerald-700">radio</span>{{ $attendant['zone'] }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-1.5 md:col-span-4">
                                        <div class="flex justify-between text-[11px] font-semibold">
                                            <span class="text-slate-600">{{ $room->status === 'maintenance' ? 'Repair triage & room hold' : 'Sanitization & linen reset' }}</span>
                                            <span class="font-mono text-secondary">{{ $progress }}%</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-[#f0eee8]">
                                            <div class="h-full rounded-full {{ $barClass }}" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <p class="text-[10px] text-slate-400">{{ $room->status === 'maintenance' ? 'Awaiting engineering clearance' : 'Checklist pacing is actively monitored' }}</p>
                                    </div>
                                    <div class="flex justify-end gap-2 md:col-span-4">
                                        <a href="{{ route('rooms.edit', $room) }}" class="rounded-lg border border-line bg-[#f6f3ed] px-3 py-2 text-xs font-bold text-primary hover:bg-line">Audit QA</a>
                                        <a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white hover:bg-primary/90">
                                            <span class="material-symbols-outlined text-sm text-[#ffdeaa]">check</span>
                                            Verify &amp; Release
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-line bg-white p-8 text-center shadow-sm">
                                <span class="material-symbols-outlined text-4xl text-slate-300">task_alt</span>
                                <p class="mt-3 font-bold text-primary">No active housekeeping task cards right now.</p>
                                <p class="mt-1 text-sm text-slate-500">Cleaning and priority rooms will appear here when room status changes.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary">
                                <span class="material-symbols-outlined text-secondary">history</span>
                                Today's Cleared Turnover Stream
                            </h4>
                            <a href="{{ route('housekeeping.page', 'completed-tasks') }}" class="text-xs font-bold text-secondary hover:underline">View Full Activity Log</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[680px] text-left text-xs">
                                <thead class="border-b border-line bg-[#f6f3ed] text-[10px] uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2.5">Room Key</th>
                                        <th class="px-3 py-2.5">Task Scope</th>
                                        <th class="px-3 py-2.5">Attendant</th>
                                        <th class="px-3 py-2.5">Turnaround Time</th>
                                        <th class="px-3 py-2.5 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line text-slate-700">
                                    @forelse ($completedTasks->take(3) as $log)
                                        <tr>
                                            <td class="px-3 py-3 font-serif font-bold text-primary">Room {{ $log->room?->room_number ?? 'N/A' }}</td>
                                            <td class="px-3 py-3">{{ $log->notes ?: 'Checkout turnover' }}</td>
                                            <td class="px-3 py-3">{{ $attendants[$loop->index % count($attendants)]['name'] }}</td>
                                            <td class="px-3 py-3 font-mono text-slate-500">{{ 22 + ($loop->index * 4) }}m {{ 5 + $loop->index }}s</td>
                                            <td class="px-3 py-3 text-right"><span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Ready for Front Desk</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-8 text-center text-sm text-slate-500">No completed turnover logs yet today.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="space-y-4 xl:col-span-4">
                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-3">
                            <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary">
                                <span class="material-symbols-outlined text-secondary">groups</span>
                                Attendant Pacing Roster
                            </h4>
                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">{{ count($attendants) }} On Shift</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach ($attendants as $attendant)
                                <div class="flex items-center justify-between rounded-lg border border-line bg-[#f6f3ed] p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-xs font-bold text-[#ffdeaa]">{{ $attendant['initials'] }}</div>
                                        <div>
                                            <p class="text-xs font-bold text-primary">{{ $attendant['name'] }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $attendant['zone'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-mono text-xs font-bold text-primary">{{ $attendant['quota'] }}</p>
                                        <p class="text-[10px] font-semibold text-emerald-700">{{ $attendant['pace'] }}% Quota</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-line bg-primary p-5 text-white shadow-sm">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-[#ffdeaa]">
                            <span class="material-symbols-outlined">sensors</span>
                            Live Operations Telemetry
                        </h4>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-slate-300">IoT Sensors</p>
                                <p class="font-serif text-2xl font-bold">{{ max(1, $totalRooms) }}</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-slate-300">RFID Latency</p>
                                <p class="font-serif text-2xl font-bold">14ms</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-slate-300">Rush Rooms</p>
                                <p class="font-serif text-2xl font-bold">{{ $vipRush }}</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-slate-300">Ready %</p>
                                <p class="font-serif text-2xl font-bold">{{ $readyPercent }}%</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary">
                            <span class="material-symbols-outlined text-secondary">inventory_2</span>
                            Linen &amp; Amenity Par Alerts
                        </h4>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">VIP amenity sets</span><strong>{{ max(8, $reservedRooms * 3 + 8) }}</strong></div>
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Fresh linen packs</span><strong>{{ max(18, $cleaningRooms->count() * 6 + 18) }}</strong></div>
                            <div class="flex justify-between"><span class="text-slate-600">Supervisor inspection queue</span><strong>{{ $priorityRooms->count() }}</strong></div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'linen-supplies') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-secondary px-4 py-2.5 text-sm font-bold text-secondary hover:bg-secondary/10">
                            <span class="material-symbols-outlined text-base">layers</span>
                            Open Linen &amp; Supplies
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'service-requests')
        @php
            $activeRequests = max(1, $guestRequestRooms->count());
            $specialCleanCount = $cleaningRooms->count() + $maintenanceQueue->count();
            $avgSla = $activeRequests ? '08m 12s' : '00m 00s';
            $requestRooms = $guestRequestRooms->take(2)->values();
            if ($requestRooms->isEmpty()) {
                $requestRooms = $allRooms->take(2)->values();
            }
            $specialRooms = $priorityRooms->take(2)->values();
            if ($specialRooms->isEmpty()) {
                $specialRooms = $allRooms->take(2)->values();
            }
            $roomMatrix = $allRooms->take(6)->values();
            $attendants = ['Carlos Reyes', 'Liza Morales', 'Elena Cruz', 'Maria Santos', 'Restituto Ramos', 'Seanne Alonzo'];
        @endphp

        <section class="space-y-6">
            <div class="relative overflow-hidden rounded-xl border border-line bg-white p-6 shadow-sm">
                <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-secondary/5"></div>
                <div class="relative z-10 flex flex-col justify-between gap-5 xl:flex-row xl:items-center">
                    <div>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded bg-secondary/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-secondary ring-1 ring-secondary/20">Service Requests</span>
                            <span class="flex items-center gap-1 text-xs text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Floor dispatch live</span>
                        </div>
                        <h3 class="font-serif text-4xl font-bold text-primary">Service Requests Hub &amp; Guest Amenities Delivery</h3>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Guest service dispatches, amenity drop-offs, and specialized turn-down items handled by housekeeping.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-line bg-[#f6f3ed] px-4 py-2.5 text-sm font-bold text-primary shadow-sm hover:bg-line">
                            <span class="material-symbols-outlined text-base text-secondary">grid_view</span>
                            Open Room Status Matrix
                        </a>
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm hover:bg-[#f6f3ed]">
                            <span class="material-symbols-outlined text-base">file_download</span>
                            Export Log
                        </button>
                        <a href="{{ route('housekeeping.page', 'guest-requests') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">add</span>
                            Log New Service Request
                        </a>
                    </div>
                </div>
            </div>

            <section class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-6">
                @foreach ([
                    ['label' => 'Total Rooms', 'value' => $totalRooms, 'caption' => 'Active property rooms', 'icon' => 'domain', 'tone' => 'text-primary', 'border' => 'border-line'],
                    ['label' => 'Available', 'value' => $availableRooms, 'caption' => 'Ready for check-in', 'icon' => 'check_circle', 'tone' => 'text-emerald-700', 'border' => 'border-l-4 border-l-emerald-600'],
                    ['label' => 'Occupied', 'value' => $occupiedRooms, 'caption' => 'In-house guests', 'icon' => 'person', 'tone' => 'text-primary', 'border' => 'border-l-4 border-l-primary'],
                    ['label' => 'Active Requests', 'value' => $activeRequests, 'caption' => 'Guest amenity queue', 'icon' => 'notifications_active', 'tone' => 'text-amber-800', 'border' => 'border-l-4 border-l-amber-500'],
                    ['label' => 'Special Clean', 'value' => $specialCleanCount, 'caption' => 'Scheduled queue', 'icon' => 'sanitizer', 'tone' => 'text-slate-700', 'border' => 'border-line'],
                    ['label' => 'Avg SLA Speed', 'value' => $avgSla, 'caption' => 'Under target', 'icon' => 'timer', 'tone' => 'text-secondary', 'border' => 'border-l-4 border-l-secondary'],
                ] as $metric)
                    <div class="rounded-lg border bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $metric['border'] }}">
                        <div class="mb-2 flex items-center justify-between text-slate-500">
                            <span class="text-[11px] font-bold uppercase tracking-wider">{{ $metric['label'] }}</span>
                            <span class="material-symbols-outlined text-base {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                        </div>
                        <p class="font-serif text-2xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                        <p class="mt-1 text-[10px] font-medium text-slate-500">{{ $metric['caption'] }}</p>
                    </div>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-3">
                <section class="overflow-hidden rounded-xl border border-line bg-white shadow-sm xl:col-span-2">
                    <div class="flex flex-col justify-between gap-3 border-b border-line bg-[#f6f3ed] p-4 md:flex-row md:items-center">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-[#ffdeaa]">
                                <span class="material-symbols-outlined text-lg">room_service</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-primary">Guest Requests Active Queue</h3>
                                <p class="text-xs text-slate-500">Real-time room-dispatch pipeline with attendant routing</p>
                            </div>
                        </div>
                        <div class="flex gap-1 rounded-lg bg-line p-1 text-xs">
                            <button type="button" class="rounded bg-white px-3 py-1 font-bold text-primary shadow-sm">All Live ({{ max(2, $activeRequests) }})</button>
                            <button type="button" class="rounded px-3 py-1 font-bold text-slate-600 hover:bg-white">High Priority</button>
                            <button type="button" class="rounded px-3 py-1 font-bold text-slate-600 hover:bg-white">Pre-Arrivals</button>
                        </div>
                    </div>
                    <div class="space-y-3.5 p-4">
                        @foreach ($requestRooms as $room)
                            @php
                                $isUrgent = $loop->first;
                                $attendant = $attendants[$loop->index % count($attendants)];
                            @endphp
                            <article class="rounded-lg border p-4 shadow-sm transition hover:border-secondary {{ $isUrgent ? 'border-amber-300 bg-amber-50/30' : 'border-line bg-[#f6f3ed]/40' }}">
                                <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg border border-secondary/30 bg-primary text-white shadow-sm">
                                            <span class="text-[9px] font-semibold uppercase tracking-wider text-[#ffdeaa]">Room</span>
                                            <span class="font-serif text-xl font-bold leading-none">{{ $room->room_number }}</span>
                                        </div>
                                        <div>
                                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $isUrgent ? 'bg-red-100 text-red-800 ring-1 ring-red-200' : 'bg-slate-200 text-slate-700' }}">
                                                    <span class="material-symbols-outlined text-xs">{{ $isUrgent ? 'bolt' : 'luggage' }}</span>
                                                    {{ $isUrgent ? 'Urgent Guest Call' : 'Pre-Arrival Request' }}
                                                </span>
                                                <span class="text-xs font-bold text-primary">{{ $isUrgent ? 'In-House VIP Guest' : 'Front Desk Sync' }}</span>
                                                <span class="text-xs text-slate-500">{{ $isUrgent ? 'Logged 4m ago' : 'Staged for arrival' }}</span>
                                            </div>
                                            <h4 class="text-base font-bold text-primary">{{ $isUrgent ? 'Extra Percale Duvet & Sateen Bathrobe Set' : 'Infant Crib Setup & Organic Baby Toiletry Basket' }}</h4>
                                            <p class="mt-1 text-xs leading-5 text-slate-600">{{ $isUrgent ? 'Guest requested hypoallergenic extra heavy duvet and two embroidered robes to the master bedroom.' : 'Place sanitized crib with organic cotton linens and baby toiletry basket before check-in.' }}</p>
                                            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-600">
                                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-secondary">timer</span>SLA: <strong class="text-primary">{{ $isUrgent ? '10m' : '45m window' }}</strong></span>
                                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">badge</span>Runner: <strong class="text-primary">{{ $attendant }}</strong></span>
                                                <span class="rounded bg-emerald-100/70 px-2 py-0.5 text-[11px] font-bold text-emerald-800">{{ $isUrgent ? 'Dispatched & In-Transit' : 'Staged at Linen Closet' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 gap-2 md:flex-col">
                                        <button type="button" class="inline-flex items-center gap-1 rounded bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-800">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            {{ $isUrgent ? 'Mark Delivered' : 'Verify Placement' }}
                                        </button>
                                        <button type="button" class="inline-flex items-center gap-1 rounded border border-line bg-white px-3 py-1.5 text-xs font-bold text-primary hover:bg-[#f6f3ed]">
                                            <span class="material-symbols-outlined text-sm text-secondary">radio</span>
                                            Call Radio
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between border-t border-line bg-[#f6f3ed] px-4 py-3 text-xs text-slate-500">
                        <span>{{ max(2, $activeRequests) }} dispatches logged today - 100% on-time turnaround rate</span>
                        <a href="{{ route('housekeeping.page', 'guest-requests') }}" class="font-bold text-secondary hover:underline">View Past 24h Delivered Folio</a>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-line bg-[#f6f3ed] p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-secondary/20 text-secondary">
                                <span class="material-symbols-outlined text-lg">sanitizer</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-primary">Special Cleaning Hub</h3>
                                <p class="text-xs text-slate-500">Deep sanitization &amp; machinery ops</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-500">tune</span>
                    </div>
                    <div class="flex h-full flex-col justify-between space-y-3.5 p-4">
                        <div class="space-y-3">
                            @foreach ($specialRooms as $room)
                                <div class="rounded-lg border border-line bg-[#f6f3ed]/50 p-4 transition hover:border-secondary">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full {{ $loop->first ? 'bg-blue-500' : 'bg-emerald-500' }}"></span>
                                            <span class="font-bold text-primary">Room {{ $room->room_number }} - {{ $room->room_type }}</span>
                                        </div>
                                        <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $loop->first ? 'bg-blue-100 text-blue-900 ring-1 ring-blue-200' : 'bg-emerald-100 text-emerald-900 ring-1 ring-emerald-200' }}">{{ $loop->first ? 'Ozone Protocol' : 'Deep Clean' }}</span>
                                    </div>
                                    <p class="mt-2 text-xs leading-5 text-slate-600">{{ $loop->first ? 'Full atmospheric ozone degassing scheduled after guest departure.' : 'Thermo-steam carpet extraction and terrace sanitizing mist before release.' }}</p>
                                    <div class="mt-3 flex items-center justify-between border-t border-line pt-2 text-xs">
                                        <span class="flex items-center gap-1 font-semibold text-slate-600"><span class="material-symbols-outlined text-sm">schedule</span>{{ $loop->first ? 'Today' : 'Tomorrow' }} {{ now()->addHours($loop->first ? 2 : 18)->format('h:i A') }} PHT</span>
                                        <span class="font-bold text-secondary">{{ $loop->first ? 'Bio-O3 Generator #02' : 'Lead: Restituto Ramos' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-line pt-4">
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>Heavy Equipment Utilization</span>
                                <span class="font-bold text-primary">{{ min(6, max(1, $specialCleanCount)) }} / 6 In Service</span>
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-line">
                                <div class="h-full rounded-full bg-secondary" style="width: {{ min(100, max(18, $specialCleanCount * 18)) }}%"></div>
                            </div>
                            <a href="{{ route('housekeeping.page', 'special-cleaning') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-secondary px-4 py-2.5 text-sm font-bold text-secondary hover:bg-secondary/10">
                                <span class="material-symbols-outlined text-base">calendar_add_on</span>
                                Schedule Sanitization / Degassing
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <section class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-line bg-[#f6f3ed] p-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-[#ffdeaa]">
                            <span class="material-symbols-outlined text-lg">hotel</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-primary">Room Status Cross-Reference Panel</h3>
                            <p class="text-xs text-slate-500">Cohort overview showing occupancy and live service request state</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500">Wing: <strong class="text-primary">All Housekeeping Rooms</strong></span>
                        <button type="button" class="inline-flex items-center gap-1 rounded border border-line bg-white px-2.5 py-1 font-bold text-primary hover:bg-line"><span class="material-symbols-outlined text-sm">refresh</span>Auto-refreshing</button>
                    </div>
                </div>
                <div class="grid divide-y divide-line md:grid-cols-2 md:divide-x md:divide-y-0 xl:grid-cols-6">
                    @foreach ($roomMatrix as $room)
                        @php
                            $isLiveRequest = $guestRequestRooms->contains('id', $room->id);
                            $statusClass = match ($room->status) {
                                'available' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
                                'occupied' => 'bg-primary text-[#ffdeaa] ring-1 ring-secondary/30',
                                'cleaning' => 'bg-amber-100 text-amber-900 ring-1 ring-amber-200',
                                'maintenance' => 'bg-red-100 text-red-800 ring-1 ring-red-200',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <div class="flex min-h-52 flex-col justify-between space-y-3 p-4 transition hover:bg-[#f6f3ed]/50 {{ $isLiveRequest ? 'bg-amber-50/40' : '' }}">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-serif text-xl font-bold text-primary">Room {{ $room->room_number }}</span>
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $statusClass }}">{{ $room->status }}</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500">{{ $room->room_type }} - {{ $room->capacity }} guests</p>
                                <div class="mt-3 rounded border border-line bg-white px-2 py-1.5 text-xs">
                                    <span class="block text-[10px] font-bold uppercase text-slate-500">Housekeeper</span>
                                    <span class="font-semibold text-primary">{{ $attendants[$loop->index % count($attendants)] }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between border-t border-line pt-2 text-[11px]">
                                    <span class="text-slate-500">Service Request:</span>
                                    <span class="{{ $isLiveRequest ? 'font-bold text-amber-800' : 'italic text-slate-400' }}">{{ $isLiveRequest ? 'Guest Amenities' : 'None' }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-1 text-[11px] font-semibold {{ $isLiveRequest ? 'text-blue-700' : 'text-emerald-700' }}">
                                    <span class="material-symbols-outlined text-xs">{{ $isLiveRequest ? 'pending' : 'verified' }}</span>{{ $isLiveRequest ? 'Dispatch Active' : 'Front Desk Ready' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>
    @elseif ($pageKey === 'linen-supplies')
        @php
            $availableCount = (int) $availableRooms;
            $occupiedCount = (int) $occupiedRooms;
            $reservedCount = (int) $reservedRooms;
            $linenRecordCount = $cleaningRooms->count();
            $supplyAlertCount = max(1, $maintenanceQueue->count() + ($occupiedCount > 0 ? 1 : 0));
            $priorityCount = $priorityRooms->count();
            $parIndex = $totalRooms > 0 ? min(3, round((($availableCount + $occupiedCount + max(1, $linenRecordCount)) / max($totalRooms, 1)) + 1.65, 1)) : 0;
            $freshLinenPacks = max(18, ($availableCount * 3) + ($cleaningRooms->count() * 6));
            $vipAmenitySets = max(8, ($reservedCount * 3) + 8);
            $chemicalStock = max(4, 12 - $supplyAlertCount);
            $ledgerRows = [
                ['item' => 'Egyptian Cotton Sheet Set', 'sku' => 'LIN-BED-300TC', 'par' => $totalRooms * 3, 'clean' => $freshLinenPacks, 'laundry' => max(0, $cleaningRooms->count() * 4), 'status' => 'Optimal', 'tone' => 'emerald'],
                ['item' => 'Sateen Bath Towel Bundle', 'sku' => 'LIN-BATH-LUX', 'par' => $totalRooms * 6, 'clean' => max(24, $availableCount * 5), 'laundry' => max(0, $occupiedCount * 2), 'status' => 'Balanced', 'tone' => 'emerald'],
                ['item' => 'VIP Amenity & Toiletry Set', 'sku' => 'AMN-VIP-SUBIC', 'par' => max(12, $reservedCount * 4 + 12), 'clean' => $vipAmenitySets, 'laundry' => 0, 'status' => 'Buffered', 'tone' => 'slate'],
                ['item' => 'Hospital-Grade Disinfectant 5L', 'sku' => 'CHEM-HG-5L', 'par' => 12, 'clean' => $chemicalStock, 'laundry' => 0, 'status' => 'Reorder', 'tone' => 'amber'],
            ];
        @endphp

        <section class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-primary/10 bg-primary p-7 text-white shadow-lg">
                <div class="absolute -bottom-16 -right-10 h-56 w-56 rounded-full bg-secondary/20"></div>
                <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-3xl space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-[0.24em] text-[#ffdeaa]">
                                <span class="material-symbols-outlined text-sm">shield</span>
                                Records &amp; Supply Monitors
                            </span>
                            <span class="h-1.5 w-1.5 rounded-full bg-[#ffdeaa]"></span>
                            <span class="text-xs font-semibold text-slate-200">Real-time cohort telemetry</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-[#ffdeaa]/40 bg-[#ffdeaa]/15 text-[#ffdeaa]">
                                <span class="material-symbols-outlined text-3xl">layers</span>
                            </div>
                            <h3 class="font-serif text-4xl font-bold leading-tight">Linen &amp; Supplies Operational Command</h3>
                        </div>
                        <p class="max-w-3xl text-sm leading-6 text-slate-200">Circulating par stocks, laundry transit telemetry, guest luxury amenities buffer, and eco-chemical replenishment across active guestrooms.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('housekeeping.page', 'housekeeping-supplies') }}" class="inline-flex items-center gap-2 rounded-xl bg-secondary px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">add_task</span>
                            Requisition Restock
                        </a>
                        <a href="{{ route('housekeeping.page', 'linen-records') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/15">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">local_shipping</span>
                            Receive Laundry Dispatch
                        </a>
                        <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-[#ffdeaa]/40 bg-black/20 px-4 py-2.5 text-xs font-bold text-[#ffdeaa] hover:bg-black/30">
                            <span class="material-symbols-outlined text-base">bed</span>
                            Open Room Status
                        </a>
                    </div>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    ['label' => 'Total Rooms', 'value' => $totalRooms, 'caption' => 'Housekeeping cohort', 'icon' => 'domain', 'tone' => 'text-primary'],
                    ['label' => 'Available', 'value' => $availableCount, 'caption' => '100% linen staged', 'icon' => 'check_circle', 'tone' => 'text-emerald-700'],
                    ['label' => 'Occupied', 'value' => $occupiedCount, 'caption' => $occupiedCount ? 'Stayover refresh due' : 'No stayovers', 'icon' => 'king_bed', 'tone' => 'text-primary'],
                    ['label' => 'Cleaning', 'value' => $cleaningRooms->count(), 'caption' => $cleaningRooms->count() ? 'Active turns' : 'All turnovers cleared', 'icon' => 'mop', 'tone' => 'text-secondary'],
                    ['label' => 'Maintenance', 'value' => $maintenanceQueue->count(), 'caption' => $maintenanceQueue->count() ? 'Workorders open' : 'Zero out-of-order', 'icon' => 'construction', 'tone' => $maintenanceQueue->count() ? 'text-red-700' : 'text-slate-700'],
                    ['label' => 'Linen Par Index', 'value' => $parIndex, 'caption' => 'of 3.0 par buffer', 'icon' => 'scale', 'tone' => 'text-secondary'],
                ] as $metric)
                    <div class="rounded-xl border border-line bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-secondary/50 hover:shadow-md">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</span>
                            <span class="material-symbols-outlined rounded bg-[#f6f3ed] p-1.5 text-base {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                        </div>
                        <p class="font-serif text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                        <p class="mt-2 text-[11px] font-semibold text-slate-500">{{ $metric['caption'] }}</p>
                    </div>
                @endforeach
            </section>

            <section class="grid gap-5 md:grid-cols-3">
                <a href="{{ route('housekeeping.page', 'linen-records') }}" class="rounded-xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <span class="material-symbols-outlined text-base text-secondary">receipt_long</span>
                                Linen Records
                            </span>
                            <div class="mt-2 flex items-baseline gap-3">
                                <span class="font-serif text-4xl font-bold text-primary">{{ $linenRecordCount }}</span>
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">Pending audits</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined rounded-xl bg-[#f6f3ed] p-3 text-2xl text-primary">inventory</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Live par counts and laundry return records for housekeeping room readiness.</p>
                </a>

                <a href="{{ route('housekeeping.page', 'housekeeping-supplies') }}" class="relative overflow-hidden rounded-xl border border-amber-300 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="absolute left-0 right-0 top-0 h-1 bg-amber-500"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-900">
                                <span class="material-symbols-outlined text-base text-amber-700">category</span>
                                Housekeeping Supplies
                            </span>
                            <div class="mt-2 flex items-baseline gap-3">
                                <span class="font-serif text-4xl font-bold text-amber-700">{{ $supplyAlertCount }}</span>
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">Refill action</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined rounded-xl bg-amber-50 p-3 text-2xl text-amber-700">warning</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Hospital-grade disinfectant and amenity stocks are monitored against minimum par levels.</p>
                </a>

                <a href="{{ route('housekeeping.page', 'priority-rooms') }}" class="rounded-xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <span class="material-symbols-outlined text-base text-secondary">star</span>
                                Priority Rooms
                            </span>
                            <div class="mt-2 flex items-baseline gap-3">
                                <span class="font-serif text-4xl font-bold text-primary">{{ $priorityCount }}</span>
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">{{ $priorityCount ? 'Needs staging' : 'Clear flow' }}</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined rounded-xl bg-emerald-50 p-3 text-2xl text-emerald-700">verified</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Rush turnarounds, VIP linen requests, and early arrival room staging appear here.</p>
                </a>
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="space-y-5 xl:col-span-8">
                    <div class="flex flex-col gap-3 border-b border-line pb-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                            <button type="button" class="rounded-lg bg-primary px-3.5 py-2 text-white shadow-sm">All Inventories</button>
                            <button type="button" class="rounded-lg border border-line bg-white px-3.5 py-2 text-slate-600 hover:bg-[#f6f3ed]">Linen &amp; Bedding Par</button>
                            <button type="button" class="rounded-lg border border-line bg-white px-3.5 py-2 text-slate-600 hover:bg-[#f6f3ed]">VIP Toiletries</button>
                            <button type="button" class="rounded-lg border border-line bg-white px-3.5 py-2 text-amber-700 hover:bg-amber-50">Sanitization Chemicals</button>
                        </div>
                        <label class="relative block">
                            <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-sm text-slate-400">search</span>
                            <input class="w-60 rounded-lg border-line py-2 pl-8 pr-3 text-xs focus:border-secondary focus:ring-secondary" placeholder="Filter SKU or supply..." type="text">
                        </label>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-line bg-[#f6f3ed] px-5 py-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="font-serif text-2xl font-bold text-primary">Circulating Par &amp; Offsite Laundry Ledger</h3>
                                <p class="mt-1 text-sm text-slate-500">Main Wing suites standard with a 3.0x circulating multiplier.</p>
                            </div>
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary hover:bg-[#f6f3ed]">
                                <span class="material-symbols-outlined text-base text-secondary">file_download</span>
                                Export Audit Sheet
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-[#f0eee8] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3">Inventory Item</th>
                                        <th class="px-5 py-3">Par Target</th>
                                        <th class="px-5 py-3">Clean Stock</th>
                                        <th class="px-5 py-3">Laundry Transit</th>
                                        <th class="px-5 py-3">Status</th>
                                        <th class="px-5 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($ledgerRows as $row)
                                        <tr class="hover:bg-[#fdfbf7]">
                                            <td class="px-5 py-4">
                                                <p class="font-bold text-primary">{{ $row['item'] }}</p>
                                                <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $row['sku'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 font-bold text-primary">{{ $row['par'] }}</td>
                                            <td class="px-5 py-4 font-bold text-emerald-700">{{ $row['clean'] }}</td>
                                            <td class="px-5 py-4 text-slate-600">{{ $row['laundry'] }}</td>
                                            <td class="px-5 py-4">
                                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $row['tone'] === 'amber' ? 'bg-amber-100 text-amber-800' : ($row['tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">{{ $row['status'] }}</span>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <a href="{{ $row['status'] === 'Reorder' ? route('housekeeping.page', 'housekeeping-supplies') : route('housekeeping.page', 'linen-records') }}" class="inline-flex items-center gap-1 rounded-lg border border-line px-3 py-1.5 text-xs font-bold text-primary hover:bg-[#f6f3ed]">
                                                    Review
                                                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="space-y-5 xl:col-span-4">
                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">Restock Console</p>
                                <h3 class="font-serif text-2xl font-bold text-primary">1-Click Requisition</h3>
                            </div>
                            <span class="material-symbols-outlined text-secondary">shopping_cart_checkout</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Supply Category</span>
                                <select class="mt-1 w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary">
                                    <option>Sanitization Chemicals</option>
                                    <option>Luxury Guest Amenities</option>
                                    <option>Bedding & Linen</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Requested Quantity</span>
                                <input class="mt-1 w-full rounded-lg border-line text-sm focus:border-secondary focus:ring-secondary" type="number" value="{{ max(1, 12 - $chemicalStock) }}">
                            </label>
                            <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white hover:bg-[#5b4313]">
                                <span class="material-symbols-outlined text-base">add_box</span>
                                Prepare Restock Request
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl bg-primary p-5 text-white shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]">
                            <span class="material-symbols-outlined">rss_feed</span>
                            Laundry Sync Telemetry
                        </h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-white/10 pb-2"><span>RFID portals</span><strong>8 / 8 Active</strong></div>
                            <div class="flex justify-between border-b border-white/10 pb-2"><span>Laundry sync</span><strong>2m ago</strong></div>
                            <div class="flex justify-between border-b border-white/10 pb-2"><span>Fresh linen packs</span><strong>{{ $freshLinenPacks }}</strong></div>
                            <div class="flex justify-between"><span>VIP amenity sets</span><strong>{{ $vipAmenitySets }}</strong></div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'linen-records') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">local_laundry_service</span>
                            Open Linen Records
                        </a>
                    </div>

                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-primary">
                            <span class="material-symbols-outlined text-secondary">event_available</span>
                            Next Logistics Milestones
                        </h3>
                        <div class="mt-4 space-y-3">
                            @foreach ([
                                ['label' => 'Shift par count verification', 'time' => now('Asia/Manila')->addMinutes(25)->format('h:i A')],
                                ['label' => 'Offsite laundry receiving window', 'time' => now('Asia/Manila')->addHours(2)->format('h:i A')],
                                ['label' => 'Supervisor supply audit', 'time' => now('Asia/Manila')->addHours(4)->format('h:i A')],
                            ] as $item)
                                <div class="rounded-lg border border-line bg-[#f6f3ed] p-3">
                                    <p class="text-sm font-bold text-primary">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-secondary">{{ $item['time'] }} PHT</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'completed-tasks')
        @php
            $completedLedger = $completedTasks->values();
            $selectedLog = $completedLedger->first();
            $completedCount = $completedLedger->count();
            $releasedCount = $completedLedger->where('status', 'available')->count();
            $qaYield = $completedCount > 0 ? min(100, round(($releasedCount / max($completedCount, 1)) * 100, 1)) : 0;
            $attendants = ['Elena Rostova', 'Carlos Mendoza', 'Grace Macapagal', 'Rowena Dalisay', 'Teresa Mendoza'];
        @endphp

        <section class="space-y-6">
            <div class="flex flex-col gap-4 border-b border-line pb-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary">
                        <span class="material-symbols-outlined text-sm">verified</span>
                        Certified Shift Operations &amp; Quality Assurance
                    </div>
                    <h3 class="font-serif text-3xl font-bold text-primary">Completed Tasks &amp; Shift Turnover History</h3>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Historical log of certified room sanitizations, completion timestamps, supervisor sign-offs, and PMS release records from real room status activity.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2 text-xs font-bold text-primary shadow-sm hover:bg-[#f6f3ed]">
                        <span class="material-symbols-outlined text-base text-secondary">print</span>
                        Print Physical Ledger
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-primary/90">
                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">file_download</span>
                        Export Shift Turnover Ledger
                    </button>
                </div>
            </div>

            <section class="rounded-lg border border-line bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Hotel Wing</label>
                        <select class="w-full rounded-lg border-line bg-[#f6f3ed] text-sm focus:border-secondary focus:ring-secondary">
                            <option>All Wings</option>
                            <option>Main Wing</option>
                            <option>Ocean Wing</option>
                            <option>Executive Villas</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Shift Schedule</label>
                        <select class="w-full rounded-lg border-secondary bg-[#f6f3ed] text-sm focus:border-secondary focus:ring-secondary">
                            <option>Shift 1 Today: 06:00 - 14:30</option>
                            <option>Shift 2: 14:00 - 22:30</option>
                            <option>Shift 3: 22:00 - 06:30</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Attendant Filter</label>
                        <select class="w-full rounded-lg border-line bg-[#f6f3ed] text-sm focus:border-secondary focus:ring-secondary">
                            <option>All Attendants</option>
                            @foreach ($attendants as $attendant)
                                <option>{{ $attendant }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Audit Date Range</label>
                        <div class="flex items-center rounded-lg border border-line bg-[#f6f3ed] px-3 py-2 text-sm">
                            <span class="material-symbols-outlined mr-2 text-base text-slate-500">calendar_today</span>
                            {{ now()->format('M d, Y') }} Today
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="button" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-[#f0eee8] px-3 py-2 text-xs font-bold text-primary hover:bg-line">
                            <span class="material-symbols-outlined text-base">tune</span>
                            Refine Criteria
                        </button>
                        <button type="button" class="rounded-lg border border-line p-2 text-slate-500 hover:bg-[#f6f3ed]" title="Clear filters">
                            <span class="material-symbols-outlined text-lg">refresh</span>
                        </button>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Shift Output', 'value' => $completedCount, 'caption' => 'Certified room updates', 'icon' => 'check_circle', 'tone' => 'text-secondary', 'bar' => 'bg-secondary'],
                    ['label' => 'Avg Turnover Duration', 'value' => $completedCount ? '24m' : '0m', 'caption' => 'Target: <= 30 mins', 'icon' => 'schedule', 'tone' => 'text-primary', 'bar' => 'bg-primary'],
                    ['label' => 'QA First-Pass Yield', 'value' => $qaYield.'%', 'caption' => $releasedCount.' released to available', 'icon' => 'verified_user', 'tone' => 'text-secondary', 'bar' => 'bg-secondary'],
                    ['label' => 'PMS Sync Rate', 'value' => $completedCount ? '100%' : '0%', 'caption' => 'Room status ledger synced', 'icon' => 'sync', 'tone' => 'text-emerald-700', 'bar' => 'bg-emerald-700'],
                ] as $metric)
                    <div class="relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="absolute left-0 right-0 top-0 h-1 {{ $metric['bar'] }}"></div>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                                <p class="mt-3 font-serif text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $metric['caption'] }}</p>
                            </div>
                            <span class="material-symbols-outlined {{ $metric['tone'] }}">{{ $metric['icon'] }}</span>
                        </div>
                    </div>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="overflow-hidden rounded-lg border border-line bg-white shadow-sm xl:col-span-8">
                    <div class="flex flex-col gap-3 border-b border-line bg-[#f6f3ed] p-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="material-symbols-outlined text-primary">fact_check</span>
                            <h3 class="font-bold text-primary">Shift 1 Completed Units Ledger</h3>
                            <span class="rounded-full bg-[#ffdeaa] px-2.5 py-0.5 text-xs font-bold text-secondary">Live Sync Active</span>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">Showing {{ $completedLedger->take(8)->count() }} of {{ $completedCount }} units</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] text-left text-sm">
                            <thead class="bg-[#f0eee8] text-xs font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">Room &amp; Wing</th>
                                    <th class="px-3 py-3">Room Type</th>
                                    <th class="px-3 py-3">Cleaning Type</th>
                                    <th class="px-3 py-3">Attendant</th>
                                    <th class="px-3 py-3">Completed At</th>
                                    <th class="px-3 py-3 text-center">Score</th>
                                    <th class="px-3 py-3">Supervisor Signoff</th>
                                    <th class="px-3 py-3">PMS Release</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($completedLedger->take(8) as $log)
                                    @php
                                        $attendant = $attendants[($loop->iteration - 1) % count($attendants)];
                                        $score = max(92, 101 - $loop->iteration);
                                        $wing = ((int) ($log->room?->room_number ?? 0) >= 300) ? 'Ocean Wing' : 'Main Wing';
                                        $cleaningType = $log->status === 'available' ? 'Turnover Certified' : 'Status Confirmation';
                                    @endphp
                                    <tr class="{{ $loop->first ? 'border-l-4 border-l-secondary bg-[#ffdeaa]/20' : 'hover:bg-[#f6f3ed]' }} transition">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="font-serif text-2xl font-bold text-primary">{{ $log->room?->room_number ?? 'N/A' }}</span>
                                                <span class="rounded bg-primary px-2 py-0.5 text-xs font-bold text-white">{{ $wing }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <p class="font-semibold text-primary">{{ $log->room?->room_type ?? 'Room' }}</p>
                                            <p class="text-xs text-slate-500">{{ $log->room?->description ?: 'Housekeeping verified' }}</p>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1 rounded bg-secondary/15 px-2.5 py-1 text-xs font-bold text-secondary">
                                                <span class="material-symbols-outlined text-sm">sanitizer</span>
                                                {{ $cleaningType }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <p class="font-semibold text-primary">{{ $attendant }}</p>
                                            <p class="text-xs text-slate-500">Attendant #{{ 100 + $loop->iteration }}</p>
                                        </td>
                                        <td class="px-3 py-3">
                                            <p>{{ $log->changed_at?->format('h:i A') ?? '--' }}</p>
                                            <p class="text-xs font-semibold text-emerald-700">{{ $log->changed_at?->format('M d, Y') ?? 'Pending timestamp' }}</p>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="rounded-full bg-[#ffdeaa] px-2 py-0.5 text-xs font-bold text-secondary">{{ $score }}/100</span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <p class="flex items-center gap-1 font-semibold text-primary"><span class="material-symbols-outlined text-base text-secondary">verified</span>Maria Santos</p>
                                            <p class="text-xs text-slate-500">Supervisor signoff</p>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                                {{ $log->status === 'available' ? 'Released' : ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-1">
                                                <a href="{{ $log->room ? route('rooms.show', $log->room) : route('housekeeping.page', 'notifications') }}" class="rounded p-1 text-secondary hover:bg-secondary/15" title="View audit certificate"><span class="material-symbols-outlined text-lg">description</span></a>
                                                <a href="{{ $log->room ? route('rooms.edit', $log->room) : route('housekeeping.page', 'notifications') }}" class="rounded p-1 text-slate-500 hover:bg-[#f6f3ed]" title="Attendant notes"><span class="material-symbols-outlined text-lg">notes</span></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-12 text-center text-sm text-slate-500">No completed room-status logs yet today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <aside class="space-y-5 xl:col-span-4">
                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <div class="border-b border-line pb-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">Audit Certificate Preview</p>
                            <h3 class="mt-1 font-serif text-2xl font-bold text-primary">
                                Room {{ $selectedLog?->room?->room_number ?? 'N/A' }} - {{ $selectedLog?->room?->room_type ?? 'No Completed Unit' }}
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $selectedLog?->notes ?: 'Select a completed unit to inspect its audit details.' }}</p>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border border-line bg-[#f6f3ed] p-3">
                                <p class="text-[11px] font-bold uppercase text-slate-500">QA Score</p>
                                <p class="mt-1 font-serif text-2xl font-bold text-secondary">{{ $selectedLog ? '100' : '--' }}/100</p>
                            </div>
                            <div class="rounded-lg border border-line bg-[#f6f3ed] p-3">
                                <p class="text-[11px] font-bold uppercase text-slate-500">PMS Release</p>
                                <p class="mt-1 font-bold text-emerald-700">{{ $selectedLog ? ucfirst($selectedLog->status) : 'Pending' }}</p>
                            </div>
                        </div>
                        <div class="mt-4 rounded-lg border border-line bg-[#fdfcfa] p-3 text-sm">
                            <p class="font-bold text-primary">Attendant Log &amp; Notes</p>
                            <p class="mt-2 leading-6 text-slate-600">{{ $selectedLog?->notes ?: 'No attendant notes have been recorded for today.' }}</p>
                        </div>
                        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                            <span class="material-symbols-outlined align-middle text-base">verified_user</span>
                            Biometric ID Verified
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary p-5 text-white shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-[#ffdeaa]"><span class="material-symbols-outlined">analytics</span>Shift Turnover Summary</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-white/10 pb-2"><span class="text-slate-300">Certified rooms</span><strong>{{ $completedCount }}</strong></div>
                            <div class="flex justify-between border-b border-white/10 pb-2"><span class="text-slate-300">Released rooms</span><strong>{{ $releasedCount }}</strong></div>
                            <div class="flex justify-between"><span class="text-slate-300">Open tasks</span><strong>{{ $cleaningRooms->count() + $maintenanceQueue->count() }}</strong></div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'room-inspection') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-3 py-2 text-xs font-bold text-white hover:bg-[#5b4313]">
                            <span class="material-symbols-outlined text-base">fact_check</span>
                            Open Inspection Queue
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'lost-found')
        @php
            $custodyRooms = $completedTasks->filter(fn ($log) => $log->room)->pluck('room')->unique('id')->take(2)->values();

            if ($custodyRooms->isEmpty()) {
                $custodyRooms = $allRooms->take(2)->values();
            }

            $custodyTemplates = [
                [
                    'reference' => 'LF-' . now()->format('Y') . '-089',
                    'category' => 'High-Value Sealed Vault Item',
                    'item' => "18k Men's Gold Watch",
                    'status' => 'Secured in Safe - Contacted Guest',
                    'statusClass' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                    'location' => 'Executive safe deposit box #04',
                    'discoveredBy' => 'Turnover attendant',
                    'barcode' => 'SEAL-VAULT-0410',
                    'icon' => 'watch',
                    'tone' => 'border-secondary/60 bg-[#fff8eb]',
                    'actions' => ['Process Claim / Release', 'Print Tamper Label', 'View Full Chain of Custody'],
                ],
                [
                    'reference' => 'LF-' . now()->format('Y') . '-090',
                    'category' => 'Consumer Electronics',
                    'item' => 'Apple iPad Pro 11" with Leather Smart Folio',
                    'status' => 'Locker B-12 - Pending Courier',
                    'statusClass' => 'border-amber-200 bg-amber-50 text-amber-800',
                    'location' => 'Lost property locker B-12',
                    'discoveredBy' => 'Room inspection team',
                    'barcode' => 'BAG-ELEC-1182',
                    'icon' => 'tablet_mac',
                    'tone' => 'border-line bg-white',
                    'actions' => ['Contact Guest', 'Print Bag Tag', 'Transfer to Central Vault'],
                ],
            ];

            $lostFoundItems = $custodyRooms->map(function ($room, $index) use ($custodyTemplates, $completedTasks) {
                $template = $custodyTemplates[$index % count($custodyTemplates)];
                $roomLog = $completedTasks->first(fn ($log) => $log->room_id === $room->id);

                return array_merge($template, [
                    'room' => $room,
                    'foundLocation' => 'Room ' . $room->room_number . ' - ' . ($room->room_type ?? 'Guest Room'),
                    'discoveryTime' => optional($roomLog?->changed_at)->format('M d, Y h:i A') ?? now()->format('M d, Y h:i A'),
                    'guest' => $room->status === 'occupied' ? 'Current in-house guest' : 'Previous guest on room folio',
                    'notes' => $roomLog?->notes ?: 'Recovered during housekeeping turnover and awaiting owner claim verification.',
                ]);
            });

            $selectedCustodyItem = $lostFoundItems->first();
        @endphp

        <section class="space-y-6">
            <div class="rounded-lg border border-line bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-secondary">Housekeeping Oversight &gt; Lost &amp; Found Chain of Custody</p>
                        <h3 class="mt-2 font-serif text-3xl font-bold text-primary">Lost &amp; Found Vault &amp; Chain of Custody Registry</h3>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Secure repository for belongings discovered during room turnovers, with locker allocation, claim status, release controls, and room-linked custody history.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('housekeeping.page', 'completed-tasks') }}" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm hover:bg-slate-50">
                            <span class="material-symbols-outlined text-base text-secondary">inventory</span>
                            Audit Vault Inventory
                        </a>
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-secondary px-4 py-2.5 text-sm font-bold text-secondary shadow-sm hover:bg-secondary/10">
                            <span class="material-symbols-outlined text-base">qr_code_2</span>
                            Print Custody Barcode Labels
                        </button>
                        <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                            <span class="material-symbols-outlined text-base text-[#ffdeaa]">add</span>
                            Log Discovered Item
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Items in Vault</p>
                        <span class="material-symbols-outlined text-secondary">inventory_2</span>
                    </div>
                    <p class="mt-3 font-serif text-3xl font-bold text-primary">{{ $lostFoundItems->count() }}</p>
                    <p class="mt-2 text-sm text-slate-500">Active custody records</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">High-Value Sealed Items</p>
                        <span class="material-symbols-outlined text-secondary">lock</span>
                    </div>
                    <p class="mt-3 font-serif text-3xl font-bold text-primary">{{ $lostFoundItems->where('category', 'High-Value Sealed Vault Item')->count() }}</p>
                    <p class="mt-2 text-sm text-slate-500">Requires supervisor release</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Claim Resolution Rate</p>
                        <span class="material-symbols-outlined text-emerald-700">verified</span>
                    </div>
                    <p class="mt-3 font-serif text-3xl font-bold text-primary">94.2%</p>
                    <p class="mt-2 text-sm text-slate-500">Housekeeping target metric</p>
                </div>
                <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Unclaimed Expiry Window</p>
                        <span class="material-symbols-outlined text-red-700">timer</span>
                    </div>
                    <p class="mt-3 font-serif text-3xl font-bold text-primary">60 Days</p>
                    <p class="mt-2 text-sm text-slate-500">Before manager review</p>
                </div>
            </div>

            <div class="rounded-lg border border-line bg-white p-4 shadow-sm">
                <div class="grid gap-3 xl:grid-cols-[1fr_auto_auto] xl:items-center">
                    <label class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-400">search</span>
                        <input type="search" class="w-full rounded-lg border-line py-2.5 pl-10 text-sm focus:border-secondary focus:ring-secondary" placeholder="Search by guest name, room number, item keyword..." />
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['All', 'Electronics', 'Jewelry & Valuables', 'Garments/Luggage', 'Documents/Passports'] as $category)
                            <button type="button" class="rounded-full border px-3 py-2 text-xs font-bold {{ $loop->first ? 'border-primary bg-primary text-white' : 'border-line bg-[#f6f3ed] text-primary hover:border-secondary' }}">{{ $category }}</button>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['In Custody', 'Claimed & Released', 'Pending Courier'] as $status)
                            <button type="button" class="rounded-full border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-secondary hover:bg-amber-100">{{ $status }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
                <section class="rounded-lg border border-line bg-white shadow-sm">
                    <div class="flex flex-col justify-between gap-3 border-b border-line p-5 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="font-serif text-2xl font-bold text-primary">Items Currently in Custody</h3>
                            <p class="mt-1 text-sm text-slate-500">Recovered belongings tied to room turnover and inspection activity.</p>
                        </div>
                        <span class="rounded-full bg-[#f6f3ed] px-3 py-1 text-xs font-bold text-secondary">{{ $lostFoundItems->count() }} open records</span>
                    </div>
                    <div class="space-y-4 p-5">
                        @forelse ($lostFoundItems as $item)
                            <article class="rounded-lg border p-5 shadow-sm {{ $item['tone'] }}">
                                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                                    <div class="flex gap-4">
                                        <span class="material-symbols-outlined flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary text-[#ffdeaa]">{{ $item['icon'] }}</span>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded-full border border-secondary/30 bg-[#ffdeaa]/50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-secondary">{{ $item['reference'] }}</span>
                                                <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider {{ $item['statusClass'] }}">{{ $item['status'] }}</span>
                                            </div>
                                            <h4 class="mt-3 font-serif text-2xl font-bold text-primary">{{ $item['item'] }}</h4>
                                            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">{{ $item['notes'] }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('rooms.show', $item['room']) }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary hover:border-secondary">
                                        <span class="material-symbols-outlined text-base text-secondary">meeting_room</span>
                                        Room {{ $item['room']->room_number }}
                                    </a>
                                </div>

                                <div class="mt-5 grid gap-3 border-t border-line pt-4 sm:grid-cols-2 xl:grid-cols-3">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Found Location</p>
                                        <p class="mt-1 text-sm font-bold text-primary">{{ $item['foundLocation'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Discovered By</p>
                                        <p class="mt-1 text-sm font-bold text-primary">{{ $item['discoveredBy'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Discovery Time</p>
                                        <p class="mt-1 text-sm font-bold text-primary">{{ $item['discoveryTime'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Secure Location</p>
                                        <p class="mt-1 text-sm font-bold text-primary">{{ $item['location'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Guest on Record</p>
                                        <p class="mt-1 text-sm font-bold text-primary">{{ $item['guest'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Custody Seal Barcode</p>
                                        <p class="mt-1 font-mono text-sm font-bold text-primary">{{ $item['barcode'] }}</p>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($item['actions'] as $action)
                                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-primary hover:border-secondary hover:text-secondary">
                                            <span class="material-symbols-outlined text-base text-secondary">{{ $loop->first ? 'assignment_turned_in' : ($loop->iteration === 2 ? 'print' : 'account_tree') }}</span>
                                            {{ $action }}
                                        </button>
                                    @endforeach
                                </div>
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-line p-8 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300">inventory_2</span>
                                <p class="mt-3 font-bold text-primary">No recovered items are in custody yet.</p>
                                <p class="mt-1 text-sm text-slate-500">Use room notes or turnover logs when an item is found during cleaning.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <aside class="space-y-4">
                    <div class="rounded-lg border border-secondary/40 bg-white shadow-sm">
                        <div class="border-b border-line p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Locked &amp; Verified</p>
                                    <h3 class="mt-1 font-serif text-2xl font-bold text-primary">Chain of Custody Record</h3>
                                </div>
                                <span class="material-symbols-outlined rounded-lg bg-[#f6f3ed] p-2 text-secondary">admin_panel_settings</span>
                            </div>
                        </div>

                        @if ($selectedCustodyItem)
                            <div class="p-5">
                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Reference Item</p>
                                    <p class="mt-1 font-serif text-xl font-bold text-primary">{{ $selectedCustodyItem['item'] }}</p>
                                    <p class="mt-1 text-sm text-slate-600">{{ $selectedCustodyItem['reference'] }} - {{ $selectedCustodyItem['foundLocation'] }}</p>
                                </div>

                                <div class="mt-4 rounded-lg border border-line bg-[#fdfcfa] p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-[#ffdeaa]">lock</span>
                                        <div>
                                            <p class="font-bold text-primary">Tamper-evident custody bag active</p>
                                            <p class="text-sm text-slate-500">{{ $selectedCustodyItem['barcode'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 space-y-4">
                                    @foreach ([
                                        ['label' => 'Discovered in turnover', 'detail' => $selectedCustodyItem['foundLocation'], 'icon' => 'cleaning_services'],
                                        ['label' => 'Supervisor verification', 'detail' => 'Photo log and condition notes checked', 'icon' => 'verified_user'],
                                        ['label' => 'Vault allocation', 'detail' => $selectedCustodyItem['location'], 'icon' => 'lock'],
                                        ['label' => 'Guest notification dispatched', 'detail' => 'Claim instructions ready for front office handoff', 'icon' => 'forward_to_inbox'],
                                    ] as $step)
                                        <div class="flex gap-3">
                                            <span class="material-symbols-outlined mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-secondary/30 bg-white text-base text-secondary">{{ $step['icon'] }}</span>
                                            <div>
                                                <p class="font-bold text-primary">{{ $step['label'] }}</p>
                                                <p class="mt-1 text-sm leading-5 text-slate-600">{{ $step['detail'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <a href="{{ route('housekeeping.page', 'notifications') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-primary/90">
                                    <span class="material-symbols-outlined text-base text-[#ffdeaa]">history</span>
                                    Open Custody Activity Log
                                </a>
                            </div>
                        @else
                            <div class="p-8 text-center text-sm text-slate-500">No chain of custody record is available yet.</div>
                        @endif
                    </div>
                </aside>
            </div>
        </section>
    @elseif ($pageKey === 'notifications')
        @php
            $alertSeedRooms = collect([$priorityRooms->first(), $maintenanceQueue->first(), $guestRequestRooms->first(), $cleaningRooms->first(), $assignedRooms->first()])
                ->filter()
                ->unique('id')
                ->values();

            if ($alertSeedRooms->isEmpty()) {
                $alertSeedRooms = $allRooms->take(3)->values();
            }

            $notificationTemplates = [
                ['type' => 'VIP ARRIVAL ALERT - HIGH PRIORITY', 'source' => 'Front Desk', 'icon' => 'priority_high', 'tone' => 'red', 'meta' => 'Priority/Emergency', 'body' => 'Early arrival advisory received. Rush housekeeping turnaround is required before the next guest handoff window.'],
                ['type' => 'MAINTENANCE RESOLUTION', 'source' => 'Engineering', 'icon' => 'build', 'tone' => 'sky', 'meta' => 'Maintenance Update', 'body' => 'Engineering has updated the work order. Housekeeping may prepare final inspection and release verification.'],
                ['type' => 'GUEST SERVICE REQUEST', 'source' => 'Guest Services', 'icon' => 'room_service', 'tone' => 'amber', 'meta' => 'Guest Request', 'body' => 'Guest-facing amenity or linen request is ready for floor attendant dispatch.'],
                ['type' => 'FRONT DESK CHECK-OUT EVENT', 'source' => 'Front Desk', 'icon' => 'logout', 'tone' => 'slate', 'meta' => 'Standard Turnover', 'body' => 'Room departure has been recorded. Prepare turnover queue, linen pull, and keycard recovery confirmation.'],
                ['type' => 'IOT SENSOR WARNING', 'source' => 'Facilities IoT', 'icon' => 'sensors', 'tone' => 'red', 'meta' => 'Sensor Alert', 'body' => 'Room device or occupancy signal requires physical verification by housekeeping before room status release.'],
            ];

            $dispatchAlerts = $alertSeedRooms->map(function ($room, $index) use ($notificationTemplates, $recentRoomLogs) {
                $template = $notificationTemplates[$index % count($notificationTemplates)];
                $log = $recentRoomLogs->first(fn ($entry) => $entry->room_id === $room->id);

                return array_merge($template, [
                    'room' => $room,
                    'title' => $template['type'],
                    'message' => 'Room '.$room->room_number.' - '.($room->room_type ?? 'Guest Room').': '.($log?->notes ?: $template['body']),
                    'time' => optional($log?->changed_at)->diffForHumans() ?? now()->subMinutes(12 + ($index * 9))->diffForHumans(),
                    'status' => ucfirst($room->status),
                ]);
            })->values();

            $unreadAlerts = max(1, min(5, $dispatchAlerts->count() + $maintenanceQueue->count()));
            $priorityAlertCount = $dispatchAlerts->where('tone', 'red')->count() + $priorityRooms->count();
            $frontDeskAlertCount = max(1, $guestRequestRooms->count() + $cleaningRooms->count());
            $toneClasses = [
                'red' => ['bar' => 'bg-red-600', 'icon' => 'bg-red-50 text-red-700', 'pill' => 'border-red-200 bg-red-50 text-red-800'],
                'sky' => ['bar' => 'bg-sky-700', 'icon' => 'bg-sky-50 text-sky-700', 'pill' => 'border-sky-200 bg-sky-50 text-sky-800'],
                'amber' => ['bar' => 'bg-secondary', 'icon' => 'bg-[#ffdeaa]/40 text-secondary', 'pill' => 'border-amber-200 bg-amber-50 text-secondary'],
                'slate' => ['bar' => 'bg-slate-500', 'icon' => 'bg-[#f6f3ed] text-slate-700', 'pill' => 'border-slate-200 bg-slate-50 text-slate-700'],
            ];
        @endphp

        <section class="space-y-6">
            <div class="flex flex-col justify-between gap-5 border-b border-line pb-6 md:flex-row md:items-end">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary">
                        <span class="material-symbols-outlined text-sm">notifications_active</span>
                        Live Dispatch Center
                    </div>
                    <h3 class="font-serif text-3xl font-bold text-primary">Housekeeping Dispatch Notifications &amp; Operational Alerts</h3>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Real-time alert center for guest priority requests, Front Desk early check-ins, maintenance work order updates, and supervisor broadcasts.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm hover:bg-slate-50">
                        <span class="material-symbols-outlined text-base text-secondary">mark_email_read</span>
                        Mark All Read
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">settings_alert</span>
                        Alert Notification Preferences
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 rounded-lg border border-line bg-white p-3 shadow-sm">
                @foreach ([
                    ['label' => 'All Alerts', 'count' => $dispatchAlerts->count() + $recentRoomLogs->count(), 'active' => true, 'tone' => 'bg-primary text-white'],
                    ['label' => 'Unread', 'count' => $unreadAlerts, 'active' => false, 'tone' => 'bg-red-50 text-red-800 border-red-200'],
                    ['label' => 'Priority/Emergency', 'count' => $priorityAlertCount, 'active' => false, 'tone' => 'bg-red-50 text-red-800 border-red-200'],
                    ['label' => 'Front Desk', 'count' => $frontDeskAlertCount, 'active' => false, 'tone' => 'bg-[#f6f3ed] text-primary border-line'],
                    ['label' => 'Maintenance', 'count' => $maintenanceQueue->count(), 'active' => false, 'tone' => 'bg-[#f6f3ed] text-primary border-line'],
                ] as $tab)
                    <button type="button" class="rounded-lg border px-4 py-2 text-xs font-bold transition {{ $tab['active'] ? 'border-primary '.$tab['tone'] : $tab['tone'].' hover:border-secondary' }}">
                        {{ $tab['label'] }}
                        <span class="ml-1 rounded-full bg-white/40 px-1.5 py-0.5 text-[10px]">{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
                <section class="space-y-4">
                    @forelse ($dispatchAlerts as $alert)
                        @php($tone = $toneClasses[$alert['tone']] ?? $toneClasses['slate'])
                        <article class="relative overflow-hidden rounded-lg border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="absolute left-0 right-0 top-0 h-1 {{ $tone['bar'] }}"></div>
                            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                                <div class="flex gap-4">
                                    <span class="material-symbols-outlined flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $tone['icon'] }}">{{ $alert['icon'] }}</span>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider {{ $tone['pill'] }}">{{ $alert['meta'] }}</span>
                                            <span class="text-xs font-semibold text-slate-500">{{ $alert['source'] }} - {{ $alert['time'] }}</span>
                                        </div>
                                        <h4 class="mt-3 font-serif text-xl font-bold text-primary">{{ $alert['title'] }}</h4>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $alert['message'] }}</p>
                                        <div class="mt-4 flex flex-wrap gap-3 text-xs font-semibold text-slate-500">
                                            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-secondary">meeting_room</span>Room {{ $alert['room']->room_number }}</span>
                                            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-secondary">hotel</span>{{ $alert['room']->room_type }}</span>
                                            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-secondary">flag</span>{{ $alert['status'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex shrink-0 flex-wrap gap-2">
                                    <a href="{{ route('rooms.show', $alert['room']) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white hover:bg-primary/90">
                                        <span class="material-symbols-outlined text-base text-[#ffdeaa]">visibility</span>
                                        Open Room
                                    </a>
                                    <a href="{{ route('rooms.edit', $alert['room']) }}" class="inline-flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-xs font-bold text-primary hover:border-secondary">
                                        <span class="material-symbols-outlined text-base text-secondary">edit_note</span>
                                        Update Status
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-line bg-white p-10 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate-300">notifications_off</span>
                            <p class="mt-3 font-bold text-primary">No dispatch alerts yet.</p>
                            <p class="mt-1 text-sm text-slate-500">Room activity will appear here when housekeeping receives updates.</p>
                        </div>
                    @endforelse
                </section>

                <aside class="space-y-5">
                    <div class="overflow-hidden rounded-lg border border-line bg-white shadow-sm">
                        <div class="flex items-center justify-between bg-primary p-4 text-white">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined rounded-lg bg-white/10 p-2 text-[#ffdeaa]">podcasts</span>
                                <div>
                                    <h4 class="font-bold">Radio Broadcast Log</h4>
                                    <p class="text-xs text-[#ffdeaa]">Subic IHMS Channel 4 Dispatch</p>
                                </div>
                            </div>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <div class="max-h-[380px] space-y-3 overflow-y-auto bg-[#fcf9f3] p-4">
                            @foreach ([
                                ['from' => 'HK Lead', 'text' => 'Priority rooms acknowledged. Dispatch linen runner and supervisor inspection after final sweep.', 'time' => now()->subMinutes(4)->format('h:i A'), 'tone' => 'text-secondary'],
                                ['from' => 'Engineering', 'text' => 'Maintenance queue update received. Housekeeping is cleared to enter resolved units for final inspection.', 'time' => now()->subMinutes(12)->format('h:i A'), 'tone' => 'text-sky-700'],
                                ['from' => 'Front Desk', 'text' => 'Early arrival advisory sent. Please keep one clean unit ready for immediate guest handoff.', 'time' => now()->subMinutes(19)->format('h:i A'), 'tone' => 'text-red-700'],
                            ] as $broadcast)
                                <div class="rounded-lg border border-line bg-white p-3 text-xs">
                                    <div class="mb-1 flex items-center justify-between font-bold">
                                        <span class="{{ $broadcast['tone'] }}">{{ $broadcast['from'] }}</span>
                                        <span class="text-slate-400">{{ $broadcast['time'] }}</span>
                                    </div>
                                    <p class="leading-5 text-slate-600">"{{ $broadcast['text'] }}"</p>
                                </div>
                            @endforeach
                        </div>
                        <form class="flex gap-2 border-t border-line p-3" onsubmit="event.preventDefault();">
                            <input class="w-full rounded-lg border-line text-xs focus:border-secondary focus:ring-secondary" placeholder="Broadcast alert or note to Channel 4..." type="text" />
                            <button type="submit" class="rounded-lg bg-primary p-2 text-[#ffdeaa] hover:bg-primary/90" title="Transmit Broadcast">
                                <span class="material-symbols-outlined text-base">send</span>
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-line bg-white p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-primary">
                            <span class="material-symbols-outlined text-secondary">supervisor_account</span>
                            Supervisor Dispatch Summary
                        </h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Unread alerts</span><strong class="text-red-700">{{ $unreadAlerts }}</strong></div>
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Priority rooms</span><strong class="text-secondary">{{ $priorityRooms->count() }}</strong></div>
                            <div class="flex justify-between border-b border-line pb-2"><span class="text-slate-600">Maintenance updates</span><strong class="text-primary">{{ $maintenanceQueue->count() }}</strong></div>
                            <div class="flex justify-between"><span class="text-slate-600">Recent room logs</span><strong class="text-primary">{{ $recentRoomLogs->count() }}</strong></div>
                        </div>
                        <a href="{{ route('housekeeping.page', 'priority-rooms') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-secondary px-4 py-2.5 text-sm font-bold text-secondary hover:bg-secondary/10">
                            <span class="material-symbols-outlined text-base">priority_high</span>
                            Open Priority Queue
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    @else
        <section class="rounded-lg border border-line bg-white shadow-sm">
            <div class="border-b border-line p-5">
                <h3 class="font-serif text-2xl font-bold">{{ $page['title'] }} Queue</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $page['subtitle'] }}</p>
            </div>
            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($pageRooms as $room)
                    <article class="rounded-lg border border-line bg-[#fdfcfa] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-secondary">Room {{ $room->room_number }}</p>
                                <h4 class="mt-1 text-lg font-bold">{{ $room->room_type }}</h4>
                            </div>
                            <span class="rounded-full border px-2 py-1 text-[11px] font-bold {{ $statusTone[$room->status] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">{{ ucfirst($room->status) }}</span>
                        </div>
                        <p class="mt-3 min-h-10 text-sm text-slate-600">{{ $room->description ?: 'No room notes recorded.' }}</p>
                        <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs text-slate-500">
                            <span>Capacity: {{ $room->capacity }} guests</span>
                            <a href="{{ route('rooms.edit', $room) }}" class="font-bold text-secondary hover:underline">Update status</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-line p-8 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">
                        No records are waiting in this housekeeping page right now.
                    </div>
                @endforelse
            </div>
        </section>
    @endif
@endsection
