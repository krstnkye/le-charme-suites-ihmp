<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Your Stay - Le Charmé Suites Subic</title>
    @include('partials.offline-assets')
    @include('partials.favicon')
    <script>tailwind.config = { theme: { extend: { fontFamily: { body: ['Manrope'], display: ['Noto Serif'] } } } };</script>
</head>
<body class="min-h-screen bg-[#f7f4ee] font-body text-[#01162d] antialiased">
    <header class="border-b border-[#ded7cc] bg-[#01162d] text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
            <div class="flex items-center gap-4">
                <div class="rounded bg-white px-3 py-2"><img src="{{ asset('images/le-charme-logo-white.png') }}" alt="Le Charmé Suites" class="h-12 w-32 object-contain"></div>
                <div><p class="font-display text-xl font-bold">Le Charmé Suites Subic</p><p class="text-xs text-[#ffdeaa]">European-inspired luxury with modern Filipino hospitality</p></div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ route('login') }}')" class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 px-3 py-2 text-sm font-bold hover:bg-white/10" aria-label="Go back to the previous page">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                    Back
                </button>
                <a href="{{ route('login') }}" class="rounded-lg border border-[#ffdeaa]/50 px-4 py-2 text-sm font-bold hover:bg-white/10">Staff Sign In</a>
            </div>
        </div>
    </header>

    <main>
        <section class="bg-[#01162d] text-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-8 lg:grid-cols-[1fr_420px] lg:items-center lg:py-14">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#ffdeaa]">Subic Bay Freeport Zone, Philippines</p>
                    <h1 class="mt-3 font-display text-4xl font-bold sm:text-5xl">Find your perfect stay</h1>
                    <p class="mt-4 max-w-2xl leading-7 text-slate-200">Business and leisure comfort in spacious European-inspired rooms, with bike-friendly features, dining, wellness, and attentive local service.</p>
                    <div class="mt-6 flex flex-wrap gap-3 text-sm">
                        @foreach (config('hotel.highlights') as $highlight)
                            <span class="inline-flex items-center gap-2 rounded border border-white/15 bg-white/5 px-3 py-2"><span class="material-symbols-outlined text-base text-[#ffdeaa]">check_circle</span>{{ $highlight }}</span>
                        @endforeach
                    </div>
                </div>
                <aside class="rounded-lg border border-[#ffdeaa]/30 bg-white/10 p-5">
                    <p class="font-display text-xl font-bold">Hotel information</p>
                    <dl class="mt-4 grid gap-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-300">Property</dt><dd class="font-bold">{{ config('hotel.published_room_count') }} rooms · {{ config('hotel.published_room_type_count') }} types</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-300">Check-in</dt><dd class="font-bold">{{ config('hotel.check_in_time') }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-300">Check-out</dt><dd class="font-bold">{{ config('hotel.check_out_time') }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-300">Telephone</dt><dd class="font-bold">{{ config('hotel.phone') }}</dd></div></dl>
                </aside>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <div class="grid gap-6 xl:grid-cols-[1fr_430px]">
                <div>
                    <h2 class="font-display text-3xl font-bold">Rooms & Suites</h2>
                    <p class="mt-2 text-sm text-slate-600">Five official room types and published starting rates from Le Charmé Suites.</p>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($roomTypes as $name => $details)
                            <article class="overflow-hidden rounded-lg border border-[#ded7cc] bg-white shadow-sm">
                                <figure class="relative aspect-[3/2] overflow-hidden bg-slate-100">
                                    <img src="{{ asset($details['image']) }}" alt="{{ $name }} at Le Charmé Suites" class="h-full w-full object-cover transition duration-300 hover:scale-[1.02]" loading="lazy">
                                    <figcaption class="absolute bottom-3 left-3 rounded bg-[#01162d]/85 px-2.5 py-1 text-xs font-bold text-white">{{ $name }}</figcaption>
                                </figure>
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4"><div><h3 class="font-display text-xl font-bold">{{ $name }}</h3><p class="mt-1 text-sm text-slate-600">{{ $details['description'] }}</p></div><span class="material-symbols-outlined text-[#8b6827]">hotel</span></div>
                                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-slate-600"><span>{{ $details['size_sqm'] }} sqm</span><span>Up to {{ $details['capacity'] }} guests</span><span>{{ $details['bed_configuration'] }}</span><span>{{ $details['bathrooms'] }} bathroom{{ $details['bathrooms'] > 1 ? 's' : '' }}</span></div>
                                    <div class="mt-4 flex items-end justify-between gap-3 border-t border-[#eee9df] pt-4"><p><span class="text-xs text-slate-500">From</span><br><strong class="font-display text-xl">PHP {{ number_format($details['rate'], 2) }}</strong> <span class="text-xs">/ night</span></p><span class="rounded bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-800">{{ $availableCounts[$name] ?? 0 }} in inventory</span></div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <aside id="booking-form" class="h-fit rounded-lg border border-[#c5a36a] bg-white p-5 shadow-lg xl:sticky xl:top-5">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#8b6827]">Secure booking request</p>
                    <h2 class="mt-1 font-display text-2xl font-bold">Reserve online</h2>
                    <p class="mt-2 text-xs leading-5 text-slate-600">Your request appears instantly in the front desk system. Staff confirmation is required before arrival.</p>
                    @if ($errors->any())<div class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800"><p class="font-bold">Please correct the highlighted details.</p><ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    <form action="{{ route('booking.store') }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3"><label class="text-xs font-bold">First name<input name="first_name" value="{{ old('first_name') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label><label class="text-xs font-bold">Last name<input name="last_name" value="{{ old('last_name') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label></div>
                        <label class="block text-xs font-bold">Email<input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label>
                        <label class="block text-xs font-bold">Mobile number<input name="phone" type="tel" value="{{ old('phone') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="+63"></label>
                        <label class="block text-xs font-bold">Room type<select name="room_type" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="">Choose a room</option>@foreach ($roomTypes as $name => $details)<option value="{{ $name }}" @selected(old('room_type') === $name)>{{ $name }} - PHP {{ number_format($details['rate'], 2) }}</option>@endforeach</select></label>
                        <div class="grid grid-cols-2 gap-3"><label class="text-xs font-bold">Check-in<input name="check_in_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('check_in_date') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label><label class="text-xs font-bold">Check-out<input name="check_out_date" type="date" min="{{ today()->addDay()->toDateString() }}" value="{{ old('check_out_date') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label></div>
                        <label class="block text-xs font-bold">Guests<input name="number_of_guests" type="number" min="1" max="7" value="{{ old('number_of_guests', 2) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label>
                        <label class="block text-xs font-bold">Special requests<textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">{{ old('notes') }}</textarea></label>
                        <label class="flex items-start gap-2 text-xs leading-5 text-slate-600"><input name="privacy_consent" value="1" type="checkbox" required class="mt-1">I consent to Le Charmé Suites using these details to process and contact me about this booking.</label>
                        <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#01162d] px-4 py-3 text-sm font-bold text-white hover:bg-[#17314d]"><span class="material-symbols-outlined text-lg text-[#ffdeaa]">calendar_add_on</span>Submit booking request</button>
                    </form>
                </aside>
            </div>
        </section>
    </main>
    <footer class="border-t border-[#ded7cc] bg-white px-5 py-6 text-center text-xs text-slate-600">{{ config('hotel.address') }} · {{ config('hotel.phone') }} · {{ config('hotel.email') }}</footer>
</body>
</html>
