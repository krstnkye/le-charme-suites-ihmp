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
            <p><strong>Maximum Capacity:</strong> {{ $room->capacity }} guests</p>
            <p><strong>Status:</strong> {{ ucfirst($room->status) }}</p>
            @if ($room->room_size_sqm)
                <p><strong>Room Size:</strong> {{ number_format($room->room_size_sqm) }} sqm</p>
            @endif
            @if ($room->bed_configuration)
                <p><strong>Bed Configuration:</strong> {{ $room->bed_configuration }}</p>
            @endif
            @if ($room->base_occupancy)
                <p><strong>Base Occupancy:</strong> {{ $room->base_occupancy }} guests</p>
            @endif
            @if ($room->bathrooms)
                <p><strong>Bathrooms:</strong> {{ $room->bathrooms }}</p>
            @endif
            @if ($room->extra_guest_rate)
                <p><strong>Extra Guest Rate:</strong> PHP {{ number_format($room->extra_guest_rate, 2) }} per night</p>
            @endif
            <p><strong>Smoking Policy:</strong> {{ $room->non_smoking ? 'Non-Smoking' : 'Smoking permitted' }}</p>
        </div>
        @if ($room->amenities)
            <div class="mt-6 border-t border-slate-200 pt-5">
                <h2 class="text-lg font-bold">Official Amenities</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($room->amenities as $amenity)
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-900">{{ $amenity }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="mt-6 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <p><strong>Check-in:</strong> {{ config('hotel.check_in_time') }}</p>
            <p><strong>Check-out:</strong> {{ config('hotel.check_out_time') }}</p>
            <p><strong>Front Office:</strong> {{ config('hotel.phone') }}</p>
            <p><strong>Email:</strong> {{ config('hotel.email') }}</p>
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
