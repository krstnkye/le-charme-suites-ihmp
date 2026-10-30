<aside data-universal-sidebar class="lc-sidebar w-72" aria-label="Owner Sidebar">
    <div class="lc-sidebar-head">
        <a href="{{ route('dashboard') }}" class="lc-brand">
            @include('partials.brand-logo', ['class' => 'w-full'])
            <span class="lc-brand-scope"><strong>Owner / Management</strong> · View only</span>
        </a>
    </div>

    <nav id="ownerSidebarNav" class="owner-scroll lc-sidebar-nav" aria-label="Owner Navigation">
        @foreach ($ownerNavGroups as $group)
            <div class="lc-nav-group">
                @foreach ($group['items'] as $item)
                    <a href="{{ $item['href'] }}" @class(['lc-nav-link', 'is-active' => $activeNavKey === $item['key']]) @if ($activeNavKey === $item['key']) aria-current="page" @endif>
                        <span class="material-symbols-outlined" aria-hidden="true">{{ $item['icon'] }}</span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                    </a>
                    @if (! empty($item['children']))
                        @foreach ($item['children'] as $child)
                            <a href="{{ $child['href'] }}" @class(['lc-nav-sub', 'is-active' => ($child['key'] ?? null) === $activePage])>{{ $child['label'] }}</a>
                        @endforeach
                    @endif
                @endforeach
            </div>
        @endforeach

        <div class="lc-sidebar-note">
            <strong>Owner restrictions</strong>
            User Management and System Settings are admin-only. Operational record changes remain with departments and administrators.
        </div>
    </nav>

    <div class="lc-sidebar-foot">
        <div class="lc-sidebar-user">
            <span class="lc-avatar">{{ $initials }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ $user?->name ?? 'Hotel Owner' }}</p>
                <p class="text-xs text-[#5b6069]">Executive view-only access</p>
            </div>
        </div>
        <a href="{{ route('owner.page', ['page' => 'profile']) }}" @class(['lc-nav-link', 'is-active' => $activePage === 'profile'])><span class="material-symbols-outlined" aria-hidden="true">account_circle</span>My Profile</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="lc-nav-link"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Logout</button></form>
    </div>
</aside>

@include('partials.universal-sidebar-controls')
