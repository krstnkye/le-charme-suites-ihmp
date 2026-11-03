@php
    $active = $active ?? 'dashboard';
    $navBase = 'lc-nav-link';
    $navActive = 'is-active';
    $navIdle = '';
    $subBase = 'lc-nav-sub';
    $subActive = 'is-active';
    $subIdle = '';
@endphp

<aside data-universal-sidebar class="lc-sidebar w-72" aria-label="Housekeeping Sidebar">
    <div class="lc-sidebar-head">
        <a href="{{ route('dashboard') }}" class="lc-brand">
            @include('partials.brand-logo', ['class' => 'w-full'])
            <span class="lc-brand-scope"><strong>Housekeeping</strong> · Rooms &amp; facilities</span>
        </a>
    </div>

    <nav id="housekeeping-sidebar-nav" class="lc-sidebar-nav" aria-label="Housekeeping Navigation">
        <div>
            <a href="{{ route('dashboard') }}" class="{{ $navBase }} {{ $active === 'dashboard' ? $navActive : $navIdle }}">
                <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
                Dashboard
            </a>
        </div>

        <div>
            <p class="lc-nav-label">Rooms</p>
            <div class="lc-nav-group">
                <a href="{{ route('rooms.index') }}" class="{{ $navBase }} {{ $active === 'rooms' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">bed</span>Rooms</a>
                <a href="{{ route('rooms.index') }}" class="{{ $subBase }} {{ $active === 'room-status' ? $subActive : $subIdle }}">Room Status</a>
                <a href="{{ route('housekeeping.page', 'assigned-rooms') }}" class="{{ $subBase }} {{ $active === 'assigned-rooms' ? $subActive : $subIdle }}">My Assigned Rooms</a>
                <a href="{{ route('housekeeping.page', 'room-inspection') }}" class="{{ $subBase }} {{ $active === 'room-inspection' ? $subActive : $subIdle }}">Room Inspection</a>
            </div>
        </div>

        <div>
            <p class="lc-nav-label">Housekeeping Tasks</p>
            <div class="lc-nav-group">
                <a href="{{ route('housekeeping.page', 'tasks') }}" class="{{ $navBase }} {{ $active === 'tasks' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">task_alt</span>Housekeeping Tasks</a>
                <a href="{{ route('housekeeping.page', 'cleaning-tasks') }}" class="{{ $subBase }} {{ $active === 'cleaning-tasks' ? $subActive : $subIdle }}">Cleaning Tasks</a>
                <a href="{{ route('housekeeping.page', 'priority-rooms') }}" class="{{ $subBase }} {{ $active === 'priority-rooms' ? $subActive : $subIdle }}">Priority Rooms</a>
                <a href="{{ route('housekeeping.page', 'completed-tasks') }}" class="{{ $subBase }} {{ $active === 'completed-tasks' ? $subActive : $subIdle }}">Completed Tasks</a>
            </div>
        </div>

        <div>
            <p class="lc-nav-label">Service Requests</p>
            <div class="lc-nav-group">
                <a href="{{ route('housekeeping.page', 'service-requests') }}" class="{{ $navBase }} {{ $active === 'service-requests' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">room_service</span>Service Requests</a>
                <a href="{{ route('housekeeping.page', 'guest-requests') }}" class="{{ $subBase }} {{ $active === 'guest-requests' ? $subActive : $subIdle }}">Guest Requests</a>
                <a href="{{ route('housekeeping.page', 'special-cleaning') }}" class="{{ $subBase }} {{ $active === 'special-cleaning' ? $subActive : $subIdle }}">Special Cleaning</a>
            </div>
        </div>

        <div>
            <p class="lc-nav-label">Maintenance Requests</p>
            <div class="lc-nav-group">
                <a href="{{ route('housekeeping.page', 'maintenance-requests') }}" class="{{ $navBase }} {{ $active === 'maintenance' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">construction</span>Maintenance Requests</a>
                <a href="{{ route('housekeeping.page', 'report-issue') }}" class="{{ $subBase }} {{ $active === 'report-issue' ? $subActive : $subIdle }}">Report Issue</a>
                <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="{{ $subBase }} {{ $active === 'reported-issues' ? $subActive : $subIdle }}">Reported Issues</a>
            </div>
        </div>

        <div class="lc-nav-group">
            <a href="{{ route('housekeeping.page', 'lost-found') }}" class="{{ $navBase }} {{ $active === 'lost-found' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">travel_explore</span>Lost &amp; Found</a>
            <a href="{{ route('housekeeping.page', 'reports') }}" class="{{ $navBase }} {{ $active === 'reports' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">monitoring</span>Reports</a>
            <a href="{{ route('housekeeping.page', 'notifications') }}" class="{{ $navBase }} {{ $active === 'notifications' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">notifications</span>Notifications</a>
            <a href="{{ route('messages.index') }}" class="{{ $navBase }} {{ request()->routeIs('messages.*') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">forum</span>Department Messages</a>
        </div>
    </nav>

    <div class="lc-sidebar-foot">
        <a href="{{ route('profile.edit') }}" class="{{ $navBase }} {{ request()->routeIs('profile.*') ? $navActive : $navIdle }}">
            <span class="material-symbols-outlined" aria-hidden="true">settings</span>
            My Profile
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="{{ $navBase }} {{ $navIdle }} w-full text-left">
                <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                Logout
            </button>
        </form>
    </div>
</aside>

@include('partials.universal-sidebar-controls')

<script>
    (() => {
        const sidebarNav = document.getElementById('housekeeping-sidebar-nav');
        if (!sidebarNav) {
            return;
        }

        const storageKey = 'lecharme.housekeepingSidebarScrollTop';
        const rememberScroll = () => sessionStorage.setItem(storageKey, String(sidebarNav.scrollTop));
        const activeLink = Array.from(sidebarNav.querySelectorAll('a[href]')).find((link) => link.href === window.location.href);
        const activeLinkIsVisible = () => {
            if (!activeLink) {
                return true;
            }

            const navRect = sidebarNav.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();

            return linkRect.top >= navRect.top && linkRect.bottom <= navRect.bottom;
        };

        const restoreScroll = () => {
            const savedScroll = sessionStorage.getItem(storageKey);

            if (savedScroll !== null) {
                sidebarNav.scrollTop = Number(savedScroll);
            }

            requestAnimationFrame(() => {
                if (activeLink && !activeLinkIsVisible()) {
                    activeLink.scrollIntoView({ block: 'center' });
                    rememberScroll();
                }
            });
        };

        restoreScroll();
        requestAnimationFrame(restoreScroll);
        setTimeout(restoreScroll, 75);

        sidebarNav.addEventListener('scroll', rememberScroll, { passive: true });
        sidebarNav.querySelectorAll('a[href]').forEach((link) => {
            link.addEventListener('pointerdown', rememberScroll);
            link.addEventListener('mousedown', rememberScroll);
            link.addEventListener('click', rememberScroll);
        });
        window.addEventListener('pageshow', restoreScroll);
        window.addEventListener('pagehide', rememberScroll);
        window.addEventListener('beforeunload', rememberScroll);
    })();
</script>
