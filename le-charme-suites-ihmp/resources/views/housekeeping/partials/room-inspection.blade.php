@php
    $roomsAwaitingInspection = $allRooms->where('status', 'cleaning')->values();
    $openTasksByRoom = $housekeepingTasks->whereNotIn('status', ['completed', 'cancelled'])->groupBy('room_id');
@endphp

<section class="space-y-4">
    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
        <h3 class="font-serif text-3xl font-bold text-primary">Room Inspection</h3>
        <p class="mt-1 text-sm text-slate-600">Rooms marked for cleaning must pass inspection before they return to available inventory. A failed inspection creates a high-priority re-clean task.</p>
    </div>

    @forelse ($roomsAwaitingInspection as $room)
        @php($roomTasks = $openTasksByRoom->get($room->id, collect()))
        <article class="rounded-xl border border-line bg-white p-4 shadow-sm" data-inspection-room="{{ $room->id }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h4 class="font-serif text-xl font-bold text-primary">Room {{ $room->room_number }} - {{ $room->room_type }}</h4>
                    <p class="mt-1 text-xs text-slate-500">
                        @if ($roomTasks->isEmpty())
                            No open cleaning task.
                        @else
                            Open: {{ $roomTasks->map(fn ($task) => $task->title.' ('.($task->assignee?->name ?? 'unassigned').', '.str_replace('_', ' ', $task->status).')')->implode('; ') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('rooms.show', $room) }}" class="rounded-lg border border-line px-3 py-1.5 text-xs font-bold text-primary hover:bg-[#f6f3ed]">Room details</a>
            </div>
            <div class="mt-3 grid gap-2 border-t border-line pt-3 md:grid-cols-[auto_1fr]">
                <form method="POST" action="{{ route('rooms.inspection.store', $room) }}">
                    @csrf
                    <input type="hidden" name="result" value="pass">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800"><span class="material-symbols-outlined text-base">verified</span>Passed - Release Room</button>
                </form>
                <form method="POST" action="{{ route('rooms.inspection.store', $room) }}" class="flex gap-2">
                    @csrf
                    <input type="hidden" name="result" value="fail">
                    <input name="notes" maxlength="1000" placeholder="What failed? e.g. bathroom mirror streaked" aria-label="Failed inspection notes for Room {{ $room->room_number }}" class="min-w-0 flex-1 rounded-lg border-line bg-white py-2 text-sm">
                    <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50">Fail</button>
                </form>
            </div>
        </article>
    @empty
        <div class="rounded-xl border border-dashed border-line bg-white p-10 text-center">
            <span class="material-symbols-outlined text-4xl text-slate-300">fact_check</span>
            <p class="mt-2 font-semibold text-slate-600">No rooms are waiting for inspection.</p>
        </div>
    @endforelse
</section>
