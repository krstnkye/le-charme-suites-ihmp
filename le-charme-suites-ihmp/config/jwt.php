<?php

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'issuer' => env('JWT_ISSUER', env('APP_URL', 'http://127.0.0.1:8000')),
    'audience' => env('JWT_AUDIENCE', 'le-charme-ihmp-api'),
    'access_ttl' => (int) env('JWT_ACCESS_TTL', 15),
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 10080),
];
