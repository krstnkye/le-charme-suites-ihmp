@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim($user?->name ?? 'Owner')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'O';
    $activePage = $page ?? 'dashboard';
    $activeNavKey = match (true) {
        in_array($activePage, ['room-inventory', 'room-availability', 'room-status'], true) => 'rooms',
        in_array($activePage, ['checkin-records', 'checkout-records'], true) => 'checkins',
        in_array($activePage, ['billing-records'], true) => 'billing',
        in_array($activePage, ['housekeeping-requests'], true) => 'housekeeping',
        in_array($activePage, ['operational-reports', 'transaction-reports', 'records'], true) => 'reports',
        default => $activePage,
    };
    $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
    $ownerNav = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard'), 'mode' => 'View'],
        ['key' => 'guests', 'label' => 'Guests', 'icon' => 'groups', 'href' => route('owner.page', ['page' => 'guests']), 'mode' => 'View'],
        ['key' => 'rooms', 'label' => 'Rooms', 'icon' => 'bed', 'href' => route('owner.page', ['page' => 'rooms']), 'mode' => 'View'],
        ['key' => 'reservations', 'label' => 'Reservations', 'icon' => 'calendar_month', 'href' => route('owner.page', ['page' => 'reservations']), 'mode' => 'View'],
        ['key' => 'checkins', 'label' => 'Check-in / Check-out', 'icon' => 'login', 'href' => route('owner.page', ['page' => 'checkins']), 'mode' => 'View'],
        ['key' => 'billing', 'label' => 'Billing & Transactions', 'icon' => 'payments', 'href' => route('owner.page', ['page' => 'billing']), 'mode' => 'View'],
        ['key' => 'housekeeping', 'label' => 'Housekeeping', 'icon' => 'cleaning_services', 'href' => route('owner.page', ['page' => 'housekeeping']), 'mode' => 'Monitor'],
        ['key' => 'reports', 'label' => 'Reports & Records', 'icon' => 'query_stats', 'href' => route('owner.page', ['page' => 'reports']), 'mode' => 'View'],
    ];
    $ownerNavGroups = [
        ['items' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard')],
            ['key' => 'guests', 'label' => 'Guests', 'icon' => 'groups', 'href' => route('owner.page', ['page' => 'guests'])],
        ]],
        ['items' => [
            ['key' => 'rooms', 'label' => 'Rooms', 'icon' => 'bed', 'href' => route('owner.page', ['page' => 'rooms']), 'children' => [
                ['key' => 'room-inventory', 'label' => 'Room Inventory', 'href' => route('owner.page', ['page' => 'room-inventory'])],
                ['key' => 'room-availability', 'label' => 'Room Availability', 'href' => route('owner.page', ['page' => 'room-availability'])],
                ['key' => 'room-status', 'label' => 'Room Status', 'href' => route('owner.page', ['page' => 'room-status'])],
            ]],
            ['key' => 'reservations', 'label' => 'Reservations', 'icon' => 'calendar_month', 'href' => route('owner.page', ['page' => 'reservations'])],
        ]],
        ['items' => [
            ['key' => 'checkins', 'label' => 'Check-in / Check-out', 'icon' => 'login', 'href' => route('owner.page', ['page' => 'checkins']), 'children' => [
                ['key' => 'checkin-records', 'label' => 'Check-in Records', 'href' => route('owner.page', ['page' => 'checkin-records'])],
                ['key' => 'checkout-records', 'label' => 'Check-out Records', 'href' => route('owner.page', ['page' => 'checkout-records'])],
            ]],
            ['key' => 'billing', 'label' => 'Billing & Transactions', 'icon' => 'payments', 'href' => route('owner.page', ['page' => 'billing']), 'children' => [
                ['key' => 'billing-records', 'label' => 'Billing Records', 'href' => route('owner.page', ['page' => 'billing-records'])],
                ['key' => 'billing', 'label' => 'Transactions', 'href' => route('owner.page', ['page' => 'billing']).'#transactions'],
            ]],
        ]],
        ['items' => [
            ['key' => 'housekeeping', 'label' => 'Housekeeping', 'icon' => 'cleaning_services', 'href' => route('owner.page', ['page' => 'housekeeping']), 'children' => [
                ['key' => 'room-status', 'label' => 'Room Status', 'href' => route('owner.page', ['page' => 'room-status'])],
                ['key' => 'housekeeping-requests', 'label' => 'Housekeeping Requests', 'href' => route('owner.page', ['page' => 'housekeeping-requests'])],
            ]],
            ['key' => 'reports', 'label' => 'Reports & Records', 'icon' => 'query_stats', 'href' => route('owner.page', ['page' => 'reports']), 'children' => [
                ['key' => 'operational-reports', 'label' => 'Operational Reports', 'href' => route('owner.page', ['page' => 'operational-reports'])],
                ['key' => 'transaction-reports', 'label' => 'Transaction Reports', 'href' => route('owner.page', ['page' => 'transaction-reports'])],
                ['key' => 'records', 'label' => 'Records', 'href' => route('owner.page', ['page' => 'records'])],
            ]],
            ['key' => 'messages', 'label' => 'Department Messages', 'icon' => 'forum', 'href' => route('owner.page', ['page' => 'messages'])],
        ]],
    ];
    $titles = [
        'dashboard' => ['Executive Owner Dashboard', 'View-only property command overview for Le Charmé Suites.'],
        'guests' => ['Guest Management Overview', 'Read-only guest registry and stay relationship monitoring.'],
        'rooms' => ['Room Management Overview', 'Room inventory, occupancy, and maintenance readiness visibility.'],
        'room-inventory' => ['Room Inventory Overview', 'Read-only room asset registry and inventory readiness visibility.'],
        'room-availability' => ['Room Availability Overview', 'Read-only availability and capacity monitoring across room types.'],
        'room-status' => ['Room Status Overview', 'Read-only occupancy, maintenance, and readiness status monitoring.'],
        'reservations' => ['Reservations Overview', 'Booking pipeline and reservation status monitoring.'],
        'checkins' => ['Check-in / Check-out Overview', 'Arrival, in-house, and departure movement visibility.'],
        'checkin-records' => ['Check-in Records', 'Read-only registry of guest arrivals, active stays, and room key issuance.'],
        'checkout-records' => ['Check-out Records', 'Audited departure archive, settlement status, and housekeeping turnover visibility.'],
        'billing' => ['Billing & Transactions Overview', 'Revenue, settlement, and transaction monitoring.'],
        'billing-records' => ['Billing Records', 'Grouped guest billing accounts, invoice totals, settlement status, and audit visibility.'],
        'housekeeping' => ['Housekeeping Monitor', 'Room readiness and housekeeping activity oversight.'],
        'housekeeping-requests' => ['Housekeeping Requests', 'Guest service, amenity, cleaning, and maintenance request monitoring.'],
        'reports' => ['Reports & Records Overview', 'Executive reporting shortcuts and operational records summary.'],
        'operational-reports' => ['Operational Reports', 'Departmental telemetry, room readiness, occupancy, and workflow pacing.'],
        'transaction-reports' => ['Transaction Reports', 'Settlement reports, payment channel totals, taxes, and financial audit exports.'],
        'records' => ['Records Archive', 'Immutable owner-view archive of guests, stays, billing, rooms, and housekeeping records.'],
        'messages' => ['Department Messages', 'Persistent interdepartment communication and management notices.'],
        'profile' => ['Owner My Profile', 'Executive credentials, security posture, notification preferences, and access logs.'],
    ];
    [$pageTitle, $pageSubtitle] = $titles[$activePage] ?? $titles['dashboard'];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} - Le Charmé Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = { theme: { extend: {
            colors: { primary: '#01162d', 'primary-container': '#172b42', secondary: '#755a28', 'secondary-container': '#fdd79a', 'secondary-fixed': '#ffdeaa', surface: '#fcf9f3', 'surface-low': '#f6f3ed', 'surface-mid': '#f0eee8', line: '#e5e2dc', error: '#ba1a1a' },
            fontFamily: { sans: ['Manrope', 'sans-serif'], serif: ['Noto Serif', 'serif'] },
        } } };
    </script>
    <style>
        .material-symbols-outlined { display: inline-block; vertical-align: middle; line-height: 1; font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24; }
        .owner-card { background: #fdfcfa; border: 1px solid #ede8df; box-shadow: 0 4px 16px -2px rgba(23, 43, 66, .05), 0 1px 3px rgba(197, 163, 106, .08); }
        .owner-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .owner-scroll::-webkit-scrollbar-track { background: #f0eee8; }
        .owner-scroll::-webkit-scrollbar-thumb { background: #c4c6cd; border-radius: 999px; }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    @include('partials.owner-sidebar')

    <div class="flex min-h-screen flex-col lg:ml-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-5 py-3 shadow-sm backdrop-blur lg:px-8">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Owner Access / View Only</p>
                <h1 class="mt-1 font-serif text-xl font-bold text-primary">Le Charmé Suites IHMP <span class="font-sans text-sm font-semibold text-slate-500">Executive Command Center</span></h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden rounded-full border border-line bg-surface-low px-3 py-1.5 text-xs font-bold text-slate-600 md:inline-flex">No record editing privileges</span>
                <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm">
                    <p id="owner-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p>
                    <p id="owner-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1700px] space-y-6 p-5 lg:p-8">
            @if ($activePage === 'dashboard')
                @include('owner.partials.dashboard')
            @elseif ($activePage === 'guests')
                @include('owner.partials.guests')
            @elseif ($activePage === 'reservations')
                @include('owner.partials.reservations')
            @elseif ($activePage === 'checkins')
                @include('owner.partials.checkins')
            @elseif ($activePage === 'checkin-records')
                @include('owner.partials.checkin-records')
            @elseif ($activePage === 'checkout-records')
                @include('owner.partials.checkout-records')
            @elseif ($activePage === 'billing-records')
                @include('owner.partials.billing-records')
            @elseif ($activePage === 'billing')
                @include('owner.partials.billing')
            @elseif ($activePage === 'housekeeping')
                @include('owner.partials.housekeeping')
            @elseif ($activePage === 'housekeeping-requests')
                @include('owner.partials.housekeeping-requests')
            @elseif ($activePage === 'room-availability')
                @include('owner.partials.room-availability')
            @elseif ($activePage === 'room-status')
                @include('owner.partials.room-status')
            @elseif (in_array($activePage, ['rooms', 'room-inventory'], true))
                @include('owner.partials.rooms')
            @elseif ($activePage === 'reports')
                @include('owner.partials.reports')
            @elseif ($activePage === 'operational-reports')
                @include('owner.partials.operational-reports')
            @elseif ($activePage === 'transaction-reports')
                @include('owner.partials.transaction-reports')
            @elseif ($activePage === 'records')
                @include('owner.partials.records')
            @elseif ($activePage === 'messages')
                @include('messages.panel')
            @elseif ($activePage === 'profile')
                @include('owner.partials.profile')
            @else
            <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-md">
                <div class="pointer-events-none absolute -bottom-20 -right-16 h-96 w-96 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                    <div>
                        <span class="rounded border border-secondary-fixed/30 bg-secondary/30 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Executive Monitoring</span>
                        <h2 class="mt-3 font-serif text-4xl font-bold leading-tight">{{ $pageTitle }}</h2>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-blue-100">{{ $pageSubtitle }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-white/10 bg-white/10 p-4 sm:grid-cols-4 xl:w-[560px]">
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Occupancy</p><p class="mt-1 font-serif text-2xl font-bold">{{ $occupancyRate }}%</p></div>
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Property Rooms</p><p class="mt-1 font-serif text-2xl font-bold">{{ $publishedRoomCount }}</p><p class="text-[10px] text-blue-100/70">{{ $totalRooms }} encoded</p></div>
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Guests</p><p class="mt-1 font-serif text-2xl font-bold">{{ $currentGuests }}</p></div>
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-blue-100/70">Revenue</p><p class="mt-1 font-serif text-xl font-bold">PHP {{ number_format($recordedRevenue, 2) }}</p></div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Guest Management</span><span class="material-symbols-outlined text-secondary">badge</span></div><p class="mt-3 font-serif text-3xl font-bold">{{ $totalGuests }}</p><p class="mt-1 text-sm text-slate-500">Registered guest profiles</p></div>
                <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Room Management</span><span class="material-symbols-outlined text-secondary">bed</span></div><p class="mt-3 font-serif text-3xl font-bold">{{ $availableRooms }}</p><p class="mt-1 text-sm text-slate-500">Available of {{ $totalRooms }} encoded rooms · {{ $publishedRoomCount }} property total</p></div>
                <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Reservations</span><span class="material-symbols-outlined text-secondary">calendar_month</span></div><p class="mt-3 font-serif text-3xl font-bold">{{ $activeReservations }}</p><p class="mt-1 text-sm text-slate-500">{{ $arrivalsToday }} arrivals today</p></div>
                <div class="owner-card rounded-xl p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Billing & Transactions</span><span class="material-symbols-outlined text-secondary">payments</span></div><p class="mt-3 font-serif text-3xl font-bold">PHP {{ number_format($collectedToday, 2) }}</p><p class="mt-1 text-sm text-slate-500">{{ $settledToday }} settled today</p></div>
            </section>
            @endif
        </main>
    </div>

    <script>
        function updateOwnerClock() {
            const now = new Date();
            document.getElementById('owner-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: '2-digit', year: 'numeric', timeZone: 'Asia/Manila' });
            document.getElementById('owner-clock').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Asia/Manila' }) + ' PHT';
        }
        updateOwnerClock();
        setInterval(updateOwnerClock, 1000);

        (() => {
            const sidebar = document.getElementById('ownerSidebarNav');
            if (!sidebar) {
                return;
            }

            const storageKey = 'le-charme-owner-sidebar-scroll';
            const savedScroll = Number.parseInt(sessionStorage.getItem(storageKey) || '0', 10);
            if (!Number.isNaN(savedScroll)) {
                requestAnimationFrame(() => {
                    sidebar.scrollTop = savedScroll;
                });
            }

            const rememberSidebarPosition = () => {
                sessionStorage.setItem(storageKey, String(sidebar.scrollTop));
            };

            sidebar.addEventListener('scroll', rememberSidebarPosition, { passive: true });
            sidebar.querySelectorAll('a[href]').forEach((link) => {
                link.addEventListener('click', rememberSidebarPosition);
            });
            window.addEventListener('beforeunload', rememberSidebarPosition);
        })();
    </script>
</body>
</html>
