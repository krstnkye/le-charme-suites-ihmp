<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Housekeeping Workspace - Le Charme Suites IHMP')</title>
    @include('partials.offline-assets')
    <script>tailwind.config={theme:{extend:{colors:{primary:'#161a20',secondary:'#5b6069',cream:'#f5f5f2',line:'#e4e4df'},fontFamily:{sans:['Manrope','sans-serif'],serif:['Manrope', 'sans-serif']}}}};</script>
    <style>.material-symbols-outlined{display:inline-block;vertical-align:middle;line-height:1;font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}</style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-cream font-sans text-primary antialiased">
    @include('partials.housekeeping-sidebar', ['active' => $active ?? 'dashboard'])
    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-cream px-6 py-3">
            <div><h1 class="text-base font-semibold">Housekeeping Workspace</h1><p class="text-xs text-[#5b6069]">Le Charmé Suites · Rooms &amp; facilities</p></div>
            <div class="flex items-center gap-4">
                <p class="hidden text-right text-xs text-[#5b6069] md:block" aria-live="polite">
                    <span id="housekeeping-live-date">{{ now('Asia/Manila')->format('M d, Y') }}</span>
                    <span class="ml-1 font-semibold tabular-nums text-primary" id="housekeeping-live-time">{{ now('Asia/Manila')->format('h:i:s A') }}</span>
                    <span>PHT</span>
                </p>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-1.5 py-1 hover:bg-[#e9e9e5]">
                    <span class="lc-avatar">{{ strtoupper(substr(auth()->user()?->name ?? 'H', 0, 1)) }}</span>
                    <span class="hidden text-left sm:block"><span class="block text-sm font-semibold leading-tight">{{ auth()->user()?->name }}</span><span class="block text-xs text-[#5b6069]">Housekeeping access</span></span>
                </a>
            </div>
        </header>
        <main class="mx-auto flex w-full max-w-[1500px] flex-col gap-6 px-6 py-8">
            @if (session('status'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
    <script>
        (() => {
            const dateTarget = document.getElementById('housekeeping-live-date');
            const timeTarget = document.getElementById('housekeeping-live-time');

            if (!dateTarget || !timeTarget) {
                return;
            }

            const timezone = 'Asia/Manila';
            const dateFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: timezone,
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                weekday: 'short',
            });
            const timeFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: timezone,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });

            const updateHousekeepingClock = () => {
                const now = new Date();
                dateTarget.textContent = dateFormatter.format(now);
                timeTarget.textContent = timeFormatter.format(now);
            };

            updateHousekeepingClock();
            window.setInterval(updateHousekeepingClock, 1000);
        })();
    </script>
</body>
</html>
