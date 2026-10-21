@php
    /**
     * Expects: $boardTitle, $boardSubtitle, and optionally $boardTypes (task types to show),
     * $boardPriorities, $boardShowCompleted (completed history instead of open work),
     * $boardDefaultType (for the create form), $boardShowCreateForm.
     */
    $taskTypeLabels = [
        'turnover' => 'Room cleaning after check-out',
        'stayover' => 'Occupied room service',
        'inspection' => 'Room inspection',
        'guest_request' => 'Guest request',
        'deep_cleaning' => 'Deep cleaning',
        'maintenance_follow_up' => 'Maintenance follow-up',
    ];
    $statusLabels = ['queued' => 'Waiting', 'in_progress' => 'In Progress', 'inspection' => 'For Inspection', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $priorityTone = ['urgent' => 'border-red-200 bg-red-50 text-red-800', 'high' => 'border-amber-200 bg-amber-50 text-amber-900', 'normal' => 'border-line bg-[#f6f3ed] text-slate-700', 'low' => 'border-line bg-white text-slate-500'];
    $boardTypes ??= null;
    $boardPriorities ??= null;
    $boardShowCompleted ??= false;
    $boardShowCreateForm ??= ! $boardShowCompleted;
    $boardDefaultType ??= $boardTypes[0] ?? 'turnover';
    $mineOnly = request('mine') === '1';

    $boardTasks = $housekeepingTasks
        ->when($boardTypes, fn ($tasks) => $tasks->whereIn('task_type', $boardTypes))
        ->when($boardPriorities, fn ($tasks) => $tasks->whereIn('priority', $boardPriorities))
        ->when($boardShowCompleted, fn ($tasks) => $tasks->where('status', 'completed'), fn ($tasks) => $tasks->whereNotIn('status', ['completed', 'cancelled']))
        ->when($mineOnly, fn ($tasks) => $tasks->where('assigned_to', auth()->id()))
        ->sortBy(fn ($task) => (['urgent' => 1, 'high' => 2, 'normal' => 3, 'low' => 4][$task->priority] ?? 5).'-'.($task->due_at?->timestamp ?? PHP_INT_MAX))
        ->values();

    if ($boardShowCompleted) {
        $boardTasks = $boardTasks->sortByDesc('completed_at')->values();
    }
@endphp

<section class="grid gap-6 {{ $boardShowCreateForm ? 'xl:grid-cols-12' : '' }}">
    <div class="space-y-4 {{ $boardShowCreateForm ? 'xl:col-span-8' : '' }}">
        <div class="flex flex-col justify-between gap-3 rounded-xl border border-line bg-white p-5 shadow-sm md:flex-row md:items-center">
            <div>
                <h3 class="font-serif text-3xl font-bold text-primary">{{ $boardTitle }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $boardSubtitle }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <a href="{{ request()->fullUrlWithQuery(['mine' => null]) }}" @class(['rounded-full border px-3 py-1.5', 'border-primary bg-primary text-white' => ! $mineOnly, 'border-line bg-white text-slate-600' => $mineOnly])>All staff</a>
                <a href="{{ request()->fullUrlWithQuery(['mine' => '1']) }}" @class(['rounded-full border px-3 py-1.5', 'border-primary bg-primary text-white' => $mineOnly, 'border-line bg-white text-slate-600' => ! $mineOnly])>Assigned to me</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1 rounded-full border border-line bg-white px-3 py-1.5 text-slate-600 hover:text-primary"><span class="material-symbols-outlined text-sm">print</span>Print</button>
            </div>
        </div>

        @forelse ($boardTasks as $task)
            <article class="rounded-xl border border-line bg-white p-4 shadow-sm" data-housekeeping-task="{{ $task->id }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="font-serif text-lg font-bold text-primary">Room {{ $task->room?->room_number ?? 'N/A' }} - {{ $task->title }}</h4>
                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase {{ $priorityTone[$task->priority] ?? '' }}">{{ ucfirst($task->priority) }}</span>
                            <span class="rounded-full bg-[#f0eee8] px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">{{ $taskTypeLabels[$task->task_type] ?? $task->task_type }}</span>
                        </div>
                        @if ($task->instructions)<p class="mt-1 text-sm text-slate-700">{{ $task->instructions }}</p>@endif
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $task->assignee?->name ? 'Assigned to '.$task->assignee->name : 'Unassigned' }}
                            @if ($task->due_at) · Due {{ $task->due_at->timezone(config('app.timezone'))->format('M d, h:i A') }}@endif
                            @if ($boardShowCompleted && $task->completed_at) · Completed {{ $task->completed_at->timezone(config('app.timezone'))->format('M d, h:i A') }}@endif
                        </p>
                        @if ($boardShowCompleted && $task->completion_notes)<p class="mt-1 text-xs italic text-slate-600">{{ $task->completion_notes }}</p>@endif
                    </div>
                    <span class="rounded-full bg-primary px-2.5 py-1 text-[10px] font-bold uppercase text-white">{{ $statusLabels[$task->status] ?? $task->status }}</span>
                </div>

                @unless ($boardShowCompleted)
                    <form method="POST" action="{{ route('housekeeping-tasks.update', $task) }}" class="mt-3 grid gap-2 border-t border-line pt-3 md:grid-cols-[1fr_1fr_1.4fr_auto] md:items-center">
                        @csrf
                        @method('PATCH')
                        <select name="assigned_to" aria-label="Assignee for Room {{ $task->room?->room_number }} task" class="rounded-lg border-line bg-white py-2 text-sm">
                            <option value="">Unassigned</option>
                            @if ($task->assignee && $task->assignee->role !== 'housekeeping')
                                <option value="{{ $task->assignee->id }}" selected>{{ $task->assignee->name }} ({{ ucfirst($task->assignee->role) }})</option>
                            @endif
                            @foreach ($housekeepingStaff as $staff)
                                <option value="{{ $staff->id }}" @selected($task->assigned_to === $staff->id)>{{ $staff->name }}{{ $staff->id === auth()->id() ? ' (me)' : '' }}</option>
                            @endforeach
                        </select>
                        <select name="status" aria-label="Status for Room {{ $task->room?->room_number }} task" class="rounded-lg border-line bg-white py-2 text-sm">
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input name="completion_notes" maxlength="2000" placeholder="Notes (e.g. restocked minibar)" class="rounded-lg border-line bg-white py-2 text-sm">
                        <button type="submit" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary hover:bg-secondary hover:text-white">Save</button>
                    </form>
                @endunless
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-line bg-white p-10 text-center">
                <span class="material-symbols-outlined text-4xl text-slate-300">task_alt</span>
                <p class="mt-2 font-semibold text-slate-600">{{ $boardShowCompleted ? 'No completed tasks yet.' : 'No open tasks here right now.' }}</p>
            </div>
        @endforelse
    </div>

    @if ($boardShowCreateForm)
        <aside class="xl:col-span-4">
            <form method="POST" action="{{ route('housekeeping-tasks.store') }}" class="space-y-3 rounded-xl border border-line bg-white p-5 shadow-sm">
                @csrf
                <h3 class="font-serif text-2xl font-bold text-primary">Add a Task</h3>
                <label class="block text-xs font-bold text-slate-600">Room
                    <select name="room_id" required class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                        <option value="">Select room</option>
                        @foreach ($allRooms as $room)
                            <option value="{{ $room->id }}">Room {{ $room->room_number }} - {{ $room->room_type }} ({{ ucfirst($room->status) }})</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-xs font-bold text-slate-600">Task type
                    <select name="task_type" required class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm" data-board-task-type>
                        @foreach ($taskTypeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($boardDefaultType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-xs font-bold text-slate-600">Title
                    <input name="title" required maxlength="255" value="{{ $taskTypeLabels[$boardDefaultType] ?? '' }}" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm" data-board-task-title>
                </label>
                <label class="block text-xs font-bold text-slate-600">Instructions
                    <textarea name="instructions" rows="2" maxlength="2000" placeholder="e.g. Guest asked for 2 extra towels" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm"></textarea>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="block text-xs font-bold text-slate-600">Priority
                        <select name="priority" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                            @foreach (['normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent', 'low' => 'Low'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-xs font-bold text-slate-600">Assign to
                        <select name="assigned_to" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                            <option value="">{{ auth()->user()->role === 'housekeeping' ? 'Me' : 'Unassigned (housekeeping queue)' }}</option>
                            @foreach ($housekeepingStaff as $staff)
                                @continue($staff->id === auth()->id())
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-primary/90"><span class="material-symbols-outlined text-base text-[#ffdeaa]">assignment_add</span>Create Task</button>
            </form>
        </aside>
        <script>
            (() => {
                const type = document.querySelector('[data-board-task-type]');
                const title = document.querySelector('[data-board-task-title]');
                type?.addEventListener('change', () => { title.value = type.options[type.selectedIndex].text; });
            })();
        </script>
    @endif
</section>
