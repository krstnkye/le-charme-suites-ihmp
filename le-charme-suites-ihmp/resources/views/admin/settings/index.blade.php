@php
    $currentUser = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Le Charme Suites IHMP</title>
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

        .paper-shadow {
            box-shadow: 0 4px 16px -2px rgba(23, 43, 66, 0.05), 0 1px 3px rgba(197, 163, 106, 0.08);
        }

        .gold-accent-top {
            border-top: 3px solid #c5a36a;
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">settings</span>
                <div>
                    <h1 class="font-serif text-xl font-bold">Le Charme Suites IHMP</h1>
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Global System Settings</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container" type="submit" form="settings-form">
                    <span class="material-symbols-outlined text-[18px] text-secondary-fixed">save</span>
                    Quick Save Changes
                </button>
                <div class="ml-2 text-right">
                    <p class="text-xs font-bold">{{ $currentUser?->name }}</p>
                    <p id="settings-clock" class="text-[11px] text-slate-500" data-timezone="{{ config('app.timezone') }}">{{ now(config('app.timezone'))->format('M d, Y - h:i A') }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary-fixed text-sm font-bold text-primary">
                    {{ strtoupper(substr($currentUser?->name ?? 'A', 0, 1)) }}
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1500px] flex-col gap-6 p-8">
            <section class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <div class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="text-secondary">System Settings</span>
                    </div>
                    <h2 class="font-serif text-4xl font-semibold tracking-tight">Global System Settings & Operational Parameters</h2>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Manage hotel information, front desk operating rules, billing policies, device integrations, room cleaning and preparation logic, and backup schedules.</p>
                </div>
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Active Node
                </span>
            </section>

            <nav class="flex gap-2 overflow-x-auto rounded-xl border border-surface-container-highest bg-surface-container-low p-2">
                @foreach ([
                    ['domain', 'Property & Branch Profile'],
                    ['account_balance_wallet', 'Billing & Tax Policies'],
                    ['schedule', 'Check-in / Check-out Rules'],
                    ['hub', 'Hardware & Device Integrations'],
                    ['nightlight', 'IHMP Cron & Night Audit'],
                    ['mark_email_unread', 'Notifications'],
                ] as $index => [$icon, $label])
                    <a href="#settings-form" @class([
                        'inline-flex shrink-0 items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold transition',
                        'bg-white text-primary shadow-sm ring-1 ring-secondary/30' => $index === 0,
                        'text-slate-600 hover:bg-white hover:text-primary' => $index !== 0,
                    ])>
                        <span class="material-symbols-outlined text-[18px] text-secondary">{{ $icon }}</span>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="rounded-xl border border-secondary/30 bg-secondary-fixed/20 p-4">
                <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-full bg-white text-secondary shadow-sm">
                            <span class="material-symbols-outlined">verified_user</span>
                        </span>
                        <div>
                            <h3 class="font-bold">Subic Bay Freeport Zone Regulatory Sync Online</h3>
                            <p class="text-sm leading-6 text-slate-600">Tax exemptions, duty-free hospitality thresholds, and local operating rules are staged for the current academic IHMP configuration.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-white px-2.5 py-1 text-xs font-bold text-slate-600">Last synced: {{ now(config('app.timezone'))->format('M d, Y h:i A') }}</span>
                        <button data-settings-action="sync" class="inline-flex items-center gap-1 text-sm font-bold text-secondary hover:text-primary" type="button">
                            Force Re-check
                            <span class="material-symbols-outlined text-[16px]">sync</span>
                        </button>
                    </div>
                </div>
            </div>

            <form id="settings-form" class="space-y-6" onsubmit="event.preventDefault(); document.getElementById('save-feedback').classList.remove('hidden');">
                <div id="save-feedback" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    Settings draft saved in this demo interface. Database persistence can be added once the settings table is finalized.
                </div>

                <div class="grid grid-cols-12 gap-6">
                    <section class="paper-shadow gold-accent-top col-span-12 rounded-xl border border-surface-container-highest bg-white p-6 lg:col-span-7">
                        <div class="mb-5 flex items-center justify-between border-b border-surface-container-highest pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-secondary">corporate_fare</span>
                                <h3 class="font-serif text-2xl font-semibold">Property Identity & Licensing</h3>
                            </div>
                            <span class="rounded border border-secondary/20 bg-secondary-fixed/30 px-2 py-0.5 text-xs font-bold text-secondary">Core Master Config</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="md:col-span-2">
                                <span class="mb-1.5 block text-sm font-bold">Official Hotel Registered Name</span>
                                <input class="w-full rounded-lg border-outline-variant bg-white px-3.5 py-2.5 text-sm focus:border-secondary focus:ring-secondary/20" type="text" value="{{ $property['name'] }}">
                            </label>
                            <label>
                                <span class="mb-1.5 block text-sm font-bold">Subic Bay Freeport Zone Registration #</span>
                                <input class="w-full rounded-lg border-outline-variant bg-white px-3.5 py-2.5 font-mono text-sm focus:border-secondary focus:ring-secondary/20" type="text" value="{{ $property['registration_number'] }}">
                            </label>
                            <label>
                                <span class="mb-1.5 block text-sm font-bold">Functional Operating Currency</span>
                                <select class="w-full rounded-lg border-outline-variant bg-white px-3.5 py-2.5 text-sm focus:border-secondary focus:ring-secondary/20">
                                    <option @selected($property['currency'] === 'PHP')>PHP - Philippine Peso</option>
                                    <option @selected($property['currency'] === 'USD')>USD - US Dollar</option>
                                </select>
                            </label>
                            <label>
                                <span class="mb-1.5 block text-sm font-bold">Primary Timezone Server</span>
                                <input class="w-full cursor-not-allowed rounded-lg border-surface-container-highest bg-surface-container-low px-3.5 py-2.5 font-mono text-sm text-slate-600" readonly type="text" value="{{ $property['timezone'] }} (UTC+08:00)">
                            </label>
                            <label>
                                <span class="mb-1.5 block text-sm font-bold">Taxpayer Identification Number</span>
                                <input class="w-full rounded-lg border-outline-variant bg-white px-3.5 py-2.5 font-mono text-sm focus:border-secondary focus:ring-secondary/20" type="text" value="{{ $property['tin'] }}">
                            </label>
                        </div>
                    </section>

                    <section class="paper-shadow col-span-12 rounded-xl border border-surface-container-highest bg-white p-6 lg:col-span-5">
                        <div class="mb-5 flex items-center gap-2.5 border-b border-surface-container-highest pb-3">
                            <span class="material-symbols-outlined text-secondary">timer</span>
                            <h3 class="font-serif text-2xl font-semibold">Front Desk Operational Defaults</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Standard Check-In</span>
                                <input class="w-full rounded-lg border-outline-variant px-3 py-2 font-bold focus:border-secondary focus:ring-secondary/20" type="time" value="{{ $operations['check_in'] }}">
                            </label>
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Standard Check-Out</span>
                                <input class="w-full rounded-lg border-outline-variant px-3 py-2 font-bold focus:border-secondary focus:ring-secondary/20" type="time" value="{{ $operations['check_out'] }}">
                            </label>
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Grace Period</span>
                                <div class="flex items-center rounded-lg border border-outline-variant bg-white px-3 py-2">
                                    <input class="w-full border-none p-0 font-bold focus:ring-0" type="number" value="{{ $operations['grace_period'] }}">
                                    <span class="text-xs font-bold text-slate-500">mins</span>
                                </div>
                            </label>
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Late Check-Out Surcharge</span>
                                <input class="w-full rounded-lg border-outline-variant px-3 py-2 font-bold focus:border-secondary focus:ring-secondary/20" type="text" value="{{ $operations['late_checkout_fee'] }}">
                            </label>
                        </div>
                        <div class="mt-4 rounded-lg border border-surface-container-highest bg-surface-container-low p-3">
                            <p class="text-sm font-bold">Auto-release unpaid reservations at:</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-secondary">alarm_off</span>{{ $operations['auto_release'] }} local time</span>
                                <span class="rounded bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">Guaranteed excluded</span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="grid grid-cols-12 gap-6">
                    <section class="paper-shadow col-span-12 rounded-xl border border-surface-container-highest bg-white p-6 lg:col-span-6">
                        <div class="mb-5 flex items-center justify-between border-b border-surface-container-highest pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-secondary">payments</span>
                                <h3 class="font-serif text-2xl font-semibold">Taxation & Freeport Exemption Rules</h3>
                            </div>
                            <span class="material-symbols-outlined text-outline" title="Governed by SBMA tax codes">info</span>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between rounded-lg border border-surface-container-highest bg-surface-container-low/50 p-3">
                                <div>
                                    <p class="font-bold">SBMA Freeport Eco-Tourism Fee</p>
                                    <p class="text-sm text-slate-500">Conservation levy applied per occupied room night.</p>
                                </div>
                                <strong class="font-serif text-xl">PHP {{ number_format($taxes['eco_tourism_fee'], 2) }}</strong>
                            </div>
                            <div class="flex items-center justify-between rounded-lg border border-surface-container-highest bg-surface-container-low/50 p-3">
                                <div>
                                    <p class="font-bold">Standard Service Charge</p>
                                    <p class="text-sm text-slate-500">Distributed to accredited hotel line staff monthly.</p>
                                </div>
                                <strong class="font-serif text-xl text-secondary">{{ number_format($taxes['service_charge'], 2) }}%</strong>
                            </div>
                            <div class="flex items-center justify-between gap-4 rounded-lg border border-secondary/30 bg-secondary-fixed/20 p-3">
                                <div>
                                    <p class="font-bold">VAT Exemption for SBFZ Locators</p>
                                    <p class="text-sm text-slate-600">Automatically waive VAT when a valid corporate exemption certificate is recorded.</p>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                    <input type="checkbox" class="peer sr-only" @checked($taxes['vat_exemption_enabled'])>
                                    <div class="h-6 w-11 rounded-full bg-outline-variant after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                </label>
                            </div>
                        </div>
                    </section>

                    <section class="paper-shadow col-span-12 rounded-xl border border-surface-container-highest bg-white p-6 lg:col-span-6">
                        <div class="mb-5 flex items-center justify-between border-b border-surface-container-highest pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-secondary">developer_board</span>
                                <h3 class="font-serif text-2xl font-semibold">Hardware Peripheral Interfaces</h3>
                            </div>
                            <button data-settings-action="poll" class="inline-flex items-center gap-1 text-sm font-bold text-secondary hover:text-primary" type="button">
                                <span class="material-symbols-outlined text-[16px]">refresh</span>
                                Poll Status
                            </button>
                        </div>
                        <div class="space-y-3">
                            @foreach ([
                                ['key', 'Saflok Quantum Keycard Server', '192.168.10.45:8080 TCP Socket', 'Connected'],
                                ['point_of_sale', 'BDO Merchant POS Terminals', 'Ethernet direct hook / auto-settlement', 'Ethernet Synced'],
                                ['print', 'Epson TM-T88VI Thermal Billing Printers', 'Front Desk Terminals 1, 2 and Guest Service', 'Online & Ready'],
                            ] as [$icon, $name, $description, $status])
                                <div class="flex items-center justify-between gap-4 rounded-lg border border-surface-container-highest p-3 transition hover:border-secondary">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container text-primary"><span class="material-symbols-outlined">{{ $icon }}</span></span>
                                        <span>
                                            <strong class="block">{{ $name }}</strong>
                                            <small class="font-mono text-slate-500">{{ $description }}</small>
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-2 rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-800">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        {{ $status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="grid grid-cols-12 gap-6">
                    <section class="paper-shadow col-span-12 rounded-xl border border-surface-container-highest bg-white p-6 lg:col-span-7">
                        <div class="mb-5 flex items-center justify-between border-b border-surface-container-highest pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-secondary">cleaning_services</span>
                                <h3 class="font-serif text-2xl font-semibold">Automatic Room Cleaning & Housekeeping Rules</h3>
                            </div>
                            <span class="rounded border border-primary/20 bg-primary/5 px-2 py-0.5 text-xs font-bold text-primary">Operational Trigger</span>
                        </div>
                        <div class="space-y-3">
                            @foreach ([
                                ['Auto-mark room as Dirty upon checkout settlement', 'Broadcasts a room cleaning and preparation task to housekeeping after the guest departure is recorded.'],
                                ['Room Preparation Response Time escalation alert', 'Notifies the housekeeping supervisor if a room turnaround exceeds 45 minutes.'],
                            ] as [$title, $description])
                                <div class="flex items-center justify-between gap-4 rounded-lg border border-surface-container-highest p-3">
                                    <div>
                                        <p class="font-bold">{{ $title }}</p>
                                        <p class="text-sm text-slate-500">{{ $description }}</p>
                                    </div>
                                    <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                        <input type="checkbox" class="peer sr-only" checked>
                                        <div class="h-6 w-11 rounded-full bg-outline-variant after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="paper-shadow col-span-12 rounded-xl border border-surface-container-highest bg-white p-6 lg:col-span-5">
                        <div class="mb-5 flex items-center justify-between border-b border-surface-container-highest pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-secondary">cloud_sync</span>
                                <h3 class="font-serif text-2xl font-semibold">Data Retention & Backups</h3>
                            </div>
                            <span class="text-xs font-bold text-slate-500">AES-256</span>
                        </div>
                        <div class="rounded-lg border border-surface-container-highest bg-surface-container-low p-4">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-secondary">schedule_send</span>
                                <div>
                                    <p class="font-bold">Daily Automated Cloud Summary</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Runs daily at <strong>03:00 Asia/Manila</strong> alongside the night audit, with a 7-year fiscal audit retention policy.</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <span class="text-sm text-slate-600">Backup archive policy: <strong class="text-primary">7 years</strong></span>
                            <button data-settings-action="backup" class="inline-flex items-center gap-1.5 rounded-lg border border-secondary px-3 py-2 text-sm font-bold text-secondary transition hover:bg-secondary-fixed/30" type="button">
                                <span class="material-symbols-outlined text-[16px]">download</span>
                                Manual Backup Now
                            </button>
                        </div>
                    </section>
                </div>
            </form>
        </main>
    </div>

    <script>
        const clock = document.getElementById('settings-clock');
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
            }).format(new Date()).replace(',', ' -');
        };
        refreshClock();
        setInterval(refreshClock, 1000);

        document.querySelectorAll('[data-settings-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const feedback = document.getElementById('save-feedback');
                const messages = {
                    sync: 'Regulatory configuration re-check completed using the current local IHMP values.',
                    poll: 'Peripheral status poll completed. All listed devices remain online.',
                    backup: 'Manual backup snapshot prepared for the current local development data.',
                };

                if (feedback) {
                    feedback.textContent = messages[button.dataset.settingsAction] || 'Settings action completed.';
                    feedback.classList.remove('hidden');
                    feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    </script>
</body>
</html>
