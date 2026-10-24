@extends('layouts.legal')

@section('title', 'Privacy Policy')

@section('content')
    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Who we are</h2>
        <p class="mt-2">Le Charmé Suites IHMP (Integrated Hotel Management Portal) is an academic mini thesis prototype built for Le Charmé Suites, {{ config('hotel.address') }}. It is not the hotel's official reservation system and runs on sample data. Please do not enter real personal information. This policy follows the Philippine Data Privacy Act of 2012 (Republic Act No. 10173).</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">What we collect and why</h2>
        <ul class="mt-2 list-disc space-y-2 pl-5">
            <li><strong>Online booking requests:</strong> first and last name, email, mobile number, room type, stay dates, number of guests, and any special requests you type. We use these only to process your booking request and to contact you about it.</li>
            <li><strong>Front desk guest records:</strong> staff may add an address and a government ID type and number at check-in to verify identity, as hotels are required to do. These fields are optional in the portal.</li>
            <li><strong>Staff accounts:</strong> name, work email, department, a hashed password, and an optional profile photo, used to sign staff in and limit each department to the pages it needs.</li>
            <li><strong>Security records:</strong> the IP address, browser, and time of sign-ins, failed sign-ins, sign-outs, and online booking submissions, used to detect misuse and keep an audit trail.</li>
        </ul>
        <p class="mt-3">We do not ask for birthdates, payment card details, or any data that is not needed for the purposes above. No payment is collected online.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Where your data is kept</h2>
        <p class="mt-2">All records stay in the portal's own local database on the hotel's computer or private network. The portal does not use third-party analytics, advertising, social media plugins, or external software development kits (SDKs). Its fonts, icons, and styling are stored locally, so pages load without contacting outside services. Links to the official hotel website open that separate site, which has its own privacy policy.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Who can see it</h2>
        <p class="mt-2">Only authorized hotel staff, limited by department. For example, housekeeping accounts cannot open guest or billing records. We do not sell or share personal data with anyone else unless the law requires it.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">How long we keep it</h2>
        <p class="mt-2">We keep booking and guest records only as long as needed for the stay, the hotel's legal record-keeping, and resolving any disputes. Because this is a prototype, demonstration data may be reset at any time.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Children's privacy</h2>
        <p class="mt-2">Online bookings must be made by an adult aged 18 or older. Children may be included in the guest count, but we do not ask for or knowingly collect their personal details online. If you believe a child's information was submitted, contact us and we will delete it. Any child's details recorded at the front desk are provided by the parent or guardian checking in with them.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Your rights</h2>
        <p class="mt-2">You may ask to access, correct, or delete your personal data, object to its processing, or withdraw your consent. Withdrawing consent does not affect processing already done, but we may be unable to keep an active booking without your contact details. To make a request, email <a href="mailto:{{ config('hotel.email') }}" class="font-bold text-[#7a5d26] underline">{{ config('hotel.email') }}</a> or call {{ config('hotel.phone') }}. You may also file a complaint with the National Privacy Commission.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Emails we send</h2>
        <p class="mt-2">The portal only sends service messages you need, such as a staff password reset link. It does not send newsletters or marketing emails. If marketing emails are ever added, every message will include an unsubscribe link.</p>
    </section>
@endsection
