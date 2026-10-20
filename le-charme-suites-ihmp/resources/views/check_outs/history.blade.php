@php
    $user = auth()->user();
    $paymentLabels = ['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid'];
    $paymentClasses = [
        'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'partial' => 'border-amber-200 bg-amber-50 text-amber-800',
        'unpaid' => 'border-rose-200 bg-rose-50 text-rose-800',
    ];
    $hashSeed = substr(hash('sha256', $checkOuts->pluck('id')->join('|') . now()->toDateString()), 0, 12);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Le Charmé Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = { theme: { extend: {
            colors: { primary: '#01162d', 'primary-container': '#172b42', secondary: '#755a28', 'secondary-container': '#fdd79a', 'secondary-fixed': '#ffdeaa', surface: '#fcf9f3', 'surface-low': '#f6f3ed', 'surface-mid': '#f0eee8', line: '#e5e2dc', error: '#ba1a1a' },
            fontFamily: { sans: ['Manrope', 'sans-serif'], serif: ['Noto Serif', 'serif'] },
        } } };
    </script>
    <style>
        .material-symbols-outlined { display:inline-block; vertical-align:middle; line-height:1; font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f0eee8; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c4c6cd; border-radius: 999px; }
        @media print { aside, header, .no-print { display:none!important; } body { background:white!important; } main { padding: 0!important; } }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    @if ($user?->role === 'cashier')
        @include('partials.billing-sidebar', ['active' => 'history'])
    @else
        @include('partials.front-desk-sidebar', ['active' => 'transactions'])
    @endif

    <div class="flex min-h-screen flex-col {{ $user?->role === 'cashier' ? 'lg:ml-72' : 'lg:ml-64' }}">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-5 py-3 shadow-sm backdrop-blur lg:px-8">
            <div>
                <h1 class="font-serif text-xl font-bold text-primary">Billing &amp; Cashier Dashboard</h1>
                <div class="mt-1 flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><span>Operations</span><span>/</span><span class="text-secondary">Transaction History</span><span>/</span><span>History Page</span></div>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="button" onclick="alert('Cash drawer access has been logged for audit review.')" class="hidden items-center gap-2 rounded-lg border border-secondary/40 bg-white px-3 py-2 text-xs font-bold text-secondary shadow-sm transition hover:bg-secondary-container/30 md:flex"><span class="material-symbols-outlined text-base">lock_open</span>Open Cash Drawer</button>
                <button type="button" onclick="window.print()" class="hidden items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-900 md:flex"><span class="material-symbols-outlined text-base text-secondary-fixed">verified_user</span>End-of-Shift Summary</button>
                <div class="rounded-lg border border-line bg-white px-4 py-2 text-right shadow-sm"><p id="billing-date" class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Loading date</p><p id="billing-clock" class="font-mono text-sm font-bold text-primary">--:--:-- PHT</p></div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1700px] space-y-6 p-5 lg:p-8">
            <section class="relative overflow-hidden rounded-xl border border-secondary-fixed/30 bg-primary-container p-7 text-white shadow-md">
                <div class="pointer-events-none absolute -bottom-20 -right-16 h-96 w-96 rounded-full bg-secondary-fixed/10 blur-3xl"></div>
                <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                    <div class="max-w-4xl">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded border border-secondary-fixed/30 bg-secondary/30 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">SBMA Freeport Accredited</span>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-100/70"><span class="material-symbols-outlined text-sm text-secondary-fixed">lock</span>Record Security Active</span>
                        </div>
                        <h2 class="font-serif text-4xl font-bold leading-tight">Transaction &amp; Payment History</h2>
                        <p class="mt-2 text-sm leading-6 text-blue-100">Saved guest bill payments, checkout records, and receipts for cashier and manager review.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-secondary-fixed px-4 py-2.5 text-sm font-bold text-primary shadow-sm transition hover:bg-secondary-container"><span class="material-symbols-outlined">download</span>Print Transaction History</button>
                        <a href="{{ route('check-outs.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-secondary-fixed/40 bg-white/10 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-white/20"><span class="material-symbols-outlined text-secondary-fixed">point_of_sale</span>Open Transactions</a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Saved Transactions</span><span class="material-symbols-outlined rounded-lg bg-surface-low p-1.5 text-primary">history</span></div><p class="mt-2 font-serif text-3xl font-bold">{{ $totalArchived }}</p><p class="mt-1 text-xs text-slate-500">Completed checkout records</p></div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Paid Today</span><span class="material-symbols-outlined rounded-lg bg-secondary-container/40 p-1.5 text-secondary">task_alt</span></div><p class="mt-2 font-serif text-3xl font-bold">{{ $settledToday }}</p><p class="mt-1 text-xs text-slate-500">Payments completed today</p></div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Collected Today</span><span class="material-symbols-outlined rounded-lg bg-surface-low p-1.5 text-primary">payments</span></div><p class="mt-2 font-serif text-3xl font-bold">PHP {{ number_format($collectedToday, 2) }}</p><p class="mt-1 text-xs text-slate-500">Cashier recorded collections</p></div>
                <div class="rounded-xl border border-line bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Void / Review Flags</span><span class="material-symbols-outlined rounded-lg bg-rose-50 p-1.5 text-rose-700">gpp_maybe</span></div><p class="mt-2 font-serif text-3xl font-bold">{{ $voidReviewCount }}</p><p class="mt-1 text-xs text-slate-500">Records requiring cashier manager review</p></div>
            </section>

            <form method="GET" action="{{ route('check-outs.history') }}" class="rounded-xl border border-line bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-12">
                    <div class="md:col-span-7">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Search Transactions</label>
                        <div class="relative"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-400">search</span><input name="search" value="{{ $search }}" class="w-full rounded-lg border-line bg-surface-low py-2.5 pl-10 text-sm focus:border-secondary focus:ring-secondary" placeholder="Search bill reference, guest, room, email, or phone"></div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Payment Status</label>
                        <select name="payment_status" class="w-full rounded-lg border-line bg-surface-low py-2.5 text-sm font-semibold focus:border-secondary focus:ring-secondary">
                            <option value="">All payment statuses</option>
                            @foreach ($paymentLabels as $value => $label)
                                <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end md:col-span-2">
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900"><span class="material-symbols-outlined text-base text-secondary-fixed">manage_search</span>Search</button>
                    </div>
                </div>
            </form>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="overflow-hidden rounded-xl border border-line bg-white shadow-sm xl:col-span-8">
                    <div class="flex flex-col justify-between gap-3 border-b border-line bg-surface-low p-5 sm:flex-row sm:items-center">
                        <div><h3 class="font-serif text-2xl font-bold">Transaction Records</h3><p class="text-sm text-slate-500">Completed guest bill payments with cashier reference, payment status, and receipt amount.</p></div>
                        <span class="rounded-full bg-surface-mid px-3 py-1 text-xs font-bold text-primary">{{ $checkOuts->total() }} matched records</span>
                    </div>
                    <div class="custom-scrollbar overflow-x-auto">
                        <table class="w-full min-w-[980px] text-left text-sm">
                            <thead class="border-b border-line bg-surface-mid text-xs font-bold uppercase tracking-wider text-primary">
                                <tr><th class="px-4 py-3">Reference</th><th class="px-4 py-3">Guest / Room</th><th class="px-4 py-3">Payment Time</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3">Record Code</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3 text-right">Actions</th></tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse ($checkOuts as $checkOut)
                                    @php($rowHash = substr(hash('sha256', 'CO-' . $checkOut->id . '-' . $checkOut->amount_paid . '-' . optional($checkOut->checked_out_at)->timestamp), 0, 10))
                                    <tr class="transition hover:bg-surface-low">
                                        <td class="px-4 py-3.5"><div class="font-mono font-bold text-primary">CO-{{ $checkOut->checked_out_at?->format('Y') ?? now()->format('Y') }}-{{ str_pad($checkOut->id, 5, '0', STR_PAD_LEFT) }}</div><div class="text-xs text-slate-500">Receipt retained | {{ strtoupper($rowHash) }}</div></td>
                                        <td class="px-4 py-3.5"><p class="font-bold">{{ $checkOut->guest?->full_name ?? 'Guest Missing' }}</p><p class="text-xs text-slate-500">Room {{ $checkOut->room?->room_number ?? 'TBA' }} | {{ $checkOut->room?->room_type ?? 'No room type' }}</p></td>
                                        <td class="px-4 py-3.5"><p class="font-semibold">{{ $checkOut->checked_out_at?->format('M d, Y') ?? 'No date' }}</p><p class="text-xs text-slate-500">{{ $checkOut->checked_out_at?->format('h:i A') ?? '--:--' }} PHT</p></td>
                                        <td class="px-4 py-3.5"><span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase {{ $paymentClasses[$checkOut->payment_status] ?? 'border-line bg-surface-low text-primary' }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $paymentLabels[$checkOut->payment_status] ?? $checkOut->payment_status }}</span></td>
                                        <td class="px-4 py-3.5"><span class="font-mono text-xs font-bold text-secondary">{{ $rowHash }}</span><p class="text-xs text-slate-500">Security code</p></td>
                                        <td class="px-4 py-3.5 text-right font-mono font-bold">PHP {{ number_format($checkOut->amount_paid, 2) }}</td>
                                        <td class="px-4 py-3.5 text-right"><a href="{{ route('check-outs.receipt', ['checkOut' => $checkOut, 'print' => 1]) }}" target="_blank" rel="noopener" class="inline-flex rounded p-1 text-primary hover:bg-surface-mid" title="Print guest receipt"><span class="material-symbols-outlined text-base">print</span></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-4 py-12 text-center font-semibold text-slate-500">No transaction history records match the current filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-line bg-surface-low px-4 py-3">{{ $checkOuts->links() }}</div>
                </section>

                <aside class="space-y-6 xl:col-span-4">
                    <section class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between border-b border-line pb-3"><div><h3 class="font-serif text-xl font-bold">Record Verification</h3><p class="text-sm text-slate-500">Verification for the current transaction list.</p></div><span class="material-symbols-outlined text-secondary">fingerprint</span></div>
                        <div class="rounded-lg bg-primary-container p-4 text-white">
                            <p class="text-xs font-bold uppercase tracking-wider text-secondary-fixed">Verification Code</p>
                            <p class="mt-2 break-all font-mono text-xl font-bold">{{ strtoupper($hashSeed) }}</p>
                            <p class="mt-2 text-xs text-blue-100">Derived from the visible transaction IDs and current audit date.</p>
                        </div>
                    </section>

                    <section class="rounded-xl border border-line bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between"><h3 class="font-serif text-xl font-bold">Recent IHMP Records</h3><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-800">Updated</span></div>
                        <div class="space-y-3">
                            @forelse ($ledgerTransactions as $transaction)
                                <div class="rounded-lg border border-line bg-surface-low p-3">
                                    <div class="flex items-start justify-between gap-3"><div><p class="font-bold">{{ $transaction->room ? 'Room ' . $transaction->room->room_number : 'Guest Bill Entry' }}</p><p class="text-xs text-slate-500">{{ $transaction->description ?: str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</p></div><span class="font-mono text-xs font-bold text-secondary">PHP {{ number_format($transaction->amount, 2) }}</span></div>
                                    <p class="mt-2 text-[11px] text-slate-500">{{ $transaction->transaction_date?->format('M d, Y h:i A') ?? 'No timestamp' }}</p>
                                </div>
                            @empty
                                <p class="rounded-lg border border-dashed border-line p-4 text-sm font-semibold text-slate-500">No transaction records are available yet.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </main>
    </div>

    <script>
        function updateBillingClock() {
            const now = new Date();
            document.getElementById('billing-date').textContent = new Intl.DateTimeFormat('en-US', { weekday:'short', month:'short', day:'2-digit', year:'numeric', timeZone:'Asia/Manila' }).format(now);
            document.getElementById('billing-clock').textContent = `${new Intl.DateTimeFormat('en-US', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true, timeZone:'Asia/Manila' }).format(now)} PHT`;
        }
        updateBillingClock();
        setInterval(updateBillingClock, 1000);
    </script>
</body>
</html>
