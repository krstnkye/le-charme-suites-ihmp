<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Charme Suites IHMP System Flipbook</title>
    @include('partials.offline-assets')
    <script>tailwind.config={theme:{extend:{colors:{primary:'#01162d',secondary:'#755a28',paper:'#fcf9f3'},fontFamily:{sans:['Manrope','sans-serif'],serif:['Noto Serif','serif']}}}};</script>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-[#e5e2dc] font-sans text-primary antialiased">
    <main class="mx-auto flex min-h-screen max-w-5xl flex-col p-3 sm:p-6">
        <div class="mb-3 flex items-center justify-between rounded-lg bg-primary px-4 py-3 text-white"><div><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#ffdeaa]">IPT101 Development Phase</p><h1 class="font-serif text-lg font-bold">IHMP Integration Flipbook</h1></div><span id="pageIndicator" class="font-mono text-xs">1 / 10</span></div>
        <div class="relative min-h-[430px] flex-1 overflow-hidden rounded-lg border border-white/50 bg-paper shadow-xl">
            @foreach ([
                ['System Overview', 'Le Charmé Suites IHMP is a role-based, local-first Laravel platform for four operating departments: Administration, Front Desk, Billing, and Housekeeping.', ['One application server', 'One shared hotel database', 'Four department workspaces', 'Internet-independent daily operation']],
                ['Department Functions', 'Each staff account opens the tools required by its assigned department. Management oversight is handled through authorized Administrator reports instead of a separate Owner dashboard.', ['Administration: staff accounts, permissions, audit history, settings, and reports', 'Front Desk: guests, bookings, confirmations, check-in, check-out, and room assignment', 'Billing: guest bills, charges, payments, receipts, and transaction reports', 'Housekeeping: task assignment, cleaning, inspection, maintenance reporting, and room release']],
                ['Shared Business Process', 'A booking becomes one connected hotel record: Front Desk confirms it, assigns a ready room, checks in the guest, Billing records charges and payment, and Housekeeping receives the turnover task after check-out.', ['Online request → Front Desk confirmation', 'Confirmed booking → check-in and room occupancy', 'Guest charges → payment and receipt', 'Check-out → assigned cleaning task → available room']],
                ['Security: Login & Logout', 'Work-email validation, password hashing, session regeneration, role middleware, CSRF protection, and persistent login/logout history protect access.', ['@lecharmehotel.com accounts only', 'Server-side authorization', 'Failed attempts recorded', 'Logout invalidates the session']],
                ['Inter / Intranet Capability', 'The system runs on one hotel host. Other department devices connect through the private LAN, even when the internet connection is unavailable.', ['Internet: optional external connectivity', 'Intranet: primary hotel operating network', 'Local fonts, icons, styles, and scripts', 'One LAN address for all departments']],
                ['Shared Database', 'Front Desk bookings, Housekeeping room status, Billing transactions, and Administrator reports use the same records. Changes become visible across authorized departments.', ['SQLite for the academic deployment', 'Central host prevents duplicate databases', 'Migrations define reproducible structure', 'Audit and message history retained']],
                ['Messaging & History', 'The Department Message Center stores interdepartment notices, priorities, sender identity, timestamps, and individual read history.', ['All-department broadcasts', 'Department targeting', 'Urgent and important priorities', 'Saved read acknowledgements']],
                ['Online Booking & API', 'The public booking form and secured booking API validate dates and capacity, prevent room conflicts, save the guest request, and place it in the Front Desk confirmation queue.', ['Public booking works from the deployed hotel site', 'API key protects system-to-system submissions', 'Booking source and transfer history are recorded', 'Live official-site connection requires hotel authorization and API access']],
                ['API & File Transfer', 'A key-protected API and CSV/JSON exports support controlled movement to another platform.', ['Payload limits for slow links', 'SHA-256 file checksum', 'Transfer event report', 'Sensitive IDs and passwords excluded']],
                ['Reliability Procedure', 'Start the host, verify database health, connect department devices to the LAN address, monitor transfer history, and back up the SQLite file after operations.', ['Run setup-and-run.bat once', 'Use run-shared-lan-server.bat daily', 'Use Integration Center for checks', 'Keep approved backups offline']],
            ] as $index => [$title, $description, $points])
                <article data-page class="absolute inset-0 flex flex-col justify-between p-7 sm:p-10 {{ $index === 0 ? '' : 'hidden' }}">
                    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Chapter {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p><h2 class="mt-3 font-serif text-3xl font-bold sm:text-4xl">{{ $title }}</h2><p class="mt-5 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">{{ $description }}</p><div class="mt-7 grid gap-3 sm:grid-cols-2">@foreach($points as $point)<div class="flex items-start gap-3 rounded-lg border border-[#e5e2dc] bg-white p-4"><span class="material-symbols-outlined text-secondary">check_circle</span><span class="text-sm font-semibold">{{ $point }}</span></div>@endforeach</div></div>
                    <p class="mt-6 border-t border-[#e5e2dc] pt-4 text-xs text-slate-500">Le Charme Suites Integrated Hotel Management Portal · Development Phase Evidence</p>
                </article>
            @endforeach
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3"><button id="previousPage" class="rounded-lg border border-primary bg-white px-4 py-3 text-sm font-bold disabled:opacity-40">Previous</button><button id="nextPage" class="rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white disabled:opacity-40">Next</button></div>
    </main>
    <script>
        const pages = Array.from(document.querySelectorAll('[data-page]'));
        const indicator = document.getElementById('pageIndicator');
        const previous = document.getElementById('previousPage');
        const next = document.getElementById('nextPage');
        let page = 0;
        const render = () => { pages.forEach((item,index) => item.classList.toggle('hidden', index !== page)); indicator.textContent = `${page + 1} / ${pages.length}`; previous.disabled = page === 0; next.disabled = page === pages.length - 1; };
        previous.addEventListener('click', () => { page = Math.max(0, page - 1); render(); });
        next.addEventListener('click', () => { page = Math.min(pages.length - 1, page + 1); render(); });
        document.addEventListener('keydown', event => { if (event.key === 'ArrowLeft') previous.click(); if (event.key === 'ArrowRight') next.click(); });
        render();
    </script>
</body>
</html>
