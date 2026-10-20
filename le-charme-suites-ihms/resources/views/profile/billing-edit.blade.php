@php
    $role = $user->role;
    $roleLabel = \Illuminate\Support\Str::headline(str_replace('_', ' ', $role));
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'B';
    $employeeId = 'BIL-' . str_pad((string) $user->id, 3, '0', STR_PAD_LEFT);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Billing Profile &amp; Terminal Credentials - Le Charmé Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = { theme: { extend: {
            colors: { primary: '#01162d', 'primary-container': '#172b42', secondary: '#755a28', 'secondary-container': '#fdd79a', 'secondary-fixed': '#ffdeaa', surface: '#fcf9f3', 'surface-low': '#f6f3ed', 'surface-mid': '#f0eee8', line: '#e5e2dc', error: '#ba1a1a' },
            fontFamily: { sans: ['Manrope', 'sans-serif'], serif: ['Noto Serif', 'serif'] },
        } } };
    </script>
    <style>
        .material-symbols-outlined { display: inline-block; vertical-align: middle; line-height: 1; font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f0eee8; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c4c6cd; border-radius: 999px; }
        .profile-card { background: #fdfcfa; border: 1px solid #ede8df; box-shadow: 0 4px 16px -2px rgba(23, 43, 66, .05), 0 1px 3px rgba(197, 163, 106, .08); }
        @media print { aside, header, .no-print { display: none !important; } body { background: white !important; } main { padding: 0 !important; } }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    @include('partials.billing-sidebar', ['active' => 'profile'])

    <div class="flex min-h-screen flex-col lg:ml-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-5 py-3 shadow-sm backdrop-blur lg:px-8">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                    <span>POS Registry</span><span>/</span><span class="text-secondary">Billing &amp; Cashier Terminal</span>
                </div>
                <h1 class="mt-1 font-serif text-xl font-bold text-primary">Cashier Profile &amp; Terminal Credentials</h1>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <span class="hidden items-center gap-2 rounded-full border border-line bg-surface-low px-3 py-1.5 text-xs font-bold text-slate-600 md:inline-flex"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>Station POS-REG-02</span>
                <button type="button" onclick="alert('Cash drawer access has been logged for audit review.')" class="hidden items-center gap-2 rounded-lg border border-secondary/40 bg-white px-3 py-2 text-xs font-bold text-secondary shadow-sm transition hover:bg-secondary-container/30 md:flex"><span class="material-symbols-outlined text-base">point_of_sale</span>Open Cash Drawer</button>
                <button type="button" onclick="alert('End of shift reconciliation has been queued for supervisor review.')" class="hidden items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-900 md:flex"><span class="material-symbols-outlined text-base text-secondary-fixed">receipt_long</span>End of Shift Reconciliation</button>
                <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm">
                    <p id="billing-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p>
                    <p id="billing-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1680px] space-y-6 p-5 lg:p-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-md">
                <div class="pointer-events-none absolute -bottom-20 -right-16 h-96 w-96 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                    <div class="max-w-4xl">
                        <span class="rounded border border-secondary-fixed/30 bg-secondary/30 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Billing Identity Vault</span>
                        <h2 class="mt-3 font-serif text-4xl font-bold leading-tight">{{ $user->name }}</h2>
                        <p class="mt-2 text-sm leading-6 text-blue-100">Manage cashier account credentials, authorized POS terminal access, shift alerts, and billing department security preferences.</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1 rounded bg-white/10 px-2 py-1 text-xs font-bold"><span class="material-symbols-outlined text-sm text-secondary-fixed">verified_user</span>{{ $roleLabel }} Active</span>
                            <span class="inline-flex items-center gap-1 rounded bg-white/10 px-2 py-1 text-xs font-bold"><span class="material-symbols-outlined text-sm text-secondary-fixed">badge</span>{{ $employeeId }}</span>
                            <span class="inline-flex items-center gap-1 rounded bg-white/10 px-2 py-1 text-xs font-bold"><span class="material-symbols-outlined text-sm text-secondary-fixed">schedule</span>Shift A</span>
                        </div>
                    </div>
                    <div class="w-full rounded-xl border border-white/10 bg-white/10 p-5 xl:w-96">
                        <div class="flex items-center gap-4">
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-secondary-fixed bg-primary font-serif text-2xl font-bold text-secondary-fixed">{{ $initials }}</div>
                            <div>
                                <p class="text-sm font-bold text-white">{{ $user->email }}</p>
                                <p class="mt-1 text-xs text-blue-100/80">Primary billing work account</p>
                                <span class="mt-3 inline-flex rounded bg-secondary-fixed px-2.5 py-1 text-xs font-bold text-primary">Terminal credentials active</span>
                            </div>
                        </div>
                        <dl class="mt-5 space-y-2 border-t border-white/10 pt-4 text-sm text-blue-100">
                            <div class="flex justify-between gap-4"><dt>Register</dt><dd class="font-bold text-white">POS-REG-02</dd></div>
                            <div class="flex justify-between gap-4"><dt>Authority</dt><dd class="font-bold text-white">Cashier & Billing</dd></div>
                            <div class="flex justify-between gap-4"><dt>Member Since</dt><dd class="font-bold text-white">{{ $user->created_at?->timezone(config('app.timezone'))->format('M d, Y') }}</dd></div>
                        </dl>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-12">
                @csrf
                @method('PUT')

                <div class="space-y-6 xl:col-span-7">
                    @include('profile.partials.photo-field')
                    <section class="profile-card rounded-xl p-6">
                        <div class="flex items-center justify-between border-b border-line pb-4">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined rounded-lg bg-surface-low p-2 text-secondary">badge</span>
                                <div>
                                    <h3 class="font-serif text-2xl font-bold">Cashier Personnel Record</h3>
                                    <p class="text-sm text-slate-500">Update the name and email used across the billing workspace.</p>
                                </div>
                            </div>
                            <span class="hidden rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-secondary sm:inline-flex">Editable</span>
                        </div>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <label class="space-y-1 sm:col-span-2">
                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Full Legal Name</span>
                                <input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20">
                            </label>
                            <label class="space-y-1 sm:col-span-2">
                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Billing Work Email</span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20">
                            </label>
                            <div class="space-y-1">
                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Employee Identifier</span>
                                <div class="flex items-center justify-between rounded-lg border border-line bg-surface-low px-3 py-2.5 font-mono text-sm"><span>{{ $employeeId }}</span><span class="material-symbols-outlined text-base text-secondary">verified</span></div>
                            </div>
                            <div class="space-y-1">
                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Department Assignment</span>
                                <div class="rounded-lg border border-line bg-surface-low px-3 py-2.5 text-sm font-bold text-secondary">Billing Department - Cashier Terminal</div>
                            </div>
                        </div>
                    </section>

                    <section class="profile-card rounded-xl p-6">
                        <div class="flex flex-col justify-between gap-3 border-b border-line pb-4 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined rounded-lg bg-surface-low p-2 text-secondary">password</span>
                                <div>
                                    <h3 class="font-serif text-2xl font-bold">Password &amp; Access Lifecycle</h3>
                                    <p class="text-sm text-slate-500">Leave both password fields blank to keep the current credential.</p>
                                </div>
                            </div>
                            <span class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white">Secure Rotation</span>
                        </div>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <label class="space-y-1">
                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">New Password</span>
                                <input type="password" name="password" autocomplete="new-password" class="w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20" placeholder="Leave blank to keep current">
                            </label>
                            <label class="space-y-1">
                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Confirm Password</span>
                                <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20">
                            </label>
                        </div>
                    </section>

                    <section class="profile-card rounded-xl p-6">
                        <div class="flex items-center justify-between border-b border-line pb-4">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined rounded-lg bg-surface-low p-2 text-secondary">key</span>
                                <div>
                                    <h3 class="font-serif text-2xl font-bold">POS Terminal Security</h3>
                                    <p class="text-sm text-slate-500">Cash drawer, guest bill settlement, and audit access bound to the cashier identity.</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            @foreach ([
                                ['icon' => 'point_of_sale', 'title' => 'Cash Drawer', 'meta' => 'Access logged per shift'],
                                ['icon' => 'receipt_long', 'title' => 'Guest Bill Settlement', 'meta' => 'Manager override required'],
                                ['icon' => 'fact_check', 'title' => 'Audit Exports', 'meta' => 'End-of-shift sealed'],
                            ] as $item)
                                <div class="rounded-lg border border-line bg-surface-low p-4">
                                    <span class="material-symbols-outlined text-secondary">{{ $item['icon'] }}</span>
                                    <h4 class="mt-2 font-bold">{{ $item['title'] }}</h4>
                                    <p class="mt-1 text-xs text-slate-500">{{ $item['meta'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <aside class="space-y-6 xl:col-span-5">
                    <section class="profile-card rounded-xl p-6">
                        <div class="flex items-center justify-between border-b border-line pb-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">devices</span>
                                <h3 class="text-lg font-bold">Authorized Billing Terminals</h3>
                            </div>
                            <span class="rounded-full bg-secondary-container px-2.5 py-1 text-xs font-bold text-secondary">3 Active</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach ([
                                ['icon' => 'point_of_sale', 'title' => 'POS-REG-02', 'meta' => 'Subic Central billing station', 'state' => 'This Terminal', 'active' => true],
                                ['icon' => 'computer', 'title' => 'Back Office Cashier Console', 'meta' => 'Revenue review station', 'state' => 'Idle', 'active' => false],
                                ['icon' => 'tablet_mac', 'title' => 'Mobile Billing Tablet', 'meta' => 'Supervisor checkout assist', 'state' => 'Standby', 'active' => false],
                            ] as $terminal)
                                <div class="rounded-xl border {{ $terminal['active'] ? 'border-2 border-secondary-fixed bg-surface-low' : 'border-line bg-white' }} p-3.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="material-symbols-outlined text-secondary">{{ $terminal['icon'] }}</span>
                                            <div><h4 class="text-sm font-bold">{{ $terminal['title'] }}</h4><p class="text-xs text-slate-500">{{ $terminal['meta'] }}</p></div>
                                        </div>
                                        <span class="text-xs font-bold {{ $terminal['active'] ? 'rounded bg-secondary-fixed px-2 py-0.5 text-primary' : 'text-slate-500' }}">{{ $terminal['state'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="profile-card rounded-xl p-6">
                        <div class="flex items-center gap-2 border-b border-line pb-4">
                            <span class="material-symbols-outlined text-secondary">notifications_active</span>
                            <h3 class="text-lg font-bold">Billing Alerts</h3>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach ([
                                ['title' => 'Shift reconciliation reminders', 'meta' => 'Prompt before daily close', 'checked' => true],
                                ['title' => 'Manager override notifications', 'meta' => 'High-value settlement approvals', 'checked' => true],
                                ['title' => 'Cash drawer variance alerts', 'meta' => 'Immediate terminal warning', 'checked' => true],
                                ['title' => 'Daily report summary', 'meta' => 'Email at end of shift', 'checked' => false],
                            ] as $trigger)
                                <div class="flex items-center justify-between gap-4 border-t border-line pt-3 first:border-t-0 first:pt-0">
                                    <div><p class="text-sm font-bold">{{ $trigger['title'] }}</p><p class="text-xs text-slate-500">{{ $trigger['meta'] }}</p></div>
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input class="peer sr-only" type="checkbox" @checked($trigger['checked'])>
                                        <span class="h-6 w-11 rounded-full bg-line after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-secondary peer-checked:after:translate-x-full"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-xl border border-secondary-fixed/40 bg-primary p-6 text-white shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined rounded-lg bg-white/10 p-2 text-secondary-fixed">shield</span>
                            <div><h3 class="font-serif text-xl font-bold">Billing Access Scope</h3><p class="text-sm text-blue-100">Current cashier permissions are active.</p></div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (['Guest Bill settlement', 'Transaction posting', 'Cash drawer audit', 'Reports center'] as $grant)
                                <span class="rounded border border-white/10 bg-white/10 px-2.5 py-1 text-xs font-bold text-blue-50">{{ $grant }}</span>
                            @endforeach
                        </div>
                    </section>
                </aside>

                <footer class="profile-card flex flex-col justify-between gap-4 rounded-xl p-5 xl:col-span-12 md:flex-row md:items-center">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary-fixed/50 text-secondary"><span class="material-symbols-outlined">enhanced_encryption</span></span>
                        <div>
                            <p class="text-sm font-bold">Billing account changes are recorded in the IHMP audit ledger.</p>
                            <p class="font-mono text-xs text-slate-500">Session Hash: {{ substr(sha1($user->email), 0, 8) }}-{{ substr(sha1((string) $user->id), -8) }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-line px-4 py-2 text-xs font-bold text-primary transition hover:bg-surface-low" type="button" onclick="alert('Recovery code generation is available to administrators only.')"><span class="material-symbols-outlined text-base">vpn_key</span>Recovery Codes</button>
                        <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-primary-container" type="submit"><span class="material-symbols-outlined text-base text-secondary-fixed">save</span>Save Profile Preferences</button>
                    </div>
                </footer>
            </form>
        </main>
    </div>

    <script>
        function updateBillingClock() {
            const now = new Date();
            document.getElementById('billing-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: '2-digit', year: 'numeric', timeZone: 'Asia/Manila' });
            document.getElementById('billing-clock').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Asia/Manila' }) + ' PHT';
        }

        updateBillingClock();
        setInterval(updateBillingClock, 1000);
    </script>
    @include('profile.partials.photo-script')
</body>
</html>
