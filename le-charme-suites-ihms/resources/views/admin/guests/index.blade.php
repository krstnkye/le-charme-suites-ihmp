@php
    $currentUser = auth()->user();
    $verificationRate = $totalGuests > 0 ? round(($verifiedGuests / $totalGuests) * 100) : 0;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Guest Management - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        secondary: '#755a28',
                        'secondary-fixed': '#ffdeaa',
                        background: '#fcf9f3',
                        surface: '#fcf9f3',
                        'surface-container': '#f0eee8',
                        'surface-container-low': '#f6f3ed',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-highest': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        error: '#ba1a1a',
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        serif: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .nav-shell {
            background-color: #172b42;
            background-image: radial-gradient(rgba(255, 222, 170, 0.14) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        .admin-card {
            background: #fffdfa;
            border: 1px solid #e5e2dc;
            box-shadow: 0 16px 42px -28px rgba(1, 22, 45, .45), 0 2px 8px rgba(117, 90, 40, .08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">supervisor_account</span>
                <div>
                    <h1 class="font-serif text-xl font-bold">Le Charme Suites IHMP</h1>
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Administrator Guest Registry</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('guests.index') }}" class="hidden rounded-lg border border-outline-variant bg-white px-3 py-2 text-xs font-bold text-primary shadow-sm transition hover:bg-surface-container sm:inline-flex">Open Front Desk View</a>
                <div class="text-right">
                    <p class="text-xs font-bold">{{ $currentUser?->name }}</p>
                    <p class="text-[11px] text-slate-500">Admin Access</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary-fixed text-sm font-bold text-primary">
                    {{ strtoupper(substr($currentUser?->name ?? 'A', 0, 1)) }}
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif

            <section class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <div class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="text-secondary">Guest Management</span>
                    </div>
                    <h2 class="font-serif text-4xl font-semibold tracking-tight">Administrative Guest Registry & Oversight</h2>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Full-power guest governance for admin users: inspect profile quality, lifetime value, stay history, identification status, and sensitive record actions.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('guests.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        Add Guest Profile
                    </a>
                    <button id="exportGuestRegistry" class="inline-flex items-center gap-2 rounded-lg border border-secondary bg-white px-4 py-2.5 text-sm font-bold text-secondary shadow-sm transition hover:bg-secondary-fixed/30" type="button">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Export Registry
                    </button>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="admin-card rounded-xl p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Guests</p>
                    <p class="mt-2 font-serif text-4xl font-bold">{{ $totalGuests }}</p>
                    <p class="mt-1 text-sm text-slate-500">Central guest registry records</p>
                </div>
                <div class="admin-card rounded-xl p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Verified Identity</p>
                    <p class="mt-2 font-serif text-4xl font-bold">{{ $verificationRate }}%</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $verifiedGuests }} profiles with ID on file</p>
                </div>
                <div class="admin-card rounded-xl p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">In-House Guests</p>
                    <p class="mt-2 font-serif text-4xl font-bold">{{ $inHouseGuests }}</p>
                    <p class="mt-1 text-sm text-slate-500">Currently checked in</p>
                </div>
                <div class="admin-card rounded-xl p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Today Movement</p>
                    <p class="mt-2 font-serif text-4xl font-bold">{{ $arrivingToday + $departingToday }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $arrivingToday }} arrivals, {{ $departingToday }} departures</p>
                </div>
                <div class="admin-card rounded-xl p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Incomplete Profiles</p>
                    <p class="mt-2 font-serif text-4xl font-bold text-error">{{ $incompleteProfiles }}</p>
                    <p class="mt-1 text-sm text-slate-500">Missing contact or identification details</p>
                </div>
            </section>

            <section class="admin-card rounded-xl p-4">
                <form method="GET" action="{{ route('admin.guests.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-500">search</span>
                        <input name="search" value="{{ $search }}" class="w-full rounded-lg border-outline-variant bg-white py-2.5 pl-9 pr-3 text-sm focus:border-secondary focus:ring-secondary/20" placeholder="Search name, email, phone, guest ID, or government ID..." type="search">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select class="rounded-lg border-outline-variant bg-surface-container-low text-sm focus:border-secondary focus:ring-secondary/20">
                            <option>All Verification States</option>
                            <option>Verified ID</option>
                            <option>Missing ID</option>
                            <option>Missing Contact Details</option>
                        </select>
                        <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white" type="submit">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Apply Search
                        </button>
                    </div>
                </form>
            </section>

            <section class="admin-card overflow-hidden rounded-xl">
                <div class="flex flex-col justify-between gap-3 border-b border-surface-container-highest p-5 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="font-serif text-2xl font-semibold">Master Guest Registry</h3>
                        <p class="mt-1 text-sm text-slate-500">Admin-level view with financial, compliance, and record-management controls.</p>
                    </div>
                    <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-bold text-secondary">{{ $guests->total() }} result{{ $guests->total() === 1 ? '' : 's' }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1180px] text-left">
                        <thead>
                            <tr class="border-b border-surface-container-highest bg-surface-container text-xs font-bold uppercase tracking-wider text-primary">
                                <th class="px-4 py-3">Guest Profile</th>
                                <th class="px-4 py-3">Contact</th>
                                <th class="px-4 py-3">Identity / Compliance</th>
                                <th class="px-4 py-3 text-center">Stay History</th>
                                <th class="px-4 py-3 text-right">Lifetime Billing</th>
                                <th class="px-4 py-3 text-right">Admin Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container-highest/70 text-sm">
                            @forelse ($guests as $guest)
                                @php
                                    $hasIdentity = filled($guest->identification_type) && filled($guest->identification_number);
                                    $hasContact = filled($guest->email) && filled($guest->phone);
                                @endphp
                                <tr class="transition hover:bg-surface-container-low/70">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                                {{ strtoupper(substr($guest->first_name, 0, 1) . substr($guest->last_name, 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="font-bold">{{ $guest->full_name }}</p>
                                                <p class="font-mono text-xs text-slate-500">GST-{{ str_pad((string) $guest->id, 4, '0', STR_PAD_LEFT) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p>{{ $guest->email ?? 'No email recorded' }}</p>
                                        <p class="text-xs text-slate-500">{{ $guest->phone ?? 'No phone recorded' }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            <span @class([
                                                'inline-flex w-fit rounded px-2 py-0.5 text-xs font-bold',
                                                'bg-emerald-50 text-emerald-800 border border-emerald-200' => $hasIdentity,
                                                'bg-amber-50 text-amber-900 border border-amber-200' => ! $hasIdentity,
                                            ])>{{ $hasIdentity ? 'Verified ID' : 'Needs ID Review' }}</span>
                                            <span class="text-xs text-slate-500">{{ $hasIdentity ? $guest->identification_type . ' - ' . $guest->identification_number : 'Identification not complete' }}</span>
                                            @unless ($hasContact)
                                                <span class="text-xs font-bold text-error">Contact details incomplete</span>
                                            @endunless
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="rounded-full bg-surface-container px-2.5 py-1 text-xs font-bold">{{ $guest->reservations_count }} stay{{ $guest->reservations_count === 1 ? '' : 's' }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <p class="font-serif text-lg font-bold">PHP {{ number_format((float) ($guest->lifetime_value ?? 0), 2) }}</p>
                                        <p class="text-xs text-slate-500">Posted billing value</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('guests.show', $guest) }}" class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white hover:bg-primary-container"><span class="material-symbols-outlined text-sm">visibility</span>Inspect</a>
                                            <a href="{{ route('guests.edit', $guest) }}" class="inline-flex items-center gap-1 rounded-lg border border-outline-variant px-3 py-2 text-xs font-bold text-primary hover:bg-surface-container"><span class="material-symbols-outlined text-sm">edit</span>Edit</a>
                                            <form method="POST" action="{{ route('guests.destroy', $guest) }}" onsubmit="return confirm('Delete this guest profile? This admin action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-error hover:bg-red-100" type="submit"><span class="material-symbols-outlined text-sm">delete</span>Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-14 text-center">
                                        <span class="material-symbols-outlined text-4xl text-slate-300">group_off</span>
                                        <p class="mt-2 font-bold text-slate-600">No guest records found.</p>
                                        <p class="mt-1 text-sm text-slate-500">Create a profile or adjust the admin search filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col items-center justify-between gap-3 border-t border-surface-container-highest bg-white p-4 text-xs sm:flex-row">
                    <p class="font-medium text-slate-500">Showing {{ $guests->firstItem() ?? 0 }} to {{ $guests->lastItem() ?? 0 }} of {{ $guests->total() }} guests</p>
                    {{ $guests->links() }}
                </div>
            </section>
        </main>
    </div>
    <script>
        document.getElementById('exportGuestRegistry')?.addEventListener('click', () => {
            const rows = Array.from(document.querySelectorAll('tbody tr')).filter((row) => row.querySelectorAll('td').length >= 6);
            if (!rows.length) {
                alert('No guest records are available to export.');
                return;
            }

            const csvRows = [['Guest Profile', 'Contact', 'Identity / Compliance', 'Stay History', 'Lifetime Billing']];
            rows.forEach((row) => {
                const cells = Array.from(row.querySelectorAll('td')).slice(0, 5).map((cell) => cell.innerText.replace(/\s+/g, ' ').trim());
                csvRows.push(cells);
            });

            const csv = csvRows.map((row) => row.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'le-charme-admin-guest-registry.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });
    </script>
</body>
</html>
