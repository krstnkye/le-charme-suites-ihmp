@extends('layouts.app')

@section('title', $guest->full_name . ' - Le Charmé Suites IHMS')
@section('section_label', 'Front Desk / Guest Management')
@section('page_title', 'Guest Profile')

@section('content')
    <section class="mb-6 overflow-hidden rounded-xl bg-[#01162d] text-white shadow-lg">
        <div class="flex flex-col justify-between gap-5 p-6 sm:p-8 md:flex-row md:items-center">
            <div class="flex items-center gap-4"><span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#fdd79a] text-xl font-bold text-[#271900]">{{ strtoupper(substr($guest->first_name, 0, 1) . substr($guest->last_name, 0, 1)) }}</span><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-[#fdd79a]">GST-{{ str_pad($guest->id, 4, '0', STR_PAD_LEFT) }}</p><h2 class="ihms-serif mt-1 text-3xl font-semibold">Guest Profile &amp; Stay History</h2><p class="mt-1 text-sm text-slate-300">{{ $guest->full_name }}</p></div></div>
            <div class="flex flex-wrap gap-3"><a href="{{ route('reservations.create', ['guest_id' => $guest->id]) }}" class="inline-flex items-center gap-2 rounded-lg bg-[#755a28] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#8a6a32]"><span class="material-symbols-outlined text-lg">calendar_add_on</span>New Reservation</a><a href="{{ route('guests.edit', $guest) }}" class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/10"><span class="material-symbols-outlined text-lg">edit</span>Edit Profile</a></div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
        <section class="ihms-card h-fit rounded-xl p-6">
            <div class="mb-5 flex items-center gap-3 border-b border-[#e5e2dc] pb-4"><span class="material-symbols-outlined text-[#755a28]">contact_page</span><h3 class="ihms-serif text-xl font-semibold text-[#01162d]">Guest Information</h3></div>
            <dl class="space-y-4 text-sm">
                <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Email address</dt><dd class="mt-1 break-words font-medium text-[#01162d]">{{ $guest->email ?? 'Not provided' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Phone number</dt><dd class="mt-1 font-medium text-[#01162d]">{{ $guest->phone ?? 'Not provided' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Residential address</dt><dd class="mt-1 leading-6 text-[#01162d]">{{ $guest->address ?? 'Not provided' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Identification</dt><dd class="mt-1 font-medium text-[#01162d]">{{ $guest->identification_type ?: 'Not recorded' }}{{ $guest->identification_number ? ' · '.$guest->identification_number : '' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Notes &amp; preferences</dt><dd class="mt-1 leading-6 text-[#01162d]">{{ $guest->notes ?? 'No service notes recorded.' }}</dd></div>
            </dl>
        </section>

        <section class="ihms-card overflow-hidden rounded-xl">
            <div class="flex items-center justify-between gap-3 border-b border-[#e5e2dc] p-5 sm:p-6"><div><h3 class="ihms-serif text-2xl font-semibold text-[#01162d]">Reservation &amp; Stay Ledger</h3><p class="mt-1 text-sm text-slate-500">All bookings associated with this guest profile.</p></div><span class="rounded-full bg-[#f6f3ed] px-3 py-1 text-xs font-bold text-[#01162d]">{{ $guest->reservations->count() }} total</span></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left text-sm"><thead class="bg-[#f6f3ed] text-[11px] font-bold uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Reservation</th><th class="px-5 py-3">Room</th><th class="px-5 py-3">Stay dates</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead><tbody class="divide-y divide-[#e5e2dc]">@forelse ($guest->reservations as $reservation)<tr class="hover:bg-[#fcf9f3]"><td class="px-5 py-4 font-mono text-xs font-bold text-[#01162d]">LC-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</td><td class="px-5 py-4 font-semibold">Room {{ $reservation->room?->room_number ?? 'Unassigned' }}</td><td class="px-5 py-4 text-slate-600">{{ $reservation->check_in_date?->format('M d, Y') }} – {{ $reservation->check_out_date?->format('M d, Y') }}</td><td class="px-5 py-4"><span class="rounded-full bg-[#f0eee8] px-2.5 py-1 text-xs font-bold text-[#01162d]">{{ ucwords(str_replace('_', ' ', $reservation->status)) }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('reservations.show', $reservation) }}" class="font-bold text-[#755a28] hover:underline">View booking</a></td></tr>@empty<tr><td colspan="5" class="px-5 py-14 text-center"><span class="material-symbols-outlined text-4xl text-slate-300">event_busy</span><p class="mt-2 font-semibold text-slate-600">No reservations recorded yet.</p><a href="{{ route('reservations.create', ['guest_id' => $guest->id]) }}" class="mt-4 inline-flex rounded-lg bg-[#01162d] px-4 py-2.5 text-sm font-bold text-white">Create reservation</a></td></tr>@endforelse</tbody></table></div>
        </section>
    </div>
@endsection
