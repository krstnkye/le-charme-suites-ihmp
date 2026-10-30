<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Le Charmé Suites IHMP')</title>
    @include('partials.offline-assets')
    <script>tailwind.config = { theme: { extend: { fontFamily: { body: ['Manrope', 'sans-serif'], headline: ['Manrope', 'sans-serif'] } } } };</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; line-height: 1; }
        body { font-family: Manrope, sans-serif; }
        .ihms-serif { font-family: Manrope, sans-serif; }
        .ihms-card { background: #fff; border: 1px solid #e4e4df; border-radius: 10px; }
    </style>
    @stack('styles')
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-[#f5f5f2] text-[#161a20] antialiased">
    @php
        $role = auth()->user()?->role;
        $hasFrontDeskSidebar = $role === 'receptionist';
    @endphp

    @if ($hasFrontDeskSidebar)
        @include('partials.front-desk-sidebar')
    @else
        <aside data-lc-shell class="lc-sidebar hidden w-64 lg:flex" aria-label="Portal Sidebar">
            <div class="lc-sidebar-head">
                <a href="{{ route('dashboard') }}" class="lc-brand">
                    @include('partials.brand-logo', ['class' => 'w-full'])
                    <span class="lc-brand-scope"><strong>Hotel management portal</strong></span>
                </a>
            </div>
            <nav class="lc-sidebar-nav" aria-label="Portal navigation">
                <div class="lc-nav-group">
                    <a @class(['lc-nav-link', 'is-active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}"><span class="material-symbols-outlined" aria-hidden="true">dashboard</span>Dashboard</a>
                    @if (in_array($role, ['admin', 'receptionist'], true))
                        <a @class(['lc-nav-link', 'is-active' => request()->routeIs('guests.*')]) href="{{ route('guests.index') }}"><span class="material-symbols-outlined" aria-hidden="true">group</span>Guests</a>
                        <a @class(['lc-nav-link', 'is-active' => request()->routeIs('reservations.*')]) href="{{ route('reservations.index') }}"><span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>Reservations</a>
                        <a @class(['lc-nav-link', 'is-active' => request()->routeIs('check-ins.*')]) href="{{ route('check-ins.index') }}"><span class="material-symbols-outlined" aria-hidden="true">login</span>Check-ins</a>
                    @endif
                    @if (in_array($role, ['admin', 'receptionist', 'housekeeping'], true))<a @class(['lc-nav-link', 'is-active' => request()->routeIs('rooms.*')]) href="{{ route('rooms.index') }}"><span class="material-symbols-outlined" aria-hidden="true">bed</span>Rooms</a>@endif
                    @if (in_array($role, ['admin', 'receptionist', 'cashier'], true))<a @class(['lc-nav-link', 'is-active' => request()->routeIs('check-outs.*')]) href="{{ route('check-outs.index') }}"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Check-outs</a>@endif
                    @if (in_array($role, ['admin', 'cashier', 'owner'], true))<a @class(['lc-nav-link', 'is-active' => request()->routeIs('reports.*')]) href="{{ route('reports.index') }}"><span class="material-symbols-outlined" aria-hidden="true">monitoring</span>Reports</a>@endif
                </div>
            </nav>
        </aside>
    @endif

    <div class="lg:ml-64">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-[#e4e4df] bg-[#f5f5f2] px-5 py-3 sm:px-8">
            <div class="min-w-0"><p class="text-xs text-[#5b6069]">@yield('section_label', 'Integrated Hotel Management Portal')</p><h1 class="truncate text-lg font-semibold text-[#161a20]">@yield('page_title', 'Dashboard')</h1></div>
            <div class="flex shrink-0 items-center gap-3">
                <div class="hidden text-right sm:block"><p class="text-sm font-semibold text-[#161a20]">{{ auth()->user()?->name }}</p><p id="ihms-layout-clock" class="text-xs tabular-nums text-[#5b6069]">{{ now(config('app.timezone'))->format('M d, Y • h:i A') }}</p></div>
                <a href="{{ route('profile.edit') }}" class="lc-avatar h-9 w-9" aria-label="Open my profile">{{ strtoupper(substr(auth()->user()?->name ?? 'ST', 0, 1)) }}</a>
                @unless ($hasFrontDeskSidebar)<form method="POST" action="{{ route('logout') }}">@csrf<button class="lc-btn lc-btn-quiet">Logout</button></form>@endunless
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1600px] p-5 sm:p-8">
            @if (session('status'))<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><p class="font-bold">Please check the highlighted information.</p><ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>

    @if (session('account_created'))<script>window.addEventListener('DOMContentLoaded', () => { const promptId = @json(session('account_created_prompt_id')); const storageKey = 'ihms-last-account-created-prompt'; if (sessionStorage.getItem(storageKey) !== promptId) { sessionStorage.setItem(storageKey, promptId); alert('Account created successfully. Welcome to the Integrated Hotel Management Portal.'); } });</script>@endif
    <script>(() => { const clock = document.getElementById('ihms-layout-clock'); if (!clock) return; const refresh = () => { clock.textContent = new Intl.DateTimeFormat('en-US', { timeZone: @json(config('app.timezone')), month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date()).replace(',', ' •'); }; refresh(); setInterval(refresh, 30000); })();</script>
    @stack('scripts')
    @include('partials.page-back-button')
</body>
</html>
