<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Front Desk Operations - Le Charmé Suites IHMP</title>
    @include('partials.offline-assets')
    <script>tailwind.config = { theme: { extend: { fontFamily: { body: ['Manrope'], display: ['Manrope'] } } } };</script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { font-family: Manrope, sans-serif; }
        .serif { font-family: Manrope, sans-serif; }
        .lux-card { background: #fff; border: 1px solid #e4e4df; border-radius: 10px; }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-[#f5f5f2] text-[#161a20] antialiased">
    @include('partials.front-desk-sidebar', ['active' => 'front-desk'])

    <div class="lg:ml-64">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-[#e4e4df] bg-[#f5f5f2] px-5 py-3 sm:px-8">
            <form method="GET" action="{{ route('reservations.index') }}" class="relative w-full max-w-sm">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-[#6b7079]" aria-hidden="true">search</span>
                <input name="search" class="h-9 w-full rounded-lg border border-[#d3d3cd] bg-white pl-9 pr-3 text-sm outline-none placeholder:text-[#6b7079] focus:border-[#17707f] focus:ring-2 focus:ring-[#17707f]/20" placeholder="Search guest, room, or reservation" aria-label="Search reservations">
            </form>
            <div class="flex shrink-0 items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p id="dashboard-clock" class="text-xs tabular-nums text-[#5b6069]" data-timezone="{{ config('app.timezone') }}">{{ now(config('app.timezone'))->format('M d, Y • h:i A') }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="lc-avatar h-9 w-9" aria-label="Open my profile">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1480px] flex-col gap-6 p-5 sm:p-8">
            @if (session('status'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
            <section class="lc-page-head">
                <div>
                    <h1>Front Desk Dashboard</h1>
                    <p>Arrivals, room readiness, reservations and guest service for today.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('check-ins.index') }}" class="lc-btn lc-btn-quiet"><span class="material-symbols-outlined" aria-hidden="true">login</span>Arrivals</a>
                    <a href="{{ route('reservations.create') }}" class="lc-btn lc-btn-primary"><span class="material-symbols-outlined" aria-hidden="true">person_add</span>Quick Walk-In Registration</a>
                </div>
            </section>

            @include('partials.room-availability-summary')

            <dl class="lc-stat-strip">
                <div class="lc-stat"><dt>Current in-house guests</dt><dd>{{ $currentGuests }}</dd><p class="lc-stat-note">Guests with active check-ins</p></div>
                <div class="lc-stat"><dt>Turnover today</dt><dd>{{ $arrivalsToday + $departuresToday }}</dd><p class="lc-stat-note">{{ $arrivalsToday }} arriving · {{ $departuresToday }} departing</p></div>
                <div class="lc-stat"><dt>Rooms ready</dt><dd>{{ $availableRooms }}<small>of {{ $totalRooms }}</small></dd><p class="lc-stat-note">{{ $totalRooms }} encoded of {{ $publishedRoomCount }} published rooms</p></div>
                <div class="lc-stat"><dt>Booking queue</dt><dd>{{ $pendingCheckIns }}</dd><p class="lc-stat-note">Online requests and confirmed arrivals</p></div>
            </dl>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12"><section class="lux-card overflow-hidden xl:col-span-8"><div class="flex flex-col justify-between gap-3 px-5 pb-3 pt-5 sm:flex-row sm:items-center"><div><h2>Active Reservations</h2><p class="mt-0.5 text-sm text-[#5b6069]">Current front-desk records and room assignments</p></div><a href="{{ route('reservations.index') }}" class="lc-btn lc-btn-quiet">View all</a></div><div class="overflow-x-auto"><table class="w-full min-w-[680px] text-left text-sm"><thead class="border-y border-[#eeeeea] text-xs"><tr><th class="px-5 py-2.5 font-semibold">Reservation</th><th class="px-5 py-2.5 font-semibold">Guest</th><th class="px-5 py-2.5 font-semibold">Room</th><th class="px-5 py-2.5 font-semibold">Dates</th><th class="px-5 py-2.5 font-semibold">Status</th><th class="px-5 py-2.5 text-right font-semibold">Action</th></tr></thead><tbody class="divide-y divide-[#eeeeea]">@forelse ($activeReservationsList as $reservation)<tr><td class="px-5 py-3.5 text-xs font-semibold tabular-nums text-[#5b6069]">#{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</td><td class="px-5 py-3.5"><p class="font-semibold text-[#161a20]">{{ $reservation->guest?->full_name ?? 'No guest' }}</p><p class="text-xs text-[#6b7079]">{{ $reservation->guest?->phone ?? 'No contact' }}</p></td><td class="px-5 py-3.5"><p class="font-semibold text-[#161a20]">Room {{ $reservation->room?->room_number ?? 'N/A' }}</p><p class="text-xs text-[#6b7079]">{{ $reservation->room?->room_type }}</p></td><td class="whitespace-nowrap px-5 py-3.5 text-xs">{{ $reservation->check_in_date?->format('M d') }} - {{ $reservation->check_out_date?->format('M d, Y') }}</td><td class="px-5 py-3.5"><span @class(['inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold', 'bg-[#e3f0f1] text-[#0e4f5a]' => $reservation->status === 'checked_in', 'bg-[#f0f0ec] text-[#3f444c]' => $reservation->status !== 'checked_in'])>{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span></td><td class="px-5 py-3.5 text-right"><a href="{{ route('reservations.show', $reservation) }}" class="text-sm font-semibold text-[#161a20] underline decoration-[#d3d3cd] underline-offset-4 hover:decoration-[#161a20]">Open</a></td></tr>@empty<tr><td colspan="6" class="px-5 py-14 text-center"><p class="font-semibold text-[#3f444c]">No reservation records yet.</p><a href="{{ route('reservations.create') }}" class="lc-btn lc-btn-primary mt-3">Create reservation</a></td></tr>@endforelse</tbody></table></div></section>

                <aside class="space-y-6 xl:col-span-4"><section class="lux-card p-5"><div class="flex items-start justify-between gap-3"><div><h2>Floor Room Matrix</h2><p class="mt-0.5 text-sm text-[#5b6069]">Live occupancy by level</p></div><a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-[#161a20] underline decoration-[#d3d3cd] underline-offset-4 hover:decoration-[#161a20]">Manage rooms</a></div><div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#5b6069]"><span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#17707f]"></span>Available</span><span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#9cc7cd]"></span>Reserved</span><span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#3f444c]"></span>Occupied</span><span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm border border-dashed border-[#a9aba5] bg-[#f0f0ec]"></span>Cleaning or maintenance</span></div><div class="mt-4 space-y-4">@forelse ($roomMatrix as $floor => $rooms)<div><div class="mb-1.5 flex items-center justify-between"><span class="text-sm font-semibold text-[#161a20]">Floor {{ $floor }}</span><span class="text-xs text-[#6b7079]">{{ $rooms->count() }} rooms</span></div><div class="grid grid-cols-5 gap-1">@foreach ($rooms as $room)@php($statusClass = match ($room->status) { 'available' => 'bg-[#17707f] text-white', 'reserved' => 'bg-[#9cc7cd] text-[#0e4f5a]', 'occupied' => 'bg-[#3f444c] text-white', 'maintenance', 'cleaning' => 'border border-dashed border-[#a9aba5] bg-[#f0f0ec] text-[#3f444c]', default => 'bg-[#e4e4df] text-[#161a20]' })<a href="{{ route('rooms.show', $room) }}" class="{{ $statusClass }} rounded-md py-1.5 text-center text-xs font-semibold tabular-nums hover:opacity-85" title="{{ $room->room_number }} - {{ ucfirst($room->status) }}">{{ $room->room_number }}</a>@endforeach</div></div>@empty<p class="text-sm text-slate-500">No rooms encoded yet.</p>@endforelse</div></section><section class="lux-card p-5"><h2 class="mb-3">Quick Actions</h2><div class="grid gap-2"><a href="{{ route('check-ins.create') }}" class="lc-btn lc-btn-primary"><span class="material-symbols-outlined" aria-hidden="true">login</span>Express Check-In</a><a href="{{ route('check-outs.create') }}" class="lc-btn lc-btn-quiet"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Express Check-Out</a><a href="{{ route('guests.create') }}" class="lc-btn lc-btn-quiet"><span class="material-symbols-outlined" aria-hidden="true">person_add</span>Add Guest</a></div></section></aside></div>
        </main>
    </div>
    <script>
        const clock = document.getElementById('dashboard-clock'); const timezone = clock.dataset.timezone || 'Asia/Manila'; const refreshClock = () => { clock.textContent = new Intl.DateTimeFormat('en-US', { timeZone: timezone, month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(new Date()).replace(',', ' •'); }; refreshClock(); setInterval(refreshClock, 1000);
    </script>
</body>
</html>
