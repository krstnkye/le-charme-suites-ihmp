@extends('layouts.app')

@section('title', 'Update Room ' . $room->room_number . ' - Le Charmé Suites IHMP')
@section('section_label', 'Front Desk / Room Management')
@section('page_title', 'Update Room')

@section('content')
    <section class="mb-6 flex flex-col justify-between gap-4 border-b border-[#e5e2dc] pb-5 md:flex-row md:items-end">
        <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-[#755a28]">Room {{ $room->room_number }} · {{ $room->room_type }}</p><h2 class="ihms-serif mt-2 text-3xl font-semibold text-[#01162d] sm:text-4xl">Update Room Configuration &amp; Status</h2><p class="mt-2 text-sm text-slate-600">Maintain room inventory data and publish its current operational readiness.</p></div>
        <a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#755a28] px-4 py-2.5 text-sm font-bold text-[#755a28] hover:bg-[#fff8e8]"><span class="material-symbols-outlined text-lg">meeting_room</span>View Room</a>
    </section>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <form method="POST" action="{{ route('rooms.update', $room) }}" class="ihms-card rounded-xl p-5 sm:p-7">
            @csrf
            @method('PUT')
            <div class="mb-6 flex items-center gap-3 border-b border-[#e5e2dc] pb-5"><span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#01162d] text-white"><span class="material-symbols-outlined">room_preferences</span></span><div><h3 class="font-bold text-[#01162d]">Inventory &amp; Readiness</h3><p class="text-xs text-slate-500">Changes are shared with authorized hotel departments.</p></div></div>
            @include('rooms.form')
            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-[#e5e2dc] pt-5 sm:flex-row sm:justify-end"><a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center justify-center rounded-lg border border-[#c4c6cd] px-5 py-3 text-sm font-bold text-[#01162d] hover:bg-[#f6f3ed]">Cancel</a><button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#01162d] px-5 py-3 text-sm font-bold text-white hover:bg-[#172b42]"><span class="material-symbols-outlined text-lg">save</span>Save Room Changes</button></div>
        </form>
        <aside class="space-y-4"><section class="rounded-xl bg-[#01162d] p-5 text-white"><span class="material-symbols-outlined text-[#fdd79a]">campaign</span><h3 class="ihms-serif mt-3 text-xl font-semibold">Operational Impact</h3><p class="mt-2 text-sm leading-6 text-slate-300">Changing room status updates availability shown to Front Desk, Housekeeping, Billing, and authorized Administrator reports.</p></section><section class="ihms-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Current state</p><p class="mt-3 text-2xl font-bold text-[#01162d]">{{ ucfirst($room->status) }}</p><p class="mt-1 text-sm text-slate-500">PHP {{ number_format($room->rate, 2) }} per night</p></section></aside>
    </div>
@endsection
