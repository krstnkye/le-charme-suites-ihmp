@php
    $currentUser = auth()->user();
    $roleMeta = [
        'admin' => [
            'label' => 'System Admin',
            'tier' => 'Tier 1',
            'department' => 'IT Infrastructure',
            'scope' => 'Full system administration, access governance, audit oversight, and global settings.',
            'icon' => 'admin_panel_settings',
            'accent' => 'border-primary bg-primary/5',
            'badge' => 'bg-primary text-white',
            'modules' => ['All modules', 'User governance', 'System settings', 'Audit logs'],
        ],
        'receptionist' => [
            'label' => 'Front Desk',
            'tier' => 'Tier 2',
            'department' => 'Front Office',
            'scope' => 'Daily front-office workflows including guests, reservations, check-ins, and check-outs.',
            'icon' => 'concierge',
            'accent' => 'border-secondary bg-secondary-fixed/20',
            'badge' => 'bg-secondary text-white',
            'modules' => ['Guest profiles', 'Reservations', 'Check-in', 'Check-out'],
        ],
        'cashier' => [
            'label' => 'Cashier',
            'tier' => 'Tier 2',
            'department' => 'Finance & Billing',
            'scope' => 'Payment handling, guest bill settlement, transaction records, and cashier reports.',
            'icon' => 'payments',
            'accent' => 'border-[#c5a36a] bg-[#fff8ea]',
            'badge' => 'bg-[#8a6420] text-white',
            'modules' => ['Transactions', 'Guest Bills', 'Reports', 'Check-out billing'],
        ],
        'housekeeping' => [
            'label' => 'Housekeeping',
            'tier' => 'Tier 4',
            'department' => 'Rooms & Facilities',
            'scope' => 'Room readiness, cleaning queues, maintenance notes, and room status updates.',
            'icon' => 'cleaning_services',
            'accent' => 'border-surface-container-highest bg-white',
            'badge' => 'bg-surface-container text-primary',
            'modules' => ['Room status', 'Housekeeping', 'Maintenance notes', 'Turnover queue'],
        ],
    ];
    $roleOrder = ['admin', 'receptionist', 'cashier', 'housekeeping'];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles - Le Charme Suites IHMP</title>
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
                        'secondary-container': '#fdd79a',
                        background: '#fcf9f3',
                        surface: '#fcf9f3',
                        'surface-container': '#f0eee8',
                        'surface-container-low': '#f6f3ed',
                        'surface-container-highest': '#e5e2dc',
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

        .folio-shadow {
            box-shadow: 0 4px 16px -2px rgba(23, 43, 66, 0.05), 0 1px 3px rgba(197, 163, 106, 0.08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="flex flex-1 items-center gap-6">
                <div class="relative w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-500">search</span>
                    <input class="w-full rounded-lg border border-outline-variant bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20" placeholder="Search roles or access scopes..." type="text">
                </div>
                <div class="hidden items-center gap-4 text-sm font-bold lg:flex">
                    <a class="text-slate-500 transition hover:text-primary" href="{{ route('admin.users.index') }}">Users</a>
                    <a class="border-b-2 border-secondary pb-1 text-secondary" href="{{ route('admin.roles.index') }}">Roles</a>
                    <a class="text-slate-500 transition hover:text-primary" href="{{ route('admin.permissions.index') }}">Permissions</a>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden rounded-full bg-surface-container px-3 py-1 text-xs font-bold text-secondary xl:flex">Subic Bay Central</div>
                <div class="text-right">
                    <p class="text-xs font-bold text-primary">{{ $currentUser?->name }}</p>
                    <p class="text-[11px] text-slate-500">Chief Systems Admin</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-secondary/40 bg-primary-container text-sm font-bold text-white">
                    {{ strtoupper(substr($currentUser?->name ?? 'A', 0, 1)) }}
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 px-8 py-8">
            <section class="flex flex-col justify-between gap-4 border-b border-surface-container-highest pb-5 xl:flex-row xl:items-end">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span>User & Role Management</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="text-secondary">Roles</span>
                    </div>
                    <div class="flex flex-wrap items-baseline gap-3">
                        <h1 class="font-serif text-4xl font-semibold tracking-tight">Role Directory & Access Tiers</h1>
                        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-[#5b4313]">{{ count($roleOrder) }} Role Tiers</span>
                    </div>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Review each system role, department assignment, authority tier, and operational scope before tuning detailed permissions.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.permissions.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        Open Permissions Matrix
                    </a>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($roleOrder as $role)
                    @php($meta = $roleMeta[$role])
                    @php($count = (int) ($roleCounts[$role] ?? 0))
                    <article class="folio-shadow rounded-xl border border-surface-container-highest bg-white p-5">
                        <div class="flex items-start justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container text-secondary">
                                <span class="material-symbols-outlined">{{ $meta['icon'] }}</span>
                            </span>
                            <span class="rounded-full bg-surface-container-low px-2 py-0.5 text-[11px] font-bold text-slate-500">{{ $meta['tier'] }}</span>
                        </div>
                        <p class="mt-4 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $meta['label'] }}</p>
                        <p class="mt-1 font-serif text-3xl font-bold">{{ $count }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $meta['department'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                @foreach ($roleOrder as $role)
                    @php($meta = $roleMeta[$role])
                    @php($count = (int) ($roleCounts[$role] ?? 0))
                    <article class="folio-shadow rounded-xl border {{ $meta['accent'] }} p-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="flex items-start gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-white text-secondary shadow-sm">
                                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">{{ $meta['icon'] }}</span>
                                </span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="font-serif text-2xl font-semibold">{{ $meta['label'] }}</h2>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $meta['badge'] }}">{{ $meta['tier'] }}</span>
                                    </div>
                                    <p class="mt-1 text-sm font-semibold text-secondary">{{ $meta['department'] }}</p>
                                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">{{ $meta['scope'] }}</p>
                                </div>
                            </div>
                            <div class="rounded-lg bg-white/80 px-4 py-3 text-center">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Assigned</p>
                                <p class="font-serif text-3xl font-bold">{{ $count }}</p>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ($meta['modules'] as $module)
                                <div class="flex items-center gap-2 rounded-lg border border-surface-container-highest bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                                    <span class="material-symbols-outlined text-sm text-secondary">check_circle</span>
                                    {{ $module }}
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-surface-container-highest pt-4">
                            <span class="text-xs text-slate-500">Detailed capabilities are controlled in the permissions matrix.</span>
                            <a href="{{ route('admin.permissions.index', ['role' => $role]) }}" class="inline-flex items-center gap-1 rounded-lg border border-secondary px-3 py-2 text-xs font-bold text-secondary hover:bg-secondary/10">
                                Configure Role
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </section>
        </main>
    </div>
</body>
</html>
