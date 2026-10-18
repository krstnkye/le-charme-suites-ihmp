@php
    $active = $active ?? match (true) {
        request()->routeIs('reservations.*') => 'reservations',
        request()->routeIs('check-ins.*') => 'check-ins',
        request()->routeIs('check-outs.create') => 'check-outs',
        request()->routeIs('check-outs.*') => 'transactions',
        request()->routeIs('rooms.*') => 'room-status',
        request()->routeIs('guests.*') => 'guests',
        request()->routeIs('messages.*') => 'messages',
        request()->routeIs('profile.*') => 'profile',
        default => 'front-desk',
    };
@endphp

<aside data-universal-sidebar class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 flex-col border-r border-white/10 bg-[#01162d] px-4 py-6 text-white shadow-xl" aria-label="Front Desk Sidebar">
    <div class="flex min-h-0 flex-1 flex-col gap-6">
        <a href="{{ route('dashboard') }}" class="block shrink-0 rounded-lg bg-white px-3 py-2 shadow-sm">
            @include('partials.brand-logo', ['class' => 'mx-auto h-auto w-full max-w-[180px] object-contain'])
            <span class="mt-1 block text-center text-[10px] font-bold uppercase tracking-[0.15em] text-[#755a28]">Front Desk IHMS</span>
        </a>

        <a href="{{ route('check-ins.create') }}" class="flex w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-[#755a28] px-3 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#5b4313]">
            <span class="material-symbols-outlined text-base">how_to_reg</span>
            Express Check-In
        </a>

        <div class="min-h-0 flex-1 overflow-y-auto pr-1">
            @include('partials.front-desk-nav', ['active' => $active])
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 shrink-0 border-t border-[#172b42] pt-4">
        @csrf
        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm text-[#7f93ae] transition hover:bg-[#172b42] hover:text-white">
            <span class="material-symbols-outlined">power_settings_new</span>
            Sign Out
        </button>
    </form>
</aside>

@include('partials.universal-sidebar-controls')
