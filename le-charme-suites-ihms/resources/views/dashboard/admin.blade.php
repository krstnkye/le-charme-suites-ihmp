@php
    $user = auth()->user();
    $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
    $readyRate = $totalRooms > 0 ? round(($availableRooms / $totalRooms) * 100) : 0;
    $adminCount = (int) ($roleCounts['admin'] ?? 0);
    $receptionistCount = (int) ($roleCounts['receptionist'] ?? 0);
    $cashierCount = (int) ($roleCounts['cashier'] ?? 0);
    $housekeepingCount = (int) ($roleCounts['housekeeping'] ?? 0);
    $ownerCount = (int) ($roleCounts['owner'] ?? 0);
    $todayMovement = $arrivalsToday + $departuresToday;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Le Charme Suites - Admin Command Center</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        secondary: '#755a28',
                        'secondary-fixed': '#ffdeaa',
                        background: '#fcf9f3',
                        surface: '#fcf9f3',
                        'surface-container': '#f0eee8',
                        'surface-container-low': '#f6f3ed',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-highest': '#e5e2dc',
                        'surface-variant': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        error: '#ba1a1a',
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        serif: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .nav-shell {
            background-color: #172b42;
            background-image: radial-gradient(rgba(255, 222, 170, 0.14) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        .command-card {
            background: #fffdfa;
            border: 1px solid #e5e2dc;
            box-shadow: 0 16px 42px -28px rgba(1, 22, 45, .45), 0 2px 8px rgba(117, 90, 40, .08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">security</span>
                <h1 class="font-serif text-xl font-bold">Le Charme Suites IHMP - System Administration Command Center</h1>
                <span class="hidden rounded-full border border-secondary/20 bg-surface-container px-3 py-1 text-xs font-bold text-secondary lg:inline-flex">
                    Subic Bay Freeport Zone
                </span>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden items-center gap-1.5 rounded border border-outline-variant/30 bg-surface-container px-3 py-1 text-xs font-semibold text-slate-600 sm:flex">
                    <span class="material-symbols-outlined text-sm text-secondary">wifi_tethering</span>
                    TLS 1.3 Active
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-primary">{{ $user?->name }}</p>
                    <p id="admin-clock" class="text-[11px] text-slate-500" data-timezone="{{ config('app.timezone') }}">{{ now(config('app.timezone'))->format('M d, Y • h:i A') }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-secondary/40 bg-secondary-fixed text-sm font-bold text-primary">
                    {{ strtoupper(substr($user?->name ?? 'A', 0, 1)) }}
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif

            <section class="relative overflow-hidden rounded-xl border border-secondary/30 bg-primary p-6 text-white shadow-md">
                <div class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-secondary/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-secondary-fixed">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                            <span class="text-xs font-bold uppercase tracking-wider">Subic Bay IHMP Master Telemetry - Live Operations</span>
                        </div>
                        <h2 class="mt-2 font-serif text-4xl font-bold">Property Systems Integrity & Control</h2>
                        <p class="mt-2 text-sm leading-6 text-[#b4c8e5]">Primary hotel operations, guest bills, room inventory, and role access are monitored from this administrator workspace.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-white/10 bg-primary-container/80 p-3 sm:grid-cols-4">
                        <div class="px-3 py-1.5">
                            <p class="text-[11px] font-bold uppercase text-[#b4c8e5]">Occupancy</p>
                            <p class="mt-1 text-lg font-bold">{{ $occupancyRate }}%</p>
                        </div>
                        <div class="px-3 py-1.5">
                            <p class="text-[11px] font-bold uppercase text-[#b4c8e5]">Rooms Managed</p>
                            <p class="mt-1 text-lg font-bold">{{ $totalRooms }} / {{ $publishedRoomCount }}</p>
                        </div>
                        <div class="px-3 py-1.5">
                            <p class="text-[11px] font-bold uppercase text-[#b4c8e5]">Staff Accounts</p>
                            <p class="mt-1 text-lg font-bold">{{ $totalUsers }}</p>
                        </div>
                        <div class="px-3 py-1.5">
                            <p class="text-[11px] font-bold uppercase text-[#b4c8e5]">Alerts</p>
                            <p class="mt-1 text-lg font-bold text-secondary-fixed">{{ $maintenanceRooms }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="command-card rounded-xl p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Property Occupancy</span><span class="material-symbols-outlined text-secondary">hotel</span></div>
                    <div class="mt-3 flex items-baseline gap-2"><span class="font-serif text-4xl font-bold">{{ $occupancyRate }}%</span><span class="text-sm text-slate-500">{{ $occupiedRooms }} / {{ $totalRooms }} rooms</span></div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-surface-container"><div class="h-full rounded-full bg-secondary" style="width: {{ min(100, $occupancyRate) }}%"></div></div>
                    <p class="mt-2 text-xs font-semibold text-secondary">{{ $availableRooms }} ready · {{ $totalRooms }} of {{ $publishedRoomCount }} rooms encoded</p>
                </div>
                <div class="command-card rounded-xl p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Reservation Flow</span><span class="material-symbols-outlined text-secondary">calendar_month</span></div>
                    <p class="mt-3 font-serif text-4xl font-bold">{{ $activeReservations }}</p>
                    <div class="mt-3 flex justify-between border-t border-surface-container pt-3 text-xs text-slate-500"><span>Arrivals <strong class="text-primary">{{ $arrivalsToday }}</strong></span><span>Departures <strong class="text-primary">{{ $departuresToday }}</strong></span></div>
                </div>
                <div class="command-card rounded-xl p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Staff On Duty</span><span class="material-symbols-outlined text-primary">badge</span></div>
                    <p class="mt-3 font-serif text-4xl font-bold">{{ $totalUsers }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-1.5 text-xs text-slate-600">
                        <span class="rounded bg-surface-container-low px-2 py-1">Front Desk: <strong>{{ $receptionistCount }}</strong></span>
                        <span class="rounded bg-surface-container-low px-2 py-1">Hskp: <strong>{{ $housekeepingCount }}</strong></span>
                        <span class="rounded bg-surface-container-low px-2 py-1">Cashier: <strong>{{ $cashierCount }}</strong></span>
                        <span class="rounded bg-surface-container-low px-2 py-1">Admin: <strong>{{ $adminCount }}</strong></span>
                    </div>
                </div>
                <div class="command-card rounded-xl p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Exceptions</span><span class="material-symbols-outlined text-error">priority_high</span></div>
                    <p class="mt-3 font-serif text-4xl font-bold text-error">{{ $maintenanceRooms }}</p>
                    <div class="mt-3 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between"><span class="font-semibold text-error">Maintenance / cleaning queue</span><a href="{{ route('admin.operations.index', ['module' => 'housekeeping']) }}" class="font-bold text-secondary hover:underline">Review</a></div>
                        <div class="flex items-center justify-between"><span class="text-slate-600">{{ $pendingCheckIns }} pending arrivals</span><a href="{{ route('admin.operations.index', ['module' => 'check-ins']) }}" class="font-bold text-secondary hover:underline">Open</a></div>
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <h2 class="font-serif text-2xl font-bold">Operational Governance & Subsystems</h2>
                        <p class="mt-1 text-sm text-slate-500">Direct administrative access and real-time record checking.</p>
                    </div>
                    <span class="rounded-full bg-surface-container-highest px-3 py-1 text-xs font-bold text-slate-600">Synchronized with local Laravel IHMP database</span>
                </div>
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="command-card rounded-xl p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary-container text-secondary"><span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">admin_panel_settings</span></span><div><h3 class="text-lg font-bold">User & Role Governance</h3><p class="text-sm text-slate-500">Active access control matrix</p></div></div>
                            <span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-bold">{{ $totalUsers }} Accounts</span>
                        </div>
                        <div class="my-5 grid grid-cols-3 gap-3 border-y border-surface-container py-3">
                            <div><span class="text-[11px] font-bold text-slate-500">DEPARTMENTS</span><p class="font-bold">4 Workspaces</p></div>
                            <div><span class="text-[11px] font-bold text-slate-500">ADMINS</span><p class="font-bold text-emerald-700">{{ $adminCount }}</p></div>
                            <div><span class="text-[11px] font-bold text-slate-500">OWNERS</span><p class="font-bold text-secondary">{{ $ownerCount }}</p></div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-secondary hover:text-primary">Manage Matrix <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
                    </div>

                    <div class="command-card rounded-xl p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-lg bg-secondary-fixed/40 text-secondary"><span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">meeting_room</span></span><div><h3 class="text-lg font-bold">Inventory & Rate Overrides</h3><p class="text-sm text-slate-500">Room inventory and availability control</p></div></div>
                            <span class="rounded-full bg-secondary/15 px-2.5 py-1 text-xs font-bold text-secondary">{{ $readyRate }}% Ready</span>
                        </div>
                        <div class="my-5 space-y-2.5 border-y border-surface-container py-3 text-sm">
                            <div class="flex justify-between"><span>Available Rooms</span><strong>{{ $availableRooms }}</strong></div>
                            <div class="flex justify-between"><span>Occupied Rooms</span><strong>{{ $occupiedRooms }}</strong></div>
                            <div class="flex justify-between"><span>Reserved Rooms</span><strong>{{ $reservedRooms }}</strong></div>
                            <div class="flex justify-between"><span>Maintenance / Cleaning</span><strong class="text-error">{{ $maintenanceRooms }}</strong></div>
                        </div>
                        <a href="{{ route('admin.operations.index', ['module' => 'housekeeping']) }}" class="inline-flex items-center gap-1 text-sm font-bold text-secondary hover:text-primary">Room Grid <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
                    </div>

                    <div class="command-card rounded-xl p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-primary"><span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">concierge</span></span><div><h3 class="text-lg font-bold">Reservation & Front Desk Flow</h3><p class="text-sm text-slate-500">Live arrival and departure movement</p></div></div>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">{{ $todayMovement }} Today</span>
                        </div>
                        <div class="my-5 grid grid-cols-2 gap-4 border-y border-surface-container py-3">
                            <div class="rounded-lg bg-surface-container-low p-3"><span class="text-[11px] font-bold text-slate-500">CHECK-INS</span><p class="mt-1 font-serif text-2xl font-bold">{{ $arrivalsToday }}</p></div>
                            <div class="rounded-lg bg-surface-container-low p-3"><span class="text-[11px] font-bold text-slate-500">CHECK-OUTS</span><p class="mt-1 font-serif text-2xl font-bold">{{ $departuresToday }}</p></div>
                        </div>
                        <a href="{{ route('admin.operations.index', ['module' => 'reservations']) }}" class="inline-flex items-center gap-1 text-sm font-bold text-secondary hover:text-primary">View Manifest <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
                    </div>

                    <div class="command-card rounded-xl p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-lg border border-outline-variant bg-surface-container text-primary"><span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_balance</span></span><div><h3 class="text-lg font-bold">Financial Records &amp; Review</h3><p class="text-sm text-slate-500">Transactions, guest bills, and reports</p></div></div>
                            <span class="rounded-full bg-secondary-fixed/40 px-2.5 py-1 text-xs font-bold text-secondary">Live Records</span>
                        </div>
                        <div class="my-5 space-y-2 border-y border-surface-container py-3 text-sm">
                            <div class="flex justify-between"><span>Recent Transactions</span><strong>{{ $recentTransactions->count() }}</strong></div>
                            <div class="flex justify-between"><span>Current Guests</span><strong>{{ $currentGuests }}</strong></div>
                            <div class="flex justify-between"><span>Open Reservations</span><strong>{{ $activeReservations }}</strong></div>
                        </div>
                        <a href="{{ route('admin.operations.index', ['module' => 'transactions']) }}" class="inline-flex items-center gap-1 text-sm font-bold text-secondary hover:text-primary">Open Ledgers <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
                    </div>
                </div>
            </section>

            <section class="command-card rounded-xl p-6">
                <div class="flex flex-col justify-between gap-3 border-b border-surface-container pb-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">history</span>
                        <div>
                            <h3 class="text-lg font-bold">Privileged System Audit & Telemetry Log</h3>
                            <p class="text-sm text-slate-500">Recent operational transactions recorded by the system.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.operations.index', ['module' => 'transactions']) }}" class="rounded-lg border border-outline-variant px-3 py-1.5 text-xs font-bold text-primary hover:bg-surface-container">Open Transaction Console</a>
                </div>
                <div class="divide-y divide-surface-container">
                    @forelse ($recentTransactions as $transaction)
                        <div class="flex items-center justify-between gap-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-secondary-fixed/50 text-secondary"><span class="material-symbols-outlined text-sm">receipt_long</span></span>
                                <div>
                                    <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }} recorded for {{ $transaction->guest?->full_name ?? 'guest record' }}</p>
                                    <p class="text-sm text-slate-500">Room {{ $transaction->room?->room_number ?? 'N/A' }} - Status: {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}</p>
                                </div>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <span>{{ $transaction->transaction_date?->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</span>
                                <p class="font-mono text-secondary">IHMP</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-sm font-semibold text-slate-500">No telemetry records yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="flex flex-col items-start justify-between gap-4 rounded-xl border border-outline-variant/30 bg-surface-container p-5 md:flex-row md:items-center">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-secondary">Master Command Bar</span>
                    <h4 class="font-bold">Privileged System Operations</h4>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('rooms.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container"><span class="material-symbols-outlined text-sm">add_business</span>Add Room</a>
                    <a href="{{ route('admin.operations.index', ['module' => 'reports']) }}" class="inline-flex items-center gap-2 rounded-lg border border-secondary bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-secondary-fixed/30"><span class="material-symbols-outlined text-sm text-secondary">mode_night</span>View Reports</a>
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-surface-container-highest"><span class="material-symbols-outlined text-sm">manage_accounts</span>Admin Profile</a>
                </div>
            </section>

            <footer class="pb-8 pt-4 text-center text-xs text-slate-500">
                <p>Le Charme Suites Integrated Hotel Management Portal (IHMP) - Subic Bay Central Server Cluster</p>
                <p class="mt-1">Authorized Administration Station - Laravel local development environment</p>
            </footer>
        </main>
    </div>

    <script>
        const clock = document.getElementById('admin-clock');
        const timezone = clock?.dataset.timezone || 'Asia/Manila';
        const refreshClock = () => {
            if (!clock) return;
            clock.textContent = new Intl.DateTimeFormat('en-US', {
                timeZone: timezone,
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            }).format(new Date()).replace(',', ' •');
        };
        refreshClock();
        setInterval(refreshClock, 1000);
    </script>
</body>
</html>
