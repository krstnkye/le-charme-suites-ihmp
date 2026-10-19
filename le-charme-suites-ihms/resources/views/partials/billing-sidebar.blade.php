@php
    $billingSidebarUser = $user ?? $currentUser ?? auth()->user();
    $active = $active ?? match (true) {
        request()->routeIs('dashboard') => 'dashboard',
        request()->routeIs('check-outs.create') => 'billing',
        request()->routeIs('check-outs.history') => 'history',
        request()->routeIs('check-outs.index') => 'transactions',
        request()->routeIs('reports.*') => 'reports',
        request()->routeIs('messages.*') => 'messages',
        request()->routeIs('profile.*') => 'profile',
        default => '',
    };
    $initials = collect(explode(' ', trim($billingSidebarUser?->name ?? 'Billing Staff')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'B';
    $billingNav = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard')],
        ['key' => 'billing', 'label' => 'Billing', 'icon' => 'credit_card', 'href' => route('check-outs.create')],
        ['key' => 'transactions', 'label' => 'Transactions', 'icon' => 'receipt_long', 'href' => route('check-outs.index')],
        ['key' => 'history', 'label' => 'Transaction History', 'icon' => 'history', 'href' => route('check-outs.history')],
        ['key' => 'reports', 'label' => 'Reports', 'icon' => 'bar_chart', 'href' => route('reports.index')],
        ['key' => 'messages', 'label' => 'Messages', 'icon' => 'forum', 'href' => route('messages.index')],
    ];
@endphp

<aside data-universal-sidebar class="fixed left-0 top-0 z-40 flex h-screen w-72 flex-col justify-between border-r border-white/10 bg-[#172b42] text-white shadow-xl" aria-label="Billing Sidebar">
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="border-b border-white/10 px-5 py-4">
            <a href="{{ route('dashboard') }}" class="block rounded-lg bg-white px-3 py-2 shadow-sm">
                @include('partials.brand-logo', ['class' => 'mx-auto h-auto w-full max-w-[190px] object-contain'])
                <span class="mt-1 block text-center text-[10px] font-bold uppercase tracking-[0.16em] text-[#755a28]">IHMP Billing Command</span>
            </a>
        </div>

        <div class="mx-4 my-4 flex items-center gap-3 rounded-lg border border-[#ffdeaa]/20 bg-[#01162d]/40 p-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#755a28] text-sm font-bold text-white ring-2 ring-[#ffdeaa]/30">{{ $initials }}</div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold">{{ $billingSidebarUser?->name ?? 'Billing Staff' }}</p>
                <div class="flex items-center gap-2 text-xs text-[#ffdeaa]"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Billing Department Access</div>
            </div>
        </div>

        <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 text-sm font-semibold" aria-label="Billing Navigation">
            @foreach ($billingNav as $item)
                <a href="{{ $item['href'] }}" @class([
                    'flex items-center gap-3 rounded-r-lg border-l-[3px] px-4 py-3 transition',
                    'border-[#ffdeaa] bg-[#01162d]/60 text-white' => $active === $item['key'],
                    'border-transparent text-blue-100/70 hover:bg-[#01162d]/40 hover:text-white' => $active !== $item['key'],
                ])>
                    <span @class(['material-symbols-outlined text-xl', 'text-[#ffdeaa]' => $active === $item['key']])>{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                    @if ($item['key'] === 'dashboard' && $active === 'dashboard')
                        <span class="ml-auto rounded-full bg-[#ffdeaa]/20 px-2 py-0.5 text-[11px] font-bold text-[#ffdeaa]">Live</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <div class="border-t border-white/10 bg-[#01162d]/20 p-3">
        <div class="mb-2 flex items-center justify-between px-3 py-1.5 text-xs text-blue-100/70"><span>SBMA Station</span><span class="font-bold text-[#ffdeaa]">POS-REG-02</span></div>
        <a href="{{ route('profile.edit') }}" @class([
            'flex items-center gap-3 rounded-r-lg border-l-[3px] px-4 py-3 text-sm font-semibold transition',
            'border-[#ffdeaa] bg-[#01162d]/60 text-white' => $active === 'profile',
            'border-transparent text-blue-100/70 hover:bg-[#01162d]/40 hover:text-white' => $active !== 'profile',
        ])>
            <span @class(['material-symbols-outlined', 'text-[#ffdeaa]' => $active === 'profile'])>account_circle</span>
            My Profile
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="mt-1 flex w-full items-center gap-3 rounded px-4 py-3 text-left text-sm font-semibold text-rose-100 transition hover:bg-rose-500/20"><span class="material-symbols-outlined text-rose-300">logout</span>Logout</button>
        </form>
    </div>
</aside>

@include('partials.universal-sidebar-controls')
