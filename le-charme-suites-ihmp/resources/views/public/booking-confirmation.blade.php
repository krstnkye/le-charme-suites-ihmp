<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Booking Request Received - Le Charmé Suites</title>@include('partials.offline-assets') @include('partials.favicon')<script>tailwind.config={theme:{extend:{fontFamily:{body:['Manrope'],display:['Noto Serif']}}}};</script></head>
<body class="min-h-screen bg-[#f7f4ee] font-body text-[#01162d]">
    <div class="border-b border-amber-300 bg-amber-50 px-4 py-2 text-center text-[11px] font-semibold leading-5 text-amber-900" role="note">
        <span class="material-symbols-outlined align-middle text-sm" aria-hidden="true">school</span>
        {{ config('integrations.academic_notice') }}
    </div>
    <main class="mx-auto flex min-h-screen max-w-3xl items-center px-5 py-10">
        <section class="w-full rounded-lg border border-[#ded7cc] bg-white p-6 shadow-lg sm:p-10">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-700"><span class="material-symbols-outlined text-3xl" aria-hidden="true">task_alt</span></div>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.2em] text-[#8b6827]">Request received</p>
            <h1 class="mt-2 font-display text-3xl font-bold">Thank you, {{ $reservation->guest->first_name }}.</h1>
            <p class="mt-3 leading-7 text-slate-600">Your online booking request is now visible in the IHMP front desk queue. Keep your reference below. The hotel will contact you after availability and booking details are confirmed.</p>
            <div class="mt-6 rounded-lg bg-[#01162d] p-5 text-white"><p class="text-xs uppercase tracking-wider text-[#ffdeaa]">Booking reference</p><p class="mt-1 font-mono text-2xl font-bold">{{ $reservation->booking_reference }}</p></div>
            <dl class="mt-6 grid gap-4 rounded-lg border border-[#ded7cc] p-5 sm:grid-cols-2"><div><dt class="text-xs text-slate-500">Room</dt><dd class="font-bold">{{ $reservation->room->room_type }}</dd></div><div><dt class="text-xs text-slate-500">Status</dt><dd class="font-bold text-amber-700">Pending front desk confirmation</dd></div><div><dt class="text-xs text-slate-500">Check-in</dt><dd class="font-bold">{{ $reservation->check_in_date->format('M d, Y') }} after {{ config('hotel.check_in_time') }}</dd></div><div><dt class="text-xs text-slate-500">Check-out</dt><dd class="font-bold">{{ $reservation->check_out_date->format('M d, Y') }} by {{ config('hotel.check_out_time') }}</dd></div></dl>
            <div class="mt-6 flex flex-wrap gap-3"><a href="{{ route($reservation->source === 'official_website_simulation' ? 'website-simulation.create' : 'booking.create') }}" class="rounded-lg bg-[#01162d] px-4 py-2.5 text-sm font-bold text-white">Make another booking</a><a href="{{ config('hotel.website') }}" class="rounded-lg border border-[#c5a36a] px-4 py-2.5 text-sm font-bold text-[#755a28]">Visit official website</a></div>
            <p class="mt-6 text-xs leading-5 text-slate-600">Need to cancel? Contact the front desk at {{ config('hotel.email') }} with your booking reference. See the <a href="{{ route('legal.refunds') }}" class="font-bold text-[#755a28] underline">Refund Policy</a> for the hotel's cancellation terms.</p>
            <nav class="mt-4 flex flex-wrap gap-x-4 gap-y-1 border-t border-[#ded7cc] pt-4 text-xs" aria-label="Policies">@include('partials.legal-links', ['linkClass' => 'font-semibold text-[#755a28] hover:underline'])</nav>
        </section>
    </main>
    @include('partials.cookie-notice')
</body>
</html>
