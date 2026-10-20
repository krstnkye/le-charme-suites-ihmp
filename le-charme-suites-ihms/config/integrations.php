<?php

return [
    'api_key' => env('IHMP_API_KEY', env('IHMS_API_KEY')),
    'max_export_records' => (int) env('IHMP_MAX_EXPORT_RECORDS', env('IHMS_MAX_EXPORT_RECORDS', 200)),
    'official_website' => [
        'url' => 'https://lecharmehotels.com/',
        'booking_url' => 'https://lecharmehotels.com/room-availability.php',
        'connection_approved' => (bool) env('IHMP_OFFICIAL_WEBSITE_APPROVED', false),
        'note' => 'The official website has its own booking workflow, but no public API or webhook is documented. Live connection requires written hotel authorization and technical credentials.',
    ],

    /*
    | IHMP is an academic capstone prototype. It is not connected to the hotel's live
    | reservation system and must never hold real guest data. The website booking
    | simulator stands in for the official website so the integration can be demonstrated.
    */
    'academic_notice' => 'Academic capstone prototype. This portal is not connected to the official Le Charmé Suites reservation system, does not confirm real stays, and uses sample data only. Do not enter real personal information.',

    'booking_sources' => [
        'front_desk' => 'Front Desk',
        'public_website' => 'IHMP Direct Booking',
        'hotel_website_api' => 'Official Website API',
        'official_website_simulation' => 'Official Website (Simulated)',
    ],
];
