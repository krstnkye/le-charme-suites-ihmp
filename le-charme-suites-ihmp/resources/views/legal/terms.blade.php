@extends('layouts.legal')

@section('title', 'Terms of Service')

@section('content')
    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">About this portal</h2>
        <p class="mt-2">Le Charmé Suites IHMP is an academic capstone prototype. It is not connected to the official Le Charmé Suites reservation system, and a request made here does not create a real hotel reservation. By using the portal you agree to these terms.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Booking requests</h2>
        <ul class="mt-2 list-disc space-y-2 pl-5">
            <li>You must be at least 18 years old to submit a booking request.</li>
            <li>A submitted request is <strong>pending</strong> until the front desk confirms it. Availability is not guaranteed until confirmation.</li>
            <li>Room rates are shown in Philippine pesos per night. Your room total is the nightly rate multiplied by the number of nights, as shown on the booking form before you submit.</li>
            <li>Extra services you use during your stay, such as dining, spa, or minibar items, are added to your bill only when you use them, and every charge is itemized on your bill before payment.</li>
        </ul>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Hotel policies</h2>
        <p class="mt-2">These are Le Charmé Suites' policies as published on the hotel's <a href="{{ config('hotel.website') }}faq.php" target="_blank" rel="noopener noreferrer" class="font-bold text-[#7a5d26] underline">official FAQ</a>:</p>
        <ul class="mt-2 list-disc space-y-2 pl-5">
            <li>Check-in is from {{ config('hotel.check_in_time') }} and check-out is by {{ config('hotel.check_out_time') }}. Early check-in and late check-out are available on request, subject to availability, and additional charges may apply.</li>
            <li>The hotel is 100% smoke-free. Smoking is allowed only in designated outdoor areas, and smoking anywhere else incurs a PHP 5,000 cleaning fee.</li>
            <li>Pets are not allowed, except certified service animals.</li>
        </ul>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Your responsibilities</h2>
        <p class="mt-2">Please give accurate details, use the portal only for genuine booking requests, and do not attempt to access staff pages without an authorized account. Staff accounts are personal and must not be shared.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Cancellations and refunds</h2>
        <p class="mt-2">See our <a href="{{ route('legal.refunds') }}" class="font-bold text-[#7a5d26] underline">Refund Policy</a>.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Privacy</h2>
        <p class="mt-2">How we handle your information is described in our <a href="{{ route('legal.privacy') }}" class="font-bold text-[#7a5d26] underline">Privacy Policy</a> and <a href="{{ route('legal.cookies') }}" class="font-bold text-[#7a5d26] underline">Cookie Policy</a>.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Contact</h2>
        <p class="mt-2">Questions about these terms can be sent to <a href="mailto:{{ config('hotel.email') }}" class="font-bold text-[#7a5d26] underline">{{ config('hotel.email') }}</a> or {{ config('hotel.phone') }}.</p>
    </section>
@endsection
