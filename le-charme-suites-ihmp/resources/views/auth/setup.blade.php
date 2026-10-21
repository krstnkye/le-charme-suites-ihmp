<!DOCTYPE html>
<html class="h-full bg-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First-Time Setup - Le Charme Suites IHMP</title>

    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        surface: '#fcf9f3',
                        primary: '#01162d',
                        secondary: '#755a28',
                        'secondary-container': '#fdd79a',
                        'surface-container-lowest': '#ffffff',
                        'outline-variant': '#c4c6cd',
                        'on-surface': '#1c1c18',
                        'on-surface-variant': '#44474d',
                    },
                    fontFamily: {
                        body: ['Manrope', 'sans-serif'],
                        headline: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    @include('partials.favicon')
</head>
<body class="h-full overflow-y-auto bg-primary font-body text-on-surface antialiased">
    <main class="relative flex min-h-screen items-center justify-center px-4 py-10">
        <div class="pointer-events-none absolute inset-0 opacity-10 bg-[radial-gradient(#ffdeaa_1px,transparent_1px)] [background-size:24px_24px]"></div>

        <section class="relative w-full max-w-xl rounded-xl border border-[#e6c185]/40 bg-surface-container-lowest p-8 shadow-2xl sm:p-10">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-4 max-w-sm overflow-hidden rounded-lg bg-white px-5 py-3">
                    @include('partials.brand-logo', ['class' => 'h-auto w-full object-contain'])
                </div>
                <h1 class="font-headline text-2xl font-bold text-primary">First-Time Setup</h1>
                <p class="mt-2 text-sm text-on-surface-variant">No administrator exists yet. Create the first administrator account for this hotel. This page closes permanently once an administrator exists.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('setup.store') }}" class="space-y-4">
                @csrf
                <label class="block text-sm font-bold text-primary">Full name
                    <input name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="mt-1.5 block w-full rounded-lg border border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary">
                </label>
                <label class="block text-sm font-bold text-primary">Work email
                    <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name&#64;{{ config('hotel.staff_email_domain') }}" class="mt-1.5 block w-full rounded-lg border border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary">
                    <span class="mt-1 block text-xs font-normal text-on-surface-variant">Must end in &#64;{{ config('hotel.staff_email_domain') }}.</span>
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-bold text-primary">Password
                        <input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1.5 block w-full rounded-lg border border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary">
                    </label>
                    <label class="block text-sm font-bold text-primary">Confirm password
                        <input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1.5 block w-full rounded-lg border border-outline-variant px-4 py-3 text-sm focus:border-secondary focus:ring-secondary">
                    </label>
                </div>
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-base font-bold text-white hover:bg-[#172b42]">
                    <span class="material-symbols-outlined text-secondary-container">admin_panel_settings</span>Create Administrator
                </button>
            </form>
        </section>
    </main>
</body>
</html>
