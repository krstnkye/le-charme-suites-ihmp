<?php

return [
    'api_key' => env('IHMP_API_KEY'),
    'max_export_records' => (int) env('IHMP_MAX_EXPORT_RECORDS', 200),
];
