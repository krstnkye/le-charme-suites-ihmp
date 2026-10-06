<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hotel Management System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    @include('partials.favicon')
</head>
<body class="bg-slate-100 text-slate-900">
    @php($role = auth()->user()?->role)
    <div class="min-h-screen">
        <aside class="fixed inset-y-0 left-0 hidden w-64 border-r border-slate-200 bg-white p-5 lg:block">
            <a href="{{ route('dashboard') }}" class="block text-xl font-bold text-slate-950">Hotel HMS</a>
            @if ($role === 'receptionist')
                @php($legacyNavLink = 'block rounded-md px-3 py-2 hover:bg-slate-100')
                @php($legacyNavActive = 'block rounded-md bg-slate-100 px-3 py-2 font-semibold text-slate-950')
                <nav class="mt-8 space-y-1 text-sm font-medium" aria-label="Front Desk Navigation">
                    <a class="{{ request()->routeIs('dashboard') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('dashboard') }}">Front Desk</a>
                    <a class="{{ request()->routeIs('reservations.*') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('reservations.index') }}">Reservations</a>
                    <a class="{{ request()->routeIs('check-ins.*') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('check-ins.index') }}">Check-ins</a>
                    <a class="{{ request()->routeIs('check-outs.create') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('check-outs.create') }}">Check-outs</a>
                    <a class="{{ request()->routeIs('rooms.*') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('rooms.index') }}">Room Status</a>
                    <a class="{{ request()->routeIs('check-outs.index') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('check-outs.index') }}">Transactions</a>
                    <a class="{{ request()->routeIs('guests.*') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('guests.index') }}">Guests</a>
                    <a class="{{ request()->routeIs('profile.*') ? $legacyNavActive : $legacyNavLink }}" href="{{ route('profile.edit') }}">My Profile</a>
                </nav>
            @else
                <nav class="mt-8 space-y-1 text-sm font-medium">
                    <a class="block rounded-md px-3 py-2 hover:bg-slate-100" href="{{ route('dashboard') }}">Dashboard</a>
                    @if (in_array($role, ['admin', 'receptionist'], true))
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-100" href="{{ route('guests.index') }}">Guests</a>
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-100" href="{{ route('reservations.index') }}">Reservations</a>
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-100" href="{{ route('check-ins.index') }}">Check-ins</a>
                    @endif
                    @if (in_array($role, ['admin', 'receptionist', 'housekeeping'], true))
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-100" href="{{ route('rooms.index') }}">Rooms</a>
                    @endif
                    @if (in_array($role, ['admin', 'receptionist', 'cashier'], true))
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-100" href="{{ route('check-outs.index') }}">Check-outs</a>
                    @endif
                    @if (in_array($role, ['admin', 'cashier', 'owner'], true))
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-100" href="{{ route('reports.index') }}">Reports</a>
                    @endif
                </nav>
            @endif
        </aside>

        <div class="lg:pl-64">
            <header class="border-b border-slate-200 bg-white px-6 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Integrated Hotel Management System</p>
                        <h1 class="text-2xl font-bold">@yield('page_title', 'Dashboard')</h1>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50">Logout</button>
                    </form>
                </div>
            </header>

            <main class="p-6">
                @if (session('status'))
                    <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @if (session('account_created'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const promptId = @json(session('account_created_prompt_id'));
                const storageKey = 'ihms-last-account-created-prompt';

                if (sessionStorage.getItem(storageKey) !== promptId) {
                    sessionStorage.setItem(storageKey, promptId);
                    alert('Account created successfully. Welcome to the Integrated Hotel Management System.');
                }
            });
        </script>
    @endif
</body>
</html>
