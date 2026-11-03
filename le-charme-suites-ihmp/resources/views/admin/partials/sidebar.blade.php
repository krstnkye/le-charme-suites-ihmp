@php
    $adminSidebarUser = $currentUser ?? auth()->user();
    $activeModule = request()->route('module');
    $activeModule = request()->routeIs('rooms.*') ? 'room-inventory' : $activeModule;

    $navBase = 'lc-nav-link';
    $navIdle = '';
    $navActive = 'is-active';
@endphp

<aside data-admin-sidebar data-universal-sidebar class="lc-sidebar w-72" aria-label="Administrator Sidebar">
    <div class="lc-sidebar-head">
        <a href="{{ route('dashboard') }}" class="lc-brand">
            @include('partials.brand-logo', ['class' => 'w-full'])
            <span class="lc-brand-scope"><strong>Administrator</strong> · Subic Bay</span>
        </a>
        <a href="{{ route('admin.operations.index', ['module' => 'reservations']) }}" class="lc-sidebar-action">
            <span class="material-symbols-outlined" aria-hidden="true">add</span>
            New Reservation
        </a>
    </div>

    <nav class="lc-sidebar-nav" aria-label="Administrator navigation">
        <div class="lc-nav-group">
            <a href="{{ route('dashboard') }}" class="{{ $navBase }} {{ request()->routeIs('dashboard') ? $navActive : $navIdle }}">
                <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
                Dashboard
            </a>
        </div>

        <div class="lc-nav-group">
            <p class="lc-nav-label">User &amp; Role Management</p>
            <a href="{{ route('admin.users.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.users.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">group</span>Users</a>
            <a href="{{ route('admin.accounts.create') }}" class="{{ $navBase }} {{ request()->routeIs('admin.accounts.*') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">person_add</span>Create Account</a>
            <a href="{{ route('admin.roles.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.roles.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">admin_panel_settings</span>Roles</a>
            <a href="{{ route('admin.permissions.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.permissions.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">lock</span>Permissions</a>
        </div>

        <div class="lc-nav-group">
            <p class="lc-nav-label">Operations</p>
            <a href="{{ route('admin.guests.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.guests.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">badge</span>Guest Management</a>
            <a href="{{ route('admin.operations.index', ['module' => 'reservations']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'reservations' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>Reservations</a>
            <a href="{{ route('admin.operations.index', ['module' => 'check-ins']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'check-ins' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">login</span>Check-in</a>
            <a href="{{ route('admin.operations.index', ['module' => 'check-outs']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'check-outs' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Check-out</a>
            <a href="{{ route('admin.operations.index', ['module' => 'housekeeping']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'housekeeping' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">cleaning_services</span>Housekeeping</a>
            <a href="{{ route('admin.operations.index', ['module' => 'transactions']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'transactions' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>Transactions / Billing</a>
        </div>

        <div class="lc-nav-group">
            <p class="lc-nav-label">Room Management</p>
            <a href="{{ route('admin.operations.index', ['module' => 'room-inventory']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-inventory' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>Room Inventory</a>
            <a href="{{ route('admin.operations.index', ['module' => 'room-types']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-types' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">hotel</span>Room Types</a>
            <a href="{{ route('admin.operations.index', ['module' => 'room-rates']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-rates' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">sell</span>Room Rates</a>
            <a href="{{ route('admin.operations.index', ['module' => 'room-status']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-status' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">bed</span>Room Status</a>
        </div>

        <div class="lc-nav-group">
            <p class="lc-nav-label">Control</p>
            <a href="{{ route('admin.operations.index', ['module' => 'reports']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'reports' ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">monitoring</span>Reports</a>
            <a href="{{ route('admin.audit-logs.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.audit-logs.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">shield</span>Audit Logs</a>
            <a href="{{ route('messages.index') }}" class="{{ $navBase }} {{ request()->routeIs('messages.*') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">forum</span>Department Messages</a>
            <a href="{{ route('admin.integration.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.integration.*') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">hub</span>Integration Center</a>
            <a href="{{ route('admin.settings.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.settings.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">settings</span>System Settings</a>
            <a href="{{ route('profile.edit') }}" class="{{ $navBase }} {{ request()->routeIs('profile.edit') ? $navActive : $navIdle }}"><span class="material-symbols-outlined" aria-hidden="true">account_circle</span>My Profile</a>
        </div>
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="lc-sidebar-foot">
        @csrf
        <button class="lc-nav-link">
            <span class="material-symbols-outlined" aria-hidden="true">logout</span>
            Log Out
        </button>
    </form>
</aside>

@include('partials.universal-sidebar-controls')

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.querySelector('[data-admin-sidebar]');
        const activeLink = sidebar?.querySelector('a.is-active');

        if (!sidebar || !activeLink) {
            return;
        }

        requestAnimationFrame(() => {
            const targetTop = activeLink.offsetTop - (sidebar.clientHeight / 2) + (activeLink.clientHeight / 2);
            sidebar.scrollTo({
                top: Math.max(targetTop, 0),
                behavior: 'auto',
            });
        });
    });
</script>
