@php
    $currentUser = auth()->user();
    $roleMeta = [
        'admin' => ['label' => 'System Admin', 'tier' => 'Tier 1', 'department' => 'IT Infrastructure', 'terminal' => 'Primary Host', 'mfa' => 'Hardware Key', 'icon' => 'admin_panel_settings', 'badge' => 'bg-primary/10 text-primary'],
        'receptionist' => ['label' => 'Front Desk', 'tier' => 'Tier 2', 'department' => 'Front Office', 'terminal' => 'Front Desk Computer', 'mfa' => 'Security App', 'icon' => 'concierge', 'badge' => 'bg-secondary-container/50 text-secondary'],
        'cashier' => ['label' => 'Cashier', 'tier' => 'Tier 2', 'department' => 'Finance & Billing', 'terminal' => 'Cashier Computer', 'mfa' => 'Cashier PIN + Code', 'icon' => 'payments', 'badge' => 'bg-secondary-container/50 text-secondary'],
        'housekeeping' => ['label' => 'Housekeeping', 'tier' => 'Tier 4', 'department' => 'Rooms & Facilities', 'terminal' => 'Mobile Tablet', 'mfa' => 'SMS OTP', 'icon' => 'cleaning_services', 'badge' => 'bg-surface-container text-slate-700'],
    ];
    $roleOrder = ['admin', 'receptionist', 'cashier', 'housekeeping'];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User & Role Management - Le Charme Suites IHMP</title>
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
                    <input class="w-full rounded-lg border border-outline-variant bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20" placeholder="Search staff, roles, or email..." type="text">
                </div>
                <div class="hidden items-center gap-4 text-sm font-bold lg:flex">
                    <a class="border-b-2 border-secondary pb-1 text-secondary" href="{{ route('admin.users.index') }}">Users</a>
                    <a class="text-slate-500 transition hover:text-primary" href="{{ route('admin.roles.index') }}">Roles</a>
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
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <div class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="text-secondary">User & Role Management</span>
                    </div>
                    <div class="flex flex-wrap items-baseline gap-3">
                        <h1 class="font-serif text-4xl font-semibold tracking-tight">Role-Based Access Control Console</h1>
                        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-[#5b4313]">{{ $totalUsers }} Active Personnel</span>
                    </div>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Manage access levels, authorized computers, staff roles, and activity review across the hotel system.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button class="inline-flex items-center gap-2 rounded-lg border border-secondary bg-white px-4 py-2.5 text-sm font-bold text-secondary shadow-sm transition hover:bg-secondary/10" onclick="window.print()" type="button">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Export Access List
                    </button>
                    <button id="provisionUserButton" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container" type="button">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        Provision User
                    </button>
                </div>
            </section>

            <section id="roles" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($roleOrder as $role)
                    @php($meta = $roleMeta[$role])
                    @php($count = (int) ($roleCounts[$role] ?? 0))
                    <div class="folio-shadow rounded-xl border border-surface-container-highest bg-white p-5">
                        <div class="flex items-start justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container text-secondary">
                                <span class="material-symbols-outlined">{{ $meta['icon'] }}</span>
                            </span>
                            <span class="rounded-full bg-surface-container-low px-2 py-0.5 text-[11px] font-bold text-slate-500">{{ $meta['tier'] }}</span>
                        </div>
                        <p class="mt-4 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $meta['label'] }}</p>
                        <p class="mt-1 font-serif text-3xl font-bold">{{ $count }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $meta['department'] }}</p>
                    </div>
                @endforeach
            </section>

            @if ($pendingUsers->isNotEmpty())
                <section id="pending-approvals" class="folio-shadow overflow-hidden rounded-xl border border-secondary/40 bg-white">
                    <div class="flex flex-col justify-between gap-2 border-b border-surface-container-highest p-5 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="font-serif text-2xl font-semibold">Pending Account Approvals</h2>
                            <p class="mt-1 text-sm text-slate-500">Self-registered staff accounts cannot sign in until an administrator approves them.</p>
                        </div>
                        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-secondary">{{ $pendingUsers->count() }} waiting</span>
                    </div>
                    <ul class="divide-y divide-surface-container-highest/60 text-sm">
                        @foreach ($pendingUsers as $pendingUser)
                            <li class="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-bold text-primary">{{ $pendingUser->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $pendingUser->email }} - requested {{ $roleMeta[$pendingUser->role]['label'] ?? ucfirst($pendingUser->role) }} access {{ $pendingUser->created_at?->diffForHumans() }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.users.approve', $pendingUser) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white hover:opacity-90"><span class="material-symbols-outlined text-sm">how_to_reg</span>Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $pendingUser) }}" onsubmit="return confirm('Reject and delete this account request?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-outline-variant px-3 py-2 text-xs font-semibold text-error hover:bg-red-50"><span class="material-symbols-outlined text-sm">person_remove</span>Reject</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <section id="users" class="folio-shadow overflow-hidden rounded-xl border border-surface-container-highest bg-white xl:col-span-8">
                    <div class="flex flex-col justify-between gap-3 border-b border-surface-container-highest p-5 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="font-serif text-2xl font-semibold">Active Personnel Directory</h2>
                            <p class="mt-1 text-sm text-slate-500">Registered user accounts and current role assignments.</p>
                        </div>
                        <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-bold text-secondary">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-left">
                            <thead>
                                <tr class="border-b border-surface-container-highest bg-surface-container text-[11px] font-bold uppercase tracking-wider text-primary">
                                    <th class="px-4 py-3">User Details</th>
                                    <th class="px-4 py-3">Role & Tier</th>
                                    <th class="px-4 py-3">Department</th>
                                    <th class="px-4 py-3">Assigned Computer / Shift</th>
                                    <th class="px-4 py-3">Security & MFA</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-container-highest/60 text-sm">
                                @forelse ($users as $employee)
                                    @php($meta = $roleMeta[$employee->role] ?? ['label' => ucfirst($employee->role), 'tier' => 'Staff', 'department' => 'General Operations', 'terminal' => 'Assigned Computer', 'mfa' => 'Password Login', 'icon' => 'badge', 'badge' => 'bg-surface-container text-slate-700'])
                                    <tr @class(['transition hover:bg-surface-container-low', 'bg-secondary-container/10' => $employee->is($currentUser)])>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white ring-2 ring-secondary/20">
                                                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-1.5 font-bold text-primary">
                                                        {{ $employee->name }}
                                                        @if ($employee->is($currentUser))
                                                            <span class="material-symbols-outlined text-sm text-secondary" title="Active console operator">stars</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-slate-500">{{ $employee->email }} - <span class="font-mono">EMP-{{ str_pad((string) $employee->id, 3, '0', STR_PAD_LEFT) }}</span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="{{ $meta['badge'] }} inline-flex rounded px-2 py-0.5 text-xs font-bold">{{ $meta['label'] }} ({{ $meta['tier'] }})</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $meta['department'] }}</td>
                                        <td class="px-4 py-3"><span class="rounded bg-surface-container px-2 py-0.5 text-xs font-semibold">{{ $meta['terminal'] }}</span></td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5 text-secondary">
                                                <span class="material-symbols-outlined text-sm">key</span>
                                                <span class="text-xs font-semibold">{{ $meta['mfa'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <button
                                                    class="rounded p-1.5 text-slate-500 transition hover:bg-surface-container hover:text-primary"
                                                    title="Update account"
                                                    type="button"
                                                    data-user-edit
                                                    data-id="{{ $employee->id }}"
                                                    data-name="{{ $employee->name }}"
                                                    data-email="{{ $employee->email }}"
                                                    data-role="{{ $employee->role }}"
                                                ><span class="material-symbols-outlined text-base">manage_accounts</span></button>
                                                <button
                                                    @disabled($employee->is($currentUser))
                                                    @class([
                                                        'rounded p-1.5 transition',
                                                        'text-slate-300 cursor-not-allowed' => $employee->is($currentUser),
                                                        'text-slate-500 hover:bg-red-50 hover:text-error' => ! $employee->is($currentUser),
                                                    ])
                                                    title="{{ $employee->is($currentUser) ? 'You cannot delete your active account' : 'Delete account' }}"
                                                    type="button"
                                                    data-user-delete
                                                    data-id="{{ $employee->id }}"
                                                    data-name="{{ $employee->name }}"
                                                    data-email="{{ $employee->email }}"
                                                ><span class="material-symbols-outlined text-base">delete</span></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center text-sm font-semibold text-slate-500">No registered users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-surface-container-highest bg-surface-container-low/30 px-4 py-3 text-sm text-slate-500">
                        {{ $users->links() }}
                    </div>
                </section>

                <section id="permissions" class="folio-shadow flex flex-col justify-between rounded-xl border border-secondary/40 bg-white p-5 xl:col-span-4">
                    <div>
                        <div class="mb-4 flex items-center justify-between border-b border-secondary/20 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">tune</span>
                                <div>
                                    <h3 class="font-bold">Permissions Table Inspector</h3>
                                    <p class="text-xs font-bold text-secondary">Front Desk Tier - Configured</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-secondary-container/40 px-2 py-0.5 text-xs font-bold text-secondary">Profile: FD-02</span>
                        </div>

                        <p class="mb-4 text-sm leading-6 text-slate-600">Inspect granular authorities for each role tier. Changes are modeled here and can be connected to persisted permission tables later.</p>

                        <div class="space-y-3.5">
                            @foreach ([
                                ['edit_calendar', 'Reservation Creation & Modification', 'Permits room blocking, VIP tags, and guest bill initiation.', true, false],
                                ['percent', 'Room Rate Discount Override', 'Maximum authority cap: 15% without manager signoff.', true, false],
                                ['nfc', 'Keycard Encoder Interface', 'Permits writing guest keys and front entrance passes.', true, false],
                                ['price_change', 'Guest Bill Zero-Balance / Refund', 'Restricted to cashier or executive approval.', false, true],
                                ['fact_check', 'Housekeeping Room Preparation Sign-off', 'Access to transition room status to inspected ready.', true, false],
                                ['download_for_offline', 'Activity Log Export', 'Exports raw transaction and staff session trails.', false, false],
                            ] as [$icon, $label, $description, $enabled, $restricted])
                                <div @class(['flex items-start justify-between gap-3 rounded-lg border p-3', 'border-error/20 bg-red-50' => $restricted, 'border-surface-container-highest bg-surface-container-low/40' => ! $restricted])>
                                    <div>
                                        <div @class(['flex items-center gap-1.5 text-sm font-bold', 'text-error' => $restricted, 'text-primary' => ! $restricted])>
                                            <span class="material-symbols-outlined text-base text-secondary">{{ $icon }}</span>
                                            <span>{{ $label }}</span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $description }}</p>
                                    </div>
                                    <label class="relative mt-0.5 inline-flex shrink-0 cursor-pointer items-center">
                                        <input type="checkbox" class="peer sr-only" @checked($enabled)>
                                        <div class="h-5 w-9 rounded-full bg-outline-variant/60 after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between border-t border-surface-container-highest pt-4">
                        <button class="text-xs font-bold text-slate-500 transition hover:text-error" type="button">Reset to Standard</button>
                        <div class="flex items-center gap-2">
                            <button class="rounded-lg border border-secondary px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-secondary/10" type="button">Cancel</button>
                            <button class="rounded-lg bg-secondary px-4 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#5b4313]" type="button">Save Overrides</button>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div id="provisionUserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 p-6">
        <div class="w-full max-w-2xl rounded-xl border border-surface-container-highest bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-surface-container-highest p-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Account Provisioning</p>
                    <h2 class="font-serif text-2xl font-bold">Provision New User</h2>
                    <p class="mt-1 text-sm text-slate-600">Create a system account and assign its operational role.</p>
                </div>
                <button class="rounded-lg border border-surface-container-highest p-2 text-slate-500 transition hover:bg-surface-container-low hover:text-primary" type="button" data-close-user-modal>
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 p-5">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Full Name</span>
                        <input name="name" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Work Email Address</span>
                        <input name="email" type="email" autocomplete="email" placeholder="sample@lecharmehotel.com" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                        <span class="mt-1 block text-xs text-slate-500">Must end in @lecharmehotel.com</span>
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Role Access</span>
                        <select name="role" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                            @foreach ($roleMeta as $role => $meta)
                                <option value="{{ $role }}">{{ $meta['label'] }} - {{ $meta['department'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Temporary Password</span>
                        <input name="password" type="password" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Confirm Password</span>
                        <input name="password_confirmation" type="password" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                    </label>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-surface-container-highest pt-4">
                    <button class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-bold text-primary transition hover:bg-surface-container" type="button" data-close-user-modal>Cancel</button>
                    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white transition hover:bg-primary-container" type="submit">Create User Account</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editUserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 p-6">
        <div class="w-full max-w-2xl rounded-xl border border-surface-container-highest bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-surface-container-highest p-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Account Control</p>
                    <h2 class="font-serif text-2xl font-bold">Update User Account</h2>
                    <p class="mt-1 text-sm text-slate-600">Change account identity, role access, or set a new password.</p>
                </div>
                <button class="rounded-lg border border-surface-container-highest p-2 text-slate-500 transition hover:bg-surface-container-low hover:text-primary" type="button" data-close-user-modal>
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
            <form id="editUserForm" method="POST" action="" class="space-y-5 p-5">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Full Name</span>
                        <input id="editUserName" name="name" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Work Email Address</span>
                        <input id="editUserEmail" name="email" type="email" autocomplete="email" placeholder="sample@lecharmehotel.com" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                        <span class="mt-1 block text-xs text-slate-500">Must end in @lecharmehotel.com</span>
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Role Access</span>
                        <select id="editUserRole" name="role" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                            @foreach ($roleMeta as $role => $meta)
                                <option value="{{ $role }}">{{ $meta['label'] }} - {{ $meta['department'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">New Password</span>
                        <input name="password" type="password" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Leave blank to keep current password">
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Confirm Password</span>
                        <input name="password_confirmation" type="password" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Repeat new password">
                    </label>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-surface-container-highest pt-4">
                    <button class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-bold text-primary transition hover:bg-surface-container" type="button" data-close-user-modal>Cancel</button>
                    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white transition hover:bg-primary-container" type="submit">Save Account Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteUserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 p-6">
        <div class="w-full max-w-lg rounded-xl border border-red-200 bg-white shadow-2xl">
            <div class="border-b border-red-100 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-error">Delete Account</p>
                <h2 class="mt-1 font-serif text-2xl font-bold">Remove System User</h2>
                <p id="deleteUserText" class="mt-2 text-sm text-slate-600"></p>
            </div>
            <form id="deleteUserForm" method="POST" action="" class="space-y-4 p-5">
                @csrf
                @method('DELETE')
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    This permanently removes the user account from the system. This cannot be undone.
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-surface-container-highest pt-4">
                    <button class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-bold text-primary transition hover:bg-surface-container" type="button" data-close-user-modal>Cancel</button>
                    <button class="rounded-lg bg-error px-4 py-2 text-sm font-bold text-white transition hover:bg-red-800" type="submit">Delete Account</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const updateUrlTemplate = @json(route('admin.users.update', ['user' => '__USER__']));
            const deleteUrlTemplate = @json(route('admin.users.destroy', ['user' => '__USER__']));
            const provisionModal = document.getElementById('provisionUserModal');
            const editModal = document.getElementById('editUserModal');
            const deleteModal = document.getElementById('deleteUserModal');
            const editForm = document.getElementById('editUserForm');
            const deleteForm = document.getElementById('deleteUserForm');
            const editName = document.getElementById('editUserName');
            const editEmail = document.getElementById('editUserEmail');
            const editRole = document.getElementById('editUserRole');
            const deleteText = document.getElementById('deleteUserText');

            const openModal = (modal) => {
                modal?.classList.remove('hidden');
                modal?.classList.add('flex');
            };

            const closeModals = () => {
                [provisionModal, editModal, deleteModal].forEach((modal) => {
                    modal?.classList.add('hidden');
                    modal?.classList.remove('flex');
                });
            };

            document.getElementById('provisionUserButton')?.addEventListener('click', () => {
                openModal(provisionModal);
                provisionModal?.querySelector('input[name="name"]')?.focus();
            });

            document.querySelectorAll('[data-user-edit]').forEach((button) => {
                button.addEventListener('click', () => {
                    editForm.action = updateUrlTemplate.replace('__USER__', button.dataset.id);
                    editName.value = button.dataset.name || '';
                    editEmail.value = button.dataset.email || '';
                    editRole.value = button.dataset.role || 'receptionist';
                    editForm.querySelectorAll('input[type="password"]').forEach((input) => input.value = '');
                    openModal(editModal);
                    editName.focus();
                });
            });

            document.querySelectorAll('[data-user-delete]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.disabled) {
                        return;
                    }

                    deleteForm.action = deleteUrlTemplate.replace('__USER__', button.dataset.id);
                    deleteText.textContent = `Delete ${button.dataset.name} (${button.dataset.email}) from the account directory?`;
                    openModal(deleteModal);
                });
            });

            document.querySelectorAll('[data-close-user-modal]').forEach((button) => {
                button.addEventListener('click', closeModals);
            });

            [provisionModal, editModal, deleteModal].forEach((modal) => {
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModals();
                    }
                });
            });
        });
    </script>
</body>
</html>
