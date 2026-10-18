<?php

return [
    'api_key' => env('IHMS_API_KEY'),
    'max_export_records' => (int) env('IHMS_MAX_EXPORT_RECORDS', 200),
];
