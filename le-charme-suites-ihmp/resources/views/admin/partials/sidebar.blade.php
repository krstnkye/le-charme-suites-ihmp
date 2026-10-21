@php
    $adminSidebarUser = $currentUser ?? auth()->user();
    $activeModule = request()->route('module');
    $activeModule = request()->routeIs('rooms.*') ? 'room-inventory' : $activeModule;

    $navBase = 'flex items-center gap-3 rounded-r-lg border-l-4 px-4 py-2 text-sm transition';
    $navIdle = 'border-transparent text-[#b4c8e5] hover:bg-[#01162d]/30 hover:text-white';
    $navActive = 'border-secondary-fixed border-[#ffdeaa] bg-[#01162d]/40 font-bold text-white';
@endphp

<aside data-admin-sidebar data-universal-sidebar class="nav-shell fixed inset-y-0 left-0 z-40 flex w-72 flex-col justify-between overflow-y-auto border-r border-white/10 bg-[#172b42] text-white shadow-md" style="background-image: radial-gradient(rgba(255, 222, 170, 0.14) 1px, transparent 1px); background-size: 26px 26px;" aria-label="Administrator Sidebar">
    <div>
        <div class="border-b border-white/10 px-6 py-5">
            <a href="{{ route('dashboard') }}" class="block rounded-lg bg-white px-3 py-2 shadow-sm">
                @include('partials.brand-logo', ['class' => 'mx-auto h-auto w-full max-w-[190px] object-contain'])
                <span class="mt-1 block text-center text-[10px] font-bold uppercase tracking-[0.16em] text-[#755a28]">Subic IHMP Command</span>
            </a>
            <a href="{{ route('admin.operations.index', ['module' => 'reservations']) }}" class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-[#755a28] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#5b4313]">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                New Reservation
            </a>
        </div>

        <nav class="space-y-4 px-3 py-4" aria-label="Administrator navigation">
            <div>
                <p class="px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-[#ffdeaa]">Administrator</p>
                <a href="{{ route('dashboard') }}" class="{{ $navBase }} mt-2 py-2.5 {{ request()->routeIs('dashboard') ? $navActive : $navIdle }}">
                    <span class="material-symbols-outlined {{ request()->routeIs('dashboard') ? 'text-[#ffdeaa]' : '' }}" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                    Dashboard
                </a>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-[#ffdeaa]">User &amp; Role Management</p>
                <a href="{{ route('admin.users.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.users.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">group</span>Users</a>
                <a href="{{ route('admin.accounts.create') }}" class="{{ $navBase }} {{ request()->routeIs('admin.accounts.*') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">person_add</span>Create Account</a>
                <a href="{{ route('admin.roles.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.roles.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">admin_panel_settings</span>Roles</a>
                <a href="{{ route('admin.permissions.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.permissions.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">lock</span>Permissions</a>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-[#b4c8e5]">Operations</p>
                <a href="{{ route('admin.guests.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.guests.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">badge</span>Guest Management</a>
                <a href="{{ route('admin.operations.index', ['module' => 'reservations']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'reservations' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">calendar_month</span>Reservations</a>
                <a href="{{ route('admin.operations.index', ['module' => 'check-ins']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'check-ins' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">login</span>Check-in</a>
                <a href="{{ route('admin.operations.index', ['module' => 'check-outs']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'check-outs' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">logout</span>Check-out</a>
                <a href="{{ route('admin.operations.index', ['module' => 'housekeeping']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'housekeeping' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">cleaning_services</span>Housekeeping</a>
                <a href="{{ route('admin.operations.index', ['module' => 'transactions']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'transactions' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">receipt_long</span>Transactions / Billing</a>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-[#b4c8e5]">Room Management</p>
                <a href="{{ route('admin.operations.index', ['module' => 'room-inventory']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-inventory' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">inventory_2</span>Room Inventory</a>
                <a href="{{ route('admin.operations.index', ['module' => 'room-types']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-types' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">hotel</span>Room Types</a>
                <a href="{{ route('admin.operations.index', ['module' => 'room-rates']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-rates' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">sell</span>Room Rates</a>
                <a href="{{ route('admin.operations.index', ['module' => 'room-status']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'room-status' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">bed</span>Room Status</a>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-[#b4c8e5]">Control</p>
                <a href="{{ route('admin.operations.index', ['module' => 'reports']) }}" class="{{ $navBase }} {{ request()->routeIs('admin.operations.index') && $activeModule === 'reports' ? $navActive : $navIdle }}"><span class="material-symbols-outlined">monitoring</span>Reports</a>
                <a href="{{ route('admin.audit-logs.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.audit-logs.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">shield</span>Activity Logs</a>
                <a href="{{ route('messages.index') }}" class="{{ $navBase }} {{ request()->routeIs('messages.*') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">forum</span>Department Messages</a>
                <a href="{{ route('admin.integration.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.integration.*') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">hub</span>Integration Center</a>
                <a href="{{ route('admin.settings.index') }}" class="{{ $navBase }} {{ request()->routeIs('admin.settings.index') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">settings</span>System Settings</a>
                <a href="{{ route('profile.edit') }}" class="{{ $navBase }} {{ request()->routeIs('profile.edit') ? $navActive : $navIdle }}"><span class="material-symbols-outlined">account_circle</span>My Profile</a>
            </div>
        </nav>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 p-3">
        @csrf
        <button class="flex w-full items-center gap-3 rounded-r-lg border-l-4 border-transparent px-4 py-2 text-left text-sm text-red-100 transition hover:bg-red-900/30 hover:text-white">
            <span class="material-symbols-outlined text-[#ffb4ab]">logout</span>
            Log Out
        </button>
    </form>
</aside>

@include('partials.universal-sidebar-controls')

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.querySelector('[data-admin-sidebar]');
        const activeLink = sidebar?.querySelector('a.border-secondary-fixed');

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
