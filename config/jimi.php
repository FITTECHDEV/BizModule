<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jimi IoT API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Jimi GPS tracking platform integration
    | Server: EU (https://eu-open.tracksolidpro.com)
    |
    */

    'base_url' => env('JIMI_BASE_URL', 'https://eu-open.tracksolidpro.com/route/rest'),

    'app_key' => env('JIMI_APP_KEY'),  // NO DEFAULT - must be in .env

    'app_secret' => env('JIMI_APP_SECRET'),  // NO DEFAULT - must be in .env

    'password_md5' => env('JIMI_PASSWORD_MD5', '426f77e303c0cf9c6a72f145d32568f7'),

    'account' => env('JIMI_ACCOUNT', 'freightintime'),

    /*
    |--------------------------------------------------------------------------
    | Vehicle IMEI Mappings
    |--------------------------------------------------------------------------
    |
    | Map vehicle registration numbers to their IMEI numbers
    |
    */

    'vehicles' => [
        'KDN 345Y' => '865784055073301',
        'KDN 352Y' => '865784055073302',
        'KDN 351Y' => '865784055074028',  // '865784055074028' => 'KDN 351Y'
        'KDN 349Y' => '865784055073304',
        'KDN 124Z' => '865784055073305',
        'KDN 346Y' => '865784055073306',
        'KDN 209Y' => '865784055073307',
        'KDN 331Y' => '865784055073308',
        'KDN 347Y' => '865784055073309',
        'KDN 348Y' => '865784055073310',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Access token cache duration (in seconds)
    | Default: 3600 (1 hour)
    |
    */

    'token_cache_duration' => env('JIMI_TOKEN_CACHE', 3600),

    /*
    |--------------------------------------------------------------------------
    | API Timeouts
    |--------------------------------------------------------------------------
    |
    | HTTP request timeouts (in seconds)
    |
    */

    'timeout' => [
        'token' => 10,
        'vehicle_list' => 15,
        'mileage' => 15,
        'track' => 20,
    ],

];
