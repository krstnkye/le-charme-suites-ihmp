<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Le Charmé Suites IHMP')</title>
    @include('partials.offline-assets')
    <script>tailwind.config = { theme: { extend: { fontFamily: { body: ['Manrope', 'sans-serif'], headline: ['Noto Serif', 'serif'] } } } };</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; line-height: 1; }
        body { font-family: Manrope, sans-serif; }
        .ihms-serif { font-family: 'Noto Serif', serif; }
        .ihms-card { background: #fff; border: 1px solid #e5e2dc; box-shadow: 0 4px 16px -3px rgba(1, 22, 45, .08); }
    </style>
    @stack('styles')
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-[#fcf9f3] text-[#1c1c18] antialiased">
    @php
        $role = auth()->user()?->role;
        $hasFrontDeskSidebar = $role === 'receptionist';
    @endphp

    @if ($hasFrontDeskSidebar)
        @include('partials.front-desk-sidebar')
    @else
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-white/10 bg-[#01162d] p-5 text-white lg:block">
            <a href="{{ route('dashboard') }}" class="block rounded-lg bg-white p-3 shadow-sm">
                @include('partials.brand-logo', ['class' => 'mx-auto h-auto w-full max-w-[190px] object-contain'])
                <span class="mt-1 block text-center text-[10px] font-bold uppercase tracking-[0.16em] text-[#755a28]">Integrated Hotel Management Portal</span>
            </a>
            <nav class="mt-8 space-y-1 text-sm font-semibold">
                <a class="block rounded-lg px-3 py-2.5 hover:bg-[#172b42]" href="{{ route('dashboard') }}">Dashboard</a>
                @if (in_array($role, ['admin', 'receptionist'], true))
                    <a class="block rounded-lg px-3 py-2.5 hover:bg-[#172b42]" href="{{ route('guests.index') }}">Guests</a>
                    <a class="block rounded-lg px-3 py-2.5 hover:bg-[#172b42]" href="{{ route('reservations.index') }}">Reservations</a>
                    <a class="block rounded-lg px-3 py-2.5 hover:bg-[#172b42]" href="{{ route('check-ins.index') }}">Check-ins</a>
                @endif
                @if (in_array($role, ['admin', 'receptionist', 'housekeeping'], true))<a class="block rounded-lg px-3 py-2.5 hover:bg-[#172b42]" href="{{ route('rooms.index') }}">Rooms</a>@endif
                @if (in_array($role, ['admin', 'receptionist', 'cashier'], true))<a class="block rounded-lg px-3 py-2.5 hover:bg-[#172b42]" href="{{ route('check-outs.index') }}">Check-outs</a>@endif
                @if (in_array($role, ['admin', 'cashier', 'owner'], true))<a class="block rounded-lg px-3 py-2.5 hover:bg-[#172b42]" href="{{ route('reports.index') }}">Reports</a>@endif
            </nav>
        </aside>
    @endif

    <div class="lg:ml-64">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-[#e5e2dc] bg-white px-5 py-3 shadow-sm sm:px-8">
            <div class="min-w-0"><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#755a28]">@yield('section_label', 'Integrated Hotel Management Portal')</p><h1 class="ihms-serif truncate text-xl font-semibold text-[#01162d] sm:text-2xl">@yield('page_title', 'Dashboard')</h1></div>
            <div class="flex shrink-0 items-center gap-3">
                <div class="hidden text-right sm:block"><p class="text-xs font-bold text-[#01162d]">{{ auth()->user()?->name }}</p><p id="ihms-layout-clock" class="text-[11px] text-slate-500">{{ now(config('app.timezone'))->format('M d, Y • h:i A') }}</p></div>
                <a href="{{ route('profile.edit') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#fdd79a] text-xs font-bold text-[#271900]" aria-label="Open my profile">{{ strtoupper(substr(auth()->user()?->name ?? 'ST', 0, 1)) }}</a>
                @unless ($hasFrontDeskSidebar)<form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg border border-[#c4c6cd] px-3 py-2 text-xs font-bold text-[#01162d] hover:bg-[#f6f3ed]">Logout</button></form>@endunless
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
