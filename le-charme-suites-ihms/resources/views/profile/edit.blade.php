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
    <title>My Profile &amp; Security Credentials - Le Charmé Suites IHMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Noto+Serif:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                    <input class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm outline-none transition focus:border-secondary focus:ring-1 focus:ring-secondary/20" placeholder="Search audit logs, terminal sessions, IP records..." type="search">
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
                        <span>System Governance</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="font-bold text-secondary">My Profile &amp; Security Credentials</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs text-outline">Session Hash: {{ substr(sha1((string) $user->id), 0, 4) }}...{{ substr(sha1($user->email), -3) }}</span>
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-outline-variant/50 bg-surface-container-high px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-surface-container-highest" type="button">
                            <span class="material-symbols-outlined text-base">cached</span>
                            Refresh Enclave
                        </button>
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
                                <p class="mt-2 text-sm font-medium text-slate-600">Administrator account for Le Charme Suites IHMS command operations.</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded bg-primary/10 px-2 py-1 text-xs font-bold text-primary"><span class="material-symbols-outlined text-sm">fingerprint</span>Admin Identity Verified</span>
                                    <span class="inline-flex items-center gap-1 rounded border border-secondary-container bg-secondary-container/40 px-2 py-1 text-xs font-bold text-secondary"><span class="material-symbols-outlined text-sm">verified_user</span>SBMA Enclave Clearance</span>
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
                                <div class="flex justify-between gap-3"><dt>Authority</dt><dd class="font-bold text-primary">Subic Bay Freeport Root CA</dd></div>
                                <div class="flex justify-between gap-3"><dt>Enclave Workstation</dt><dd class="font-bold text-primary">Master Station #01</dd></div>
                            </dl>
                            <div class="mt-3">
                                <div class="h-1.5 rounded-full bg-surface-container-highest"><div class="h-1.5 rounded-full bg-secondary" style="width: 100%"></div></div>
                                <p class="mt-1 text-right text-[10px] text-outline">Cryptographic handshake 100% solid</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-4 border-t border-surface-container-highest bg-surface-container-low px-6 py-3 text-sm text-slate-600 sm:grid-cols-3 lg:px-8">
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">mail</span><strong class="text-primary">Email:</strong> {{ $user->email }}</p>
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">badge</span><strong class="text-primary">Employee ID:</strong> {{ $employeeId }}</p>
                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">schedule</span><strong class="text-primary">Last sync:</strong> {{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p>
                    </div>
                </section>

                <form method="POST" action="{{ route('profile.update') }}" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6 lg:col-span-7">
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
                                    <span class="text-[10px] text-outline">This changes the display name shown across the IHMS portal.</span>
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
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Primary Enclave Work Email</span>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-sm text-primary outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20">
                                </label>
                                <div class="space-y-1 sm:col-span-2">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Department &amp; Operational Unit</span>
                                    <div class="rounded-lg border border-surface-container-highest bg-surface-container-low px-3 py-2.5 text-sm text-primary">Information Technology &amp; Administrative Governance Division - Subic Bay Command Enclave</div>
                                </div>
                            </div>
                        </section>

                        <section class="hotel-card rounded-xl p-6">
                            <div class="flex flex-col justify-between gap-3 border-b border-surface-container-highest pb-3 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-secondary">password</span>
                                    <div>
                                        <h2 class="font-serif text-2xl font-semibold">Master Secret &amp; Password Lifecycle</h2>
                                        <p class="text-xs text-slate-500">Leave password fields blank to keep the current credential.</p>
                                    </div>
                                </div>
                                <span class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white">90-Day Rotation Policy</span>
                            </div>
                            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                        <h2 class="font-serif text-2xl font-semibold">Hardware MFA &amp; Cryptographic Key Vault</h2>
                                        <p class="text-xs text-slate-500">Security credentials bound to the administrator enclave.</p>
                                    </div>
                                </div>
                                <button class="inline-flex items-center gap-1.5 rounded-lg bg-secondary px-3 py-1.5 text-xs font-bold text-white" type="button"><span class="material-symbols-outlined text-base">add</span>Register Key</button>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ([
                                    ['icon' => 'usb', 'name' => 'YubiKey 5C NFC (Primary)', 'meta' => 'Serial #YK-' . str_pad((string) $user->id, 8, '0', STR_PAD_LEFT) . ' - Physical presence verification active', 'status' => 'Active Primary', 'active' => true],
                                    ['icon' => 'pin', 'name' => 'RSA SecurID Token', 'meta' => '60s time-based sync - Stored in station vault', 'status' => 'Standby Key', 'active' => false],
                                    ['icon' => 'fingerprint', 'name' => 'Platform Biometric Enclave', 'meta' => 'Bound to workstation master profile - Local hardware attestation', 'status' => 'Enrolled', 'active' => false],
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
                                    <h2 class="text-lg font-bold">Active Authorized Terminals</h2>
                                </div>
                                <span class="rounded-full bg-secondary-fixed px-2 py-0.5 text-[11px] font-bold text-primary">3 Terminals</span>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ([
                                    ['icon' => 'computer', 'title' => 'Master Station #01', 'meta' => 'Chrome - Windows workstation', 'foot' => 'Current browser session', 'state' => 'Active Now', 'current' => true],
                                    ['icon' => 'tablet_mac', 'title' => 'Backup Inspection Tablet', 'meta' => 'Admin-Air - Secure Wi-Fi', 'foot' => 'Subic Bay staff network', 'state' => 'Active 45m ago', 'current' => false],
                                    ['icon' => 'point_of_sale', 'title' => 'Front Desk Emergency Station', 'meta' => 'Terminal #02 - Main Wing Lobby', 'foot' => 'Restricted override terminal', 'state' => 'Idle 4 hrs ago', 'current' => false],
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
                                            <span class="text-xs font-bold {{ $terminal['current'] ? 'rounded bg-secondary-fixed px-2 py-0.5 text-primary' : 'text-error' }}">{{ $terminal['current'] ? 'This Terminal' : 'Revoke' }}</span>
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
                                @foreach (['All Module Governance', 'Audit Logs', 'Database Reports', 'Room Management', 'Guest Bill Oversight', 'System Settings'] as $grant)
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
                                    ['title' => 'Terminal Lockouts & Intrusion', 'meta' => '3 failed root challenges anywhere in cluster', 'checked' => true],
                                    ['title' => 'Manager Override Variances Above PHP 10,000', 'meta' => 'Immediate front-desk dispatch ping', 'checked' => true],
                                    ['title' => 'Night Audit Imbalances', 'meta' => 'Daily reconciliation anomaly flags', 'checked' => true],
                                    ['title' => 'Daily Cluster Health Summary', 'meta' => 'Digest email delivered at 06:00', 'checked' => false],
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
                                <p class="text-sm font-bold text-primary">Current Session Cryptographically Bound</p>
                                <p class="font-mono text-xs text-outline">Hardware Key Hash: {{ substr(sha1($user->email), 0, 8) }}-{{ substr(sha1((string) $user->id), 0, 4) }}-{{ substr(sha1($user->name), -12) }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-3">
                            <button class="inline-flex items-center gap-2 rounded-lg border border-outline-variant/60 px-4 py-2 text-xs font-bold text-primary transition hover:bg-surface-container-high" type="button"><span class="material-symbols-outlined text-base">download</span>Audit Signature</button>
                            <button class="inline-flex items-center gap-2 rounded-lg border border-outline-variant/60 px-4 py-2 text-xs font-bold text-primary transition hover:bg-surface-container-high" type="button"><span class="material-symbols-outlined text-base">vpn_key</span>Recovery Codes</button>
                            <button class="inline-flex items-center gap-2 rounded-lg bg-primary-container px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-primary" type="submit"><span class="material-symbols-outlined text-base text-secondary-fixed">save</span>Save Profile Preferences</button>
                        </div>
                    </footer>
                </form>

                <p class="pb-6 text-center text-xs text-outline">Le Charmé Suites Subic Integrated Hotel Management System (IHMS) - SEC Registered - SBMA Freeport Zone - Cryptographic Level 4 Active</p>
            </main>
        </div>
    @else
        @if ($role === 'receptionist')
            @include('partials.front-desk-sidebar', ['active' => 'profile'])
        @else
            <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col justify-between border-r border-white/10 bg-[#01162d] px-4 py-6 text-white lg:flex">
                <div class="space-y-6">
                    <a href="{{ route('dashboard') }}" class="block rounded-lg bg-white px-3 py-2 shadow-sm">@include('partials.brand-logo', ['class' => 'mx-auto h-auto w-full max-w-[180px] object-contain'])<span class="mt-1 block text-center text-[10px] font-bold uppercase tracking-[0.15em] text-[#755a28]">Integrated Hotel Management System</span></a>
                    <nav class="space-y-1.5" aria-label="Employee Navigation">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-r-lg border-l-4 border-transparent px-3 py-3 text-sm text-[#7f93ae] hover:bg-[#172b42] hover:text-white"><span class="material-symbols-outlined">dashboard</span>Dashboard</a>
                        @if (in_array($role, ['admin', 'housekeeping'], true))<a href="{{ route('rooms.index') }}" class="flex items-center gap-3 rounded-r-lg border-l-4 border-transparent px-3 py-3 text-sm text-[#7f93ae] hover:bg-[#172b42] hover:text-white"><span class="material-symbols-outlined">bed</span>Room Status</a>@endif
                        @if (in_array($role, ['admin', 'cashier', 'owner'], true))<a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-r-lg border-l-4 border-transparent px-3 py-3 text-sm text-[#7f93ae] hover:bg-[#172b42] hover:text-white"><span class="material-symbols-outlined">query_stats</span>Reports</a>@endif
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-r-lg border-l-4 border-[#e6c185] bg-[#172b42] px-3 py-3 text-sm font-semibold text-white"><span class="material-symbols-outlined">account_circle</span>My Profile</a>
                    </nav>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="border-t border-[#172b42] pt-4">@csrf<button class="flex w-full items-center gap-3 px-3 py-2 text-sm text-[#7f93ae] hover:text-white"><span class="material-symbols-outlined">logout</span>Sign Out</button></form>
            </aside>
        @endif

        <div class="lg:ml-64">
            <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-[#e5e2dc] bg-white px-5 py-3 shadow-sm sm:px-8"><div class="flex items-center gap-3"><span class="font-serif text-lg font-semibold text-[#01162d] sm:text-xl">Le Charmé Suites IHMS</span><span class="hidden h-5 w-px bg-[#e5e2dc] sm:block"></span><span class="hidden text-xs font-semibold uppercase tracking-wider text-[#755a28] sm:block">Employee Profile</span></div><div class="flex items-center gap-3"><div class="text-right"><p class="text-xs font-bold text-[#01162d]">{{ $user->name }}</p><p class="text-[11px] text-slate-500">{{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p></div><span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#fdd79a] text-xs font-bold text-[#271900]">{{ $initials }}</span></div></header>

            <main class="mx-auto flex w-full max-w-[1200px] flex-col gap-6 bg-[#fcf9f3] p-5 sm:p-8">
                @if (session('status'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <section class="border-b border-[#e5e2dc] pb-5"><span class="text-xs font-bold uppercase tracking-wider text-[#755a28]">Account Settings</span><h1 class="font-serif mt-1 text-3xl font-semibold tracking-tight text-[#01162d] sm:text-4xl">My Profile</h1><p class="mt-2 text-sm text-slate-600">Manage your employee account details for the IHMS portal.</p></section>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <form method="POST" action="{{ route('profile.update') }}" class="hotel-card rounded-xl p-5 sm:p-7">
                        @csrf
                        @method('PUT')
                        <div class="mb-6 flex items-center gap-3 border-b border-[#e5e2dc] pb-4"><span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#01162d] text-[#ffdeaa]"><span class="material-symbols-outlined">person</span></span><div><h2 class="font-bold text-[#01162d]">Personal Information</h2><p class="text-xs text-slate-500">Only your account details can be changed here.</p></div></div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="sm:col-span-2"><span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Full name</span><input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></label>
                            <label class="sm:col-span-2"><span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Email address</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></label>
                            <label><span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">New password</span><input type="password" name="password" autocomplete="new-password" class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" placeholder="Leave blank to keep current"></label>
                            <label><span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Confirm password</span><input type="password" name="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border border-[#c4c6cd] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></label>
                        </div>
                        <div class="mt-6 flex justify-end border-t border-[#e5e2dc] pt-5"><button class="inline-flex items-center gap-2 rounded-lg bg-[#01162d] px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#172b42]"><span class="material-symbols-outlined text-base text-[#ffdeaa]">save</span>Save Changes</button></div>
                    </form>

                    <aside class="space-y-6">
                        <section class="hotel-card rounded-xl border-t-2 border-t-[#755a28] p-5"><div class="flex items-center gap-3"><span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#fdd79a] text-sm font-bold text-[#271900]">{{ $initials }}</span><div><h2 class="font-bold text-[#01162d]">{{ $user->name }}</h2><p class="text-xs text-slate-500">Employee account</p></div></div><dl class="mt-5 space-y-3 border-t border-[#e5e2dc] pt-4 text-sm"><div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Role</dt><dd class="font-bold capitalize text-[#755a28]">{{ str_replace('_', ' ', $role) }}</dd></div><div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Status</dt><dd class="flex items-center gap-1.5 font-semibold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>Active</dd></div><div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Member since</dt><dd class="font-medium text-[#01162d]">{{ $user->created_at?->timezone(config('app.timezone'))->format('M d, Y') }}</dd></div></dl></section>
                        <section class="rounded-xl border border-[#e5e2dc] bg-[#f6f3ed] p-5"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#755a28]">shield</span><h2 class="font-bold text-[#01162d]">Role Access</h2></div><p class="mt-2 text-sm leading-6 text-slate-600">Your portal access follows your assigned <strong class="capitalize">{{ str_replace('_', ' ', $role) }}</strong> role. Contact an administrator if your responsibilities change.</p></section>
                    </aside>
                </div>
            </main>
        </div>
    @endif
</body>
</html>
