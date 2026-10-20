<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Your Stay - Le Charmé Suites Subic</title>
    @include('partials.offline-assets')
    @include('partials.favicon')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { navy: '#0c1c2e', deep: '#112235', surface: '#172b42', accent: '#c5a36a', cream: '#fcf9f3' } },
                    fontFamily: { body: ['Manrope', 'sans-serif'], display: ['Noto Serif', 'serif'] },
                },
            },
        };
    </script>
</head>
<body class="flex min-h-screen flex-col bg-[#f8f6f0] font-body text-slate-800 antialiased selection:bg-amber-200">
    <header class="sticky top-0 z-50 border-b border-white/10 bg-[#0c1c2e] text-white shadow-sm">
        <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('booking.create') }}" class="group flex min-w-0 items-center gap-3 sm:gap-4" aria-label="Le Charmé Suites booking home">
                <span class="flex h-14 w-32 shrink-0 items-center justify-center rounded-lg bg-white px-3 shadow-sm sm:w-36">
                    <img src="{{ asset('images/le-charme-logo-white.png') }}" alt="Le Charmé Suites" class="h-11 w-full object-contain">
                </span>
                <span class="hidden min-w-0 sm:block">
                    <span class="block truncate font-display text-xl font-bold transition group-hover:text-amber-100">Le Charmé Suites Subic</span>
                    <span class="block truncate text-xs text-[#e6cda3]">European-inspired luxury with modern Filipino hospitality</span>
                </span>
            </a>

            <nav class="flex shrink-0 items-center gap-2" aria-label="Booking actions">
                <a href="#booking-form" class="hidden items-center gap-2 rounded-lg px-3 py-2 text-xs font-bold uppercase tracking-wider text-white/90 transition hover:bg-white/10 hover:text-white md:inline-flex">
                    <span class="material-symbols-outlined text-base text-[#c5a36a]">calendar_month</span>
                    Check Rates
                </a>
                <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ route('login') }}')" class="inline-flex items-center gap-1.5 rounded-lg border border-white/25 px-3 py-2 text-sm font-bold transition hover:border-white/50 hover:bg-white/10" aria-label="Go back to the previous page">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                    <span class="hidden sm:inline">Back</span>
                </button>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#c5a36a]/60 px-3 py-2 text-sm font-bold transition hover:bg-white/10">
                    <span class="material-symbols-outlined text-base text-[#c5a36a]">lock</span>
                    <span class="hidden sm:inline">Staff Sign In</span>
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <section class="relative overflow-hidden border-b border-amber-900/20 bg-gradient-to-b from-[#0c1c2e] via-[#112235] to-[#172b42] text-white">
            <div class="pointer-events-none absolute inset-0 opacity-[0.06] bg-[radial-gradient(#c5a36a_1px,transparent_1px)] [background-size:24px_24px]"></div>
            <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-8 px-4 py-12 sm:px-6 lg:grid-cols-12 lg:gap-12 lg:px-8 lg:py-16">
                <div class="space-y-6 lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-[#c5a36a]/30 bg-amber-500/10 px-3 py-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#c5a36a]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#e6cda3]">Subic Bay Freeport Zone, Philippines</span>
                    </div>
                    <div>
                        <h1 class="font-display text-4xl font-bold leading-tight sm:text-5xl">Find your perfect stay</h1>
                        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-200">Business and leisure comfort in spacious European-inspired rooms, with bike-friendly features, dining, wellness, and attentive local service.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 text-sm">
                        @foreach (config('hotel.highlights') as $highlight)
                            <span class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2">
                                <span class="material-symbols-outlined text-base text-[#e6c185]">check_circle</span>
                                {{ $highlight }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <aside class="rounded-xl border border-white/15 bg-white/10 p-6 shadow-xl backdrop-blur-sm lg:col-span-4">
                    <div class="mb-4 flex items-center justify-between border-b border-white/10 pb-3">
                        <h2 class="font-display text-xl font-bold">Hotel information</h2>
                        <span class="material-symbols-outlined text-[#c5a36a]">hotel_class</span>
                    </div>
                    <dl class="grid gap-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-300">Property</dt><dd class="text-right font-bold">{{ config('hotel.published_room_count') }} rooms · {{ config('hotel.published_room_type_count') }} types</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-300">Check-in</dt><dd class="font-bold">{{ config('hotel.check_in_time') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-300">Check-out</dt><dd class="font-bold">{{ config('hotel.check_out_time') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-300">Telephone</dt><dd class="font-bold">{{ config('hotel.phone') }}</dd></div>
                    </dl>
                </aside>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-12 lg:px-8 lg:py-14">
            <div class="lg:col-span-7">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#9c7938]">Accommodation catalogue</p>
                    <h2 class="mt-1 font-display text-3xl font-bold text-[#112235]">Rooms & Suites</h2>
                    <p class="mt-2 text-sm text-slate-600">Five official room types and published starting rates from Le Charmé Suites.</p>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    @foreach ($roomTypes as $name => $details)
                        <article @class([
                            'group flex flex-col overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#c5a36a]/60 hover:shadow-lg',
                            'sm:col-span-2 sm:w-full sm:max-w-[calc(50%-0.625rem)] sm:justify-self-center' => $loop->last && $loop->count % 2 !== 0,
                        ])>
                            <figure class="relative aspect-[3/2] overflow-hidden bg-slate-100">
                                <img src="{{ asset($details['image']) }}" alt="{{ $name }} at Le Charmé Suites" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.035]" loading="lazy">
                                <figcaption class="absolute bottom-3 left-3 rounded-md bg-[#0c1c2e]/90 px-2.5 py-1 text-xs font-bold text-white shadow">{{ $name }}</figcaption>
                            </figure>
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-display text-xl font-bold text-[#112235] transition group-hover:text-[#9c7938]">{{ $name }}</h3>
                                        <p class="mt-1.5 text-xs leading-5 text-slate-500">{{ $details['description'] }}</p>
                                    </div>
                                    <span class="material-symbols-outlined text-[#9c7938]">bed</span>
                                </div>
                                <div class="my-4 grid grid-cols-2 gap-x-2 gap-y-2 border-t border-slate-100 pt-3 text-xs text-slate-600">
                                    <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-slate-400">square_foot</span>{{ $details['size_sqm'] }} sqm</span>
                                    <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-slate-400">group</span>Up to {{ $details['capacity'] }} guests</span>
                                    <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-slate-400">king_bed</span>{{ $details['bed_configuration'] }}</span>
                                    <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-slate-400">shower</span>{{ $details['bathrooms'] }} bathroom{{ $details['bathrooms'] > 1 ? 's' : '' }}</span>
                                </div>
                                <div class="mt-auto flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
                                    <p><span class="block text-[11px] font-medium uppercase tracking-wider text-slate-400">From</span><strong class="font-display text-lg text-[#112235]">PHP {{ number_format($details['rate'], 2) }}</strong> <span class="text-xs text-slate-500">/ night</span></p>
                                    @php($inventory = $availableCounts[$name] ?? 0)
                                    <span @class([
                                        'inline-flex rounded-md border px-2.5 py-1 text-[11px] font-bold',
                                        'border-emerald-200 bg-emerald-50 text-emerald-700' => $inventory > 0,
                                        'border-amber-200 bg-amber-50 text-amber-800' => $inventory === 0,
                                    ])>{{ $inventory > 0 ? $inventory.' available' : 'Request only' }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-5 flex items-start gap-3.5 rounded-xl border border-amber-200/70 bg-amber-50/70 p-4 text-xs leading-5 text-amber-950">
                    <span class="material-symbols-outlined flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[#9c7938]">directions_bike</span>
                    <p><strong>Bike-Friendly Destination:</strong> Le Charmé Suites features in-room bike racks and dedicated support tailored for triathletes and leisure cyclists.</p>
                </div>
            </div>

            <aside id="booking-form" class="h-fit scroll-mt-28 rounded-2xl border-2 border-[#c5a36a]/50 bg-white p-6 shadow-xl sm:p-7 lg:sticky lg:top-24 lg:col-span-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#9c7938]">Secure booking request</p>
                <h2 class="mt-1 font-display text-3xl font-bold text-[#112235]">Reserve online</h2>
                <p class="mt-2 text-xs leading-5 text-slate-500">Your request appears instantly in the front desk system. Staff confirmation is required before arrival.</p>

                @if ($errors->any())
                    <div class="mt-5 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">
                        <span class="material-symbols-outlined text-lg">error</span>
                        <div><p class="font-bold">Please correct the highlighted details.</p><ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    </div>
                @endif

                <form action="{{ route('booking.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-xs font-bold text-slate-700" for="first_name">First name<input id="first_name" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" placeholder="Juan" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"></label>
                        <label class="text-xs font-bold text-slate-700" for="last_name">Last name<input id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" placeholder="Dela Cruz" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"></label>
                    </div>
                    <label class="block text-xs font-bold text-slate-700" for="email">Email<input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="guest@example.com" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"></label>
                    <label class="block text-xs font-bold text-slate-700" for="phone">Mobile number<input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+63 917 123 4567" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"></label>
                    <label class="block text-xs font-bold text-slate-700" for="room_type">Room type<select id="room_type" name="room_type" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"><option value="">Choose a room</option>@foreach ($roomTypes as $name => $details)<option value="{{ $name }}" @selected(old('room_type') === $name)>{{ $name }} - PHP {{ number_format($details['rate'], 2) }}</option>@endforeach</select></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="text-xs font-bold text-slate-700" for="check_in_date">Check-in<input id="check_in_date" name="check_in_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('check_in_date') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"></label>
                        <label class="text-xs font-bold text-slate-700" for="check_out_date">Check-out<input id="check_out_date" name="check_out_date" type="date" min="{{ today()->addDay()->toDateString() }}" value="{{ old('check_out_date') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"></label>
                    </div>
                    <label class="block text-xs font-bold text-slate-700" for="number_of_guests">Guests<input id="number_of_guests" name="number_of_guests" type="number" min="1" max="7" value="{{ old('number_of_guests', 2) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30"></label>
                    <label class="block text-xs font-bold text-slate-700" for="notes">Special requests <span class="font-normal text-slate-400">(Optional)</span><textarea id="notes" name="notes" rows="3" placeholder="Bike rack requirements, dietary notes, arrival window..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm transition focus:border-[#c5a36a] focus:ring-[#c5a36a]/30">{{ old('notes') }}</textarea></label>
                    <label class="flex items-start gap-2 text-xs leading-5 text-slate-600"><input name="privacy_consent" value="1" type="checkbox" required @checked(old('privacy_consent')) class="mt-1 rounded border-slate-300 text-[#112235] focus:ring-[#c5a36a]">I consent to Le Charmé Suites using these details to process and contact me about this booking.</label>
                    <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-[#c5a36a]/40 bg-[#112235] px-4 py-3 text-xs font-bold uppercase tracking-wider text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#0c1c2e] hover:shadow-lg active:translate-y-0" type="submit">
                        Submit Booking Request
                        <span class="material-symbols-outlined text-lg text-[#c5a36a]">arrow_forward</span>
                    </button>
                    <p class="text-center text-[10px] leading-4 text-slate-400">Direct booking request · Secure local processing · No hidden system fees</p>
                </form>
            </aside>
        </section>
    </main>

    <footer class="border-t border-white/10 bg-[#0c1c2e] py-7 text-xs text-slate-400">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 text-center sm:px-6 md:flex-row md:text-left lg:px-8">
            <div><span class="font-display font-bold text-white">Le Charmé Suites Subic</span><span class="ml-2">© {{ now()->year }}. All Rights Reserved.</span></div>
            <div class="flex flex-wrap justify-center gap-x-5 gap-y-2">
                <a href="{{ config('hotel.website') }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-white">Official Website</a>
                <a href="mailto:{{ config('hotel.email') }}" class="transition hover:text-white">Front Desk Support</a>
                <a href="tel:{{ preg_replace('/[^+0-9]/', '', config('hotel.phone')) }}" class="transition hover:text-white">{{ config('hotel.phone') }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
