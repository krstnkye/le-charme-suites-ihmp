@php
    $active = $active ?? 'dashboard';
    $navBase = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition';
    $navActive = 'bg-white/12 font-bold text-white shadow-inner border-l-4 border-[#e6c185]';
    $navIdle = 'text-[#b8c7d9] hover:bg-white/10 hover:text-white';
    $subBase = 'block rounded-md py-1.5 pl-11 pr-3 text-xs transition';
    $subActive = 'font-bold text-[#ffdeaa]';
    $subIdle = 'text-[#8fa4bd] hover:text-white';
@endphp

<aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col bg-[#01162d] px-4 py-5 text-white shadow-xl lg:flex">
    <div class="border-b border-white/10 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-[#ffdeaa]/40 bg-[#ffdeaa]/10 text-[#ffdeaa]">
                <span class="material-symbols-outlined text-3xl">cleaning_services</span>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#ffdeaa]">Hotel Management</p>
                <h1 class="font-serif text-xl font-bold leading-tight">Housekeeping</h1>
            </div>
        </div>
    </div>

    <nav id="housekeeping-sidebar-nav" class="mt-5 flex-1 space-y-5 overflow-y-auto pr-1" aria-label="Housekeeping Navigation">
        <div>
            <a href="{{ route('dashboard') }}" class="{{ $navBase }} {{ $active === 'dashboard' ? $navActive : $navIdle }}">
                <span class="material-symbols-outlined">dashboard</span>
                Dashboard
            </a>
        </div>

        <div>
            <p class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-[#ffdeaa]">Rooms</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('rooms.index') }}" class="{{ $navBase }} {{ $active === 'rooms' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">bed</span>Rooms</a>
                <a href="{{ route('rooms.index') }}" class="{{ $subBase }} {{ $active === 'room-status' ? $subActive : $subIdle }}">Room Status</a>
                <a href="{{ route('housekeeping.page', 'assigned-rooms') }}" class="{{ $subBase }} {{ $active === 'assigned-rooms' ? $subActive : $subIdle }}">My Assigned Rooms</a>
                <a href="{{ route('housekeeping.page', 'room-inspection') }}" class="{{ $subBase }} {{ $active === 'room-inspection' ? $subActive : $subIdle }}">Room Inspection</a>
            </div>
        </div>

        <div>
            <p class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-[#ffdeaa]">Housekeeping Tasks</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('housekeeping.page', 'tasks') }}" class="{{ $navBase }} {{ $active === 'tasks' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">task_alt</span>Housekeeping Tasks</a>
                <a href="{{ route('housekeeping.page', 'cleaning-tasks') }}" class="{{ $subBase }} {{ $active === 'cleaning-tasks' ? $subActive : $subIdle }}">Cleaning Tasks</a>
                <a href="{{ route('housekeeping.page', 'priority-rooms') }}" class="{{ $subBase }} {{ $active === 'priority-rooms' ? $subActive : $subIdle }}">Priority Rooms</a>
                <a href="{{ route('housekeeping.page', 'completed-tasks') }}" class="{{ $subBase }} {{ $active === 'completed-tasks' ? $subActive : $subIdle }}">Completed Tasks</a>
            </div>
        </div>

        <div>
            <p class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-[#ffdeaa]">Service Requests</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('housekeeping.page', 'service-requests') }}" class="{{ $navBase }} {{ $active === 'service-requests' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">room_service</span>Service Requests</a>
                <a href="{{ route('housekeeping.page', 'guest-requests') }}" class="{{ $subBase }} {{ $active === 'guest-requests' ? $subActive : $subIdle }}">Guest Requests</a>
                <a href="{{ route('housekeeping.page', 'special-cleaning') }}" class="{{ $subBase }} {{ $active === 'special-cleaning' ? $subActive : $subIdle }}">Special Cleaning</a>
            </div>
        </div>

        <div>
            <p class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-[#ffdeaa]">Maintenance Requests</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('housekeeping.page', 'maintenance-requests') }}" class="{{ $navBase }} {{ $active === 'maintenance' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">construction</span>Maintenance Requests</a>
                <a href="{{ route('housekeeping.page', 'report-issue') }}" class="{{ $subBase }} {{ $active === 'report-issue' ? $subActive : $subIdle }}">Report Issue</a>
                <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="{{ $subBase }} {{ $active === 'reported-issues' ? $subActive : $subIdle }}">Reported Issues</a>
            </div>
        </div>

        <div class="space-y-1">
            <a href="{{ route('housekeeping.page', 'lost-found') }}" class="{{ $navBase }} {{ $active === 'lost-found' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">travel_explore</span>Lost &amp; Found</a>
            <a href="{{ route('housekeeping.page', 'linen-supplies') }}" class="{{ $navBase }} {{ $active === 'linen-supplies' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">inventory_2</span>Linen &amp; Supplies</a>
            <a href="{{ route('housekeeping.page', 'linen-records') }}" class="{{ $subBase }} {{ $active === 'linen-records' ? $subActive : $subIdle }}">Linen Records</a>
            <a href="{{ route('housekeeping.page', 'housekeeping-supplies') }}" class="{{ $subBase }} {{ $active === 'housekeeping-supplies' ? $subActive : $subIdle }}">Housekeeping Supplies</a>
            <a href="{{ route('housekeeping.page', 'reports') }}" class="{{ $navBase }} {{ $active === 'reports' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">monitoring</span>Reports</a>
            <a href="{{ route('housekeeping.page', 'notifications') }}" class="{{ $navBase }} {{ $active === 'notifications' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">notifications</span>Notifications</a>
        </div>
    </nav>

    <div class="mt-5 border-t border-white/10 pt-4">
        <a href="{{ route('profile.edit') }}" class="{{ $navBase }} {{ request()->routeIs('profile.*') ? $navActive : $navIdle }}">
            <span class="material-symbols-outlined">settings</span>
            My Profile
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button class="{{ $navBase }} {{ $navIdle }} w-full text-left">
                <span class="material-symbols-outlined">logout</span>
                Logout
            </button>
        </form>
    </div>
</aside>

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
