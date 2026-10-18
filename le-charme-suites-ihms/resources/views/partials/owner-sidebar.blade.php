<aside data-universal-sidebar class="fixed left-0 top-0 z-40 flex h-screen w-72 flex-col justify-between border-r border-white/10 bg-primary-container text-white shadow-xl" aria-label="Owner Sidebar">
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="border-b border-white/10 px-5 py-5">
            <a href="{{ route('dashboard') }}" class="block rounded-lg bg-white px-3 py-2 shadow-sm">
                @include('partials.brand-logo', ['class' => 'mx-auto h-auto w-full max-w-[190px] object-contain'])
                <span class="mt-1 block text-center text-[10px] font-bold uppercase tracking-[0.16em] text-secondary">Owner / Management</span>
            </a>
            <div class="mt-4 rounded-lg border border-secondary-fixed/20 bg-primary/40 p-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-sm font-bold text-white ring-2 ring-secondary-fixed/30">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold">{{ $user?->name ?? 'Hotel Owner' }}</p>
                        <p class="text-xs text-secondary-fixed">Executive view-only access</p>
                    </div>
                </div>
            </div>
        </div>

        <nav id="ownerSidebarNav" class="owner-scroll flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto px-3 py-4 text-sm font-semibold" aria-label="Owner Navigation">
            @foreach ($ownerNavGroups as $group)
                <div class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['href'] }}" @class(['flex items-center gap-3 rounded-r-lg border-l-[3px] px-4 py-3 transition', 'border-secondary-fixed bg-primary/60 text-white' => $activeNavKey === $item['key'], 'border-transparent text-blue-100/70 hover:bg-primary/40 hover:text-white' => $activeNavKey !== $item['key']])>
                            <span @class(['material-symbols-outlined text-xl', 'text-secondary-fixed' => $activeNavKey === $item['key']])>{{ $item['icon'] }}</span>
                            <span class="flex-1">{{ $item['label'] }}</span>
                        </a>
                        @if (! empty($item['children']))
                            <div class="ml-12 space-y-1 pb-1">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['href'] }}" @class(['block rounded px-2 py-1.5 text-xs font-semibold transition', 'bg-secondary-fixed/15 text-secondary-fixed' => ($child['key'] ?? null) === $activePage, 'text-blue-100/60 hover:bg-primary/35 hover:text-white' => ($child['key'] ?? null) !== $activePage])>{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach

            <div class="mt-4 rounded-lg border border-white/10 bg-primary/30 p-3 text-xs text-blue-100/80">
                <p class="font-bold text-secondary-fixed">Owner Restrictions</p>
                <p class="mt-1 leading-5">User Management and System Settings are admin-only. Operational record changes remain with departments and administrators.</p>
            </div>
        </nav>
    </div>

    <div class="border-t border-white/10 bg-primary/20 p-3">
        <a href="{{ route('owner.page', ['page' => 'profile']) }}" @class(['flex items-center gap-3 rounded px-3 py-2 transition', 'bg-primary/60 text-white' => $activePage === 'profile', 'text-blue-100/70 hover:bg-primary/40 hover:text-white' => $activePage !== 'profile'])><span @class(['material-symbols-outlined', 'text-secondary-fixed' => $activePage === 'profile'])>account_circle</span><span class="text-sm font-semibold">My Profile</span></a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-3 rounded px-3 py-2 text-left text-rose-100 transition hover:bg-rose-500/20"><span class="material-symbols-outlined text-rose-300">logout</span><span class="text-sm font-semibold">Logout</span></button></form>
    </div>
</aside>

@include('partials.universal-sidebar-controls')
