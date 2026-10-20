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
];
