@php($active = $active ?? '')
<nav class="lc-nav-group" aria-label="Front Desk Navigation">
    <a href="{{ route('dashboard') }}" @class(['lc-nav-link', 'is-active' => $active === 'front-desk']) @if ($active === 'front-desk') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">concierge</span>Front Desk</a>
    <a href="{{ route('reservations.index') }}" @class(['lc-nav-link', 'is-active' => $active === 'reservations']) @if ($active === 'reservations') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>Reservations</a>
    <a href="{{ route('check-ins.index') }}" @class(['lc-nav-link', 'is-active' => $active === 'check-ins']) @if ($active === 'check-ins') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">login</span>Check-ins</a>
    <a href="{{ route('check-outs.create') }}" @class(['lc-nav-link', 'is-active' => $active === 'check-outs']) @if ($active === 'check-outs') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">logout</span>Check-outs</a>
    <a href="{{ route('rooms.index') }}" @class(['lc-nav-link', 'is-active' => $active === 'room-status']) @if ($active === 'room-status') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">bed</span>Room Status</a>
    <a href="{{ route('check-outs.index') }}" @class(['lc-nav-link', 'is-active' => $active === 'transactions']) @if ($active === 'transactions') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>Transactions</a>
    <a href="{{ route('guests.index') }}" @class(['lc-nav-link', 'is-active' => $active === 'guests']) @if ($active === 'guests') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">group</span>Guests</a>
    <a href="{{ route('messages.index') }}" @class(['lc-nav-link', 'is-active' => $active === 'messages']) @if ($active === 'messages') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">forum</span>Messages</a>
    <a href="{{ route('profile.edit') }}" @class(['lc-nav-link', 'is-active' => $active === 'profile']) @if ($active === 'profile') aria-current="page" @endif><span class="material-symbols-outlined" aria-hidden="true">account_circle</span>My Profile</a>
</nav>
