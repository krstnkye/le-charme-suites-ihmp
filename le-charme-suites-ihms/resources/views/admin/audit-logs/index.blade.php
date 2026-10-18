@php
    $currentUser = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security & Audit Logs - Le Charme Suites IHMS</title>
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

        .audit-card {
            box-shadow: 0 4px 16px -2px rgba(23, 43, 66, 0.05), 0 1px 3px rgba(197, 163, 106, 0.08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="flex items-center gap-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Administration</p>
                    <h1 class="font-serif text-xl font-bold">Security & Compliance Audit Log</h1>
                </div>
                <span class="hidden items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 lg:inline-flex">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                    </span>
                    Live Stream Active
                </span>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden items-center gap-2 rounded-lg border border-outline-variant bg-surface-container-low px-3 py-1.5 text-sm font-semibold text-slate-600 md:flex">
                    <span class="material-symbols-outlined text-base text-secondary">calendar_today</span>
                    {{ now(config('app.timezone'))->format('M d, Y') }}
                </div>
                <button id="exportComplianceAudit" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container" type="button">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    Export Compliance Audit
                </button>
                <div class="text-right">
                    <p class="text-xs font-bold">{{ $currentUser?->name }}</p>
                    <p class="text-[11px] text-slate-500">Security Console</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-container text-sm font-bold text-secondary-fixed">
                    {{ strtoupper(substr($currentUser?->name ?? 'A', 0, 1)) }}
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-8">
            <section class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <div class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="text-secondary">Security & Audit Logs</span>
                    </div>
                    <h2 class="font-serif text-4xl font-semibold tracking-tight">Security & Audit Logs Command Console</h2>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Monitor role access, privileged changes, guest bill events, room updates, active sessions, and compliance proof records for the IHMS portal.</p>
                </div>
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-secondary/30 bg-secondary-fixed/40 px-3 py-1.5 text-xs font-bold text-secondary">
                    <span class="material-symbols-outlined text-sm">lock</span>
                    Cryptographic Audit Node
                </span>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="audit-card rounded-xl border border-outline-variant/30 bg-white p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Audit Events</span><span class="material-symbols-outlined text-primary">receipt_long</span></div>
                    <p class="mt-3 font-serif text-4xl font-bold">{{ number_format($totalAuditEvents) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Includes account registration, login, logout, room, and transaction events.</p>
                </div>
                <div class="audit-card rounded-xl border border-outline-variant/30 bg-white p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Critical Alerts</span><span class="material-symbols-outlined text-error">gpp_maybe</span></div>
                    <p class="mt-3 font-serif text-4xl font-bold">{{ $blockedAttempts }} Blocked</p>
                    <p class="mt-1 text-sm text-slate-500">Failed login attempts preserved for administrator review.</p>
                </div>
                <div class="audit-card rounded-xl border border-outline-variant/30 bg-white p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Privileged Actions</span><span class="material-symbols-outlined text-secondary">key</span></div>
                    <p class="mt-3 font-serif text-4xl font-bold">{{ $registeredAccounts }}</p>
                    <p class="mt-1 text-sm text-slate-500">Staff accounts created through the registration page.</p>
                </div>
                <div class="audit-card rounded-xl border border-outline-variant/30 bg-white p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Sessions</span><span class="material-symbols-outlined text-primary">devices</span></div>
                    <p class="mt-3 font-serif text-4xl font-bold">{{ $successfulLogins }}</p>
                    <p class="mt-1 text-sm text-slate-500">Successful sign-ins recorded in the local database.</p>
                </div>
            </section>

            <section class="audit-card rounded-xl border border-outline-variant/30 bg-white p-4">
                <div class="flex flex-col justify-between gap-3 xl:flex-row xl:items-center">
                    <div class="flex flex-wrap gap-2">
                        <select id="auditModuleFilter" class="rounded-lg border-outline-variant bg-surface-container-low text-sm focus:border-secondary focus:ring-secondary/20">
                            <option value="">All Modules</option>
                            <option value="access">User Access</option>
                            <option value="account">User Management</option>
                            <option value="billing">Transactions / Billing</option>
                            <option value="room">Room Status</option>
                        </select>
                        <select id="auditLevelFilter" class="rounded-lg border-outline-variant bg-surface-container-low text-sm focus:border-secondary focus:ring-secondary/20">
                            <option value="">All Levels</option>
                            <option value="verified">Verified</option>
                            <option value="blocked">Blocked</option>
                            <option value="review">Review</option>
                            <option value="privileged">Privileged</option>
                        </select>
                        <select id="auditOperatorFilter" class="rounded-lg border-outline-variant bg-surface-container-low text-sm focus:border-secondary focus:ring-secondary/20">
                            <option value="">All Operators</option>
                            @foreach ($activeSessions as $sessionUser)
                                <option value="{{ strtolower($sessionUser->name) }}">{{ $sessionUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative min-w-[280px]">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-500">search</span>
                        <input id="auditSearch" class="w-full rounded-lg border-outline-variant bg-surface-container-low py-2 pl-9 pr-3 text-sm focus:border-secondary focus:ring-secondary/20" placeholder="Search email, IP, record ID, or actor..." type="search">
                    </div>
                </div>
            </section>

            <section class="audit-card overflow-hidden rounded-xl border border-outline-variant/30 bg-white">
                <div class="flex flex-col justify-between gap-3 border-b border-surface-container-highest bg-surface-container-lowest p-5 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">dataset</span>
                        <div>
                            <h3 class="font-serif text-2xl font-semibold">Cryptographic Audit Ledger Stream</h3>
                            <p class="text-sm text-slate-500">Recent system records formatted for compliance review.</p>
                        </div>
                    </div>
                    <span class="rounded bg-primary/10 px-2 py-0.5 font-mono text-xs font-bold text-primary">Auto-refresh: 5s</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1180px] text-left">
                        <thead>
                            <tr class="border-b border-surface-container-highest bg-surface-container text-xs font-bold uppercase tracking-wider text-primary">
                                <th class="px-4 py-3">Timestamp</th>
                                <th class="px-4 py-3">User / Actor</th>
                                <th class="px-4 py-3">Action / Event</th>
                                <th class="px-4 py-3">Target Entity</th>
                                <th class="px-4 py-3">IP & Terminal</th>
                                <th class="px-4 py-3">Details / Diff</th>
                                <th class="px-4 py-3 text-right">Proof Verification</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container-highest/70 text-sm">
                            @forelse ($auditEvents as $event)
                                @php
                                    $eventModule = str_contains($event['event'], 'LOGIN') || in_array($event['event'], ['LOGOUT', 'ACCOUNT_REGISTERED'], true)
                                        ? 'access'
                                        : (str_contains($event['event'], 'USER_') ? 'account' : (str_contains($event['event'], 'ROOM_') ? 'room' : 'billing'));
                                @endphp
                                <tr data-audit-row data-module="{{ $eventModule }}" data-level="{{ strtolower($event['severity']) }}" data-operator="{{ strtolower($event['actor']) }}" class="transition hover:bg-surface-container-low/70">
                                    <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs">
                                        <div>{{ optional($event['timestamp'])->timezone(config('app.timezone'))->format('M d, Y') }}</div>
                                        <div class="text-[11px] text-slate-500">{{ optional($event['timestamp'])->timezone(config('app.timezone'))->format('H:i:s') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <div class="font-bold">{{ $event['actor'] }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $event['role'] }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1 rounded border border-secondary/20 bg-secondary-fixed/40 px-2 py-0.5 font-mono text-[11px] font-bold text-secondary">
                                            <span class="material-symbols-outlined text-xs">{{ $event['icon'] }}</span>
                                            {{ $event['event'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="font-mono text-xs font-bold">{{ $event['target'] }}</span>
                                        <div class="text-[11px] text-slate-500">{{ $event['context'] }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-slate-600">
                                        <div>{{ $event['terminal'] }}</div>
                                        <div class="text-[11px]">{{ $event['ip'] }}</div>
                                    </td>
                                    <td class="max-w-xs px-4 py-3.5">
                                        <div class="truncate" title="{{ $event['details'] }}">{{ $event['details'] }}</div>
                                        <div class="text-[11px] text-slate-500">Severity: {{ $event['severity'] }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                        <button data-proof-json='@json($event)' class="inline-flex items-center gap-1 rounded border border-outline-variant bg-surface-container-highest/60 px-2.5 py-1 text-xs font-bold text-primary transition hover:bg-secondary-fixed/40" type="button">
                                            <span class="material-symbols-outlined text-sm text-secondary">data_object</span>
                                            View Proof JSON
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-sm font-semibold text-slate-500">No audit events recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col justify-between gap-3 border-t border-surface-container-highest bg-surface-container-low p-4 text-sm text-slate-600 lg:flex-row lg:items-center">
                    <div class="flex min-w-0 items-center gap-2 font-mono text-xs">
                        <span class="material-symbols-outlined text-sm text-secondary">lock</span>
                        <span>Current Merkle Root:</span>
                        <span class="truncate font-bold text-primary">{{ $ledgerRoot }}</span>
                    </div>
                    <span id="auditVisibleCount" class="rounded bg-white px-3 py-1 text-xs font-bold text-primary">Showing {{ $auditEvents->count() }} entries</span>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="audit-card rounded-xl border border-outline-variant/30 bg-white p-6 lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between border-b border-surface-container-highest pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary">policy</span>
                            <div>
                                <h3 class="font-serif text-2xl font-semibold">IHMS Access Control & Security Policies</h3>
                                <p class="text-sm text-slate-500">Configured for classroom-grade compliance modeling and operational safeguards.</p>
                            </div>
                        </div>
                        <span class="rounded border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800">Enforcing Active</span>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border border-surface-container-highest bg-surface-container-low p-4">
                            <p class="text-xs font-bold text-slate-500">Failed Attempt Lockout</p>
                            <p class="mt-1 font-serif text-2xl font-bold">5 Attempts</p>
                            <p class="mt-1 text-xs text-slate-500">Triggers 30-minute workstation lock.</p>
                        </div>
                        <div class="rounded-lg border border-surface-container-highest bg-surface-container-low p-4">
                            <p class="text-xs font-bold text-slate-500">Inactivity Timeout</p>
                            <p class="mt-1 font-serif text-2xl font-bold">15 Minutes</p>
                            <p class="mt-1 text-xs text-slate-500">Auto-locks sensitive guest bill screens.</p>
                        </div>
                        <div class="rounded-lg border border-surface-container-highest bg-surface-container-low p-4">
                            <p class="text-xs font-bold text-slate-500">Override Validation</p>
                            <p class="mt-1 font-serif text-2xl font-bold">2-Factor</p>
                            <p class="mt-1 text-xs text-slate-500">Required for high-value discounts.</p>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/70 p-3 text-sm text-amber-950">
                        <strong>Security Rule Notice:</strong> All privileged actions should be replicated to an off-site audit ledger before production deployment.
                    </div>
                </div>

                <div class="audit-card rounded-xl border border-outline-variant/30 bg-white p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-surface-container-highest pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">devices</span>
                            <h3 class="font-serif text-2xl font-semibold">Active Sessions</h3>
                        </div>
                        <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">{{ $activeSessions->count() }} Live</span>
                    </div>
                    <div class="space-y-2.5">
                        @forelse ($activeSessions as $sessionUser)
                            <div class="flex items-center justify-between border-b border-surface-container pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold">{{ ucfirst((string) $sessionUser->role) }} Portal</span>
                                </div>
                                <span class="font-mono text-xs text-slate-500">{{ $sessionUser->name }}</span>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm font-semibold text-slate-500">No active users to display.</p>
                        @endforelse
                    </div>
                    <button id="terminateRemoteSessions" class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-error px-3 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-800" type="button">
                        <span class="material-symbols-outlined">emergency</span>
                        Terminate All Remote Sessions
                    </button>
                    <p class="mt-1.5 text-center text-[11px] text-slate-500">Requires master administrator confirmation.</p>
                </div>
            </section>
        </main>
    </div>
    <script>
        document.getElementById('exportComplianceAudit')?.addEventListener('click', () => {
            const rows = Array.from(document.querySelectorAll('tbody tr')).filter((row) => row.querySelectorAll('td').length >= 7);
            if (!rows.length) {
                alert('No audit records are available to export.');
                return;
            }

            const csvRows = [['Timestamp', 'Actor', 'Event', 'Target', 'Terminal', 'Details']];
            rows.forEach((row) => {
                const cells = Array.from(row.querySelectorAll('td')).slice(0, 6).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim());
                csvRows.push(cells);
            });

            const csv = csvRows.map((row) => row.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'le-charme-compliance-audit.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });

        document.querySelectorAll('[data-proof-json]').forEach((button) => {
            button.addEventListener('click', () => {
                const proof = JSON.parse(button.dataset.proofJson || '{}');
                alert(JSON.stringify(proof, null, 2));
            });
        });

        document.getElementById('terminateRemoteSessions')?.addEventListener('click', () => {
            if (confirm('Terminate all remote sessions in this admin simulation?')) {
                alert('Remote session termination request recorded for administrator review.');
            }
        });

        const auditRows = Array.from(document.querySelectorAll('[data-audit-row]'));
        const auditFilters = ['auditModuleFilter', 'auditLevelFilter', 'auditOperatorFilter', 'auditSearch'];
        const applyAuditFilters = () => {
            const module = document.getElementById('auditModuleFilter')?.value || '';
            const level = document.getElementById('auditLevelFilter')?.value || '';
            const operator = document.getElementById('auditOperatorFilter')?.value || '';
            const search = (document.getElementById('auditSearch')?.value || '').trim().toLowerCase();
            let visible = 0;

            auditRows.forEach((row) => {
                const matches = (!module || row.dataset.module === module)
                    && (!level || row.dataset.level === level)
                    && (!operator || row.dataset.operator === operator)
                    && (!search || row.innerText.toLowerCase().includes(search));
                row.hidden = !matches;
                visible += matches ? 1 : 0;
            });

            const count = document.getElementById('auditVisibleCount');
            if (count) count.textContent = `Showing ${visible} entries`;
        };

        auditFilters.forEach((id) => {
            const element = document.getElementById(id);
            element?.addEventListener(element.tagName === 'INPUT' ? 'input' : 'change', applyAuditFilters);
        });
    </script>
</body>
</html>
