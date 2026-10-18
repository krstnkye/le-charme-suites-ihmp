@php($currentUser = auth()->user())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integration & Reliability Center - Le Charme Suites IHMS</title>
    @include('partials.offline-assets')
    <script>tailwind.config={theme:{extend:{colors:{primary:'#01162d','primary-container':'#172b42',secondary:'#755a28','secondary-fixed':'#ffdeaa',surface:'#fcf9f3',line:'#e5e2dc'},fontFamily:{sans:['Manrope','sans-serif'],serif:['Noto Serif','serif']}}}};</script>
    <style>.material-symbols-outlined{display:inline-block;vertical-align:middle;line-height:1;font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}</style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-surface font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between gap-4 border-b border-line bg-white px-5 py-3 shadow-sm lg:px-8">
            <div><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">Administration / Development Phase</p><h1 class="font-serif text-xl font-bold">Integration & Reliability Center</h1></div>
            <a href="{{ route('system-manual') }}" class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-sm font-bold"><span class="material-symbols-outlined text-secondary">menu_book</span>Open System Flipbook</a>
        </header>

        <main class="mx-auto w-full max-w-[1600px] space-y-6 p-5 lg:p-8">
            <section class="rounded-xl bg-primary-container p-6 text-white shadow-md">
                <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-center">
                    <div><span class="rounded border border-secondary-fixed/30 bg-white/10 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-secondary-fixed">Autonomous Hotel Node</span><h2 class="mt-3 font-serif text-4xl font-bold">System Integration & Network Reliability</h2><p class="mt-2 max-w-4xl text-sm leading-6 text-blue-100">Verifiable status for shared data, local-first operation, secured API transfer, saved history, and interdepartment communication.</p></div>
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-white/10 bg-white/10 p-4 sm:grid-cols-4">
                        <div><p class="text-[10px] font-bold uppercase text-blue-100/70">Mode</p><p class="mt-1 text-sm font-bold">{{ $networkMode }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-blue-100/70">Database</p><p class="mt-1 text-sm font-bold uppercase">{{ $databaseStatus['driver'] }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-blue-100/70">API</p><p class="mt-1 text-sm font-bold {{ $apiEnabled ? 'text-emerald-300' : 'text-amber-200' }}">{{ $apiEnabled ? 'Enabled' : 'Disabled' }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-blue-100/70">Internet</p><p class="mt-1 text-sm font-bold text-emerald-300">Optional</p></div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($recordCounts as $label => $count)
                    <div class="rounded-lg border border-line bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-2 font-serif text-3xl font-bold">{{ number_format($count) }}</p><p class="mt-1 text-xs text-slate-500">Shared central records</p></div>
                @endforeach
            </section>

            <section class="grid gap-5 xl:grid-cols-3">
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">database</span><h3 class="font-serif text-xl font-bold">Shared Database Health</h3></div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Driver</dt><dd class="font-bold uppercase">{{ $databaseStatus['driver'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">File size</dt><dd class="font-bold">{{ number_format($databaseStatus['size'] / 1024, 1) }} KB</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Writable</dt><dd class="font-bold {{ $databaseStatus['writable'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $databaseStatus['writable'] ? 'Yes' : 'No' }}</dd></div>
                    </dl>
                    <p class="mt-4 break-all rounded bg-slate-50 p-3 font-mono text-[11px] text-slate-500">{{ $databaseStatus['path'] }}</p>
                </div>
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">memory</span><h3 class="font-serif text-xl font-bold">Lightweight Runtime</h3></div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">Current PHP memory</dt><dd class="font-bold">{{ number_format($memoryUsage / 1048576, 1) }} MB</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Configured limit</dt><dd class="font-bold">{{ $memoryLimit }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Frontend build server</dt><dd class="font-bold text-emerald-700">Not required</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">External CDN</dt><dd class="font-bold text-emerald-700">None</dd></div>
                    </dl>
                </div>
                <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary">lan</span><h3 class="font-serif text-xl font-bold">Inter / Intranet Operation</h3></div>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600"><p><strong class="text-primary">Intranet:</strong> departments access one host over private Wi-Fi/LAN and share one database.</p><p><strong class="text-primary">Internet:</strong> optional for Git updates or approved external integrations.</p><p><strong class="text-primary">Slow link:</strong> local assets and capped API payloads reduce bandwidth use.</p></div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-line px-5 py-4 lg:flex-row lg:items-center"><div><h3 class="font-serif text-2xl font-bold">Platform File Transfer</h3><p class="text-sm text-slate-500">Export portable, server-generated datasets. Every transfer receives a SHA-256 checksum and saved audit event.</p></div><a href="{{ route('admin.integration.transfer-report') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white"><span class="material-symbols-outlined text-secondary-fixed">description</span>Download Transfer Report</a></div>
                <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($datasets as $dataset)
                        <div class="rounded-lg border border-line bg-[#fcf9f3] p-4"><div class="flex items-center justify-between"><strong class="capitalize">{{ $dataset }}</strong><span class="material-symbols-outlined text-secondary">dataset</span></div><div class="mt-4 grid grid-cols-2 gap-2"><a href="{{ route('admin.integration.export', [$dataset, 'csv']) }}" class="rounded border border-line bg-white px-3 py-2 text-center text-xs font-bold">CSV</a><a href="{{ route('admin.integration.export', [$dataset, 'json']) }}" class="rounded bg-primary px-3 py-2 text-center text-xs font-bold text-white">JSON</a></div></div>
                    @endforeach
                </div>
            </section>

            <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_520px]">
                <div class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                    <div class="border-b border-line px-5 py-4"><h3 class="font-serif text-2xl font-bold">Saved Transfer & Messaging History</h3><p class="text-sm text-slate-500">Latest integration events from the shared database.</p></div>
                    <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-[#f0eee8] text-[11px] font-bold uppercase text-slate-600"><tr><th class="px-4 py-3">Time</th><th class="px-4 py-3">Channel</th><th class="px-4 py-3">Dataset</th><th class="px-4 py-3">Records</th><th class="px-4 py-3">Checksum</th></tr></thead><tbody class="divide-y divide-line">@forelse($events as $event)<tr><td class="px-4 py-3 font-mono text-xs">{{ $event->occurred_at?->timezone(config('app.timezone'))->format('M d, h:i A') }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($event->channel)) }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($event->dataset)) }}</td><td class="px-4 py-3">{{ $event->record_count }}</td><td class="max-w-[180px] truncate px-4 py-3 font-mono text-[11px] text-slate-500">{{ $event->checksum ?? 'N/A' }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">No transfer events yet. Export a dataset to create the first audited record.</td></tr>@endforelse</tbody></table></div>
                </div>
                <div class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                    <div class="border-b border-line px-5 py-4"><h3 class="font-serif text-2xl font-bold">Embedded System Flipbook</h3><p class="text-sm text-slate-500">Local, presentation-ready integration guide.</p></div>
                    <iframe class="h-[520px] w-full" src="{{ route('system-manual', ['embed' => 1]) }}" title="IHMS system integration flipbook"></iframe>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
