@php
    $roleNames = $departments;
    $priorityStyles = [
        'normal' => 'border-slate-200 bg-slate-50 text-slate-600',
        'important' => 'border-amber-200 bg-amber-50 text-amber-800',
        'urgent' => 'border-red-200 bg-red-50 text-red-700',
    ];
@endphp

@if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
@endif

<section class="overflow-hidden rounded-xl border border-[#e5e2dc] bg-white shadow-sm">
    <div class="flex flex-col justify-between gap-4 border-b border-[#e5e2dc] bg-[#f6f3ed] px-5 py-5 lg:flex-row lg:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#755a28]">Interdepartment Communication</p>
            <h2 class="mt-1 font-serif text-3xl font-bold text-[#01162d]">Shared Message Center</h2>
            <p class="mt-1 text-sm text-slate-600">Messages and individual read history are retained in the central IHMP database.</p>
        </div>
        <div class="rounded-lg border border-[#e5e2dc] bg-white px-4 py-3 text-right">
            <p class="text-xs font-bold uppercase text-slate-500">Unread for you</p>
            <p class="font-serif text-2xl font-bold text-[#01162d]">{{ $unreadCount }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-[360px_minmax(0,1fr)]">
        <form method="POST" action="{{ route('messages.store') }}" class="space-y-4 border-b border-[#e5e2dc] p-5 lg:border-b-0 lg:border-r">
            @csrf
            <div>
                <label for="recipient_department" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Send to</label>
                <select id="recipient_department" name="recipient_department" class="w-full rounded-lg border-[#c4c6cd] text-sm focus:border-[#755a28] focus:ring-[#755a28]/20" required>
                    @foreach ($departments as $value => $label)
                        <option value="{{ $value }}" @selected(old('recipient_department') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="message_subject" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Subject</label>
                <input id="message_subject" name="subject" value="{{ old('subject') }}" maxlength="140" class="w-full rounded-lg border-[#c4c6cd] text-sm focus:border-[#755a28] focus:ring-[#755a28]/20" required>
            </div>
            <div>
                <label for="message_priority" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Priority</label>
                <select id="message_priority" name="priority" class="w-full rounded-lg border-[#c4c6cd] text-sm focus:border-[#755a28] focus:ring-[#755a28]/20" required>
                    <option value="normal">Normal</option>
                    <option value="important">Important</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div>
                <label for="message_body" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Message</label>
                <textarea id="message_body" name="body" rows="6" maxlength="5000" class="w-full rounded-lg border-[#c4c6cd] text-sm focus:border-[#755a28] focus:ring-[#755a28]/20" required>{{ old('body') }}</textarea>
            </div>
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#01162d] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#172b42]">
                <span class="material-symbols-outlined text-lg text-[#ffdeaa]">send</span>
                Send and Save Message
            </button>
        </form>

        <div class="min-w-0">
            <div class="flex items-center justify-between border-b border-[#e5e2dc] px-5 py-4">
                <h3 class="font-serif text-xl font-bold text-[#01162d]">Message History</h3>
                <span class="text-xs font-semibold text-slate-500">Latest {{ $messages->count() }} records</span>
            </div>
            <div class="max-h-[680px] divide-y divide-[#e5e2dc] overflow-y-auto">
                @forelse ($messages as $message)
                    @php($isRead = $message->sender_id === $user->id || $message->readers->isNotEmpty())
                    <article class="p-5 {{ $isRead ? 'bg-white' : 'bg-amber-50/40' }}">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded border px-2 py-0.5 text-[10px] font-bold uppercase {{ $priorityStyles[$message->priority] ?? $priorityStyles['normal'] }}">{{ $message->priority }}</span>
                                    @unless ($isRead)<span class="rounded bg-[#01162d] px-2 py-0.5 text-[10px] font-bold uppercase text-white">Unread</span>@endunless
                                    <span class="text-xs font-semibold text-slate-500">To {{ $roleNames[$message->recipient_department] ?? ucfirst($message->recipient_department) }}</span>
                                </div>
                                <h4 class="mt-2 text-base font-bold text-[#01162d]">{{ $message->subject }}</h4>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $message->body }}</p>
                            </div>
                            <div class="shrink-0 text-right text-xs text-slate-500">
                                <p class="font-bold text-[#755a28]">{{ $message->sender?->name ?? 'Former staff account' }}</p>
                                <p>{{ $message->created_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        @if (! $isRead && $message->sender_id !== $user->id)
                            <form method="POST" action="{{ route('messages.read', $message) }}" class="mt-3 text-right">
                                @csrf
                                <button class="inline-flex items-center gap-1 text-xs font-bold text-[#755a28] hover:text-[#01162d]"><span class="material-symbols-outlined text-base">done_all</span>Mark as read</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="px-6 py-16 text-center text-sm text-slate-500">No department messages have been recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
