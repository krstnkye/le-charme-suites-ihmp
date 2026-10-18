@extends('layouts.app')

@section('title', 'Room ' . $room->room_number . ' - Le Charmé Suites IHMS')
@section('section_label', 'Front Desk / Room Management')
@section('page_title', 'Room ' . $room->room_number)

@section('content')
    @php
        $statusClasses = ['available' => 'bg-emerald-100 text-emerald-800', 'reserved' => 'bg-amber-100 text-amber-800', 'occupied' => 'bg-[#dce8f5] text-[#01162d]', 'cleaning' => 'bg-sky-100 text-sky-800', 'maintenance' => 'bg-red-100 text-red-800'];
    @endphp
    <section class="mb-6 overflow-hidden rounded-xl bg-[#01162d] p-6 text-white shadow-lg sm:p-8">
        <div class="flex flex-col justify-between gap-5 md:flex-row md:items-center"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-[#fdd79a]">Room Operations &amp; Availability Detail</p><div class="mt-2 flex flex-wrap items-center gap-3"><h2 class="ihms-serif text-4xl font-semibold">Room {{ $room->room_number }}</h2><span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses[$room->status] ?? 'bg-white/10 text-white' }}">{{ ucfirst($room->status) }}</span></div><p class="mt-2 text-sm text-slate-300">{{ $room->room_type }} · Maximum {{ $room->capacity }} guests</p></div>
        @if (in_array(auth()->user()?->role, ['admin', 'housekeeping'], true))<a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#755a28] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#8a6a32]"><span class="material-symbols-outlined text-lg">edit</span>Update Room</a>@endif</div>
    </section>

    <section class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="ihms-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nightly rate</p><p class="ihms-serif mt-2 text-2xl font-semibold text-[#01162d]">PHP {{ number_format($room->rate, 2) }}</p></div>
        <div class="ihms-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Capacity</p><p class="ihms-serif mt-2 text-2xl font-semibold text-[#01162d]">{{ $room->capacity }} guests</p></div>
        <div class="ihms-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Room size</p><p class="ihms-serif mt-2 text-2xl font-semibold text-[#01162d]">{{ $room->room_size_sqm ? number_format($room->room_size_sqm) . ' sqm' : 'Not listed' }}</p></div>
        <div class="ihms-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Bed setup</p><p class="mt-2 font-bold text-[#01162d]">{{ $room->bed_configuration ?? 'Not listed' }}</p></div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section class="ihms-card rounded-xl p-6">
            <div class="grid gap-x-10 gap-y-5 sm:grid-cols-2"><div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Base occupancy</p><p class="mt-1 font-semibold text-[#01162d]">{{ $room->base_occupancy ?? $room->capacity }} guests</p></div><div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Bathrooms</p><p class="mt-1 font-semibold text-[#01162d]">{{ $room->bathrooms ?? 'Not listed' }}</p></div><div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Extra guest rate</p><p class="mt-1 font-semibold text-[#01162d]">{{ $room->extra_guest_rate ? 'PHP '.number_format($room->extra_guest_rate, 2).' per night' : 'Not listed' }}</p></div><div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Smoking policy</p><p class="mt-1 font-semibold text-[#01162d]">{{ $room->non_smoking ? 'Non-Smoking' : 'Smoking permitted' }}</p></div></div>
            <div class="mt-6 border-t border-[#e5e2dc] pt-5"><h3 class="ihms-serif text-xl font-semibold text-[#01162d]">Official Amenities</h3><div class="mt-3 flex flex-wrap gap-2">@forelse ($room->amenities ?? [] as $amenity)<span class="rounded-full border border-[#e6c185] bg-[#fff8e8] px-3 py-1.5 text-xs font-semibold text-[#755a28]">{{ $amenity }}</span>@empty<span class="text-sm text-slate-500">No amenities listed.</span>@endforelse</div></div>
            @if ($room->description)<div class="mt-6 border-t border-[#e5e2dc] pt-5"><h3 class="font-bold text-[#01162d]">Room notes</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $room->description }}</p></div>@endif
        </section>
        <aside class="space-y-6"><section class="ihms-card rounded-xl p-5"><h3 class="ihms-serif text-xl font-semibold text-[#01162d]">Hotel Service Times</h3><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-3"><dt class="text-slate-500">Check-in</dt><dd class="font-bold">{{ config('hotel.check_in_time') }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Check-out</dt><dd class="font-bold">{{ config('hotel.check_out_time') }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Front Office</dt><dd class="font-bold">{{ config('hotel.phone') }}</dd></div><div><dt class="text-slate-500">Email</dt><dd class="mt-1 break-all font-bold">{{ config('hotel.email') }}</dd></div></dl></section></aside>
    </div>

    <section class="ihms-card mt-6 overflow-hidden rounded-xl"><div class="border-b border-[#e5e2dc] p-5"><h3 class="ihms-serif text-2xl font-semibold text-[#01162d]">Room Status History</h3><p class="mt-1 text-sm text-slate-500">Recorded readiness and operational status changes.</p></div><div class="divide-y divide-[#e5e2dc]">@forelse ($room->statusLogs as $log)<div class="grid gap-2 p-5 text-sm sm:grid-cols-[180px_140px_minmax(0,1fr)]"><span class="font-mono text-xs text-slate-500">{{ $log->changed_at?->format('M d, Y h:i A') }}</span><span class="font-bold text-[#01162d]">{{ ucfirst($log->status) }}</span><span class="text-slate-600">{{ $log->notes ?: 'No notes recorded.' }}</span></div>@empty<div class="p-10 text-center text-sm text-slate-500">No status history has been recorded for this room.</div>@endforelse</div></section>
@endsection
