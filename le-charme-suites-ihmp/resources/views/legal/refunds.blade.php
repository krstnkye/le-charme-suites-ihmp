@extends('layouts.legal')

@section('title', 'Refund Policy')

@section('content')
    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Booking requests made in this portal</h2>
        <p class="mt-2">The portal does not collect any payment online. A booking request stays pending until the front desk confirms it, and you can withdraw a pending request at no charge by contacting the front desk with your booking reference.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Confirmed reservations</h2>
        <p class="mt-2">Once the hotel confirms a reservation, Le Charmé Suites' official cancellation terms apply, as published on the hotel's <a href="{{ config('hotel.website') }}faq.php" target="_blank" rel="noopener noreferrer" class="font-bold text-[#7a5d26] underline">official FAQ</a>:</p>
        <ul class="mt-2 list-disc space-y-2 pl-5">
            <li>All confirmed reservations are non-refundable.</li>
            <li><strong>8 days or more before arrival:</strong> no cancellation charge, and you may rebook your stay.</li>
            <li><strong>Within 7 days of arrival:</strong> a charge equal to one night per room reserved.</li>
            <li><strong>Within 24 hours of arrival, or no-show:</strong> a charge of 100% of the total booking value.</li>
        </ul>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Refunds</h2>
        <p class="mt-2">Under the hotel's official policy, money is refunded only in verified cases of force majeure, with supporting documents, and hotel management makes the final decision. If you were charged in error or paid more than your itemized bill, the front desk will correct the bill.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">How to cancel or ask about a refund</h2>
        <p class="mt-2">Contact the front desk at <a href="mailto:{{ config('hotel.email') }}" class="font-bold text-[#7a5d26] underline">{{ config('hotel.email') }}</a> or {{ config('hotel.phone') }}, and include your booking reference or receipt number. These terms do not limit any rights you have under the Consumer Act of the Philippines (Republic Act No. 7394).</p>
    </section>
@endsection
