@php
    $safeTotalGuests = max($totalGuests, 1);
    $verifiedPercent = round((($identifiedGuests ?? 0) / $safeTotalGuests) * 100);
    $averageGuestValue = $totalGuests > 0 ? (($guestSpendTotal ?? 0) / $totalGuests) : 0;
    $selectedGuest = $guests->first();
    $selectedSpend = $selectedGuest?->transactions_sum_amount ?? 0;
    $selectedInitials = $selectedGuest
        ? collect(explode(' ', trim($selectedGuest->full_name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->join('')
        : 'NA';
@endphp

<section class="relative overflow-hidden rounded-xl border border-line bg-white p-6 shadow-sm">
    <div class="pointer-events-none absolute -right-10 -top-10 h-64 w-64 rounded-full bg-secondary-fixed/20 blur-2xl"></div>
    <div class="relative z-10 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
        <div class="max-w-4xl">
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-secondary/20 bg-secondary-container/40 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-secondary">
                <span class="material-symbols-outlined text-sm">insights</span>
                Executive Monitoring · CRM & Guest Lifetime Intelligence
            </div>
            <h2 class="font-serif text-4xl font-semibold leading-tight text-primary">Guest Management & Corporate Roster Overview</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                Read-only executive oversight of registered guest profiles, stay frequency, verified identification coverage, and lifetime transaction value across Le Charmé Suites.
            </p>
        </div>
        <div class="grid shrink-0 grid-cols-2 gap-2.5 rounded-lg border border-line bg-surface-low p-3 sm:grid-cols-4">
            <div class="px-3 py-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Registered</span>
                <span class="font-serif text-lg font-bold text-primary">{{ $totalGuests }}</span>
            </div>
            <div class="border-l border-line px-3 py-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">In-House Now</span>
                <span class="font-serif text-lg font-bold text-emerald-700">{{ $currentGuests }} Guests</span>
            </div>
            <div class="border-l border-line px-3 py-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">ID Verified</span>
                <span class="font-serif text-lg font-bold text-primary">{{ $verifiedPercent }}%</span>
            </div>
            <div class="border-l border-line px-3 py-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Avg Value</span>
                <span class="font-serif text-lg font-bold text-secondary">PHP {{ number_format($averageGuestValue, 2) }}</span>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/40">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Registered Profiles</span>
            <span class="rounded-lg bg-surface-low p-2 text-primary"><span class="material-symbols-outlined">badge</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="font-serif text-3xl font-bold text-primary">{{ $totalGuests }}</span>
            <span class="text-xs font-semibold text-emerald-700">Live roster</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">All guest profiles saved in the central database.</p>
    </div>
    <div class="owner-card rounded-xl border-t-2 border-t-emerald-600 p-5 transition hover:border-secondary/40">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Current In-House Guests</span>
            <span class="rounded-lg bg-emerald-50 p-2 text-emerald-800"><span class="material-symbols-outlined">hotel</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="font-serif text-3xl font-bold text-primary">{{ $currentGuests }}</span>
            <span class="text-xs text-slate-500">active stays</span>
        </div>
        <p class="mt-2 text-xs font-medium text-emerald-700">Visible from checked-in reservation records.</p>
    </div>
    <div class="owner-card rounded-xl border-t-2 border-t-secondary p-5 transition hover:border-secondary/40">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Verified Guest IDs</span>
            <span class="rounded-lg bg-secondary-fixed/30 p-2 text-secondary"><span class="material-symbols-outlined">military_tech</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="font-serif text-3xl font-bold text-primary">{{ $identifiedGuests ?? 0 }}</span>
            <span class="text-xs font-bold text-secondary">{{ $verifiedPercent }}% coverage</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Profiles with government or official ID numbers on file.</p>
    </div>
    <div class="owner-card rounded-xl p-5 transition hover:border-secondary/40">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Avg Guest Lifetime Value</span>
            <span class="rounded-lg bg-surface-low p-2 text-primary"><span class="material-symbols-outlined">payments</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <span class="font-serif text-3xl font-bold text-primary">PHP {{ number_format($averageGuestValue, 2) }}</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Based on total transaction value divided by registered guests.</p>
    </div>
</section>

<section class="owner-card rounded-xl p-4">
    <div class="flex flex-col justify-between gap-4 xl:flex-row xl:items-center">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 xl:pb-0">
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white shadow-sm">
                All Guests <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-[10px]">{{ $totalGuests }}</span>
            </span>
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-600">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span> In-House Active
                <span class="rounded-full bg-surface-mid px-1.5 py-0.5 text-[10px]">{{ $currentGuests }}</span>
            </span>
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-600">
                <span class="material-symbols-outlined text-sm text-secondary">verified_user</span> ID Verified
                <span class="rounded-full bg-secondary-fixed/40 px-1.5 py-0.5 text-[10px]">{{ $identifiedGuests ?? 0 }}</span>
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="relative w-full sm:w-80">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-base text-secondary">search</span>
                <input class="w-full rounded-lg border border-line bg-white py-2 pl-9 pr-3 text-xs placeholder-slate-400 focus:border-secondary focus:ring-secondary/30" placeholder="Search name, email, phone, or ID..." type="text" readonly>
            </div>
            <span class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-slate-600">Read-only filters</span>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <div class="owner-card overflow-hidden rounded-xl xl:col-span-8">
        <div class="flex items-center justify-between border-b border-line bg-surface-low px-5 py-4">
            <div>
                <h3 class="font-serif text-xl font-bold text-primary">Master Guest Records & Roster</h3>
                <p class="mt-0.5 text-xs text-slate-500">Showing latest executive-visible profiles with verified stay histories.</p>
            </div>
            <span class="rounded bg-primary/10 px-2 py-1 text-[10px] font-bold text-primary">Live Data Synchronized</span>
        </div>
        <div class="owner-scroll overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-xs">
                <thead class="border-b border-line bg-surface-mid text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Guest Profile & Tier</th>
                        <th class="px-3 py-3">Contact</th>
                        <th class="px-3 py-3">ID / Compliance</th>
                        <th class="px-3 py-3 text-center">Stays</th>
                        <th class="px-3 py-3 text-right">Lifetime Spend</th>
                        <th class="px-3 py-3">Access</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($guests as $guest)
                        @php
                            $guestInitials = collect(explode(' ', trim($guest->full_name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                ->join('') ?: 'G';
                            $spend = $guest->transactions_sum_amount ?? 0;
                            $tier = $spend >= 50000 ? 'Executive Patron' : ($guest->reservations_count > 1 ? 'Returning Guest' : 'Standard Guest');
                        @endphp
                        <tr @class(['transition hover:bg-surface-low', 'bg-secondary-fixed/15 hover:bg-secondary-fixed/25' => $loop->first])>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-secondary bg-primary text-xs font-bold text-secondary-fixed shadow-sm">{{ $guestInitials }}</span>
                                    <div>
                                        <div class="flex items-center gap-1.5 font-bold text-primary">
                                            <span>{{ $guest->full_name }}</span>
                                            @if ($loop->first)
                                                <span class="material-symbols-outlined text-sm text-secondary" style="font-variation-settings:'FILL' 1;">stars</span>
                                            @endif
                                        </div>
                                        <span class="mt-0.5 inline-block rounded border border-secondary/30 bg-secondary/15 px-1.5 py-0.5 text-[10px] font-bold text-secondary">{{ $tier }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="block max-w-[180px] truncate text-primary">{{ $guest->email ?? 'No email recorded' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $guest->phone ?? 'No phone recorded' }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="block font-bold text-primary">{{ $guest->identification_type ?? 'ID pending' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $guest->identification_number ?? 'No ID number' }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="font-bold text-primary">{{ $guest->reservations_count }}</span> stays
                                <div class="text-[10px] text-slate-500">reservation history</div>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <span class="block font-bold text-primary">PHP {{ number_format($spend, 2) }}</span>
                                <span class="text-[10px] text-secondary">System records</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-surface-mid px-2 py-1 text-[10px] font-bold text-primary">
                                    <span class="material-symbols-outlined text-xs">visibility</span> View only
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No guest profiles have been added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between border-t border-line bg-surface-low px-5 py-3 text-xs text-slate-500">
            <span>Showing <strong class="text-primary">{{ $guests->count() }}</strong> of <strong class="text-primary">{{ $totalGuests }}</strong> verified guests</span>
            <span class="rounded border border-line bg-white px-2.5 py-1 font-bold text-primary">Page 1</span>
        </div>
    </div>

    <aside class="owner-card relative overflow-hidden rounded-xl border-2 border-secondary/40 p-5 xl:col-span-4">
        <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-secondary-fixed via-secondary to-primary-container"></div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-secondary">
                    <span class="material-symbols-outlined text-xs">folder_special</span>
                    Executive Dossier Inspection
                </span>
                <h3 class="mt-1 font-serif text-xl font-bold text-primary">{{ $selectedGuest?->full_name ?? 'No guest selected' }}</h3>
                <p class="text-xs text-slate-500">{{ $selectedGuest?->email ?? 'Latest guest snapshot will appear here.' }}</p>
            </div>
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-secondary bg-primary font-serif text-lg font-bold text-secondary-fixed shadow">{{ $selectedInitials }}</span>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2 rounded-lg border border-line bg-surface-low p-3 text-center">
            <div>
                <span class="block text-[10px] font-bold uppercase text-slate-500">Total Stays</span>
                <span class="text-sm font-bold text-primary">{{ $selectedGuest?->reservations_count ?? 0 }}</span>
            </div>
            <div class="border-x border-line">
                <span class="block text-[10px] font-bold uppercase text-slate-500">ID Status</span>
                <span class="text-sm font-bold text-primary">{{ $selectedGuest?->identification_number ? 'Filed' : 'Pending' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold uppercase text-slate-500">Gross Spend</span>
                <span class="text-sm font-bold text-secondary">PHP {{ number_format($selectedSpend, 2) }}</span>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50/70 p-3.5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                    <span class="text-xs font-bold text-emerald-950">OWNER VIEW: READ-ONLY PROFILE</span>
                </div>
                <span class="rounded bg-emerald-200/80 px-1.5 py-0.5 text-[10px] font-bold uppercase text-emerald-900">Protected</span>
            </div>
            <div class="mt-2 space-y-1 text-[11.5px] text-emerald-900/90">
                <div class="flex justify-between gap-4"><span>Contact:</span><span class="font-semibold">{{ $selectedGuest?->phone ?? 'Not recorded' }}</span></div>
                <div class="flex justify-between gap-4"><span>Address:</span><span class="text-right font-semibold">{{ $selectedGuest?->address ?? 'Not recorded' }}</span></div>
            </div>
        </div>

        <div class="mt-4 space-y-3">
            <div>
                <h4 class="flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <span class="material-symbols-outlined text-xs text-secondary">room_service</span>
                    Executive Notes
                </h4>
                <div class="mt-1.5 rounded-lg border border-line bg-white p-3 text-xs leading-5 text-slate-600">
                    {{ $selectedGuest?->notes ?? 'No guest notes recorded. Front Desk may update notes from their operational workspace.' }}
                </div>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-line bg-white p-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded bg-secondary-fixed/30 text-secondary"><span class="material-symbols-outlined text-sm">policy</span></span>
                    <div>
                        <span class="block text-xs font-bold text-primary">Guest Privacy Lock</span>
                        <span class="text-[10px] text-slate-500">Protected owner-visible profile snapshot</span>
                    </div>
                </div>
                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Validated</span>
            </div>
        </div>

        <div class="mt-5 border-t border-line pt-3">
            <div class="flex items-start gap-2 rounded-lg border border-line bg-surface-mid/70 p-2.5">
                <span class="material-symbols-outlined mt-0.5 shrink-0 text-sm text-secondary">lock</span>
                <p class="text-[10.5px] leading-tight text-slate-600">
                    <strong class="font-bold text-primary">Read-only profile snapshot.</strong> Guest profile changes, ID uploads, and sensitive corrections remain with Front Desk or Admin staff.
                </p>
            </div>
        </div>
    </aside>
</section>
