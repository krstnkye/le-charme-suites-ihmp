@php
    $user = auth()->user();
    $billingNav = [
        ['label' => 'Dashboard', 'icon' => 'home', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        ['label' => 'Billing', 'icon' => 'payments', 'href' => route('check-outs.create'), 'active' => request()->routeIs('check-outs.create')],
        ['label' => 'Transactions', 'icon' => 'receipt_long', 'href' => route('check-outs.index'), 'active' => request()->routeIs('check-outs.index')],
        ['label' => 'Transaction History', 'icon' => 'list_alt', 'href' => route('check-outs.index') . '#transaction-history', 'active' => false],
        ['label' => 'Reports', 'icon' => 'query_stats', 'href' => route('reports.index'), 'active' => request()->routeIs('reports.*')],
        ['label' => 'My Profile', 'icon' => 'manage_accounts', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
    ];
    $paymentClasses = [
        'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'partial' => 'border-amber-200 bg-amber-50 text-amber-800',
        'unpaid' => 'border-rose-200 bg-rose-50 text-rose-800',
        'recorded' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];
    $totalOpenExposure = $activeCheckIns->sum(fn ($checkIn) => (float) ($checkIn->room?->rate ?? 0));
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Dashboard - Le Charme Suites IHMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        secondary: '#8a6426',
                        cream: '#fcf9f3',
                        panel: '#f6f3ed',
                        line: '#e5e2dc',
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
            font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-cream font-sans text-primary antialiased">
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-white/10 bg-primary text-white shadow-xl lg:flex">
        <div class="border-b border-white/10 p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-secondary/40 bg-white/10 text-amber-200">
                    <span class="material-symbols-outlined text-2xl">payments</span>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-amber-200">Le Charmé Suites</p>
                    <h1 class="font-serif text-2xl font-bold leading-tight">IHMS</h1>
                    <p class="text-xs text-blue-100/70">Billing Department</p>
                </div>
            </div>
        </div>

        <div class="px-5 py-5">
            <a href="{{ route('check-outs.create') }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-amber-700">
                <span class="material-symbols-outlined text-lg">add_card</span>
                Process Settlement
            </a>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto px-4 pb-6 text-sm font-semibold" aria-label="Billing Navigation">
            @foreach ($billingNav as $item)
                <a href="{{ $item['href'] }}" @class([
                    'flex items-center gap-3 rounded-r-lg border-l-4 px-3 py-3 transition',
                    'border-amber-200 bg-white/10 text-white' => $item['active'],
                    'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white' => ! $item['active'],
                ])>
                    <span class="material-symbols-outlined text-xl">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-r-lg border-l-4 border-transparent px-3 py-3 text-left text-blue-100/70 transition hover:bg-white/10 hover:text-white">
                    <span class="material-symbols-outlined text-xl">logout</span>
                    Logout
                </button>
            </form>
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="mb-3 rounded-lg bg-white/10 p-3">
                <p class="text-sm font-bold">{{ $user?->name ?? 'Billing Staff' }}</p>
                <p class="text-xs text-blue-100/70">Billing Department Access</p>
            </div>
        </div>
    </aside>

    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-line bg-cream/95 px-5 py-4 shadow-sm backdrop-blur lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Billing Department Workspace</p>
                    <h2 class="font-serif text-2xl font-bold text-primary">Le Charme Suites IHMS - Billing Command Center</h2>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm">
                        <p id="billing-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p>
                        <p id="billing-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary text-sm font-bold text-white ring-2 ring-secondary">
                        {{ strtoupper(substr($user?->name ?? 'B', 0, 1)) }}
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 px-5 py-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="overflow-hidden rounded-xl bg-primary text-white shadow-lg">
                <div class="grid gap-6 p-7 xl:grid-cols-[1.3fr_0.9fr]">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-amber-200">Live Folio Operations</p>
                        <h1 class="mt-3 font-serif text-4xl font-bold leading-tight">Billing Ledger, Settlements & Department Revenue Control</h1>
                        <p class="mt-4 max-w-3xl text-sm leading-6 text-blue-100">Review open stay folios, collect check-out payments, monitor settlement activity, and open reports from a dedicated Billing department dashboard.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 rounded-xl border border-white/10 bg-white/10 p-4">
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-100/70">Open folios</p>
                            <p class="mt-2 text-3xl font-bold">{{ $pendingDepartures }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-100/70">Settled today</p>
                            <p class="mt-2 text-3xl font-bold">{{ $settledToday }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-100/70">Collected</p>
                            <p class="mt-2 text-2xl font-bold">PHP {{ number_format($collectedToday, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-100/70">Departures due</p>
                            <p class="mt-2 text-3xl font-bold">{{ $departuresToday }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Open Folio Exposure', 'value' => 'PHP ' . number_format($totalOpenExposure, 2), 'icon' => 'account_balance_wallet', 'note' => 'Based on active room rates'],
                    ['label' => 'Recorded Revenue Today', 'value' => 'PHP ' . number_format($recordedRevenue, 2), 'icon' => 'monitoring', 'note' => 'Hotel transaction ledger'],
                    ['label' => 'Unpaid Folios', 'value' => $unpaidFolios, 'icon' => 'warning', 'note' => 'Requires settlement review'],
                    ['label' => 'Partial Folios', 'value' => $partialFolios, 'icon' => 'fact_check', 'note' => 'Needs final balance verification'],
                ] as $metric)
                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                                <p class="mt-3 font-serif text-3xl font-bold text-primary">{{ $metric['value'] }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $metric['note'] }}</p>
                            </div>
                            <span class="material-symbols-outlined text-2xl text-secondary">{{ $metric['icon'] }}</span>
                        </div>
                    </div>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.35fr_0.85fr]">
                <div class="rounded-xl border border-line bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line p-5">
                        <div>
                            <h3 class="font-serif text-2xl font-bold">Folio Settlement Queue</h3>
                            <p class="mt-1 text-sm text-slate-600">Active in-house guests waiting for billing review or departure settlement.</p>
                        </div>
                        <a href="{{ route('check-outs.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-900">
                            <span class="material-symbols-outlined text-base">point_of_sale</span>
                            Open Billing Form
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-panel text-xs uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="px-5 py-3">Guest / Folio</th>
                                    <th class="px-5 py-3">Room</th>
                                    <th class="px-5 py-3">Check-Out Date</th>
                                    <th class="px-5 py-3 text-right">Est. Room Charge</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($settlementQueue as $checkIn)
                                    <tr class="hover:bg-panel/60">
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-primary">{{ $checkIn->guest?->full_name ?? 'Guest record unavailable' }}</p>
                                            <p class="text-xs text-slate-500">Folio #{{ str_pad((string) $checkIn->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="rounded bg-panel px-2 py-1 font-bold">{{ $checkIn->room?->room_number ?? 'N/A' }}</span>
                                            <span class="ml-2 text-xs text-slate-500">{{ $checkIn->room?->room_type }}</span>
                                        </td>
                                        <td class="px-5 py-4">{{ $checkIn->reservation?->check_out_date?->format('M d, Y') ?? 'Not set' }}</td>
                                        <td class="px-5 py-4 text-right font-bold">PHP {{ number_format((float) ($checkIn->room?->rate ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center font-semibold text-slate-500">No active folios are waiting for Billing right now.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Department Actions</h3>
                        <div class="mt-4 grid gap-3">
                            <a href="{{ route('check-outs.index') }}" class="flex items-center justify-between rounded-lg border border-line px-4 py-3 font-bold transition hover:border-secondary hover:bg-panel">
                                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">receipt_long</span> Review Folios</span>
                                <span class="material-symbols-outlined text-base">arrow_forward</span>
                            </a>
                            <a href="{{ route('check-outs.create') }}" class="flex items-center justify-between rounded-lg border border-line px-4 py-3 font-bold transition hover:border-secondary hover:bg-panel">
                                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">payments</span> Process Check-Out Payment</span>
                                <span class="material-symbols-outlined text-base">arrow_forward</span>
                            </a>
                            <a href="{{ route('reports.index') }}" class="flex items-center justify-between rounded-lg border border-line px-4 py-3 font-bold transition hover:border-secondary hover:bg-panel">
                                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">query_stats</span> Open Billing Reports</span>
                                <span class="material-symbols-outlined text-base">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                    <div class="rounded-xl bg-primary p-5 text-white shadow-sm">
                        <h3 class="font-serif text-2xl font-bold">Shift Control</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between border-b border-white/10 pb-3"><span class="text-blue-100/70">Cash drawer state</span><strong>Balanced</strong></div>
                            <div class="flex justify-between border-b border-white/10 pb-3"><span class="text-blue-100/70">Audit export</span><strong>Ready</strong></div>
                            <div class="flex justify-between"><span class="text-blue-100/70">Reports access</span><strong class="text-amber-200">Enabled</strong></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-line bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line p-5">
                    <div>
                        <h3 class="font-serif text-2xl font-bold">Recent Billing Ledger Activity</h3>
                        <p class="mt-1 text-sm text-slate-600">Latest recorded hotel transactions and settlement events.</p>
                    </div>
                    <a href="{{ route('reports.index') }}" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary transition hover:bg-secondary hover:text-white">Open Reports</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-panel text-xs uppercase tracking-wider text-slate-600">
                            <tr>
                                <th class="px-5 py-3">Date</th>
                                <th class="px-5 py-3">Guest</th>
                                <th class="px-5 py-3">Type</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($folioActivity as $transaction)
                                <tr class="hover:bg-panel/60">
                                    <td class="px-5 py-4 font-mono text-xs">{{ $transaction->transaction_date?->format('M d, Y h:i A') }}</td>
                                    <td class="px-5 py-4 font-bold">{{ $transaction->guest?->full_name ?? 'N/A' }}</td>
                                    <td class="px-5 py-4">{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $paymentClasses[$transaction->status] ?? $paymentClasses['recorded'] }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right font-bold">PHP {{ number_format($transaction->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center font-semibold text-slate-500">No billing ledger activity has been recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        const dateEl = document.getElementById('billing-date');
        const clockEl = document.getElementById('billing-clock');

        function updateBillingClock() {
            const now = new Date();
            dateEl.textContent = new Intl.DateTimeFormat('en-US', {
                weekday: 'short',
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                timeZone: 'Asia/Manila',
            }).format(now);
            clockEl.textContent = new Intl.DateTimeFormat('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila',
            }).format(now) + ' PHT';
        }

        updateBillingClock();
        setInterval(updateBillingClock, 1000);
    </script>
</body>
</html>
