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

<aside data-universal-sidebar class="lc-sidebar w-64" aria-label="Front Desk Sidebar">
    <div class="lc-sidebar-head">
        <a href="{{ route('dashboard') }}" class="lc-brand">
            @include('partials.brand-logo', ['class' => 'w-full'])
            <span class="lc-brand-scope"><strong>Front Desk IHMP</strong> · Subic Bay</span>
        </a>

        <a href="{{ route('check-ins.create') }}" class="lc-sidebar-action">
            <span class="material-symbols-outlined" aria-hidden="true">how_to_reg</span>
            Express Check-In
        </a>
    </div>

    <div class="lc-sidebar-nav">
        @include('partials.front-desk-nav', ['active' => $active])
    </div>

    <form method="POST" action="{{ route('logout') }}" class="lc-sidebar-foot">
        @csrf
        <button type="submit" class="lc-nav-link">
            <span class="material-symbols-outlined" aria-hidden="true">logout</span>
            Sign Out
        </button>
    </form>
</aside>

@include('partials.universal-sidebar-controls')

@if (auth()->user()?->hasPermissionTo('reservations.read'))
    @include('partials.online-booking-alerts')
@endif
