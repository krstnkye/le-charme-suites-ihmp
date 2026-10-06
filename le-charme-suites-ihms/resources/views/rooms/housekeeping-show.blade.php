@extends('layouts.housekeeping')

@section('title', 'Room ' . $room->room_number . ' - Housekeeping')

@section('content')
    <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Housekeeping / Room Status</p><h2 class="mt-1 font-serif text-3xl font-bold">Room {{ $room->room_number }}</h2></div><a href="{{ route('rooms.index') }}" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary">Back to Room Status</a></div>
    <section class="rounded-xl border border-line bg-white p-6 shadow-sm"><div class="grid gap-4 sm:grid-cols-2"><p><strong>Type:</strong> {{ $room->room_type }}</p><p><strong>Rate:</strong> PHP {{ number_format($room->rate, 2) }}</p><p><strong>Capacity:</strong> {{ $room->capacity }}</p><p><strong>Status:</strong> {{ ucfirst($room->status) }}</p></div><div class="mt-6 flex justify-end"><a href="{{ route('rooms.edit', $room) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white">Update Room Status</a></div><h3 class="mt-8 border-t border-line pt-6 text-lg font-bold">Status History</h3><ul class="mt-3 space-y-2 text-sm">@forelse ($room->statusLogs as $log)<li class="rounded-md bg-slate-50 p-3">{{ $log->changed_at?->format('M d, Y h:i A') }} - {{ ucfirst($log->status) }} - {{ $log->notes }}</li>@empty<li class="text-slate-500">No status history yet.</li>@endforelse</ul></section>
@endsection
