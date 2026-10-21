@php
    $myOpenTasks = $housekeepingTasks->where('assigned_to', auth()->id())->whereNotIn('status', ['completed', 'cancelled'])->sortByDesc('created_at')->values();
    $unreadMessages = $housekeepingMessages->filter(fn ($message) => $message->sender_id !== auth()->id() && $message->readers->isEmpty());
@endphp

<section class="space-y-5">
    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
        <h3 class="font-serif text-3xl font-bold text-primary">Housekeeping Assignment Notifications &amp; Operational Alerts</h3>
        <p class="mt-1 text-sm text-slate-600">Messages sent to Housekeeping, tasks assigned to you, and the latest room status changes.</p>
        <p class="mt-2 text-xs font-bold text-secondary">{{ $unreadMessages->count() }} unread message{{ $unreadMessages->count() === 1 ? '' : 's' }} · {{ $myOpenTasks->count() }} open task{{ $myOpenTasks->count() === 1 ? '' : 's' }} assigned to you</p>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        <div class="space-y-3 xl:col-span-2">
            <h4 class="font-bold text-primary">Department Messages</h4>
            @forelse ($housekeepingMessages as $message)
                @php($isUnread = $message->sender_id !== auth()->id() && $message->readers->isEmpty())
                <article @class(['rounded-xl border bg-white p-4 shadow-sm', 'border-secondary' => $isUnread, 'border-line' => ! $isUnread])>
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-bold text-primary">{{ $message->subject }}</p>
                            <p class="text-xs text-slate-500">From {{ $message->sender?->name ?? 'Former staff' }} · {{ $message->created_at?->timezone(config('app.timezone'))->format('M d, h:i A') }} · {{ ucfirst($message->priority) }}</p>
                        </div>
                        @if ($isUnread)
                            <form method="POST" action="{{ route('messages.read', $message) }}">
                                @csrf
                                <button type="submit" class="rounded-lg border border-secondary px-3 py-1 text-xs font-bold text-secondary hover:bg-secondary hover:text-white">Mark read</button>
                            </form>
                        @else
                            <span class="text-[11px] font-semibold text-slate-400">Read</span>
                        @endif
                    </div>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $message->body }}</p>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-line bg-white p-6 text-center text-sm text-slate-500">No messages for Housekeeping yet.</p>
            @endforelse
            <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white"><span class="material-symbols-outlined text-sm">forum</span>Open Department Messages</a>
        </div>

        <div class="space-y-5">
            <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                <h4 class="font-bold text-primary">Assigned to You</h4>
                <ul class="mt-2 space-y-2 text-sm">
                    @forelse ($myOpenTasks->take(10) as $task)
                        <li class="rounded-lg bg-[#f6f3ed] px-3 py-2"><span class="font-bold">Room {{ $task->room?->room_number }}</span> - {{ $task->title }} <span class="text-xs text-slate-500">({{ ucfirst($task->priority) }})</span></li>
                    @empty
                        <li class="text-slate-500">Nothing assigned to you.</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                <h4 class="font-bold text-primary">Recent Room Status Changes</h4>
                <ul class="mt-2 space-y-2 text-xs">
                    @forelse ($recentRoomLogs->take(10) as $log)
                        <li><span class="font-bold text-primary">Room {{ $log->room?->room_number }}</span> → {{ ucfirst($log->status) }} <span class="text-slate-500">{{ $log->changed_at?->timezone(config('app.timezone'))->format('M d, h:i A') }}</span><br><span class="text-slate-500">{{ $log->notes }}</span></li>
                    @empty
                        <li class="text-slate-500">No room changes recorded.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</section>
