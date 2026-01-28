<?php

return [
    'email' => [
        'delay_between_sends' => env('SHIPMENT_EMAIL_DELAY', 2), // seconds
        'max_retries' => env('SHIPMENT_EMAIL_RETRIES', 3),
        'retry_delay' => env('SHIPMENT_RETRY_DELAY', 5), // seconds
        'max_attachment_size_mb' => env('SHIPMENT_MAX_ATTACHMENT_MB', 10),
    ],
    
    'processing' => [
        'progress_log_interval' => 10, // Log every N clients
        'checkpoint_interval' => 50, // Save progress every N clients
    ],
    
    'temp_directory' => storage_path('app/temp/shipment-reports'),
];