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

<aside data-universal-sidebar class="lc-sidebar w-72" aria-label="Billing Sidebar">
    <div class="lc-sidebar-head">
        <a href="{{ route('dashboard') }}" class="lc-brand">
            @include('partials.brand-logo', ['class' => 'w-full'])
            <span class="lc-brand-scope"><strong>Billing</strong> · Register 02</span>
        </a>
    </div>

    <nav class="lc-sidebar-nav" aria-label="Billing Navigation">
        <div class="lc-nav-group">
            @foreach ($billingNav as $item)
                <a href="{{ $item['href'] }}" @class(['lc-nav-link', 'is-active' => $active === $item['key']]) @if ($active === $item['key']) aria-current="page" @endif>
                    <span class="material-symbols-outlined" aria-hidden="true">{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <div class="lc-sidebar-foot">
        <div class="lc-sidebar-user">
            <span class="lc-avatar">{{ $initials }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ $billingSidebarUser?->name ?? 'Billing Staff' }}</p>
                <p class="text-xs text-[#5b6069]">Billing Department Access</p>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" @class(['lc-nav-link', 'is-active' => $active === 'profile']) @if ($active === 'profile') aria-current="page" @endif>
            <span class="material-symbols-outlined" aria-hidden="true">account_circle</span>
            My Profile
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="lc-nav-link"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Logout</button>
        </form>
    </div>
</aside>

@include('partials.universal-sidebar-controls')
