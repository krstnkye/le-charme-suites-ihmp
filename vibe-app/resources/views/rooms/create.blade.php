@php
    $currentUser = auth()->user();
    $statusOptions = [
        'available' => 'Available',
        'reserved' => 'Reserved',
        'occupied' => 'Occupied',
        'cleaning' => 'Cleaning',
        'maintenance' => 'Maintenance',
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Room - Le Charme Suites IHMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Noto+Serif:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
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
            background: #172b42;
            background-image: radial-gradient(rgba(255, 222, 170, .14) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        .parchment-card {
            background-color: #fdfcfa;
            border: 1px solid #ede8df;
            box-shadow: 0 4px 16px -2px rgba(23, 43, 66, .05), 0 1px 3px rgba(197, 163, 106, .08);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">add_business</span>
                <h1 class="font-serif text-xl font-bold">Le Charme Suites IHMS - Room Inventory Administration</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-bold text-secondary">Administrator-only workspace</span>
                <div class="text-right">
                    <p class="text-xs font-bold">{{ $currentUser?->name }}</p>
                    <p class="text-[11px] text-slate-500">Administrator Access</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-secondary/40 bg-primary-container text-sm font-bold text-white">{{ strtoupper(substr($currentUser?->name ?? 'A', 0, 1)) }}</a>
            </div>
        </header>

        <main class="p-8">
            <section class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-surface-container-highest pb-5">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <span>Administration</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span>Room Management</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="font-bold text-primary">Add Room</span>
                    </div>
                    <h2 class="font-serif text-4xl font-bold">Add Room Inventory Record</h2>
                    <p class="mt-2 max-w-3xl text-sm text-slate-600">Create a room unit, assign its type, rate, capacity, and starting operational status for the IHMS room inventory.</p>
                </div>
                <a href="{{ route('admin.operations.index', ['module' => 'room-inventory']) }}" class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-bold shadow-sm transition hover:bg-surface-container">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Room Inventory
                </a>
            </section>

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <form method="POST" action="{{ route('rooms.store') }}" class="parchment-card rounded-xl border-t-4 border-t-primary p-6">
                    @csrf

                    <div class="mb-5 flex items-start justify-between gap-4 border-b border-surface-container-highest pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">meeting_room</span>
                                <h3 class="text-xl font-bold">Room Details</h3>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">Use a unique room number and keep rates aligned with the current tariff table.</p>
                        </div>
                        <span class="rounded bg-surface-container px-3 py-1 text-xs font-bold text-slate-600">IPT-MOD-RI</span>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Room Number *</span>
                            <input name="room_number" value="{{ old('room_number') }}" class="mt-2 w-full rounded-lg border-outline-variant bg-white px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Example: 401" required>
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Room Type *</span>
                            <input name="room_type" list="room-type-options" value="{{ old('room_type', request('room_type')) }}" class="mt-2 w-full rounded-lg border-outline-variant bg-white px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Standard, Deluxe, Suite..." required>
                            <datalist id="room-type-options">
                                @foreach ($roomTypes as $roomType)
                                    <option value="{{ $roomType }}"></option>
                                @endforeach
                            </datalist>
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Nightly Rate *</span>
                            <div class="mt-2 flex rounded-lg border border-outline-variant bg-white focus-within:border-secondary focus-within:ring-1 focus-within:ring-secondary">
                                <span class="flex items-center border-r border-outline-variant px-4 text-sm font-bold text-secondary">PHP</span>
                                <input name="rate" type="number" step="0.01" min="0" value="{{ old('rate', '0.00') }}" class="w-full border-0 bg-transparent px-4 py-3 text-sm focus:ring-0" required>
                            </div>
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Capacity *</span>
                            <input name="capacity" type="number" min="1" value="{{ old('capacity', 1) }}" class="mt-2 w-full rounded-lg border-outline-variant bg-white px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Opening Status *</span>
                            <select name="status" class="mt-2 w-full rounded-lg border-outline-variant bg-white px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'available') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-700">Description</span>
                            <textarea name="description" rows="5" class="mt-2 w-full rounded-lg border-outline-variant bg-white px-4 py-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Add bed setup, wing, amenities, accessibility notes, or maintenance remarks.">{{ old('description') }}</textarea>
                        </label>
                    </div>

                    <div class="mt-7 flex flex-wrap items-center justify-end gap-3 border-t border-surface-container-highest pt-5">
                        <a href="{{ route('admin.operations.index', ['module' => 'room-inventory']) }}" class="rounded-lg border border-outline-variant bg-white px-5 py-3 text-sm font-bold transition hover:bg-surface-container">Cancel</a>
                        <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                            Save Room
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </button>
                    </div>
                </form>

                <aside class="space-y-5">
                    <section class="parchment-card rounded-xl p-5">
                        <div class="mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary">rule</span>
                            <h3 class="text-lg font-bold">Inventory Rules</h3>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>Room number must be unique across the property.</li>
                            <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>Available rooms can be selected for new reservations.</li>
                            <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>Cleaning and maintenance rooms remain blocked from booking.</li>
                        </ul>
                    </section>

                    <section class="rounded-xl border border-secondary/30 bg-secondary/10 p-5">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary">history</span>
                            <h3 class="text-lg font-bold">Status Log</h3>
                        </div>
                        <p class="text-sm text-slate-600">Saving this room also records the initial room status in the room-status audit log.</p>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
