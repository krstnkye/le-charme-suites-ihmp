@php
    $currentUser = auth()->user();
    $roleLabels = [
        'admin' => 'System Administrator',
        'receptionist' => 'Front Desk Lead',
        'cashier' => 'Cashier & Finance',
        'housekeeping' => 'Housekeeping Lead',
    ];
    $roleTiers = [
        'admin' => 'Tier 1',
        'receptionist' => 'Tier 2',
        'cashier' => 'Tier 2',
        'housekeeping' => 'Tier 4',
    ];
    $roleDepartments = [
        'admin' => 'IT Infrastructure',
        'receptionist' => 'Front Office',
        'cashier' => 'Finance & Billing',
        'housekeeping' => 'Rooms & Facilities',
    ];
    $saved = $permissionState[$selectedRole] ?? [];
    $capabilities = collect($capabilities)->map(fn ($capability, $key) => [...$capability, 'key' => $key])->values()->all();
    $focusCapability = collect($capabilities)->firstWhere('key', 'guest_bills') ?? $capabilities[0];
    $selectedRoleCount = (int) ($roleCounts[$selectedRole] ?? 0);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissions Matrix & Capability Grants - Le Charme Suites IHMP</title>
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
                        'surface-container-lowest': '#ffffff',
                        'surface-container-high': '#ebe8e2',
                        'surface-container-highest': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6',
                        'on-error-container': '#93000a',
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

        .gold-glow {
            box-shadow: 0 4px 20px -2px rgba(23, 43, 66, 0.06), 0 1px 4px rgba(197, 163, 106, 0.12);
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
                    <input class="w-full rounded-lg border border-outline-variant/60 bg-white py-2 pl-9 pr-3 text-sm outline-none transition focus:border-secondary focus:ring-2 focus:ring-secondary/20" placeholder="Search permissions, tokens, or modules..." type="search">
                </div>
                <nav class="hidden items-center gap-5 border-l border-surface-container-highest pl-6 text-sm font-bold lg:flex">
                    <a href="{{ route('admin.users.index') }}" class="text-slate-500 transition hover:text-primary">Users</a>
                    <a href="{{ route('admin.roles.index') }}" class="text-slate-500 transition hover:text-primary">Roles</a>
                    <a href="{{ route('admin.permissions.index') }}" class="border-b-2 border-secondary pb-1 text-secondary">Permissions</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden items-center gap-2 rounded-full border border-surface-container-highest bg-surface-container px-2.5 py-1 xl:flex">
                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                    <span class="text-xs font-semibold text-slate-600">Live RBAC Enforced</span>
                </div>
                <div class="hidden items-center gap-1.5 rounded-lg border border-outline-variant/40 bg-surface-container-low px-3 py-1 text-sm font-semibold text-primary xl:flex">
                    <span class="material-symbols-outlined text-base text-secondary">domain</span>
                    Subic Bay Central
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-primary">{{ $currentUser?->name }}</p>
                    <p class="text-[11px] font-semibold text-secondary">Chief Systems Admin (Tier 1)</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-secondary/40 bg-primary-container text-sm font-bold text-secondary-fixed shadow-inner">
                    {{ collect(explode(' ', $currentUser?->name ?? 'A'))->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') }}
                </a>
            </div>
        </header>

        <main class="flex flex-col gap-6 p-8">
            <section class="flex flex-col justify-between gap-4 border-b border-surface-container-highest pb-6 xl:flex-row xl:items-end">
                <div>
                    <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <span>System Administration</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span>User Management</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="font-bold text-secondary">Permissions</span>
                    </nav>
                    <h1 class="font-serif text-4xl font-semibold tracking-tight">Granular Permissions Matrix &amp; Capability Grants</h1>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Manage read, write, execute, override, and export access for each hotel role across core IHMP modules with administrator-level audit tracking.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-outline-variant/60 bg-white px-3.5 py-2 text-sm font-bold text-primary shadow-sm transition hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-base text-secondary">receipt_long</span>
                        Audit History
                    </a>
                    <form method="POST" action="{{ route('admin.permissions.reset') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ $selectedRole }}">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-outline-variant/60 bg-white px-3.5 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-surface-container-low" type="submit">
                            <span class="material-symbols-outlined text-base">restart_alt</span>
                            Reset Role
                        </button>
                    </form>
                    <button form="permissionMatrixForm" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-md transition hover:bg-primary-container active:scale-95" type="submit">
                        <span class="material-symbols-outlined text-base text-secondary-fixed">save</span>
                        Save Matrix Grants
                    </button>
                </div>
            </section>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif

            <section class="gold-glow flex flex-wrap items-center justify-between gap-4 rounded-xl border border-surface-container-highest bg-white p-2">
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach ($roles as $role)
                        <a href="{{ route('admin.permissions.index', ['role' => $role]) }}" class="rounded-lg px-4 py-2 text-sm font-bold transition {{ $role === $selectedRole ? 'border border-secondary-fixed/30 bg-primary-container text-white shadow-sm' : 'text-slate-600 hover:bg-surface-container-low hover:text-primary' }}">
                            @if ($role === $selectedRole)
                                <span class="mr-2 inline-block h-2 w-2 rounded-full bg-secondary-fixed"></span>
                            @endif
                            {{ $roleLabels[$role] }}
                        </a>
                    @endforeach
                </div>
                <div class="flex items-center gap-3 rounded-lg border border-outline-variant/30 bg-surface-container-low px-3 py-1.5 text-sm font-medium text-slate-600">
                    <span class="flex items-center gap-1 font-bold text-secondary">
                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                        {{ $roleTiers[$selectedRole] }} Permissions
                    </span>
                    <span class="text-outline-variant">|</span>
                    <span>{{ $selectedRoleCount }} active {{ Str::plural('account', $selectedRoleCount) }}</span>
                </div>
            </section>

            <form id="permissionMatrixForm" method="POST" action="{{ route('admin.permissions.save') }}" class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
                @csrf
                <input type="hidden" name="role" value="{{ $selectedRole }}">

                <section class="space-y-4 lg:col-span-8">
                    <div class="gold-glow overflow-hidden rounded-xl border border-surface-container-highest bg-white">
                        <div class="flex items-center justify-between border-b border-surface-container-highest bg-surface-container-low/60 p-5">
                            <div>
                                <h2 class="font-serif text-2xl font-semibold">Capability Matrix</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $roleLabels[$selectedRole] }} access across core IHMP modules.</p>
                            </div>
                            <span class="rounded-full bg-surface-container-highest/80 px-3 py-1 text-xs font-bold uppercase tracking-wider text-primary">{{ count($capabilities) }} Active Modules</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[820px] border-collapse text-left">
                                <thead>
                                    <tr class="border-b border-surface-container-highest bg-surface-container text-xs font-bold uppercase tracking-wider text-[#264a67]">
                                        <th class="px-6 py-3">Capability &amp; Token</th>
                                        @foreach ($actions as $action)
                                            <th class="w-20 px-3 py-3 text-center">{{ $action }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-container-highest text-sm">
                                    @foreach ($capabilities as $capability)
                                        @php($isFocus = $capability['key'] === $focusCapability['key'])
                                        <tr class="{{ $isFocus ? 'border-l-4 border-secondary bg-secondary-container/20' : 'hover:bg-surface-container-low/60' }} transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-0.5 rounded-lg p-2 transition-colors {{ $isFocus ? 'bg-secondary text-white shadow-sm' : 'bg-surface-container text-primary group-hover:bg-primary group-hover:text-white' }}">
                                                        <span class="material-symbols-outlined text-lg" @if($isFocus) style="font-variation-settings: 'FILL' 1;" @endif>{{ $capability['icon'] }}</span>
                                                    </div>
                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="text-base font-bold text-primary">{{ $capability['module'] }}</span>
                                                            @if ($isFocus)
                                                                <span class="rounded bg-secondary/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Active Focus</span>
                                                            @endif
                                                        </div>
                                                        <div class="font-mono text-xs font-semibold text-secondary">{{ $capability['token'] }}</div>
                                                        <p class="mt-1 max-w-xl text-xs leading-5 text-slate-500">{{ $capability['description'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach ($actions as $key => $action)
                                                <td class="px-3 py-4 text-center">
                                                    <input class="h-4 w-4 cursor-pointer rounded border-outline text-primary focus:ring-secondary/40" type="checkbox" name="permissions[{{ $capability['key'] }}][{{ $key }}]" value="1" data-action="{{ $key }}" @checked((bool) data_get($saved, $capability['key'].'.'.$key, $capability['defaults'][$selectedRole][$key]))>
                                                    <span class="sr-only">{{ $action }} {{ $capability['module'] }}</span>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex flex-col justify-between gap-2 border-t border-surface-container-highest bg-surface-container-low/30 p-4 text-xs text-slate-500 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-secondary">info</span>
                                <span>Saved changes are enforced immediately on protected pages.</span>
                            </div>
                            <span class="font-mono font-semibold text-outline">Role token: {{ $selectedRole }}.{{ Str::slug($roleTiers[$selectedRole]) }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between gap-3 rounded-xl border border-outline-variant/30 bg-surface-container-low p-4 text-sm sm:flex-row sm:items-center">
                        <span class="font-semibold text-slate-600">Batch Grant Actions:</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <button class="rounded-lg border border-outline-variant/50 bg-white px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-surface-container" type="button" data-batch-action="read">Select All Read</button>
                            <button class="rounded-lg border border-outline-variant/50 bg-white px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-surface-container" type="button" data-batch-action="write">Select All Write</button>
                            <button class="rounded-lg border border-outline-variant/50 bg-white px-3 py-1.5 text-xs font-bold text-error transition hover:bg-error-container/30" type="button" data-revoke-action="override">Revoke All Overrides</button>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6 lg:col-span-4">
                    <section class="gold-glow relative overflow-hidden rounded-xl border border-surface-container-highest bg-white p-6">
                        <div class="absolute inset-x-0 top-0 h-1 bg-secondary"></div>
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Capability Inspector</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-error-container px-2.5 py-0.5 text-xs font-bold text-on-error-container">
                                <span class="h-1.5 w-1.5 rounded-full bg-error"></span>
                                {{ $focusCapability['risk'] }}
                            </span>
                        </div>
                        <h3 class="font-serif text-2xl font-semibold">{{ $focusCapability['module'] }}</h3>
                        <div class="mt-3 flex items-center justify-between rounded-lg border border-outline-variant/40 bg-surface-container-low p-2.5">
                            <code class="font-mono text-sm font-bold text-primary">token: {{ $focusCapability['token'] }}</code>
                            <button class="p-1 text-secondary transition hover:text-primary" type="button" onclick="navigator.clipboard?.writeText('{{ $focusCapability['token'] }}')" title="Copy token key">
                                <span class="material-symbols-outlined text-base">content_copy</span>
                            </button>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-600">{{ $focusCapability['description'] }} High-risk financial changes should be reviewed through the audit log.</p>
                        <div class="mt-6 grid grid-cols-2 gap-4 border-t border-surface-container-highest pt-5">
                            <div>
                                <span class="text-xs font-bold uppercase text-slate-500">Active Target Role</span>
                                <p class="mt-1 font-bold text-primary">{{ $roleLabels[$selectedRole] }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold uppercase text-slate-500">Active Users Bound</span>
                                <p class="mt-1 font-serif text-2xl font-bold text-primary">{{ $selectedRoleCount }}</p>
                            </div>
                        </div>
                        <div class="mt-6 space-y-2.5 border-t border-surface-container-highest pt-5">
                            <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-outline-variant/60 bg-white px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-surface-container" type="button" onclick="alert('Access simulation uses the selected role and current matrix settings.')">
                                <span class="material-symbols-outlined text-base text-secondary">terminal</span>
                                Simulate Role Access
                            </button>
                            <button class="flex w-full items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-bold text-error transition hover:bg-error-container/30" type="button" data-revoke-action="override">
                                <span class="material-symbols-outlined text-base">lock_reset</span>
                                Revoke Overrides for {{ $roleTiers[$selectedRole] }}
                            </button>
                        </div>
                    </section>

                    <section class="gold-glow rounded-xl border border-surface-container-highest bg-white p-6">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 rounded-lg bg-secondary-container/40 p-2.5 text-secondary">
                                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">security</span>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-primary">Least-Privilege Control</h4>
                                <p class="mt-1.5 text-sm leading-6 text-slate-600">Only administrators can modify this matrix. Role access remains strictly segregated from front-desk, cashier, and housekeeping workflows.</p>
                            </div>
                        </div>
                        <div class="mt-5 space-y-1 rounded-lg border-l-2 border-secondary bg-surface-container-low p-3.5">
                            <div class="text-sm font-bold text-primary">Dual-Authorization Rule</div>
                            <div class="text-xs leading-5 text-slate-600">Modifications to financial tokens can be audited and escalated through administrator approval records.</div>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-sm font-semibold text-secondary">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">verified</span>
                                Admin Controlled
                            </span>
                            <a class="underline transition hover:text-primary" href="{{ route('admin.audit-logs.index') }}">View Audit Log</a>
                        </div>
                    </section>

                    <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/20 bg-primary p-5 text-white shadow-lg">
                        <div class="relative z-10 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-secondary-fixed">Command Scope</span>
                                <h5 class="mt-1 text-lg font-bold">{{ $roleTiers[$selectedRole] }} Access</h5>
                                <p class="mt-1 text-xs leading-5 text-[#b4c8e5]">{{ $roleDepartments[$selectedRole] }} permission profile for {{ $roleLabels[$selectedRole] }}.</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-secondary-fixed/40 bg-primary-container text-secondary-fixed">
                                <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
                            </div>
                        </div>
                    </section>
                </aside>
            </form>
        </main>
    </div>

    <script>
        document.querySelectorAll('[data-batch-action]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll(`input[data-action="${button.dataset.batchAction}"]`).forEach((input) => {
                    input.checked = true;
                });
            });
        });

        document.querySelectorAll('[data-revoke-action]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll(`input[data-action="${button.dataset.revokeAction}"]`).forEach((input) => {
                    input.checked = false;
                });
            });
        });
    </script>
</body>
</html>
