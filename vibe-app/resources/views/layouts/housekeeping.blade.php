<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Housekeeping Workspace - Le Charme Suites IHMS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#01162d',secondary:'#755a28',cream:'#fcf9f3',line:'#e5e2dc'},fontFamily:{sans:['Manrope','sans-serif'],serif:['Noto Serif','serif']}}}};</script>
    <style>.material-symbols-outlined{display:inline-block;vertical-align:middle;line-height:1;font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}</style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-cream font-sans text-primary antialiased">
    @include('partials.housekeeping-sidebar', ['active' => $active ?? 'dashboard'])
    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-cream/95 px-6 py-3 shadow-sm backdrop-blur">
            <div class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary">cleaning_services</span><div><h1 class="font-serif text-xl font-bold">Le Charme Suites IHMS - Housekeeping Workspace</h1><p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-secondary">Rooms &amp; Facilities Operations</p></div></div>
            <div class="flex items-center gap-3">
                <div class="hidden rounded-lg border border-line bg-white/80 px-3 py-2 text-right shadow-sm md:block" aria-live="polite">
                    <p id="housekeeping-live-date" class="text-[11px] font-bold uppercase tracking-[0.14em] text-secondary">{{ now('Asia/Manila')->format('M d, Y') }}</p>
                    <p class="flex items-center justify-end gap-1.5 font-mono text-sm font-bold text-primary">
                        <span class="material-symbols-outlined text-sm text-secondary">schedule</span>
                        <span id="housekeeping-live-time">{{ now('Asia/Manila')->format('h:i:s A') }}</span>
                        <span class="text-[10px] font-sans font-bold text-slate-500">PHT</span>
                    </p>
                </div>
                <div class="hidden text-right sm:block"><p class="text-xs font-bold">{{ auth()->user()?->name }}</p><p class="text-[11px] text-slate-500">Housekeeping Access</p></div><a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#e6c185] bg-primary text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()?->name ?? 'H', 0, 1)) }}</a>
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
