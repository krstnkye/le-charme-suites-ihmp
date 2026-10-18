@php
    $currentUser = auth()->user();
    $isReports = $module === 'reports';
    $isRoomTypes = $module === 'room-types';
    $isRoomRates = $module === 'room-rates';
    $statMap = collect($stats)->mapWithKeys(fn ($stat) => [$stat['label'] => $stat['value']]);
    $roomCount = $statMap['Rooms tracked'] ?? 0;
    $reservationCount = $statMap['Reservations tracked'] ?? 0;
    $transactionCount = $statMap['Transaction records'] ?? 0;
    $ledgerTotal = $statMap['Ledger total'] ?? 'PHP 0.00';
    $roomTypeRows = collect($records)->values();
    $selectedRoomType = $roomTypeRows->first();
    $officialRoomCatalog = config('hotel.room_types', []);
    $roomTypeAmenities = config('hotel.highlights', []);
    $roomTypeWings = ['Main Wing', 'Ocean Wing', 'Executive Villas'];
    $roomTypeCatalog = $roomTypeRows->map(function ($roomType, $index) use ($roomTypeWings, $officialRoomCatalog) {
        $specification = $officialRoomCatalog[(string) $roomType[0]] ?? null;
        $bedConfiguration = $specification['bed_configuration'] ?? 'Custom configuration';

        return [
            'type' => (string) $roomType[0],
            'code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $roomType[0]), 0, 3) ?: 'RMT') . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            'wing' => $roomTypeWings[$index % count($roomTypeWings)],
            'inventory' => (string) $roomType[1],
            'capacity' => (string) $roomType[2],
            'average_rate' => (string) $roomType[3],
            'availability' => (string) $roomType[4],
            'bed_type' => $bedConfiguration,
        ];
    })->values();
    $roomRateRows = collect($records)->values();
    $selectedRoomRate = $roomRateRows->get(1) ?? $roomRateRows->first();
    $reportRows = [
        ['group' => 'financial', 'icon' => 'summarize', 'title' => "Daily Manager's Flash Report", 'code' => 'LCS-FIN-FLSH-01', 'description' => 'Comprehensive daily yield and occupancy ledger', 'category' => 'Financial', 'formats' => ['PDF', 'XLSX'], 'cadence' => 'Daily at 03:00 PHT', 'status' => 'SBMA Certified', 'last' => now(config('app.timezone'))->format('M d, Y h:i A')],
        ['group' => 'statutory', 'icon' => 'account_balance', 'title' => 'SBMA Freeport Tourism Eco-Levy & Tax Ledger', 'code' => 'LCS-TAX-SBMA-09', 'description' => 'Statutory exemption and municipal surcharge registry', 'category' => 'Statutory', 'formats' => ['PDF', 'CSV'], 'cadence' => 'Monthly close', 'status' => 'Ready', 'last' => 'Pending run'],
        ['group' => 'front-desk', 'icon' => 'hotel', 'title' => 'Room Occupancy & Availability Summary', 'code' => 'LCS-RMS-OCC-04', 'description' => 'Room status, occupancy, reserved inventory, and maintenance view', 'category' => 'Rooms', 'formats' => ['PDF', 'XLSX'], 'cadence' => 'Hourly snapshot', 'status' => 'Live Synced', 'last' => now(config('app.timezone'))->format('h:i A')],
        ['group' => 'financial', 'icon' => 'receipt_long', 'title' => 'Transactions & Billing Reconciliation', 'code' => 'LCS-FOL-REC-07', 'description' => 'Payment collection, guest bill activity, and settlement audit trail', 'category' => 'Finance', 'formats' => ['PDF', 'XLSX'], 'cadence' => 'End of shift', 'status' => 'Balanced', 'last' => $transactionCount ? now(config('app.timezone'))->format('M d, Y') : 'No entries'],
        ['group' => 'housekeeping', 'icon' => 'cleaning_services', 'title' => 'Housekeeping & Maintenance SLA Report', 'code' => 'LCS-HSK-SLA-06', 'description' => 'Room turnover, cleaning queue, and facilities readiness report', 'category' => 'Housekeeping', 'formats' => ['PDF', 'XLSX'], 'cadence' => 'Shift close', 'status' => 'Ready', 'last' => now(config('app.timezone'))->format('M d, Y')],
        ['group' => 'audit', 'icon' => 'shield', 'title' => 'Security & Audit Activity Export', 'code' => 'LCS-AUD-LOG-02', 'description' => 'Administrative access, role changes, and protected event stream', 'category' => 'Audit', 'formats' => ['PDF'], 'cadence' => 'On demand', 'status' => 'Admin Only', 'last' => now(config('app.timezone'))->format('M d, Y')],
    ];
    $reportFilters = [
        'All Reports' => ['key' => 'all', 'count' => count($reportRows)],
        'Financial & Revenue' => ['key' => 'financial', 'count' => collect($reportRows)->where('group', 'financial')->count()],
        'Statutory & SBMA Tax' => ['key' => 'statutory', 'count' => collect($reportRows)->where('group', 'statutory')->count()],
        'Front Desk & Rooms' => ['key' => 'front-desk', 'count' => collect($reportRows)->where('group', 'front-desk')->count()],
        'Housekeeping & Maintenance SLA' => ['key' => 'housekeeping', 'count' => collect($reportRows)->where('group', 'housekeeping')->count()],
        'Audit & Cryptographic Logs' => ['key' => 'audit', 'count' => collect($reportRows)->where('group', 'audit')->count()],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - Le Charme Suites IHMS</title>
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
            background: #172b42;
            background-image: radial-gradient(rgba(255, 222, 170, .14) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        .parchment-card {
            background-color: #fdfcfa;
            border: 1px solid #ede8df;
            box-shadow: 0 4px 16px -2px rgba(23, 43, 66, .05), 0 1px 3px rgba(197, 163, 106, .08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">{{ $isReports ? 'monitoring' : ($isRoomTypes ? 'bed' : ($isRoomRates ? 'price_change' : 'security')) }}</span>
                <h1 class="font-serif text-xl font-bold">Le Charme Suites IHMS - {{ $isReports ? 'Executive Reports & Auditing Center' : ($isRoomTypes ? 'Room Types & Suite Specifications' : ($isRoomRates ? 'Room Rates & Tariffs Management' : 'Administrator Operations')) }}</h1>
            </div>
            <div class="flex items-center gap-4">
                @if ($isReports || $isRoomTypes || $isRoomRates)
                    <div class="hidden items-center gap-2 rounded border border-outline-variant/40 bg-surface-container px-3 py-1 text-xs font-bold text-primary md:flex">
                        <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                        Subic Bay Central <span class="font-mono font-normal text-slate-500">| Live Sync</span>
                    </div>
                @endif
                <div class="text-right">
                    <p class="text-xs font-bold">{{ $currentUser?->name }}</p>
                    <p class="text-[11px] text-slate-500">Administrator Access</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-secondary/40 bg-primary-container text-sm font-bold text-white">{{ strtoupper(substr($currentUser?->name ?? 'A', 0, 1)) }}</a>
            </div>
        </header>

        @if ($isReports)
            <main class="flex flex-col gap-6 p-8">
                <section class="flex flex-wrap items-center justify-between gap-4 border-b border-surface-container-highest pb-4">
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span>Control & Governance</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="font-bold text-primary">Operational & Financial Reports Center</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-secondary/30 bg-secondary/15 px-2.5 py-1 text-xs font-bold text-secondary">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                            Freeport Statutory & Audit Suite
                        </span>
                        <span class="rounded-full bg-surface-container-high px-2.5 py-1 text-xs font-bold text-slate-600">SBMA Act 7227 Compliant</span>
                    </div>
                </section>

                <section class="flex flex-col justify-between gap-6 xl:flex-row xl:items-end">
                    <div>
                        <h2 class="font-serif text-4xl font-semibold tracking-tight">Executive & Departmental Reports Center</h2>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Generate, schedule, parameterize, and export institutional hotel reports, tax reconciliation ledgers, and operational telemetry for Le Charme Suites operations.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm font-semibold text-primary shadow-sm">
                            <span class="material-symbols-outlined text-lg text-secondary">calendar_today</span>
                            {{ now(config('app.timezone'))->startOfMonth()->format('M d') }} - {{ now(config('app.timezone'))->format('M d, Y') }} (Month-to-Date)
                        </div>
                        <button id="reportFormatButton" class="flex items-center gap-2 rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm font-semibold text-primary transition hover:border-secondary" type="button">
                            <span class="material-symbols-outlined text-lg">download</span>
                            Format: PDF
                        </button>
                        <button id="scheduleReportRun" class="flex items-center gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-high px-3.5 py-2 text-sm font-bold text-primary transition hover:bg-surface-container" type="button">
                            <span class="material-symbols-outlined text-lg">schedule</span>
                            Schedule Run
                        </button>
                        <button id="generateCustomReport" class="flex items-center gap-2 rounded-lg border border-secondary-fixed/30 bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container" type="button">
                            <span class="material-symbols-outlined text-lg text-secondary-fixed">play_arrow</span>
                            Generate Custom Report
                        </button>
                    </div>
                </section>

                <section class="flex gap-2 overflow-x-auto pb-1 text-sm" id="reportCategoryFilters">
                    @foreach ($reportFilters as $filter => $filterMeta)
                        <button class="report-filter whitespace-nowrap rounded-full px-4 py-1.5 font-semibold transition {{ $loop->first ? 'bg-primary text-white shadow-sm' : 'bg-surface-container text-primary hover:bg-surface-container-high' }}" type="button" data-report-filter="{{ $filterMeta['key'] }}">
                            {{ $filter }}
                            <span class="ml-1 rounded-full {{ $loop->first ? 'bg-secondary text-white' : 'bg-white text-primary' }} px-1.5 py-0.5 text-[10px]">{{ $filterMeta['count'] }}</span>
                        </button>
                    @endforeach
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="parchment-card relative overflow-hidden rounded-xl border-t-[3px] border-t-secondary p-6 xl:col-span-8">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-surface-container-highest pb-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-outline-variant/40 bg-primary/5 text-secondary">
                                    <span class="material-symbols-outlined text-2xl">tune</span>
                                </span>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-secondary">Active Parameter Console</span>
                                    <h3 class="font-serif text-2xl font-semibold">Daily Manager's Operational Flash & Revenue Ledger</h3>
                                </div>
                            </div>
                            <span class="rounded border border-outline-variant/30 bg-surface-container px-2.5 py-1 text-xs font-semibold text-slate-600">Doc ID: LCS-REP-{{ now(config('app.timezone'))->format('Ymd') }}</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-3">
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Reporting Date</span>
                                <input class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-primary focus:border-secondary focus:ring-1 focus:ring-secondary/20" type="date" value="{{ now(config('app.timezone'))->toDateString() }}">
                            </label>
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Property Scope</span>
                                <select class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-primary focus:border-secondary focus:ring-1 focus:ring-secondary/20">
                                    <option>All Property Wings (Consolidated)</option>
                                    <option>Main Wing Only</option>
                                    <option>Ocean Wing Only</option>
                                    <option>Executive Villas Only</option>
                                </select>
                            </label>
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Currency Base</span>
                                <select class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-primary focus:border-secondary focus:ring-1 focus:ring-secondary/20">
                                    <option>Philippine Peso (PHP) - Local</option>
                                    <option>US Dollar (USD) - Reference</option>
                                    <option>Dual Ledger (PHP / USD)</option>
                                </select>
                            </label>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-6 rounded-lg border border-outline-variant/30 bg-surface-container-low p-3.5 text-sm">
                            @foreach (['Departmental splits', 'Include voided guest bills', 'SBMA tax breakdown', 'Manager signature seal'] as $option)
                                <label class="flex cursor-pointer items-center gap-2 font-semibold text-primary">
                                    <input class="rounded border-outline-variant text-primary focus:ring-secondary" type="checkbox" @checked(!$loop->index || $loop->index === 1 || $loop->index === 3)>
                                    {{ $option }}
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-4 border-t border-surface-container-highest pt-4">
                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                                <span class="material-symbols-outlined text-base text-emerald-700">check_circle</span>
                                {{ $transactionCount }} ledger records available for compilation
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <button data-report-action="preview" class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-surface-container px-4 py-2 text-sm font-bold text-primary transition hover:bg-surface-container-high" type="button"><span class="material-symbols-outlined text-lg">visibility</span>Preview</button>
                                <button data-report-action="download" class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:brightness-110" type="button"><span class="material-symbols-outlined text-lg">picture_as_pdf</span>PDF Download</button>
                                <button data-report-action="email" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white transition hover:bg-primary-container" type="button"><span class="material-symbols-outlined text-lg">forward_to_inbox</span>Email to Board & GM</button>
                            </div>
                        </div>
                    </div>

                    <div class="parchment-card flex flex-col justify-between rounded-xl p-6 xl:col-span-4">
                        <div>
                            <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-secondary">Live Audit Telemetry</span>
                                <span class="font-mono text-[11px] text-outline">MTD Summary</span>
                            </div>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-center justify-between rounded-lg border border-outline-variant/30 bg-surface-container-low p-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">Gross Month-to-Date Revenue</p>
                                        <p class="font-serif text-2xl font-bold">{{ $ledgerTotal }}</p>
                                    </div>
                                    <span class="flex items-center gap-0.5 text-xs font-bold text-emerald-700"><span class="material-symbols-outlined text-sm">trending_up</span>Live</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg border border-outline-variant/30 bg-surface-container-low p-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">Reservations Tracked</p>
                                        <p class="font-serif text-2xl font-bold">{{ $reservationCount }}</p>
                                    </div>
                                    <span class="text-xs font-bold text-secondary">{{ $roomCount }} rooms</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg border border-outline-variant/30 bg-surface-container-low p-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">Unreconciled Variances</p>
                                        <p class="font-serif text-2xl font-bold text-emerald-800">PHP 0.00</p>
                                    </div>
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">BALANCED</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-surface-container-highest pt-3 text-[11px] text-slate-500">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-secondary">encrypted</span>Audit Chain Hash: {{ substr(sha1((string) $transactionCount), 0, 4) }}...{{ substr(sha1((string) $reservationCount), -4) }}</span>
                            <a class="font-semibold text-primary hover:underline" href="{{ route('admin.audit-logs.index') }}">Inspect Block</a>
                        </div>
                    </div>
                </section>

                <section class="parchment-card overflow-hidden rounded-xl">
                    <div class="flex flex-col justify-between gap-4 border-b border-surface-container-highest bg-surface-container-low p-5 md:flex-row md:items-center">
                        <div>
                            <h3 class="font-serif text-2xl font-semibold">Standard Institutional Report Directory</h3>
                            <p class="text-sm text-slate-500">Mandatory operational, statutory, and audit reports scheduled for hospitality and Freeport compliance.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-base text-outline">filter_list</span>
                                <input id="reportDirectorySearch" class="rounded-lg border border-outline-variant bg-white py-1.5 pl-8 pr-3 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary/20" placeholder="Filter by report name or code..." type="search">
                            </div>
                            <button id="sortReportDirectory" class="inline-flex items-center gap-1.5 rounded-lg border border-outline-variant bg-white px-3 py-1.5 text-sm font-semibold text-primary transition hover:border-secondary" type="button"><span class="material-symbols-outlined text-base">sort</span>Sort</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] border-collapse text-left">
                            <thead>
                                <tr class="border-b border-surface-container-highest bg-surface-container text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <th class="px-4 py-3">Report Identifier & Title</th>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3">Formats</th>
                                    <th class="px-4 py-3">Frequency / Cadence</th>
                                    <th class="px-4 py-3">Compliance Status</th>
                                    <th class="px-4 py-3">Last Run</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-container-highest text-sm">
                                @foreach ($reportRows as $report)
                                    <tr class="report-row transition hover:bg-surface-container-low/70" data-report-group="{{ $report['group'] }}" data-report-title="{{ $report['title'] }}" data-report-code="{{ $report['code'] }}">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined mt-0.5 text-secondary">{{ $report['icon'] }}</span>
                                                <div>
                                                    <span class="block font-bold text-primary">{{ $report['title'] }}</span>
                                                    <span class="font-mono text-[11px] text-slate-500">{{ $report['code'] }} - {{ $report['description'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5"><span class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">{{ $report['category'] }}</span></td>
                                        <td class="px-4 py-3.5 font-mono text-[11px]">
                                            @foreach ($report['formats'] as $format)
                                                <span class="mr-1 rounded border border-outline-variant/40 bg-surface-container px-1.5 py-0.5">{{ $format }}</span>
                                            @endforeach
                                        </td>
                                        <td class="px-4 py-3.5 text-slate-500">{{ $report['cadence'] }}</td>
                                        <td class="px-4 py-3.5"><span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>{{ $report['status'] }}</span></td>
                                        <td class="px-4 py-3.5 font-mono text-[11px] text-slate-500">{{ $report['last'] }}</td>
                                        <td class="px-4 py-3.5 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button data-report-row-action="run" data-report-title="{{ $report['title'] }}" class="rounded bg-primary px-2.5 py-1 text-xs font-bold text-white transition hover:bg-primary-container" type="button">Run Now</button>
                                                <button data-report-row-action="configure" data-report-title="{{ $report['title'] }}" class="p-1 text-slate-500 hover:text-secondary" title="Configure Parameters" type="button"><span class="material-symbols-outlined text-base">settings</span></button>
                                                <button data-report-row-action="history" data-report-title="{{ $report['title'] }}" class="p-1 text-slate-500 hover:text-secondary" title="History" type="button"><span class="material-symbols-outlined text-base">history</span></button>
                                                <button data-report-row-action="download" data-report-title="{{ $report['title'] }}" class="p-1 text-slate-500 hover:text-secondary" title="Download" type="button"><span class="material-symbols-outlined text-base">download</span></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr id="emptyReportFilterRow" class="hidden">
                                    <td colspan="7" class="px-4 py-12 text-center text-sm font-semibold text-slate-500">No reports match this category yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        @elseif ($isRoomRates)
            <main class="flex flex-col gap-6 p-8">
                <section class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <nav class="mb-1.5 flex items-center gap-2 text-xs font-semibold text-outline">
                            <span>System Administration</span>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span>Room Management</span>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                            <span class="font-bold text-secondary">Room Rates &amp; Tariff Schedules</span>
                        </nav>
                        <h2 class="font-serif text-4xl font-semibold tracking-tight text-primary">Room Rates &amp; Dynamic Tariffs</h2>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Configure tier base tariffs, seasonal yield modifiers, weekend surge rules, SBMA Freeport tax policies, and corporate negotiated rates.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-outline-variant/50 bg-white px-3.5 py-2 text-xs font-bold text-primary shadow-sm transition hover:bg-surface-container" type="button">
                            <span class="material-symbols-outlined text-base">download</span>
                            Export Tariff Schedule
                        </button>
                        <button class="inline-flex items-center gap-2 rounded-lg border border-secondary/40 bg-secondary-fixed/30 px-3.5 py-2 text-xs font-bold text-secondary shadow-sm transition hover:bg-secondary-fixed/50" type="button">
                            <span class="material-symbols-outlined text-base">trending_up</span>
                            Apply Seasonal Surge
                        </button>
                        <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-primary-container" type="button">
                            <span class="material-symbols-outlined text-base text-secondary-fixed">add_circle</span>
                            Create Rate Plan
                        </button>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="relative overflow-hidden rounded-xl border border-surface-container-highest bg-white p-5 shadow-sm">
                            <div class="absolute inset-x-0 top-0 h-0.5 {{ $loop->index === 0 ? 'bg-secondary' : ($loop->index === 1 ? 'bg-secondary-fixed' : ($loop->index === 2 ? 'bg-primary' : 'bg-secondary')) }}"></div>
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-outline">{{ ['Active Base Rate Index', 'Lowest Published Tariff', 'Highest Published Tariff', 'Average Base Tariff'][$loop->index] ?? $stat['label'] }}</span>
                                    <p class="mt-1 font-serif text-2xl font-bold text-primary">{{ $stat['value'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-outline-variant/30 bg-surface-container-low text-primary">
                                    <span class="material-symbols-outlined text-xl">{{ ['currency_exchange', 'price_check', 'trending_up', 'account_balance'][$loop->index] ?? 'sell' }}</span>
                                </span>
                            </div>
                            <div class="mt-4 flex items-center gap-1.5 border-t border-outline-variant/20 pt-3 text-xs font-semibold text-emerald-800">
                                <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
                                Live database synchronized
                            </div>
                        </div>
                    @endforeach
                </section>

                <section class="flex flex-col items-center justify-between gap-4 rounded-xl border border-surface-container-highest bg-white p-4 shadow-sm lg:flex-row">
                    <div class="flex w-full items-center gap-1 overflow-x-auto pb-1 lg:w-auto lg:pb-0">
                        @foreach (['All Active Plans', 'Standard Base Tariffs', 'Dynamic / Seasonal Yields', 'Corporate & Diplomatic OTA', 'Promotional Packages'] as $tab)
                            <button class="whitespace-nowrap rounded-lg px-3.5 py-1.5 text-xs font-bold transition {{ $loop->first ? 'bg-primary text-white shadow-sm' : 'text-slate-600 hover:bg-surface-container hover:text-primary' }}" type="button">{{ $tab }}</button>
                        @endforeach
                    </div>
                    <div class="flex w-full flex-wrap items-center justify-end gap-3 lg:w-auto">
                        <div class="flex items-center gap-2 rounded-lg border border-outline-variant/60 bg-surface-container-low px-2.5 py-1.5">
                            <span class="material-symbols-outlined text-base text-outline">apartment</span>
                            <select class="border-none bg-transparent p-0 pr-6 text-xs font-semibold text-primary focus:outline-none focus:ring-0">
                                <option>All Hotel Wings</option>
                                <option>Main Wing</option>
                                <option>Ocean Wing</option>
                                <option>Executive Villas</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg border border-outline-variant/60 bg-surface-container-low px-2.5 py-1.5">
                            <span class="text-xs font-bold text-secondary">PHP</span>
                            <span class="material-symbols-outlined text-sm text-outline">expand_more</span>
                        </div>
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-outline-variant px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-surface-container" type="button"><span class="material-symbols-outlined text-base">tune</span>Yield Rules Engine</button>
                        <button class="inline-flex items-center gap-1.5 rounded-lg bg-surface-container px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-surface-container-high" type="button"><span class="material-symbols-outlined text-base">layers</span>Bulk Rate Adjustment</button>
                    </div>
                </section>

                <section class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">
                    <div class="overflow-hidden rounded-xl border border-surface-container-highest bg-white shadow-sm xl:col-span-8 2xl:col-span-9">
                        <div class="flex items-center justify-between border-b border-surface-container-highest bg-surface-container-low/60 px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">view_list</span>
                                <h3 class="font-serif text-2xl font-semibold text-primary">Master Room Tariffs Matrix</h3>
                                <span class="ml-2 rounded border border-secondary/30 bg-secondary-fixed/30 px-2 py-0.5 text-xs font-bold text-secondary">Live Yield Applied</span>
                            </div>
                            <span class="text-xs font-semibold text-outline">Viewing {{ $roomRateRows->count() }} configured tiers</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1100px] border-collapse text-left">
                                <thead>
                                    <tr class="border-b border-surface-container-highest bg-surface-container text-xs font-bold uppercase tracking-wider text-primary">
                                        <th class="px-4 py-3">Room Tier &amp; Code</th>
                                        <th class="px-4 py-3">Base Rack Rate</th>
                                        <th class="px-4 py-3">Active Multiplier</th>
                                        <th class="px-4 py-3">Effective Nightly</th>
                                        <th class="px-4 py-3">Weekend Extra</th>
                                        <th class="px-4 py-3">Extra Guest</th>
                                        <th class="px-4 py-3">SBMA 5%</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-container-highest text-sm">
                                    @forelse ($roomRateRows as $rateRow)
                                        @php
                                            [$typeName, $inventory, $minimumRate, $maximumRate, $averageRate] = $rateRow;
                                            $baseRate = (float) str_replace([',', 'PHP', ' '], '', $averageRate);
                                            $multiplier = $loop->even ? 1.15 : 1;
                                            $effectiveRate = $baseRate * $multiplier;
                                            $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $typeName), 0, 3) ?: 'RTE') . '-' . str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT);
                                            $selected = $selectedRoomRate && $selectedRoomRate[0] === $typeName;
                                        @endphp
                                        <tr class="{{ $selected ? 'border-l-4 border-l-secondary bg-secondary-fixed/10' : 'hover:bg-surface-container-low/70' }} transition">
                                            <td class="px-4 py-3.5">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-9 w-9 items-center justify-center rounded border {{ $selected ? 'border-secondary/40 bg-secondary-fixed/30 text-secondary' : 'border-outline-variant/40 bg-surface-container text-primary' }}"><span class="material-symbols-outlined text-lg">{{ $loop->iteration % 3 === 0 ? 'weekend' : ($loop->iteration % 2 === 0 ? 'single_bed' : 'bed') }}</span></span>
                                                    <div>
                                                        <p class="font-bold text-primary">{{ Str::headline($typeName) }}</p>
                                                        <p class="font-mono text-[10px] text-outline">{{ $code }} - {{ $roomTypeWings[$loop->index % count($roomTypeWings)] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 font-mono font-semibold">{{ $averageRate }}</td>
                                            <td class="px-4 py-3.5">
                                                <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-[11px] font-bold {{ $multiplier > 1 ? 'border border-secondary/30 bg-secondary-fixed/30 text-secondary' : 'bg-surface-container-highest text-slate-600' }}">{{ $multiplier > 1 ? '+15% Summer Peak' : 'Standard (1.0x)' }}</span>
                                            </td>
                                            <td class="px-4 py-3.5 font-mono text-[15px] font-bold text-primary">PHP {{ number_format($effectiveRate, 2) }}</td>
                                            <td class="px-4 py-3.5 font-mono text-outline">+PHP {{ number_format(max($baseRate * 0.12, 500), 2) }}</td>
                                            <td class="px-4 py-3.5 font-mono text-outline">PHP {{ number_format(max($baseRate * 0.18, 700), 2) }}</td>
                                            <td class="px-4 py-3.5 font-mono text-[11px] text-secondary">PHP {{ number_format($effectiveRate * 0.05, 2) }}</td>
                                            <td class="px-4 py-3.5"><span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">Active</span></td>
                                            <td class="px-4 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <button class="rounded {{ $selected ? 'bg-secondary text-white' : 'bg-surface-container text-primary hover:bg-secondary-fixed/40' }} px-2 py-1 text-[11px] font-bold transition" type="button">{{ $selected ? 'Inspect' : 'Edit' }}</button>
                                                    <button class="rounded p-1 text-outline transition hover:text-primary" title="Audit History" type="button"><span class="material-symbols-outlined text-base">history</span></button>
                                                    <button class="rounded p-1 text-outline transition hover:text-primary" title="Tariff Restrictions" type="button"><span class="material-symbols-outlined text-base">rule</span></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="px-4 py-16 text-center text-sm font-semibold text-slate-500">No room rate tiers found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <aside class="space-y-4 xl:col-span-4 2xl:col-span-3">
                        <section class="rounded-xl border border-secondary/40 bg-white shadow-sm">
                            <div class="border-b border-surface-container-highest bg-surface-container-low p-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-secondary">Rate Inspector</p>
                                <h3 class="font-serif text-2xl font-bold text-primary">{{ $selectedRoomRate ? Str::headline($selectedRoomRate[0]) : 'No Rate Selected' }}</h3>
                            </div>
                            <div class="space-y-4 p-5 text-sm">
                                <dl class="grid grid-cols-2 gap-3">
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Minimum</dt><dd class="mt-1 font-bold text-primary">{{ $selectedRoomRate[2] ?? 'PHP 0.00' }}</dd></div>
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Maximum</dt><dd class="mt-1 font-bold text-primary">{{ $selectedRoomRate[3] ?? 'PHP 0.00' }}</dd></div>
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Average</dt><dd class="mt-1 font-bold text-primary">{{ $selectedRoomRate[4] ?? 'PHP 0.00' }}</dd></div>
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Inventory</dt><dd class="mt-1 font-bold text-emerald-700">{{ $selectedRoomRate[1] ?? '0 rooms' }}</dd></div>
                                </dl>
                                <div class="rounded-lg border border-surface-container-highest p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-outline">Active Policy Stack</p>
                                    <div class="mt-3 space-y-2 text-xs text-slate-600">
                                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-sm text-secondary">wb_sunny</span>Subic Summer Peak: +15%</p>
                                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-sm text-secondary">calendar_today</span>Weekend multiplier: 1.25x</p>
                                        <p class="flex items-center gap-2"><span class="material-symbols-outlined text-sm text-secondary">account_balance</span>SBMA eco-levy: 5%</p>
                                    </div>
                                </div>
                                <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container" type="button">
                                    <span class="material-symbols-outlined text-base text-secondary-fixed">edit</span>
                                    Edit Selected Rate
                                </button>
                            </div>
                        </section>

                        <section class="rounded-xl border border-surface-container-highest bg-white p-5 shadow-sm">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">shield</span>
                                <h3 class="font-bold text-primary">Tariff Governance</h3>
                            </div>
                            <ul class="space-y-3 text-xs leading-5 text-slate-600">
                                <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-700">check_circle</span>Base tariffs come from encoded room inventory.</li>
                                <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-700">check_circle</span>Seasonal multipliers require admin approval before publishing.</li>
                                <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-700">check_circle</span>SBMA tax estimates are shown for review and reconciliation.</li>
                            </ul>
                        </section>
                    </aside>
                </section>

            </main>
        @elseif ($isRoomTypes)
            <main class="flex min-h-[calc(100vh-64px)] flex-col gap-6 p-8">
                <section class="flex flex-col gap-4 border-b border-surface-container-highest/80 pb-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="mb-1 flex items-center gap-2 text-xs font-semibold text-outline">
                            <span>System Administration</span>
                            <span class="material-symbols-outlined text-xs">chevron_right</span>
                            <span>Room Management</span>
                            <span class="material-symbols-outlined text-xs">chevron_right</span>
                            <span class="font-bold text-secondary">Room Types &amp; Suite Classifications</span>
                        </nav>
                        <h2 class="font-serif text-4xl font-bold tracking-tight text-primary">Room Types &amp; Suite Specifications</h2>
                        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Manage hotel room tiers, bed configurations, standard tariffs, capacity limits, and inventory allocations across Subic Bay property wings.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button id="exportRoomTypesCatalog" class="inline-flex items-center gap-2 rounded-lg border border-surface-container-highest bg-white px-3.5 py-2 text-xs font-bold text-primary shadow-sm transition hover:bg-surface-container-low" type="button">
                            <span class="material-symbols-outlined text-base">download</span>
                            Export Catalog
                        </button>
                        <button id="addRoomTypeButton" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-primary-container" type="button">
                            <span class="material-symbols-outlined text-base text-secondary-fixed">add</span>
                            Add Room Type
                        </button>
                    </div>
                </section>

                @if (session('status'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="relative overflow-hidden rounded-xl border border-surface-container-highest bg-white p-4 shadow-sm">
                            <div class="absolute inset-x-0 top-0 h-1 {{ $loop->index === 0 ? 'bg-secondary' : ($loop->index === 1 ? 'bg-primary' : ($loop->index === 2 ? 'bg-secondary-fixed' : 'bg-primary-container')) }}"></div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-outline">{{ $stat['label'] }}</span>
                                <span class="rounded-lg bg-surface-container-low p-1.5 text-secondary">
                                    <span class="material-symbols-outlined text-lg">{{ ['category', 'key', 'price_check', 'group'][$loop->index] ?? 'hotel' }}</span>
                                </span>
                            </div>
                            <p class="font-serif text-3xl font-bold text-primary">{{ $stat['value'] }}</p>
                            <p class="mt-2 border-t border-surface-container-highest/60 pt-2 font-mono text-[11px] text-slate-500">Live configuration value</p>
                        </div>
                    @endforeach
                </section>

                <section class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-surface-container-highest bg-white p-3 shadow-sm">
                    <div class="flex min-w-[280px] flex-1 flex-wrap items-center gap-2">
                        <div class="relative w-64">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-base text-outline">filter_alt</span>
                            <input id="roomTypeSearch" class="w-full rounded-lg border border-surface-container-highest bg-surface-container-low py-1.5 pl-8 pr-3 text-xs outline-none focus:border-secondary" placeholder="Filter room name or code..." type="search">
                        </div>
                        <div class="relative">
                            <select id="roomTypeWingFilter" class="appearance-none rounded-lg border border-surface-container-highest bg-surface-container-low py-1.5 pl-3 pr-8 text-xs font-semibold text-primary outline-none focus:border-secondary">
                                <option value="all">All Wings (3)</option>
                                @foreach ($roomTypeWings as $wing)
                                    <option value="{{ $wing }}">{{ $wing }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-sm text-outline">expand_more</span>
                        </div>
                        <div class="relative">
                            <select id="roomTypeBedFilter" class="appearance-none rounded-lg border border-surface-container-highest bg-surface-container-low py-1.5 pl-3 pr-8 text-xs font-semibold text-primary outline-none focus:border-secondary">
                                <option value="all">All Bed Types</option>
                                <option value="King Beds">King Beds</option>
                                <option value="Twin Queens">Twin Queens</option>
                                <option value="Suite Layouts">Suite Layouts</option>
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-sm text-outline">expand_more</span>
                        </div>
                        <button id="resetRoomTypeFilters" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-outline transition hover:text-primary" type="button">
                            <span class="material-symbols-outlined text-sm">restart_alt</span>
                            Reset
                        </button>
                    </div>
                    <div class="flex items-center gap-1 rounded-lg border border-surface-container-highest bg-surface-container-low p-1">
                        <button id="roomTypeTableView" class="inline-flex items-center gap-1.5 rounded bg-white px-2.5 py-1 text-xs font-bold text-primary shadow-sm" type="button"><span class="material-symbols-outlined text-sm">table_rows</span>Table View</button>
                        <button id="roomTypeGridView" class="inline-flex items-center gap-1.5 rounded px-2.5 py-1 text-xs font-bold text-slate-500 transition hover:text-primary" type="button"><span class="material-symbols-outlined text-sm">grid_view</span>Grid Cards</button>
                    </div>
                </section>

                <section class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
                    <div id="roomTypeList" class="space-y-4 lg:col-span-8">
                        @forelse ($roomTypeRows as $roomType)
                            @php
                                [$typeName, $inventory, $capacityRange, $averageRate, $availability] = $roomType;
                                $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $typeName), 0, 3) ?: 'RMT') . '-' . str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT);
                                $isSelected = $loop->first;
                                $wing = $roomTypeWings[($loop->index) % count($roomTypeWings)];
                                $officialSpecification = $officialRoomCatalog[$typeName] ?? null;
                                $bedConfiguration = $officialSpecification['bed_configuration'] ?? 'Custom configuration';
                                $bedType = str_contains($bedConfiguration, 'King') ? 'King Beds' : (str_contains($bedConfiguration, 'Queen') ? 'Twin Queens' : 'Suite Layouts');
                                $typeAmenities = $officialSpecification['amenities'] ?? $roomTypeAmenities;
                            @endphp
                            <article data-room-type-card data-type="{{ Str::headline($typeName) }}" data-code="{{ $code }}" data-wing="{{ $wing }}" data-bed="{{ $bedType }}" class="overflow-hidden rounded-xl border {{ $isSelected ? 'border-2 border-secondary/60 shadow-[0_4px_16px_-2px_rgba(23,43,66,0.08),0_1px_3px_rgba(197,163,106,0.12)]' : 'border-surface-container-highest shadow-sm' }} bg-white transition hover:-translate-y-0.5 hover:border-secondary/50">
                                @if ($isSelected)
                                    <div class="h-1.5 bg-gradient-to-r from-secondary via-secondary-fixed to-secondary"></div>
                                @endif
                                <div class="p-5">
                                    <div class="flex flex-col justify-between gap-3 border-b border-surface-container-highest/60 pb-4 sm:flex-row sm:items-start">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2.5">
                                                <h3 class="font-serif text-2xl font-bold text-primary">{{ Str::headline($typeName) }}</h3>
                                                <span class="rounded border {{ $isSelected ? 'border-secondary/30 bg-secondary/15 text-secondary' : 'border-surface-container-highest bg-surface-container text-slate-600' }} px-2 py-0.5 font-mono text-[11px] font-bold">{{ $code }}</span>
                                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>Active - Booking Engine</span>
                                            </div>
                                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-500">
                                                <span class="flex items-center gap-1 text-primary"><span class="material-symbols-outlined text-sm text-secondary">domain</span>{{ $wing }}</span>
                                                <span class="hidden sm:inline">-</span>
                                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-secondary">vpn_key</span>{{ $inventory }}</span>
                                                <span class="hidden sm:inline">-</span>
                                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-secondary">square_foot</span>{{ $officialSpecification ? number_format($officialSpecification['size_sqm']) . ' sq.m.' : 'Custom size' }}</span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-left sm:text-right">
                                            <span class="block text-[11px] font-bold uppercase tracking-wider text-outline">Base Nightly Tariff</span>
                                            <span class="font-mono text-lg font-bold text-primary">{{ $averageRate }}</span>
                                            <span class="mt-0.5 block rounded border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 font-mono text-[10px] text-emerald-700">{{ $availability }}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 border-b border-surface-container-highest/50 py-3.5 text-xs md:grid-cols-2">
                                        <div class="space-y-1.5">
                                            <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-outline">king_bed</span><strong class="text-primary">Bedding:</strong><span class="text-slate-600">{{ $bedConfiguration }}</span></p>
                                            <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-outline">person</span><strong class="text-primary">Max Occupancy:</strong><span class="text-slate-600">{{ $capacityRange }}</span></p>
                                        </div>
                                        <div class="space-y-1.5">
                                            <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-outline">bathtub</span><strong class="text-primary">Bathrooms:</strong><span class="text-slate-600">{{ $officialSpecification['bathrooms'] ?? 'Custom' }}</span></p>
                                            <p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-outline">schedule</span><strong class="text-primary">Stay Policy:</strong><span class="text-slate-600">{{ config('hotel.check_in_time') }} in / {{ config('hotel.check_out_time') }} out</span></p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @foreach ($typeAmenities as $amenity)
                                                <span class="rounded border border-surface-container-highest bg-surface-container-low px-2 py-0.5 text-[11px] text-slate-600">{{ $amenity }}</span>
                                            @endforeach
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button data-room-type-action="edit" data-type="{{ Str::headline($typeName) }}" class="rounded px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-surface-container-low" type="button">Edit Type</button>
                                            <a class="rounded border border-secondary/40 px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-secondary-fixed/20" href="{{ route('admin.operations.index', ['module' => 'room-rates']) }}">Manage Tariffs</a>
                                            <a class="rounded bg-primary px-3 py-1.5 text-xs font-bold text-white transition hover:bg-primary-container" href="{{ route('admin.operations.index', ['module' => 'room-inventory']) }}">View Units</a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-surface-container-highest bg-white p-12 text-center text-sm font-semibold text-slate-500">No room types have been configured yet.</div>
                        @endforelse
                    </div>

                    <aside class="space-y-4 lg:col-span-4">
                        <section class="rounded-xl border border-secondary/40 bg-white shadow-sm">
                            <div class="border-b border-surface-container-highest bg-surface-container-low p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-secondary">Quick Inspector</p>
                                        <h3 class="font-serif text-2xl font-bold text-primary">{{ $selectedRoomType ? Str::headline($selectedRoomType[0]) : 'No Type Selected' }}</h3>
                                    </div>
                                    <span class="material-symbols-outlined text-secondary">bed</span>
                                </div>
                            </div>
                            <div class="space-y-4 p-5 text-sm">
                                <dl class="grid grid-cols-2 gap-3">
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Inventory</dt><dd class="mt-1 font-bold text-primary">{{ $selectedRoomType[1] ?? '0 rooms' }}</dd></div>
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Capacity</dt><dd class="mt-1 font-bold text-primary">{{ $selectedRoomType[2] ?? '0 guests' }}</dd></div>
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Avg. Tariff</dt><dd class="mt-1 font-bold text-primary">{{ $selectedRoomType[3] ?? 'PHP 0.00' }}</dd></div>
                                    <div class="rounded-lg bg-surface-container-low p-3"><dt class="text-xs font-bold uppercase text-outline">Readiness</dt><dd class="mt-1 font-bold text-emerald-700">{{ $selectedRoomType[4] ?? '0 available' }}</dd></div>
                                </dl>
                                <div class="rounded-lg border border-surface-container-highest p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-outline">Specification Completeness</p>
                                    <div class="mt-3 h-2 rounded-full bg-surface-container-highest"><div class="h-2 rounded-full bg-secondary" style="width: 86%"></div></div>
                                    <p class="mt-2 text-xs text-slate-500">Amenities, capacity, base rate, and inventory mapping are ready for administrative review.</p>
                                </div>
                                <button data-room-type-action="edit" data-type="{{ $selectedRoomType ? Str::headline($selectedRoomType[0]) : '' }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary-container" type="button">
                                    <span class="material-symbols-outlined text-base text-secondary-fixed">edit</span>
                                    Open Type Editor
                                </button>
                            </div>
                        </section>

                        <section class="rounded-xl border border-surface-container-highest bg-white p-5 shadow-sm">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">rule</span>
                                <h3 class="font-bold text-primary">Governance Checklist</h3>
                            </div>
                            <ul class="space-y-3 text-xs leading-5 text-slate-600">
                                <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-700">check_circle</span>Every type must map to at least one encoded room.</li>
                                <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-700">check_circle</span>Rate changes should be reviewed through Room Rates.</li>
                                <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-700">check_circle</span>Capacity limits control reservation guest counts.</li>
                            </ul>
                        </section>
                    </aside>
                </section>

                <div id="roomTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 p-6">
                    <div class="w-full max-w-xl rounded-xl border border-surface-container-highest bg-white shadow-2xl">
                        <div class="flex items-start justify-between gap-4 border-b border-surface-container-highest p-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-secondary">Room Type Action</p>
                                <h3 id="roomTypeModalTitle" class="font-serif text-2xl font-bold text-primary">Add Room Type</h3>
                                <p id="roomTypeModalText" class="mt-1 text-sm text-slate-600">Room types are created by encoding the first room under that category.</p>
                            </div>
                            <button id="closeRoomTypeModal" class="rounded-lg border border-surface-container-highest p-2 text-slate-500 transition hover:bg-surface-container-low hover:text-primary" type="button" aria-label="Close room type dialog">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('rooms.store') }}" class="space-y-4 p-5">
                            @csrf
                            <input type="hidden" name="redirect_to" value="admin.room-types">

                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="block sm:col-span-2">
                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Room Type Name</span>
                                    <input id="roomTypeModalInput" name="room_type" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Example: Premier Bay Suite" required>
                                </label>

                                <label class="block">
                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700">First Room Number</span>
                                    <input name="room_number" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Example: 401" required>
                                </label>

                                <label class="block">
                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Opening Status</span>
                                    <select name="status" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                                        <option value="available">Available</option>
                                        <option value="reserved">Reserved</option>
                                        <option value="occupied">Occupied</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Nightly Rate</span>
                                    <div class="mt-2 flex rounded-lg border border-outline-variant bg-white focus-within:border-secondary focus-within:ring-1 focus-within:ring-secondary">
                                        <span class="flex items-center border-r border-outline-variant px-3 text-xs font-bold text-secondary">PHP</span>
                                        <input name="rate" type="number" step="0.01" min="0" value="0.00" class="w-full border-0 bg-transparent px-3 py-3 text-sm focus:ring-0" required>
                                    </div>
                                </label>

                                <label class="block">
                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Capacity</span>
                                    <input name="capacity" type="number" min="1" value="1" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                                </label>

                                <label class="block sm:col-span-2">
                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Description</span>
                                    <textarea name="description" rows="3" class="mt-2 w-full rounded-lg border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Add bed setup, wing, amenities, or operating notes for this first room unit."></textarea>
                                </label>
                            </div>

                            <div class="rounded-lg border border-secondary/20 bg-secondary/10 p-3 text-xs leading-5 text-slate-600">
                                Saving creates a real room inventory record. The new type will then appear in Room Types, Room Inventory, Room Rates, and Room Status.
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-surface-container-highest pt-4">
                                <button id="cancelRoomTypeModal" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-bold text-primary transition hover:bg-surface-container" type="button">Cancel</button>
                                <a href="{{ route('admin.operations.index', ['module' => 'room-rates']) }}" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary transition hover:bg-secondary/10">Open Tariffs</a>
                                <button class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white transition hover:bg-primary-container" type="submit">Save Room Type</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        @else
            <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 px-8 py-8">
                <section class="flex flex-col justify-between gap-4 border-b border-surface-container-highest pb-5 md:flex-row md:items-end">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500"><span>Administration</span><span class="material-symbols-outlined text-xs">chevron_right</span><span>Operations</span></div>
                        <h2 class="font-serif text-4xl font-semibold tracking-tight">{{ $title }}</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">{{ $subtitle }}</p>
                    </div>
                    <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-[#5b4313]">Administrator-only workspace</span>
                </section>
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="rounded-xl border border-surface-container-highest bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                            <p class="mt-2 font-serif text-3xl font-bold">{{ $stat['value'] }}</p>
                            <p class="mt-2 text-xs text-slate-500">Live database value</p>
                        </div>
                    @endforeach
                </section>
                <section class="overflow-hidden rounded-xl border border-surface-container-highest bg-white shadow-sm">
                    <div class="flex flex-col justify-between gap-3 border-b border-surface-container-highest p-5 md:flex-row md:items-center">
                        <div>
                            <h3 class="font-serif text-2xl font-semibold">Administrative Records</h3>
                            <p class="mt-1 text-sm text-slate-500">These records are displayed from the shared hotel database with administrator-level oversight.</p>
                        </div>
                        <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary hover:bg-secondary/10">Open Audit Logs</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left">
                            <thead>
                                <tr class="border-b border-surface-container-highest bg-surface-container text-xs font-bold uppercase tracking-wider">
                                    @foreach ($headers as $header)
                                        <th class="px-4 py-3">{{ $header }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-right">Admin Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-container-highest">
                                @forelse ($records as $record)
                                    <tr class="hover:bg-surface-container-low">
                                        @foreach ($record as $value)
                                            <td class="px-4 py-4 text-sm">{{ $value }}</td>
                                        @endforeach
                                        <td class="px-4 py-4 text-right"><a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg border border-outline-variant px-3 py-1.5 text-xs font-bold text-primary hover:bg-surface-container">Review</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ count($headers) + 1 }}" class="px-4 py-16 text-center text-sm font-semibold text-slate-500">No {{ strtolower($title) }} records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        @endif
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reportFilters = Array.from(document.querySelectorAll('[data-report-filter]'));
            const reportRows = Array.from(document.querySelectorAll('[data-report-group]'));
            const emptyReportRow = document.getElementById('emptyReportFilterRow');

            if (reportFilters.length && reportRows.length) {
                const activeClasses = ['bg-primary', 'text-white', 'shadow-sm'];
                const idleClasses = ['bg-surface-container', 'text-primary', 'hover:bg-surface-container-high'];

                const setActiveFilter = (activeButton) => {
                    reportFilters.forEach((button) => {
                        const badge = button.querySelector('span');
                        const isActive = button === activeButton;

                        button.classList.toggle(activeClasses[0], isActive);
                        button.classList.toggle(activeClasses[1], isActive);
                        button.classList.toggle(activeClasses[2], isActive);
                        button.classList.toggle(idleClasses[0], !isActive);
                        button.classList.toggle(idleClasses[1], !isActive);
                        button.classList.toggle(idleClasses[2], !isActive);

                        if (badge) {
                            badge.classList.toggle('bg-secondary', isActive);
                            badge.classList.toggle('text-white', isActive);
                            badge.classList.toggle('bg-white', !isActive);
                            badge.classList.toggle('text-primary', !isActive);
                        }
                    });
                };

                reportFilters.forEach((button) => {
                    button.addEventListener('click', () => {
                        const filter = button.dataset.reportFilter;
                        let visibleCount = 0;

                        reportRows.forEach((row) => {
                            const visible = filter === 'all' || row.dataset.reportGroup === filter;
                            row.classList.toggle('hidden', !visible);
                            if (visible) {
                                visibleCount += 1;
                            }
                        });

                        emptyReportRow?.classList.toggle('hidden', visibleCount > 0);
                        setActiveFilter(button);
                    });
                });

                const reportSearch = document.getElementById('reportDirectorySearch');
                const sortReportDirectory = document.getElementById('sortReportDirectory');
                const reportTableBody = reportRows[0]?.parentElement;

                reportSearch?.addEventListener('input', () => {
                    const query = reportSearch.value.trim().toLowerCase();
                    let visibleCount = 0;

                    reportRows.forEach((row) => {
                        const text = `${row.dataset.reportTitle} ${row.dataset.reportCode}`.toLowerCase();
                        const visible = !query || text.includes(query);
                        row.classList.toggle('hidden', !visible);
                        if (visible) {
                            visibleCount += 1;
                        }
                    });

                    emptyReportRow?.classList.toggle('hidden', visibleCount > 0);
                });

                sortReportDirectory?.addEventListener('click', () => {
                    if (!reportTableBody) {
                        return;
                    }

                    const sorted = [...reportRows].sort((a, b) => (a.dataset.reportTitle || '').localeCompare(b.dataset.reportTitle || ''));
                    sorted.forEach((row) => reportTableBody.appendChild(row));
                    if (emptyReportRow) {
                        reportTableBody.appendChild(emptyReportRow);
                    }
                });

                document.querySelectorAll('[data-report-action], [data-report-row-action]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const action = button.dataset.reportAction || button.dataset.reportRowAction;
                        const reportTitle = button.dataset.reportTitle || "Daily Manager's Operational Flash & Revenue Ledger";

                        if (action === 'download') {
                            window.print();
                            return;
                        }

                        if (action === 'preview' || action === 'run') {
                            alert(`${reportTitle} is ready for preview using the current live database values.`);
                            return;
                        }

                        if (action === 'email') {
                            alert('Report email queued for Board and General Manager review.');
                            return;
                        }

                        if (action === 'configure') {
                            document.getElementById('reportCategoryFilters')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return;
                        }

                        if (action === 'history') {
                            window.location.href = @json(route('admin.audit-logs.index'));
                        }
                    });
                });

                document.getElementById('reportFormatButton')?.addEventListener('click', () => {
                    alert('Report export format is set to PDF for this admin session.');
                });

                document.getElementById('scheduleReportRun')?.addEventListener('click', () => {
                    alert('Scheduled report run staged for the next night audit cycle.');
                });

                document.getElementById('generateCustomReport')?.addEventListener('click', () => {
                    alert('Custom report generated from the selected date, scope, currency, and options.');
                });
            }

            const roomTypeCatalog = @json($roomTypeCatalog);
            const roomTypeCards = Array.from(document.querySelectorAll('[data-room-type-card]'));
            const roomTypeList = document.getElementById('roomTypeList');
            const searchInput = document.getElementById('roomTypeSearch');
            const wingFilter = document.getElementById('roomTypeWingFilter');
            const bedFilter = document.getElementById('roomTypeBedFilter');
            const resetButton = document.getElementById('resetRoomTypeFilters');
            const tableButton = document.getElementById('roomTypeTableView');
            const gridButton = document.getElementById('roomTypeGridView');
            const exportButton = document.getElementById('exportRoomTypesCatalog');
            const addButton = document.getElementById('addRoomTypeButton');
            const modal = document.getElementById('roomTypeModal');
            const modalTitle = document.getElementById('roomTypeModalTitle');
            const modalText = document.getElementById('roomTypeModalText');
            const modalInput = document.getElementById('roomTypeModalInput');
            const closeModal = document.getElementById('closeRoomTypeModal');
            const cancelModal = document.getElementById('cancelRoomTypeModal');

            const filterRoomTypes = () => {
                const query = (searchInput?.value || '').trim().toLowerCase();
                const wing = wingFilter?.value || 'all';
                const bed = bedFilter?.value || 'all';

                roomTypeCards.forEach((card) => {
                    const searchable = `${card.dataset.type} ${card.dataset.code}`.toLowerCase();
                    const matchesSearch = !query || searchable.includes(query);
                    const matchesWing = wing === 'all' || card.dataset.wing === wing;
                    const matchesBed = bed === 'all' || card.dataset.bed === bed;

                    card.classList.toggle('hidden', !(matchesSearch && matchesWing && matchesBed));
                });
            };

            const setRoomTypeView = (mode) => {
                if (!roomTypeList) {
                    return;
                }

                const gridMode = mode === 'grid';
                roomTypeList.classList.toggle('space-y-4', !gridMode);
                roomTypeList.classList.toggle('grid', gridMode);
                roomTypeList.classList.toggle('gap-4', gridMode);
                roomTypeList.classList.toggle('xl:grid-cols-2', gridMode);
                tableButton?.classList.toggle('bg-white', !gridMode);
                tableButton?.classList.toggle('text-primary', !gridMode);
                tableButton?.classList.toggle('shadow-sm', !gridMode);
                tableButton?.classList.toggle('text-slate-500', gridMode);
                gridButton?.classList.toggle('bg-white', gridMode);
                gridButton?.classList.toggle('text-primary', gridMode);
                gridButton?.classList.toggle('shadow-sm', gridMode);
                gridButton?.classList.toggle('text-slate-500', !gridMode);
            };

            const openRoomTypeModal = (mode, type = '') => {
                if (!modal || !modalInput || !modalTitle || !modalText) {
                    return;
                }

                const isEdit = mode === 'edit';
                modalTitle.textContent = isEdit ? `Add Unit to ${type || 'Room Type'}` : 'Add Room Type';
                modalText.textContent = isEdit
                    ? 'Add another real room unit under this room type. Inventory, availability, and rates update after saving.'
                    : 'Create a room type by saving its first real room unit.';
                modalInput.value = type || '';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modalInput.focus();
            };

            const hideRoomTypeModal = () => {
                modal?.classList.add('hidden');
                modal?.classList.remove('flex');
            };

            const exportCatalog = () => {
                if (!roomTypeCatalog.length) {
                    alert('No room type records are available to export yet.');
                    return;
                }

                const headings = ['Code', 'Room Type', 'Wing', 'Inventory', 'Capacity', 'Average Rate', 'Availability', 'Bed Type'];
                const rows = roomTypeCatalog.map((item) => [
                    item.code,
                    item.type,
                    item.wing,
                    item.inventory,
                    item.capacity,
                    item.average_rate,
                    item.availability,
                    item.bed_type,
                ]);
                const csv = [headings, ...rows]
                    .map((row) => row.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(','))
                    .join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'le-charme-room-type-catalog.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            };

            searchInput?.addEventListener('input', filterRoomTypes);
            wingFilter?.addEventListener('change', filterRoomTypes);
            bedFilter?.addEventListener('change', filterRoomTypes);
            resetButton?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (wingFilter) wingFilter.value = 'all';
                if (bedFilter) bedFilter.value = 'all';
                filterRoomTypes();
            });
            tableButton?.addEventListener('click', () => setRoomTypeView('table'));
            gridButton?.addEventListener('click', () => setRoomTypeView('grid'));
            exportButton?.addEventListener('click', exportCatalog);
            addButton?.addEventListener('click', () => openRoomTypeModal('add'));
            closeModal?.addEventListener('click', hideRoomTypeModal);
            cancelModal?.addEventListener('click', hideRoomTypeModal);
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    hideRoomTypeModal();
                }
            });
            document.querySelectorAll('[data-room-type-action="edit"]').forEach((button) => {
                button.addEventListener('click', () => openRoomTypeModal('edit', button.dataset.type || ''));
            });
        });
    </script>
</body>
</html>
