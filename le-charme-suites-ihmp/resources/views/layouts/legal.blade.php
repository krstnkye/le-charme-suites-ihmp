<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Le Charmé Suites IHMP</title>
    @include('partials.offline-assets')
    @include('partials.favicon')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { body: ['Manrope', 'sans-serif'], display: ['Noto Serif', 'serif'] },
                },
            },
        };
    </script>
</head>
<body class="flex min-h-screen flex-col bg-[#f8f6f0] font-body text-slate-800 antialiased">
    <a href="#main-content" class="sr-only z-[60] rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#112235] shadow focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to main content</a>
    <div class="border-b border-amber-300 bg-amber-50 px-4 py-2 text-center text-[11px] font-semibold leading-5 text-amber-900" role="note">
        <span class="material-symbols-outlined align-middle text-sm" aria-hidden="true">school</span>
        {{ config('integrations.academic_notice') }}
    </div>
    <header class="ihms-focus-light border-b border-white/10 bg-[#0c1c2e] text-white shadow-sm">
        <div class="mx-auto flex min-h-20 max-w-5xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            @php($homeUrl = auth()->check() ? route('dashboard') : route('login'))
            <a href="{{ $homeUrl }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-14 w-32 shrink-0 items-center justify-center rounded-lg bg-white px-3 shadow-sm">
                    <img src="{{ asset('images/le-charme-logo-white.png') }}" alt="Le Charmé Suites home" class="h-11 w-full object-contain">
                </span>
                <span class="hidden truncate font-display text-xl font-bold sm:block">Le Charmé Suites Subic</span>
            </a>
            <nav class="flex shrink-0 items-center gap-2" aria-label="Page actions">
                @auth
                    <button type="button" data-legal-back data-fallback-url="{{ $homeUrl }}" class="inline-flex items-center gap-1.5 rounded-lg border border-white/25 px-3 py-2 text-sm font-bold transition hover:border-white/50 hover:bg-white/10" aria-label="Go back to the previous page">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                        Back
                    </button>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-white/25 px-3 py-2 text-sm font-bold transition hover:border-white/50 hover:bg-white/10" aria-label="Back to the login page">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                        Back to Login
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main-content" class="flex-1">
        <article class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:py-14">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a5d26]">Policies</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-[#112235] sm:text-4xl">@yield('title')</h1>
            <p class="mt-2 text-sm text-slate-600">Last updated {{ \Illuminate\Support\Carbon::parse('2026-10-24')->format('F j, Y') }}</p>

            <div class="legal-copy mt-8 space-y-6 rounded-xl border border-slate-200 bg-white p-6 text-sm leading-7 text-slate-700 shadow-sm sm:p-8">
                @yield('content')
            </div>

            <nav class="mt-8 flex flex-wrap gap-x-5 gap-y-2 text-sm" aria-label="Other policies">
                @include('partials.legal-links', ['linkClass' => 'font-semibold text-[#7a5d26] underline-offset-4 hover:underline'])
            </nav>
        </article>
    </main>

    <footer class="ihms-focus-light border-t border-white/10 bg-[#0c1c2e] py-6 text-xs text-slate-300">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-3 px-4 text-center sm:px-6 md:flex-row md:text-left">
            <span><span class="font-display font-bold text-white">Le Charmé Suites IHMP</span> · Academic mini thesis prototype</span>
            <a href="mailto:{{ config('hotel.email') }}" class="transition hover:text-white">{{ config('hotel.email') }}</a>
        </div>
    </footer>

    @include('partials.cookie-notice')
    <script>
        document.querySelector('[data-legal-back]')?.addEventListener('click', (event) => {
            const cameFromThisPortal = document.referrer.startsWith(window.location.origin);

            if (cameFromThisPortal && window.history.length > 1) {
                window.history.back();
            } else {
                window.location.assign(event.currentTarget.dataset.fallbackUrl);
            }
        });
    </script>
</body>
</html>
