<?php

return [
    'temp_directory' => storage_path('app/temp/shipment-reports'),
    
    'processing' => [
        'progress_log_interval' => env('SHIPMENT_REPORTS_PROGRESS_LOG_INTERVAL', 10),
    ],
    
    'email' => [
        'delay_between_sends' => env('SHIPMENT_REPORTS_DELAY_BETWEEN_SENDS', 2), // seconds
        'max_retries' => env('SHIPMENT_REPORTS_MAX_RETRIES', 3),
        'retry_delay' => env('SHIPMENT_REPORTS_RETRY_DELAY', 5), // seconds
        'max_attachment_size_mb' => env('SHIPMENT_REPORTS_MAX_ATTACHMENT_MB', 10),
    ],
];