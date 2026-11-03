@extends('layouts.app')

@section('title', 'Edit Guest - Le Charmé Suites IHMP')
@section('section_label', 'Front Desk / Guest Management')
@section('page_title', 'Edit Guest Profile')

@section('content')
    <section class="mb-6 flex flex-col justify-between gap-4 border-b border-[#e4e4df] pb-5 md:flex-row md:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#5b6069]">Guest Registry · GST-{{ str_pad($guest->id, 4, '0', STR_PAD_LEFT) }}</p>
            <h2 class="ihms-serif mt-2 text-3xl font-semibold text-[#161a20] sm:text-4xl">Edit Guest Profile &amp; Stay Preferences</h2>
            <p class="mt-2 text-sm text-slate-600">Keep identity, contact, and service information accurate for future reservations.</p>
        </div>
        <a href="{{ route('guests.show', $guest) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#5b6069] px-4 py-2.5 text-sm font-bold text-[#5b6069] hover:bg-[#f7f7f4]"><span class="material-symbols-outlined text-lg">person</span>View Profile</a>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <form method="POST" action="{{ route('guests.update', $guest) }}" class="ihms-card rounded-xl p-5 sm:p-7">
            @csrf
            @method('PUT')
            <div class="mb-6 flex items-center gap-3 border-b border-[#e4e4df] pb-5"><span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#161a20] text-white"><span class="material-symbols-outlined">badge</span></span><div><h3 class="font-bold text-[#161a20]">Guest Identity &amp; Contact</h3><p class="text-xs text-slate-500">Fields marked with an asterisk are required.</p></div></div>
            @include('guests.form')
            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-[#e4e4df] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('guests.show', $guest) }}" class="inline-flex items-center justify-center rounded-lg border border-[#d3d3cd] px-5 py-3 text-sm font-bold text-[#161a20] hover:bg-[#f0f0ec]">Cancel</a>
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#161a20] px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#2a2f37]"><span class="material-symbols-outlined text-lg">save</span>Save Guest Changes</button>
            </div>
        </form>

        <aside class="space-y-4">
            <section class="lc-flip rounded-xl bg-[#161a20] p-5 text-white shadow-sm"><span class="material-symbols-outlined text-[#e8e8e4]">verified_user</span><h3 class="ihms-serif mt-3 text-xl font-semibold">Record Integrity</h3><p class="mt-2 text-sm leading-6 text-slate-300">Updates apply to this guest's shared profile and remain available to authorized Front Desk records.</p></section>
            <section class="ihms-card rounded-xl p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Current profile</p><p class="mt-3 font-bold text-[#161a20]">{{ $guest->full_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $guest->email ?? 'No email recorded' }}</p><p class="mt-4 text-xs text-slate-500">Registered {{ $guest->created_at?->format('M d, Y') }}</p></section>
        </aside>
    </div>
@endsection
