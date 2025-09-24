<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the HTTP client used for PMS API requests
    |
    */
    'http' => [
        /*
        | Request timeout in seconds
        */
        'timeout' => env('PHP_PMS_HTTP_TIMEOUT', 30),

        /*
        | SSL certificate verification
        */
        'verify_ssl' => env('PHP_PMS_VERIFY_SSL', true),

        /*
        | Enable HTTP debug output (cURL verbose mode)
        | Only works in local environment for security
        | Set PHP_PMS_HTTP_DEBUG=true to enable debug output
        */
        'debug' => env('PHP_PMS_HTTP_DEBUG', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | BookingManager Configuration
    |--------------------------------------------------------------------------
    */
    'booking_manager' => [
        'base_url' => env('BOOKING_MANAGER_BASE_URL', 'https://xml.billypds.com'),
        'api_key' => env('BOOKING_MANAGER_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    'logging' => [
        /*
        | Log API requests and responses
        */
        'enabled' => env('PHP_PMS_LOGGING', false),

        /*
        | Log channel to use for PMS requests
        */
        'channel' => env('PHP_PMS_LOG_CHANNEL', 'default'),
    ],
];