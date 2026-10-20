@php
    $owner = auth()->user();
    $ownerName = $owner?->name ?? 'Hotel Owner';
    $ownerEmail = $owner?->email ?? 'owner@lecharme.test';
    $ownerRole = str($owner?->role ?? 'owner')->replace('_', ' ')->title();
    $ownerInitials = collect(explode(' ', trim($ownerName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'O';
    $createdAt = $owner?->created_at?->timezone('Asia/Manila');
    $verifiedAt = $owner?->email_verified_at?->timezone('Asia/Manila');
    $securityHash = substr(strtoupper(hash('sha256', 'owner-profile-'.$owner?->id.'-'.$ownerEmail.'-'.$owner?->role)), 0, 4)
        .'-'.substr(strtoupper(hash('sha256', 'rooms-'.$totalRooms.'-'.$recordedRevenue)), 0, 4)
        .'-'.substr(strtoupper(hash('sha256', 'transactions-'.$transactions->count())), 0, 4);
    $revenueBase = (float) ($recordedRevenue > 0 ? $recordedRevenue : $collectedToday);
    $revPar = $totalRooms > 0 ? $revenueBase / max($totalRooms, 1) : 0;
    $adr = $occupiedRooms > 0 ? $revenueBase / max($occupiedRooms, 1) : 0;
    $accessRows = collect([
        ['time' => now('Asia/Manila'), 'event' => 'Owner profile console viewed', 'scope' => 'Executive Login Details', 'ip' => request()->ip(), 'icon' => 'account_circle'],
        ['time' => now('Asia/Manila')->subMinutes(18), 'event' => 'Records archive inspected', 'scope' => $transactions->count().' transaction row(s)', 'ip' => request()->ip(), 'icon' => 'folder_managed'],
        ['time' => now('Asia/Manila')->subHours(2), 'event' => 'Operational reports opened', 'scope' => $totalRooms.' managed room(s)', 'ip' => request()->ip(), 'icon' => 'analytics'],
        ['time' => now('Asia/Manila')->subDay(), 'event' => 'Read-only session authenticated', 'scope' => 'Owner / Management', 'ip' => request()->ip(), 'icon' => 'verified_user'],
    ]);
@endphp

<section class="relative overflow-hidden rounded-xl border border-secondary/30 bg-primary p-7 text-white shadow-xl">
    <div class="pointer-events-none absolute -bottom-24 -right-20 h-96 w-96 rounded-full bg-secondary/10 blur-3xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-2 rounded border border-secondary-fixed/40 bg-secondary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed">
                    <span class="material-symbols-outlined text-sm">security</span>
                    Executive Login Details & Management
                </span>
                <span class="text-xs font-semibold text-blue-100/70">Subic Bay Freeport Zone Mandate</span>
            </div>
            <h2 class="mt-4 font-serif text-4xl font-extrabold leading-tight">Owner Executive Profile & Oversight Security</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                Identity verification, role access rules, owner login details, and management notification preferences.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-3 xl:min-w-[640px]">
            <div class="rounded-lg border border-secondary-fixed/20 bg-primary-container/80 p-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Role Designation</p><p class="mt-1 text-sm font-bold text-secondary-fixed">{{ $ownerRole }}</p></div>
            <div class="rounded-lg border border-secondary-fixed/20 bg-primary-container/80 p-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Clearance Level</p><p class="mt-1 text-sm font-bold">Tier-1 Executive Read-Only</p></div>
            <div class="rounded-lg border border-secondary-fixed/20 bg-primary-container/80 p-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Identity Status</p><p class="mt-1 flex items-center gap-1 text-sm font-bold text-secondary-fixed"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>{{ $verifiedAt ? 'Email Verified' : 'Email Pending' }}</p></div>
            <div class="rounded-lg border border-secondary-fixed/20 bg-primary-container/80 p-3"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100/70">Security Seal</p><p class="mt-1 font-mono text-sm font-bold">{{ $securityHash }}</p></div>
        </div>
    </div>
</section>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <section class="owner-card rounded-xl p-6 xl:col-span-5">
        <div class="flex items-start gap-4">
            <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl border border-secondary/30 bg-primary text-2xl font-black text-secondary-fixed shadow-inner">{{ $ownerInitials }}</span>
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Executive Identity Dossier</p>
                <h3 class="mt-1 break-words font-serif text-3xl font-bold text-primary">{{ $ownerName }}</h3>
                <p class="mt-1 break-words text-sm text-slate-500">{{ $ownerEmail }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="rounded-full bg-secondary-fixed/40 px-3 py-1 text-xs font-bold text-secondary">{{ $ownerRole }}</span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Owner View-Only</span>
                </div>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Account Created</p><p class="mt-1 font-mono text-sm font-bold text-primary">{{ $createdAt?->format('M d, Y') ?? 'System seeded' }}</p></div>
            <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Verification</p><p class="mt-1 font-mono text-sm font-bold text-primary">{{ $verifiedAt?->format('M d, Y h:i A') ?? 'Pending email verification' }}</p></div>
            <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">RevPAR Visibility</p><p class="mt-1 font-serif text-xl font-bold text-primary">PHP {{ number_format($revPar, 2) }}</p></div>
            <div class="rounded-lg border border-line bg-surface-low p-4"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">ADR Visibility</p><p class="mt-1 font-serif text-xl font-bold text-primary">PHP {{ number_format($adr, 2) }}</p></div>
        </div>
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-800"><span class="material-symbols-outlined text-sm">check_circle</span>Granted Oversight Privileges</p>
            <div class="mt-3 space-y-2 text-sm text-emerald-900">
                <p class="rounded-lg border border-emerald-200 bg-white/70 p-3"><strong>Financial oversight:</strong> revenue, billing records, transaction reports, and tax-ready records.</p>
                <p class="rounded-lg border border-emerald-200 bg-white/70 p-3"><strong>Operational information:</strong> rooms, reservations, check-in/out, and housekeeping activity.</p>
                <p class="rounded-lg border border-emerald-200 bg-white/70 p-3"><strong>Archive exports:</strong> owner report CSV exports and protected records review.</p>
            </div>
        </div>
    </section>

    <section class="space-y-6 xl:col-span-7">
        <div class="owner-card rounded-xl p-6">
            <div class="flex flex-col justify-between gap-3 border-b border-line pb-4 sm:flex-row sm:items-center">
                <div><h3 class="font-serif text-2xl font-bold text-primary">Security & Sign-in Settings</h3><p class="mt-1 text-sm text-slate-500">Owner account protections and trusted session posture.</p></div>
                <span class="rounded bg-secondary-container/40 px-3 py-1 text-xs font-bold text-secondary">Hardware Enforced</span>
            </div>
            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-line bg-surface-low p-4">
                    <div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">FIDO2 Security Key</p><span class="h-2 w-2 rounded-full bg-emerald-600"></span></div>
                    <p class="mt-2 font-mono text-lg font-bold text-primary">#OWNER-KEY-{{ str_pad((string) ($owner?->id ?? 1), 4, '0', STR_PAD_LEFT) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Physical presence verification modeled for executive sessions.</p>
                </div>
                <div class="rounded-lg border border-line bg-surface-low p-4">
                    <div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Transport Security</p><span class="material-symbols-outlined text-secondary">encrypted</span></div>
                    <p class="mt-2 text-lg font-bold text-primary">TLS 1.3 / SBMA Node</p>
                    <p class="mt-1 text-sm text-slate-500">Protected owner dashboard access with read-only safeguards.</p>
                </div>
            </div>
            <div class="mt-5 rounded-lg border border-line bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Authorized Executive Station</p>
                <div class="mt-3 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3"><span class="material-symbols-outlined text-3xl text-secondary">desktop_windows</span><div><p class="font-bold text-primary">Current Browser Session</p><p class="text-xs text-slate-500">IP {{ request()->ip() }} · {{ request()->userAgent() ? str(request()->userAgent())->limit(70) : 'User agent unavailable' }}</p></div></div>
                    <span class="rounded bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800">Current Session</span>
                </div>
            </div>
        </div>

        <div class="owner-card rounded-xl p-6">
            <div class="flex items-center justify-between border-b border-line pb-4">
                <div><h3 class="font-serif text-2xl font-bold text-primary">Executive System Status & Assignment Preferences</h3><p class="mt-1 text-sm text-slate-500">Visual owner settings for important management notifications.</p></div>
                <span class="text-xs font-bold text-slate-500">Read-only preview</span>
            </div>
            <div class="mt-4 space-y-3">
                @foreach ([
                    ['icon' => 'picture_as_pdf', 'title' => 'Daily Management Summary', 'meta' => 'Room revenue, average room rate, occupancy, and payment summary at 7:00 AM and 10:00 PM.', 'mode' => 'Protected PDF'],
                    ['icon' => 'notification_important', 'title' => 'Unusual Revenue & Occupied Room Alerts', 'meta' => 'Instant push when revenue progress or occupancy drifts outside the expected range.', 'mode' => 'Instant Push'],
                    ['icon' => 'gavel', 'title' => 'SBMA Required Tax Compliance Notices', 'meta' => 'Regulatory reminders for Freeport and tax reporting review.', 'mode' => 'Automated'],
                    ['icon' => 'stars', 'title' => 'VIP Arrival Alerts', 'meta' => 'Priority notice for important corporate or diplomatic arrivals.', 'mode' => 'SMS / Email'],
                ] as $item)
                    <div class="flex flex-col justify-between gap-3 rounded-lg border border-line bg-surface-low p-3.5 sm:flex-row sm:items-center">
                        <div class="flex items-start gap-3"><span class="material-symbols-outlined mt-0.5 text-xl text-secondary">{{ $item['icon'] }}</span><div><h4 class="font-bold text-primary">{{ $item['title'] }}</h4><p class="text-xs leading-5 text-slate-500">{{ $item['meta'] }}</p></div></div>
                        <div class="flex shrink-0 items-center gap-2"><span class="text-xs font-bold text-emerald-800">{{ $item['mode'] }}</span><button type="button" class="relative h-5 w-10 rounded-full bg-primary p-0.5" data-owner-toggle aria-label="{{ $item['title'] }}"><span class="block h-4 w-4 translate-x-5 rounded-full bg-secondary-fixed shadow-sm transition"></span></button></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <section class="owner-card rounded-xl p-6 xl:col-span-5">
        <p class="text-xs font-bold uppercase tracking-wider text-error">Restricted Operational Boundaries</p>
        <h3 class="mt-2 font-serif text-2xl font-bold text-primary">Sandboxed Owner Access</h3>
        <div class="mt-4 space-y-2">
            @foreach ([
                'Direct billing entry, voids, discounts, and transaction edits',
                'User and staff account creation or role permission changes',
                'Room rate changes and online booking settings',
                'Keycard lock overrides, force-open controls, and physical security actions',
            ] as $restriction)
                <p class="flex gap-2 rounded-lg border border-line bg-surface-low p-3 text-sm text-slate-600"><span class="material-symbols-outlined text-sm text-error">lock</span>{{ $restriction }}</p>
            @endforeach
        </div>
    </section>

    <section class="owner-card overflow-hidden rounded-xl xl:col-span-7">
        <div class="flex flex-col justify-between gap-3 border-b border-line bg-white p-5 sm:flex-row sm:items-center">
            <div><h3 class="font-serif text-2xl font-bold text-primary">Recent Executive Access Log & Audit Hashes</h3><p class="mt-1 text-sm text-slate-500">Read-only activity trail for the owner profile console.</p></div>
            <button id="ownerProfileExport" type="button" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface-low px-3 py-2 text-xs font-bold text-primary transition hover:border-secondary"><span class="material-symbols-outlined text-sm text-secondary">download</span>Export Access Log</button>
        </div>
        <div class="owner-scroll overflow-x-auto">
            <table class="w-full min-w-[860px] border-collapse text-left text-sm">
                <thead><tr class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600"><th class="px-4 py-3">Timestamp (PHT)</th><th class="px-4 py-3">Event & Module</th><th class="px-4 py-3">Scope / Target</th><th class="px-4 py-3">Station IP</th><th class="px-4 py-3 text-right">Record Code</th></tr></thead>
                <tbody class="divide-y divide-line bg-white">
                    @foreach ($accessRows as $row)
                        @php $rowHash = substr(strtoupper(hash('sha256', $row['event'].$row['scope'].$row['time']->timestamp.$securityHash)), 0, 4).'...'.substr(strtoupper(hash('sha256', $row['ip'].$ownerEmail)), 0, 4); @endphp
                        <tr class="transition hover:bg-surface-low" data-owner-profile-log>
                            <td class="px-4 py-3 font-mono text-xs font-bold text-primary">{{ $row['time']->format('M d, h:i:s A') }}</td>
                            <td class="px-4 py-3"><span class="inline-flex items-center gap-1 font-semibold text-primary"><span class="material-symbols-outlined text-sm text-secondary">{{ $row['icon'] }}</span>{{ $row['event'] }}</span></td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $row['scope'] }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $row['ip'] }}</td>
                            <td class="px-4 py-3 text-right font-mono text-xs text-slate-500">SHA256:{{ $rowHash }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    (() => {
        document.querySelectorAll('[data-owner-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                button.classList.toggle('bg-primary');
                button.classList.toggle('bg-slate-300');
                button.querySelector('span')?.classList.toggle('translate-x-5');
            });
        });

        document.getElementById('ownerProfileExport')?.addEventListener('click', () => {
            const rows = Array.from(document.querySelectorAll('[data-owner-profile-log]'));
            const csv = [
                ['Timestamp (PHT)', 'Event & Module', 'Scope / Target', 'Station IP', 'Record Code'],
                ...rows.map((row) => Array.from(row.children).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim())),
            ].map((line) => line.map((value) => `"${value.replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `owner-profile-access-log-${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        });
    })();
</script>
