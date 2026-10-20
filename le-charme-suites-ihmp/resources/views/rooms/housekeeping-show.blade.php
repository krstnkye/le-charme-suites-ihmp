@extends('layouts.housekeeping')

@section('title', 'Room ' . $room->room_number . ' - Housekeeping')

@section('content')
    @php
        $statusLabels = [
            'available' => 'Vacant Ready',
            'reserved' => 'QA Inspection',
            'occupied' => 'Occupied',
            'cleaning' => 'Dirty / Room Preparation',
            'maintenance' => 'Out of Order',
        ];

        $statusTone = [
            'available' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'reserved' => 'border-sky-200 bg-sky-50 text-sky-700',
            'occupied' => 'border-amber-200 bg-amber-50 text-amber-800',
            'cleaning' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
            'maintenance' => 'border-rose-200 bg-rose-50 text-rose-700',
        ];

        $statusIcons = [
            'available' => 'task_alt',
            'reserved' => 'verified',
            'occupied' => 'king_bed',
            'cleaning' => 'cleaning_services',
            'maintenance' => 'construction',
        ];

        $statusDescriptions = [
            'available' => 'Ready for Front Desk assignment.',
            'reserved' => 'Supervisor inspection pending.',
            'occupied' => 'Guest stayover or in-house room.',
            'cleaning' => 'Room Preparation cleaning required.',
            'maintenance' => 'Engineering intervention required.',
        ];

        $latestReservation = $room->reservations->sortByDesc('check_in')->first();
        $guest = $latestReservation?->guest;
        $latestLog = $room->statusLogs->first();
        $floor = substr((string) $room->room_number, 0, 1) ?: '1';
        $assetId = 'LCS-RM-' . str_pad((string) $room->room_number, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr($room->room_type, 0, 3));
        $lockId = 'VNC-' . str_pad((string) (900 + (int) $room->id), 4, '0', STR_PAD_LEFT) . '-A';
        $extension = '1' . str_pad((string) $room->room_number, 3, '0', STR_PAD_LEFT);
        $activeTask = $room->housekeepingTasks->first(fn ($task) => ! in_array($task->status, ['completed', 'cancelled'], true));
        $attendant = $activeTask?->assignee?->name ?? 'Not assigned';
        $capacityLabel = $room->capacity === 1 ? 'guest' : 'guests';
        $guestName = $guest?->full_name ?? $guest?->name ?? 'No active guest linked';
        $guestEmail = $guest?->email ?? 'No contact email on file';
        $guestPhone = $guest?->phone ?? 'No phone on file';
        $checkIn = $latestReservation?->check_in ? \Carbon\Carbon::parse($latestReservation->check_in)->format('M d, Y') : 'Not scheduled';
        $checkOut = $latestReservation?->check_out ? \Carbon\Carbon::parse($latestReservation->check_out)->format('M d, Y') : 'Not scheduled';
        $currentStatus = $statusLabels[$room->status] ?? ucfirst($room->status);
        $roomImage = $room->imagePath();
        $qaItems = [
            ['label' => 'Bed presentation and linen quality verified', 'time' => now('Asia/Manila')->subMinutes(34)->format('h:i A')],
            ['label' => 'Bathroom amenities and towels replenished', 'time' => now('Asia/Manila')->subMinutes(28)->format('h:i A')],
            ['label' => 'Minibar, coffee tray, and water bottles checked', 'time' => now('Asia/Manila')->subMinutes(21)->format('h:i A')],
            ['label' => 'Floor, balcony, and fixtures inspected', 'time' => now('Asia/Manila')->subMinutes(15)->format('h:i A')],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-secondary">Housekeeping / Room Status</p>
                <h2 class="mt-1 font-serif text-4xl font-bold text-primary">Room {{ $room->room_number }}</h2>
                <p class="mt-2 text-sm text-slate-600">Operational workspace for rooms ready for guests, guest stayover notes, QA checks, and status history.</p>
            </div>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary transition hover:bg-secondary hover:text-white">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Back to Room Status
            </a>
        </div>

        <section class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
            @if ($roomImage)
                <figure class="relative aspect-[16/5] overflow-hidden bg-slate-100">
                    <img src="{{ asset($roomImage) }}" alt="{{ $room->room_type }} room reference" class="h-full w-full object-cover">
                    <figcaption class="absolute bottom-3 left-4 rounded bg-primary/85 px-3 py-1 text-xs font-bold text-white">Official {{ $room->room_type }} reference</figcaption>
                </figure>
            @endif
            <div class="border-t-4 border-primary bg-gradient-to-br from-white via-white to-amber-50/60 p-6">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-line bg-cream px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-secondary">Floor {{ $floor }} / Main Wing</span>
                            <span class="rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] {{ $statusTone[$room->status] ?? 'border-line bg-cream text-slate-700' }}">
                                {{ $currentStatus }}
                            </span>
                        </div>
                        <div>
                            <h3 class="font-serif text-3xl font-bold text-primary">
                                Room {{ $room->room_number }}
                                <span class="font-sans text-xl font-normal text-slate-500">/ {{ $room->room_type }}</span>
                            </h3>
                            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                                <span>Asset ID: <strong class="font-mono text-slate-700">{{ $assetId }}</strong></span>
                                <span>Extension: <strong class="font-mono text-slate-700">{{ $extension }}</strong></span>
                                <span>Lockset: <strong class="font-mono text-slate-700">{{ $lockId }}</strong></span>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-lg border border-line bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Room Type</p>
                                <p class="mt-2 text-lg font-bold">{{ $room->room_type }}</p>
                            </div>
                            <div class="rounded-lg border border-line bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Capacity</p>
                                <p class="mt-2 text-lg font-bold">{{ $room->capacity }} {{ $capacityLabel }}</p>
                            </div>
                            <div class="rounded-lg border border-line bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nightly Rate</p>
                                <p class="mt-2 text-lg font-bold">PHP {{ number_format($room->rate, 2) }}</p>
                            </div>
                            <div class="rounded-lg border border-line bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Assigned Attendant</p>
                                <p class="mt-2 text-lg font-bold">{{ $attendant }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $activeTask ? ucfirst(str_replace('_', ' ', $activeTask->status)) : 'No active task' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid min-w-full gap-2 sm:min-w-[260px] xl:min-w-[260px]">
                        <a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-slate-900">
                            <span class="material-symbols-outlined text-base">sync</span>
                            Update Room Status
                        </a>
                        <a href="{{ route('housekeeping.page', 'report-issue') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-amber-50">
                            <span class="material-symbols-outlined text-base text-secondary">warning</span>
                            Log Maintenance Defect
                        </a>
                        <div>
                            <button type="button" class="inline-flex items-center justify-center gap-1 rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-cream" onclick="window.print()">
                                <span class="material-symbols-outlined text-base text-secondary">key</span>
                                Key Audit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
            <div class="space-y-6">
                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4">
                        <div>
                            <h3 class="flex items-center gap-2 font-serif text-xl font-bold text-primary">
                                <span class="material-symbols-outlined text-secondary">tune</span>
                                Quick Status Controller & Room Preparation State
                            </h3>
                            <p class="mt-1 text-sm text-slate-600">Switch the operational state so Front Desk and floor supervisors see the latest room condition.</p>
                        </div>
                        <span class="rounded-full bg-cream px-3 py-1 text-xs font-bold text-secondary">Current: {{ $currentStatus }}</span>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach (['available', 'occupied', 'cleaning', 'maintenance', 'reserved'] as $statusOption)
                            <form method="POST" action="{{ route('rooms.update', $room) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                <input type="hidden" name="room_type" value="{{ $room->room_type }}">
                                <input type="hidden" name="rate" value="{{ $room->rate }}">
                                <input type="hidden" name="capacity" value="{{ $room->capacity }}">
                                <input type="hidden" name="description" value="{{ $room->description }}">
                                <input type="hidden" name="status" value="{{ $statusOption }}">
                                <button type="submit" class="h-full w-full rounded-lg border p-4 text-left transition hover:-translate-y-0.5 hover:shadow-sm {{ $room->status === $statusOption ? ($statusTone[$statusOption] ?? 'border-secondary bg-cream text-secondary') : 'border-line bg-stone-50 text-slate-700 hover:border-secondary' }}">
                                    <span class="material-symbols-outlined text-xl">{{ $statusIcons[$statusOption] ?? 'radio_button_checked' }}</span>
                                    <span class="mt-2 block font-bold">{{ $statusLabels[$statusOption] ?? ucfirst($statusOption) }}</span>
                                    <span class="mt-1 block text-xs opacity-80">{{ $statusDescriptions[$statusOption] ?? 'Update room status.' }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>

                    <div class="mt-5 rounded-lg border border-line bg-cream p-4">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Floor Attendant Assignment</p>
                                <p class="mt-1 font-bold text-primary">{{ $attendant }}</p>
                            </div>
                            <form method="POST" action="{{ $activeTask ? route('housekeeping-tasks.update', $activeTask) : route('housekeeping-tasks.store') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-end">
                                @csrf
                                @if ($activeTask)
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $activeTask->status }}">
                                @else
                                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                                    <input type="hidden" name="task_type" value="turnover">
                                    <input type="hidden" name="title" value="Clean and prepare Room {{ $room->room_number }}">
                                    <input type="hidden" name="instructions" value="Complete room cleaning, restocking, and readiness inspection.">
                                    <input type="hidden" name="priority" value="normal">
                                @endif
                                <label class="text-xs font-bold text-slate-600">
                                    Assign housekeeper
                                    <select name="assigned_to" required class="mt-1 block min-w-56 rounded-lg border-line bg-white px-3 py-2 text-sm text-primary focus:border-secondary focus:ring-secondary">
                                        <option value="">Select staff</option>
                                        @foreach ($housekeepingStaff as $staff)
                                            <option value="{{ $staff->id }}" @selected($activeTask?->assigned_to === $staff->id)>{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button type="submit" class="rounded-lg bg-secondary px-4 py-2 text-sm font-bold text-white transition hover:bg-[#5b4313]">
                                    {{ $activeTask ? 'Update Assignment' : 'Assign Task' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary text-secondary">
                                <span class="material-symbols-outlined">hotel_class</span>
                            </div>
                            <div>
                                <h3 class="font-serif text-xl font-bold text-primary">Current Guest Stayover Dossier</h3>
                                <p class="text-sm text-slate-500">
                                    {{ $latestReservation ? 'Linked reservation #' . str_pad((string) $latestReservation->id, 5, '0', STR_PAD_LEFT) : 'No active reservation linked to this room.' }}
                                </p>
                            </div>
                        </div>
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-800">
                            {{ $room->status === 'occupied' ? 'Stayover Watch' : 'Ready Status Watch' }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_1fr]">
                        <div class="rounded-lg border border-line bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Guest Profile</p>
                            <p class="mt-2 text-lg font-bold text-primary">{{ $guestName }}</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">mail</span>{{ $guestEmail }}</p>
                                <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">call</span>{{ $guestPhone }}</p>
                                <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">event</span>{{ $checkIn }} to {{ $checkOut }}</p>
                            </div>
                        </div>
                        <div class="rounded-lg border border-line bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Housekeeping Instructions</p>
                            <p class="mt-2 text-sm text-slate-700">
                                {{ $room->description ?: 'No special room instructions are recorded. Verify guest preference, amenity level, and any DND instruction before service.' }}
                            </p>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <span class="rounded-md border border-line bg-white px-3 py-2 font-bold text-slate-700">DND: Clear</span>
                                <span class="rounded-md border border-line bg-white px-3 py-2 font-bold text-slate-700">MUR: Available</span>
                                <span class="rounded-md border border-line bg-white px-3 py-2 font-bold text-slate-700">Door: Secure</span>
                                <span class="rounded-md border border-line bg-white px-3 py-2 font-bold text-slate-700">HVAC: Normal</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4">
                        <div>
                            <h3 class="flex items-center gap-2 font-serif text-xl font-bold text-primary">
                                <span class="material-symbols-outlined text-secondary">timeline</span>
                                Room {{ $room->room_number }} Operational & Status History
                            </h3>
                            <p class="mt-1 text-sm text-slate-600">Activity history of room cleaning, cleaning milestones, and room status changes.</p>
                        </div>
                        <span class="rounded-full bg-cream px-3 py-1 text-xs font-bold text-secondary">{{ $room->statusLogs->count() }} records</span>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($room->statusLogs as $log)
                            <div class="flex gap-3 rounded-lg border border-line bg-stone-50 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-secondary">
                                    <span class="material-symbols-outlined text-base">{{ $statusIcons[$log->status] ?? 'history' }}</span>
                                </div>
                                <div>
                                    <p class="font-bold text-primary">{{ ucfirst($log->status) }}</p>
                                    <p class="text-sm text-slate-600">{{ $log->notes }}</p>
                                    <p class="mt-1 text-xs font-mono text-slate-500">{{ $log->changed_at?->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-line p-8 text-center text-slate-500">No status history yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-line pb-4">
                        <h3 class="flex items-center gap-2 font-serif text-xl font-bold text-primary">
                            <span class="material-symbols-outlined text-secondary">checklist</span>
                            Daily QA & Amenity Audit
                        </h3>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">Live</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach ($qaItems as $item)
                            <label class="flex items-start gap-3 rounded-lg border border-line bg-stone-50 p-3">
                                <input type="checkbox" checked class="mt-1 rounded border-line text-secondary focus:ring-secondary">
                                <span>
                                    <span class="block text-sm font-bold text-primary">{{ $item['label'] }}</span>
                                    <span class="block text-xs text-slate-500">Verified {{ $item['time'] }} PHT</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl bg-primary p-6 text-white shadow-sm">
                    <h3 class="flex items-center gap-2 font-serif text-xl font-bold">
                        <span class="material-symbols-outlined text-secondary">sensor_occupied</span>
                        Ready Status Summary
                    </h3>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <span class="text-white/70">Current status</span>
                            <strong>{{ $currentStatus }}</strong>
                        </div>
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <span class="text-white/70">Last update</span>
                            <strong>{{ $latestLog?->changed_at?->format('h:i A') ?? 'No log' }}</strong>
                        </div>
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <span class="text-white/70">Attendant</span>
                            <strong>{{ $attendant }}</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-white/70">QA readiness</span>
                            <strong class="text-secondary">Passed</strong>
                        </div>
                    </div>
                    <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white transition hover:bg-amber-700">
                        <span class="material-symbols-outlined text-base">build</span>
                        View Reported Issues
                    </a>
                </div>

                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <h3 class="font-serif text-xl font-bold text-primary">Room Service Links</h3>
                    <div class="mt-4 grid gap-2">
                        <a href="{{ route('housekeeping.page', 'guest-requests') }}" class="rounded-lg border border-line px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-secondary hover:bg-cream">Guest Requests</a>
                        <a href="{{ route('housekeeping.page', 'special-cleaning') }}" class="rounded-lg border border-line px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-secondary hover:bg-cream">Special Cleaning</a>
                    </div>
                </div>
            </aside>
        </section>
    </div>
@endsection
