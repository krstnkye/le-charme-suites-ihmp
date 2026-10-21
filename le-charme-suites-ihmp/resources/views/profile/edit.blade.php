@php
    $role = $user->role;
    $isAdmin = $role === 'admin';
    $roleLabel = \Illuminate\Support\Str::headline(str_replace('_', ' ', $role));
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'U';
    $employeeId = 'EMP-' . str_pad((string) $user->id, 3, '0', STR_PAD_LEFT);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isAdmin ? 'My Profile & Security Login Details' : 'Front Desk Agent Profile & Shift Login Details' }} - Le Charmé Suites IHMP</title>
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
                        'secondary-fixed-dim': '#e6c185',
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

        .hotel-card {
            background: #fdfcfa;
            border: 1px solid #ede8df;
            box-shadow: 0 4px 16px -2px rgba(23, 43, 66, .05), 0 1px 3px rgba(197, 163, 106, .08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @if ($isAdmin)
        @include('admin.partials.sidebar')

        <div class="min-h-screen pl-72">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
                <div class="flex w-full max-w-md items-center gap-3">
                    <span class="material-symbols-outlined text-outline">search</span>
                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm outline-none transition focus:border-secondary focus:ring-1 focus:ring-secondary/20" placeholder="Search activity logs, signed-in devices, or IP records..." type="search">
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden items-center gap-2 rounded-full border border-outline-variant/40 bg-surface-container-high px-3 py-1 text-xs font-semibold text-slate-600 xl:inline-flex">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-secondary"></span>
                        Subic Bay Central - HSM L4 Enforced
                    </span>
                    <div class="text-right">
                        <p class="text-xs font-bold">{{ $user->name }}</p>
                        <p class="text-[11px] text-slate-500">{{ $roleLabel }} Access</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-secondary-fixed bg-primary text-sm font-bold text-secondary-fixed">{{ $initials }}</span>
                </div>
            </header>

            <main class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-8">
                @if (session('status'))
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span>System Management</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="font-bold text-secondary">My Profile &amp; Security Login Details</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs text-outline">Session Hash: {{ substr(sha1((string) $user->id), 0, 4) }}...{{ substr(sha1($user->email), -3) }}</span>
                    </div>
                </section>

                <section class="hotel-card overflow-hidden rounded-xl">
                    <div class="h-1 bg-gradient-to-r from-secondary-fixed via-secondary to-primary-container"></div>
                    <div class="flex flex-col justify-between gap-6 p-6 lg:flex-row lg:items-center lg:p-8">
                        <div class="flex items-start gap-6">
                            <div class="relative">
                                <div class="flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-secondary-fixed bg-primary-container font-serif text-2xl font-bold text-secondary-fixed shadow-md">{{ $initials }}</div>
                                <span class="absolute -bottom-2 -right-1 rounded-md bg-secondary px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-sm">Root</span>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="font-serif text-4xl font-semibold tracking-tight text-primary">{{ $user->name }}</h1>
                                    <span class="rounded-full border border-secondary/30 bg-secondary-fixed/40 px-2.5 py-0.5 text-xs font-bold text-secondary">Tier 1 System Admin - Root Level 5</span>
                                </div>
                                <p class="mt-2 text-sm font-medium text-slate-600">Administrator account for Le Charme Suites IHMP command operations.</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded bg-primary/10 px-2 py-1 text-xs font-bold text-primary"><span class="material-symbols-outlined text-sm">fingerprint</span>Admin Identity Verified</span>
                                    <span class="inline-flex items-center gap-1 rounded border border-secondary-container bg-secondary-container/40 px-2 py-1 text-xs font-bold text-secondary"><span class="material-symbols-outlined text-sm">verified_user</span>SBMA Access</span>
                                    <span class="inline-flex items-center gap-1 rounded bg-surface-container-high px-2 py-1 text-xs font-bold text-slate-600"><span class="material-symbols-outlined text-sm">key</span>Security Token Bound</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-full min-w-[280px] rounded-xl border border-outline-variant/50 bg-surface-container-low p-4 lg:w-80">
                            <div class="flex items-center justify-between border-b border-surface-container-highest pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary">token</span>
                                    <span class="text-xs font-bold uppercase tracking-wider">HSM Certificate Status</span>
                                </div>
                                <span class="rounded bg-secondary-fixed px-2 py-0.5 text-[10px] font-bold uppercase text-primary">Active</span>
                            </div>
                            <dl class="mt-3 space-y-2 text-xs text-slate-600">
                                <div class="flex justify-between gap-3"><dt>Hardware Level</dt><dd class="font-bold text-primary">FIPS 140-2 Level 4</dd></div>
                                <div class="flex justify-between gap-3"><dt>Access</dt><dd class="font-bold text-primary">Subic Bay Freeport Root CA</dd></div>
                                <div class="flex justify-between gap-3"><dt>Assigned Computer</dt><dd class="font-bold text-primary">Master Station #01</dd></div>
                            </dl>
                            <div class="mt-3">
                                <div class="h-1.5 rounded-full bg-surface-container-highest"><div class="h-1.5 rounded-full bg-secondary" style="width: 100%"></div></div>
                                <p class="mt-1 text-right text-[10px] text-outline">Secure connection active</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-4 border-t border-surface-container-highest bg-surface-container-low px-6 py-3 text-sm text-slate-600 sm:grid-cols-3 lg:px-8">
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">mail</span><strong class="text-primary">Email:</strong> {{ $user->email }}</p>
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">badge</span><strong class="text-primary">Employee ID:</strong> {{ $employeeId }}</p>
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">schedule</span><strong class="text-primary">Last sync:</strong> {{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p>
                    </div>
                </section>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6 lg:col-span-7">
                        @include('profile.partials.photo-field')
                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-secondary">badge</span>
                                    <h2 class="font-serif text-2xl font-semibold">Official Personnel &amp; Designation</h2>
                                </div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider text-outline"><span class="material-symbols-outlined text-sm">lock</span>Record Sealed</span>
                            </div>
                            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="space-y-1 sm:col-span-2">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Full Legal Name</span>
                                    <input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-sm text-primary outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20">
                                    <span class="text-[10px] text-outline">This changes the display name shown across the IHMP portal.</span>
                                </label>
                                <label class="space-y-1">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Employee Identifier</span>
                                    <div class="flex items-center justify-between rounded-lg border border-surface-container-highest bg-surface-container-low px-3 py-2.5 font-mono text-sm text-primary">
                                        <span>{{ $employeeId }} [Root]</span>
                                        <span class="material-symbols-outlined text-base text-secondary">verified</span>
                                    </div>
                                </label>
                                <label class="space-y-1">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Role Assignment</span>
                                    <div class="rounded-lg border border-surface-container-highest bg-surface-container-low px-3 py-2.5 text-sm font-bold text-secondary">{{ $roleLabel }} - Tier 1</div>
                                </label>
                                <label class="space-y-1 sm:col-span-2">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Primary Work Email</span>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-sm text-primary outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20">
                                </label>
                                <div class="space-y-1 sm:col-span-2">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Department &amp; Operational Unit</span>
                                    <div class="rounded-lg border border-surface-container-highest bg-surface-container-low px-3 py-2.5 text-sm text-primary">Information Technology &amp; Administrative Management Division - Subic Bay Office</div>
                                </div>
                            </div>
                        </section>

                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex flex-col justify-between gap-3 border-b border-surface-container-highest pb-3 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-secondary">password</span>
                                    <div>
                                        <h2 class="font-serif text-2xl font-semibold">Master Secret &amp; Password Lifecycle</h2>
                                        <p class="text-xs text-slate-500">Leave the password fields blank to keep your current password.</p>
                                    </div>
                                </div>
                                <span class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white">90-Day Rotation Policy</span>
                            </div>
                            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="space-y-1 sm:col-span-2">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Current Password</span>
                                    <input type="password" name="current_password" autocomplete="current-password" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20" placeholder="Required to change your email or password">
                                </label>
                                <label class="space-y-1">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">New Password</span>
                                    <input type="password" name="password" autocomplete="new-password" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20" placeholder="Leave blank to keep current">
                                </label>
                                <label class="space-y-1">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Confirm Password</span>
                                    <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20">
                                </label>
                            </div>
                        </section>

                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-secondary">key_vertical</span>
                                    <div>
                                        <h2 class="font-serif text-2xl font-semibold">Two-Step Verification &amp; Security Keys</h2>
                                        <p class="text-xs text-slate-500">Login details protected for the administrator account.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ([
                                    ['icon' => 'usb', 'name' => 'YubiKey 5C NFC (Primary)', 'meta' => 'Serial #YK-' . str_pad((string) $user->id, 8, '0', STR_PAD_LEFT) . ' - Physical presence verification active', 'status' => 'Active Primary', 'active' => true],
                                    ['icon' => 'pin', 'name' => 'Security Code Device', 'meta' => '60s time-based sync - Stored on the assigned computer', 'status' => 'Standby Key', 'active' => false],
                                    ['icon' => 'fingerprint', 'name' => 'Device Fingerprint Login', 'meta' => 'Bound to workstation master profile - Local device check', 'status' => 'Enrolled', 'active' => false],
                                ] as $token)
                                    <div class="flex flex-col justify-between gap-3 rounded-xl border {{ $token['active'] ? 'border-secondary/40 bg-surface-container-low/60' : 'border-surface-container-highest bg-white' }} p-3.5 sm:flex-row sm:items-center">
                                        <div class="flex items-center gap-3.5">
                                            <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ $token['active'] ? 'bg-primary-container text-secondary-fixed' : 'bg-surface-container-high text-primary' }}"><span class="material-symbols-outlined text-2xl">{{ $token['icon'] }}</span></span>
                                            <div>
                                                <h3 class="font-bold text-primary">{{ $token['name'] }}</h3>
                                                <p class="text-xs text-slate-500">{{ $token['meta'] }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold {{ $token['active'] ? 'text-secondary' : 'text-outline' }}">{{ $token['status'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-6 lg:col-span-5">
                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary">devices</span>
                                    <h2 class="text-lg font-bold">Approved Signed-in Devices</h2>
                                </div>
                                <span class="rounded-full bg-secondary-fixed px-2 py-0.5 text-[11px] font-bold text-primary">3 Devices</span>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ([
                                    ['icon' => 'computer', 'title' => 'Master Station #01', 'meta' => 'Chrome - Windows workstation', 'foot' => 'Current browser session', 'state' => 'Active Now', 'current' => true],
                                    ['icon' => 'tablet_mac', 'title' => 'Backup Inspection Tablet', 'meta' => 'Admin-Air - Secure Wi-Fi', 'foot' => 'Subic Bay staff network', 'state' => 'Active 45m ago', 'current' => false],
                                    ['icon' => 'point_of_sale', 'title' => 'Front Desk Emergency Station', 'meta' => 'Station #02 - Main Wing Lobby', 'foot' => 'Restricted staff computer', 'state' => 'Idle 4 hrs ago', 'current' => false],
                                ] as $terminal)
                                    <div class="rounded-xl border {{ $terminal['current'] ? 'border-2 border-secondary-fixed bg-surface-container-low/40' : 'border-surface-container-highest bg-white' }} p-3.5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-2.5">
                                                <span class="material-symbols-outlined text-xl text-secondary">{{ $terminal['icon'] }}</span>
                                                <div>
                                                    <h3 class="text-sm font-bold text-primary">{{ $terminal['title'] }}</h3>
                                                    <p class="text-xs text-outline">{{ $terminal['meta'] }}</p>
                                                </div>
                                            </div>
                                            <span class="text-xs font-bold {{ $terminal['current'] ? 'rounded bg-secondary-fixed px-2 py-0.5 text-primary' : 'text-error' }}">{{ $terminal['current'] ? 'This Station' : 'Revoke' }}</span>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between border-t border-surface-container-highest pt-2 font-mono text-[11px] text-slate-500">
                                            <span>{{ $terminal['foot'] }}</span>
                                            <span>{{ $terminal['state'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary">verified</span>
                                    <h2 class="text-lg font-bold">Root Privilege Scope</h2>
                                </div>
                                <span class="text-xs font-bold text-secondary">Clearance Level 5</span>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-slate-600">The following operational modules are available under administrator access.</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach (['All Module Management', 'Activity Logs', 'Database Reports', 'Room Management', 'Guest Bill Oversight', 'System Settings'] as $grant)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-outline-variant/30 bg-surface-container-high px-2.5 py-1 text-xs font-bold text-primary"><span class="material-symbols-outlined text-sm text-secondary">check_circle</span>{{ $grant }}</span>
                                @endforeach
                            </div>
                        </section>

                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary">notifications_active</span>
                                    <h2 class="text-lg font-bold">Priority Security Triggers</h2>
                                </div>
                                <span class="text-xs font-semibold text-outline">Push + SMS</span>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ([
                                    ['title' => 'Station Lockouts & Intrusion', 'meta' => '3 failed root challenges anywhere in cluster', 'checked' => true],
                                    ['title' => 'Manager Approval for Differences Above PHP 10,000', 'meta' => 'Immediate Front Desk notification', 'checked' => true],
                                    ['title' => 'End-of-Day Payment Differences', 'meta' => 'Daily payment difference alerts', 'checked' => true],
                                    ['title' => 'Daily System Health Summary', 'meta' => 'Digest email delivered at 06:00', 'checked' => false],
                                ] as $trigger)
                                    <div class="flex items-center justify-between gap-4 border-t border-surface-container-highest/60 pt-3 first:border-t-0 first:pt-0">
                                        <div>
                                            <p class="text-sm font-bold text-primary">{{ $trigger['title'] }}</p>
                                            <p class="text-[11px] text-outline">{{ $trigger['meta'] }}</p>
                                        </div>
                                        <label class="relative inline-flex cursor-pointer items-center">
                                            <input class="peer sr-only" type="checkbox" @checked($trigger['checked'])>
                                            <span class="h-6 w-11 rounded-full bg-surface-container-highest after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-secondary peer-checked:after:translate-x-full"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </aside>

                    <footer class="hotel-card flex flex-col items-center justify-between gap-4 rounded-xl border-secondary/30 p-5 lg:col-span-12 md:flex-row">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary-fixed/50 text-secondary"><span class="material-symbols-outlined">enhanced_encryption</span></span>
                            <div>
                                <p class="text-sm font-bold text-primary">Current Sign-in Is Secure</p>
                                <p class="font-mono text-xs text-outline">Hardware Key Hash: {{ substr(sha1($user->email), 0, 8) }}-{{ substr(sha1((string) $user->id), 0, 4) }}-{{ substr(sha1($user->name), -12) }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-3">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-primary-container px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-primary" type="submit"><span class="material-symbols-outlined text-base text-secondary-fixed">save</span>Save Profile Preferences</button>
                        </div>
                    </footer>
                </form>

                <p class="pb-6 text-center text-xs text-outline">Le Charmé Suites Subic Integrated Hotel Management Portal (IHMP) - SEC Registered - SBMA Freeport Zone - Secure Account Protection Active</p>
            </main>
        </div>
    @else
        @include('partials.front-desk-sidebar', ['active' => 'profile'])

        <div class="min-h-screen bg-[#fcf9f3] lg:ml-64">
            <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-[#e5e2dc] bg-white px-5 py-3 shadow-sm sm:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="material-symbols-outlined text-xl text-[#755a28]">desk</span>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-slate-500">Front Desk Console / Agent Profile</p>
                        <p class="truncate font-serif text-lg font-semibold text-[#01162d]">Shift Profile &amp; Access</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden rounded-lg border border-[#e5e2dc] bg-[#f6f3ed] px-3 py-1.5 text-right sm:block">
                        <p id="profile-clock" class="text-xs font-bold text-[#01162d]" data-timezone="{{ config('app.timezone') }}">{{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-slate-500">Philippine Standard Time</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#755a28] bg-[#01162d] text-xs font-bold text-[#ffdeaa]">{{ $initials }}</span>
                </div>
            </header>

            <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-5 sm:p-8">
                @if (session('status'))
                    <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
                        <span class="material-symbols-outlined">check_circle</span>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <div class="flex items-center gap-2 font-bold"><span class="material-symbols-outlined">error</span>Please correct the highlighted account details.</div>
                        <ul class="mt-2 list-disc pl-10">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="relative overflow-hidden rounded-xl border border-[#755a28]/30 bg-[#01162d] p-6 text-white shadow-sm sm:p-8">
                    <span class="material-symbols-outlined pointer-events-none absolute -bottom-12 right-4 text-[220px] text-white opacity-[0.04]">shield</span>
                    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                        <div>
                            <span class="inline-flex items-center gap-2 rounded-full border border-[#ffdeaa]/30 bg-[#ffdeaa]/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-[#ffdeaa]"><span class="material-symbols-outlined text-sm">verified</span>Front Office Operations · Shift Login Details</span>
                            <h1 class="mt-3 font-serif text-3xl font-semibold tracking-tight sm:text-4xl">Front Desk Agent Profile &amp; Station Access</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-[#b4c8e5]">Manage your verified employee identity, work email and password, and frontline access for Le Charmé Suites hotel operations.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#ffdeaa] px-4 py-2.5 text-xs font-bold text-[#271900] shadow-sm transition hover:bg-[#e6c185]"><span class="material-symbols-outlined text-base">add</span>New Reservation</a>
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-white/15"><span class="material-symbols-outlined text-base">arrow_back</span>Front Desk Center</a>
                        </div>
                    </div>

                    <div class="relative z-10 mt-6 grid grid-cols-2 gap-3 border-t border-white/10 pt-6 sm:grid-cols-3 xl:grid-cols-5">
                        <div class="rounded-lg border border-[#ffdeaa]/15 bg-[#172b42] p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-[#ffdeaa]">Active Agent</p><p class="mt-1 truncate text-sm font-bold">{{ $user->name }}</p><span class="text-[11px] text-[#b4c8e5]">Front Office</span></div>
                        <div class="rounded-lg border border-[#ffdeaa]/15 bg-[#172b42] p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-[#ffdeaa]">Agent Badge ID</p><p class="mt-1 font-mono text-sm font-bold">LCS-FDA-{{ str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) }}</p><span class="text-[11px] text-[#b4c8e5]">{{ $employeeId }}</span></div>
                        <div class="rounded-lg border border-[#ffdeaa]/15 bg-[#172b42] p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-[#ffdeaa]">Access</p><p class="mt-1 text-sm font-bold">Front Desk</p><span class="text-[11px] text-[#b4c8e5]">Role key: {{ $role }}</span></div>
                        <div class="rounded-lg border border-[#ffdeaa]/15 bg-[#172b42] p-3.5"><p class="text-[10px] font-bold uppercase tracking-wider text-[#ffdeaa]">Account Status</p><p class="mt-1 flex items-center gap-1.5 text-sm font-bold text-emerald-300"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Active</p><span class="text-[11px] text-[#b4c8e5]">Local access enabled</span></div>
                        <div class="col-span-2 rounded-lg border border-[#ffdeaa]/15 bg-[#172b42] p-3.5 sm:col-span-1"><p class="text-[10px] font-bold uppercase tracking-wider text-[#ffdeaa]">Member Since</p><p class="mt-1 text-sm font-bold">{{ $user->created_at?->timezone(config('app.timezone'))->format('M d, Y') }}</p><span class="text-[11px] text-[#b4c8e5]">IHMP personnel record</span></div>
                    </div>
                </section>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="space-y-6 xl:col-span-7">
                        <section class="hotel-card relative overflow-hidden rounded-xl p-6">
                            <div class="absolute inset-x-0 top-0 h-1.5 bg-[#755a28]"></div>
                            <div class="flex flex-col gap-5 border-b border-[#e5e2dc] pb-5 sm:flex-row sm:items-center">
                                <div class="relative shrink-0">
                                    @if ($user->profile_photo_path)
                                        <img src="{{ route('profile.photo', ['v' => $user->updated_at?->timestamp]) }}" alt="{{ $user->name }} profile photo" class="h-24 w-24 rounded-xl border-2 border-[#755a28] object-cover shadow-md" data-profile-photo-preview>
                                        <span class="hidden h-24 w-24 items-center justify-center rounded-xl border-2 border-[#755a28] bg-[#01162d] font-serif text-3xl font-bold text-[#ffdeaa] shadow-md" data-profile-photo-placeholder>{{ $initials }}</span>
                                    @else
                                        <img src="" alt="New profile photo preview" class="hidden h-24 w-24 rounded-xl border-2 border-[#755a28] object-cover shadow-md" data-profile-photo-preview>
                                        <span class="flex h-24 w-24 items-center justify-center rounded-xl border-2 border-[#755a28] bg-[#01162d] font-serif text-3xl font-bold text-[#ffdeaa] shadow-md" data-profile-photo-placeholder>{{ $initials }}</span>
                                    @endif
                                    <span class="absolute -bottom-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-[#755a28] text-white shadow-sm"><span class="material-symbols-outlined text-base">verified</span></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div><h2 class="font-serif text-2xl font-semibold text-[#01162d]">{{ $user->name }}</h2><p class="mt-1 text-sm font-bold text-[#755a28]">Front Desk Agent · Rooms Division</p></div>
                                        <span class="rounded-full border border-[#755a28]/30 bg-[#fdd79a]/25 px-3 py-1 font-mono text-xs font-bold text-[#5b4313]">{{ $employeeId }}</span>
                                    </div>
                                    <div class="mt-4 grid gap-2 text-xs text-slate-600 sm:grid-cols-2">
                                        <span class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-[#755a28]">domain</span>Front Office Department</span>
                                        <span class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-[#755a28]">desktop_windows</span>IHMP Front Desk Station</span>
                                        <span class="flex items-center gap-2 sm:col-span-2"><span class="material-symbols-outlined text-base text-[#755a28]">mail</span><span class="truncate">{{ $user->email }}</span></span>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6">
                                @csrf
                                @method('PUT')
                                @include('profile.partials.photo-field')
                                <div class="mb-5 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#f0eee8] text-[#755a28]"><span class="material-symbols-outlined">manage_accounts</span></span><div><h3 class="font-bold text-[#01162d]">Official Personnel Details</h3><p class="text-xs text-slate-500">Update your own account record and sign-in details.</p></div></div>
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <label class="sm:col-span-2"><span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Full name</span><input name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></label>
                                    <label class="sm:col-span-2"><span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Official work email</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"><span class="mt-1 block text-[11px] text-slate-500">Use your approved @lecharmehotel.com address.</span></label>
                                    <label class="sm:col-span-2"><span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Current password</span><input type="password" name="current_password" autocomplete="current-password" class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" placeholder="Required to change your email or password"></label>
                                    <label><span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-600">New password</span><input type="password" name="password" autocomplete="new-password" class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" placeholder="Leave blank to keep current"></label>
                                    <label><span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Confirm password</span><input type="password" name="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm text-[#01162d] outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" placeholder="Repeat new password"></label>
                                </div>
                                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-[#e5e2dc] pt-5 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="flex items-center gap-2 text-xs text-slate-500"><span class="material-symbols-outlined text-base text-[#755a28]">shield_lock</span>Changes apply only to your signed-in account.</p>
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#01162d] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#172b42]"><span class="material-symbols-outlined text-base text-[#ffdeaa]">save</span>Save Profile Changes</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <aside class="space-y-6 xl:col-span-5">
                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex items-center justify-between border-b border-[#e5e2dc] pb-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#fdd79a]/35 text-[#755a28]"><span class="material-symbols-outlined">admin_panel_settings</span></span><div><h2 class="font-bold text-[#01162d]">Station Access</h2><p class="text-xs text-slate-500">Frontline access assigned to this role</p></div></div><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700">Authorized</span></div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ([
                                    ['icon' => 'calendar_month', 'title' => 'Reservations', 'detail' => 'Create and manage stays'],
                                    ['icon' => 'how_to_reg', 'title' => 'Check-in Desk', 'detail' => 'Process guest arrivals'],
                                    ['icon' => 'badge', 'title' => 'Guest Records', 'detail' => 'Maintain guest profiles'],
                                    ['icon' => 'bed', 'title' => 'Room Status', 'detail' => 'View live availability'],
                                ] as $authority)
                                    <div class="rounded-lg border border-[#e5e2dc] bg-[#f6f3ed] p-3.5"><span class="material-symbols-outlined text-xl text-[#755a28]">{{ $authority['icon'] }}</span><h3 class="mt-2 text-sm font-bold text-[#01162d]">{{ $authority['title'] }}</h3><p class="mt-0.5 text-xs text-slate-500">{{ $authority['detail'] }}</p></div>
                                @endforeach
                            </div>
                        </section>

                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex items-center gap-3 border-b border-[#e5e2dc] pb-4"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#f0eee8] text-[#755a28]"><span class="material-symbols-outlined">security</span></span><div><h2 class="font-bold text-[#01162d]">Security &amp; Account Status</h2><p class="text-xs text-slate-500">Current account protection</p></div></div>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-4 rounded-lg bg-[#f6f3ed] px-4 py-3"><dt class="flex items-center gap-2 text-slate-600"><span class="material-symbols-outlined text-lg text-[#755a28]">alternate_email</span>Work email</dt><dd class="font-bold text-emerald-700">Approved domain</dd></div>
                                <div class="flex items-center justify-between gap-4 rounded-lg bg-[#f6f3ed] px-4 py-3"><dt class="flex items-center gap-2 text-slate-600"><span class="material-symbols-outlined text-lg text-[#755a28]">password</span>Password</dt><dd class="font-bold text-[#01162d]">Protected</dd></div>
                                <div class="flex items-center justify-between gap-4 rounded-lg bg-[#f6f3ed] px-4 py-3"><dt class="flex items-center gap-2 text-slate-600"><span class="material-symbols-outlined text-lg text-[#755a28]">history</span>Last profile update</dt><dd class="text-right text-xs font-bold text-[#01162d]">{{ $user->updated_at?->timezone(config('app.timezone'))->format('M d, Y') }}</dd></div>
                            </dl>
                        </section>

                        <section class="rounded-xl border border-[#e5e2dc] bg-[#f0eee8] p-5">
                            <div class="flex items-start gap-3"><span class="material-symbols-outlined mt-0.5 text-[#755a28]">info</span><div><h2 class="font-bold text-[#01162d]">Login Detail Support</h2><p class="mt-1 text-sm leading-6 text-slate-600">Contact an administrator to change your department, role authority, or employee badge assignment.</p><a href="{{ route('messages.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#755a28] hover:underline">Open Department Messages<span class="material-symbols-outlined text-sm">arrow_forward</span></a></div></div>
                        </section>
                    </aside>
                </div>
            </main>
        </div>

        <script>
            const profileClock = document.getElementById('profile-clock');
            const profileTimezone = profileClock?.dataset.timezone || 'Asia/Manila';
            const refreshProfileClock = () => {
                if (!profileClock) {
                    return;
                }

                profileClock.textContent = new Intl.DateTimeFormat('en-US', {
                    timeZone: profileTimezone,
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                }).format(new Date()).replace(',', ' -');
            };

            refreshProfileClock();
            setInterval(refreshProfileClock, 1000);
        </script>
    @endif
    @include('profile.partials.photo-script')
</body>
</html>
