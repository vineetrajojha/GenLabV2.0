<?php

use App\Models\AttendanceRecord;

return [
    'essl' => [
        'enabled' => (bool) env('ESSL_WEBHOOK_SECRET'),
        'webhook_secret' => env('ESSL_WEBHOOK_SECRET'),
        'default_status' => env('ESSL_DEFAULT_STATUS', AttendanceRecord::STATUS_PRESENT),
        'allowed_ips' => array_filter(array_map('trim', explode(',', (string) env('ESSL_ALLOWED_IPS', '')))),
    ],
];
