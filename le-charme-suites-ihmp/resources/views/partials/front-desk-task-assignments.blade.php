@php
    $currentUser = auth()->user();
    $statusLabels = ['queued' => 'Waiting', 'in_progress' => 'In Progress', 'inspection' => 'For Inspection', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $taskTypes = ['turnover' => 'Room cleaning after check-out', 'stayover' => 'Occupied room service', 'inspection' => 'Room inspection', 'guest_request' => 'Guest request', 'deep_cleaning' => 'Deep cleaning', 'maintenance_follow_up' => 'Maintenance follow-up'];
@endphp

<section id="task-assignments" data-front-desk-task-assignments class="lux-card overflow-hidden rounded-xl">
    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[#e5e2dc] p-5">
        <div>
            <h3 class="serif text-2xl font-semibold text-[#01162d]">Cleaning Task Assignments</h3>
            <p class="mt-1 text-sm text-slate-500">Assign a room task to a housekeeper, or take it yourself.</p>
        </div>
        <span class="rounded-full bg-[#f6f3ed] px-3 py-1 text-xs font-bold text-[#755a28]">{{ $housekeepingTasks->count() }} open task{{ $housekeepingTasks->count() === 1 ? '' : 's' }}</span>
    </div>

    <form method="POST" action="{{ route('housekeeping-tasks.store') }}" class="grid gap-3 border-b border-[#e5e2dc] bg-[#fcf9f3] p-5 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_0.7fr_auto] xl:items-end">
        @csrf
        <label class="text-xs font-bold text-slate-600">Room
            <select name="room_id" required class="mt-1 block w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] focus:border-[#755a28] focus:ring-[#755a28]">
                <option value="">Select room</option>
                @foreach ($taskRooms as $room)
                    <option value="{{ $room->id }}" @selected((int) old('room_id') === $room->id)>Room {{ $room->room_number }} - {{ $room->room_type }} ({{ ucfirst($room->status) }})</option>
                @endforeach
            </select>
        </label>
        <label class="text-xs font-bold text-slate-600">Task
            <select name="task_type" required class="mt-1 block w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] focus:border-[#755a28] focus:ring-[#755a28]" data-task-type>
                @foreach ($taskTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('task_type', 'turnover') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="hidden" name="title" value="{{ old('title', $taskTypes['turnover']) }}" data-task-title>
        </label>
        <label class="text-xs font-bold text-slate-600">Assign to
            <select name="assigned_to" class="mt-1 block w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] focus:border-[#755a28] focus:ring-[#755a28]">
                <option value="{{ $currentUser->id }}" @selected((string) old('assigned_to') === (string) $currentUser->id)>Myself ({{ $currentUser->name }})</option>
                <optgroup label="Housekeeping staff">
                    @foreach ($housekeepingStaff as $staff)
                        <option value="{{ $staff->id }}" @selected((string) old('assigned_to') === (string) $staff->id)>{{ $staff->name }}</option>
                    @endforeach
                </optgroup>
                <option value="" @selected(old('assigned_to') === '')>Leave unassigned (housekeeping queue)</option>
            </select>
        </label>
        <label class="text-xs font-bold text-slate-600">Priority
            <select name="priority" required class="mt-1 block w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] focus:border-[#755a28] focus:ring-[#755a28]">
                @foreach (['normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent', 'low' => 'Low'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#01162d] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#172b42]">
            <span class="material-symbols-outlined text-base text-[#ffdeaa]">assignment_add</span>
            Assign Task
        </button>
        <label class="text-xs font-bold text-slate-600 md:col-span-2 xl:col-span-5">Instructions <span class="font-normal text-slate-400">(optional)</span>
            <input name="instructions" value="{{ old('instructions') }}" maxlength="2000" placeholder="e.g. Guest requested extra towels; check minibar" class="mt-1 block w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] focus:border-[#755a28] focus:ring-[#755a28]">
        </label>
    </form>

    <div class="space-y-3 p-5">
        <h4 class="font-bold text-[#01162d]">Open Assignments</h4>
        @forelse ($housekeepingTasks as $task)
            <form method="POST" action="{{ route('housekeeping-tasks.update', $task) }}" class="grid gap-3 rounded-lg border border-[#e5e2dc] bg-white p-3 md:grid-cols-[1.3fr_1fr_0.9fr_auto] md:items-center">
                @csrf
                @method('PATCH')
                <div>
                    <p class="font-bold text-[#01162d]">Room {{ $task->room?->room_number ?? 'N/A' }} - {{ $task->title }}</p>
                    <p class="text-xs text-slate-500">
                        {{ ucfirst($task->priority) }} priority ·
                        @if ($task->assigned_to === $currentUser->id)
                            <span class="font-bold text-[#755a28]">Assigned to you</span>
                        @else
                            {{ $task->assignee?->name ?? 'Unassigned' }}
                        @endif
                    </p>
                </div>
                <select name="assigned_to" aria-label="Assign Room {{ $task->room?->room_number }} task" class="rounded-lg border border-[#c4c6cd] bg-white px-3 py-2 text-sm focus:border-[#755a28] focus:ring-[#755a28]">
                    <option value="{{ $currentUser->id }}" @selected($task->assigned_to === $currentUser->id)>Myself ({{ $currentUser->name }})</option>
                    @if ($task->assignee && $task->assigned_to !== $currentUser->id && $task->assignee->role !== 'housekeeping')
                        <option value="{{ $task->assignee->id }}" selected>{{ $task->assignee->name }}</option>
                    @endif
                    @foreach ($housekeepingStaff as $staff)
                        <option value="{{ $staff->id }}" @selected($task->assigned_to === $staff->id)>{{ $staff->name }}</option>
                    @endforeach
                    <option value="" @selected($task->assigned_to === null)>Unassigned</option>
                </select>
                <select name="status" required aria-label="Status of Room {{ $task->room?->room_number }} task" class="rounded-lg border border-[#c4c6cd] bg-white px-3 py-2 text-sm focus:border-[#755a28] focus:ring-[#755a28]">
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-[#755a28] px-4 py-2 text-sm font-bold text-[#755a28] hover:bg-[#755a28] hover:text-white">Save</button>
            </form>
        @empty
            <p class="rounded-lg border border-dashed border-[#e5e2dc] p-5 text-center text-sm text-slate-500">No open cleaning tasks. Use the form above to assign one.</p>
        @endforelse
    </div>
</section>

<script>
    (() => {
        const typeSelect = document.querySelector('[data-front-desk-task-assignments] [data-task-type]');
        const titleInput = document.querySelector('[data-front-desk-task-assignments] [data-task-title]');

        typeSelect?.addEventListener('change', () => {
            titleInput.value = typeSelect.options[typeSelect.selectedIndex].text;
        });
    })();
</script>
