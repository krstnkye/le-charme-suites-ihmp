@extends('layouts.app')

@section('page_title', 'Room ' . $room->room_number)

@section('content')
    @if (in_array(auth()->user()?->role, ['admin', 'housekeeping'], true))
        <div class="mb-4">
            <a href="{{ route('rooms.edit', $room) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Update Room Status</a>
        </div>
    @endif

    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2">
            <p><strong>Type:</strong> {{ $room->room_type }}</p>
            <p><strong>Rate:</strong> PHP {{ number_format($room->rate, 2) }}</p>
            <p><strong>Capacity:</strong> {{ $room->capacity }}</p>
            <p><strong>Status:</strong> {{ ucfirst($room->status) }}</p>
        </div>
        <h2 class="mt-8 text-lg font-bold">Status History</h2>
        <ul class="mt-3 space-y-2 text-sm">
            @forelse ($room->statusLogs as $log)
                <li class="rounded-md bg-slate-50 p-3">{{ $log->changed_at?->format('M d, Y h:i A') }} - {{ ucfirst($log->status) }} - {{ $log->notes }}</li>
            @empty
                <li class="text-slate-500">No status history yet.</li>
            @endforelse
        </ul>
    </section>
@endsection
