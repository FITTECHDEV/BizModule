<?php

return [
    /*
    |--------------------------------------------------------------------------
    | cPanel API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your cPanel API connection details here.
    | Set CPANEL_MOCK_MODE=true in .env to test without real credentials.
    |
    */

    'url' => env('CPANEL_URL', 'cpanel.yourdomain.com'),
    
    'port' => env('CPANEL_PORT', 2083),
    
    'username' => env('CPANEL_USERNAME', ''),
    
    'api_token' => env('CPANEL_API_TOKEN', ''),
    
    'password' => env('CPANEL_PASSWORD', ''),
    
    'default_domain' => env('CPANEL_DEFAULT_DOMAIN', 'yourdomain.com'),
    
    'default_quota' => env('CPANEL_DEFAULT_QUOTA', 250), // In MB, 0 = unlimited
    
    'mock_mode' => env('CPANEL_MOCK_MODE', true),
    
    'timeout' => env('CPANEL_TIMEOUT', 30), // seconds
    
    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    */
    
    'email' => [
        'min_password_length' => 8,
        'max_quota' => 1000, // MB
        'send_welcome_email' => false,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Mailing List Configuration
    |--------------------------------------------------------------------------
    */
    
    'mailing_list' => [
        'default_privacy' => 'private', // private or public
        'require_confirmation' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    
    'endpoints' => [
        'create_email' => '/execute/Email/add_pop',
        'delete_email' => '/execute/Email/delete_pop',
        'list_emails' => '/execute/Email/list_pops',
        'change_password' => '/execute/Email/passwd_pop',
        'create_mailing_list' => '/execute/Email/add_mailing_list',
        'delete_mailing_list' => '/execute/Email/delete_mailing_list',
        'add_subscriber' => '/execute/Email/add_mailing_list_subscriber',
        'remove_subscriber' => '/execute/Email/remove_mailing_list_subscriber',
        'list_mailing_lists' => '/execute/Email/list_mailing_lists',
    ],
];