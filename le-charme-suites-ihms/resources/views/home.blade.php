<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Le Charme Suites IHMP</title>

    @include('partials.offline-assets')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-[#fcf9f3] text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-6 py-12">
        <section class="w-full rounded-lg border border-[#e6c185] bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-[#755a28]">Integrated Hotel Management Portal</p>
            <h1 class="mt-3 text-4xl font-bold text-[#01162d]">Le Charme Suites - Subic Bay</h1>
            <p class="mt-4 max-w-2xl text-slate-600">
                Manage guests, room availability, reservations, check-ins, check-outs, transactions, and reports from one centralized Laravel system.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('login') }}" class="rounded-md bg-[#01162d] px-5 py-3 text-center text-sm font-semibold text-white">Sign In</a>
                <a href="{{ route('register') }}" class="rounded-md border border-[#755a28] px-5 py-3 text-center text-sm font-semibold text-[#755a28]">Create Staff Account</a>
            </div>
        </section>
    </main>
</body>
</html>
