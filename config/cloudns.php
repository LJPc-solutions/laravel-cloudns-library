<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | ClouDNS API Authentication
    |--------------------------------------------------------------------------
    |
    | Configure your ClouDNS API credentials here. You can use either
    | main account credentials or sub-user credentials.
    |
    */

    'auth_id' => env('CLOUDNS_AUTH_ID', ''),
    'auth_password' => env('CLOUDNS_AUTH_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Sub-User Configuration
    |--------------------------------------------------------------------------
    |
    | If you're using a sub-user account, set 'is_sub_user' to true.
    | If the sub-user uses a username instead of ID, also set 'use_sub_username' to true.
    |
    */

    'is_sub_user' => env('CLOUDNS_IS_SUB_USER', false),
    'use_sub_username' => env('CLOUDNS_USE_SUB_USERNAME', false),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the base URL and response format for the ClouDNS API.
    |
    */

    'base_url' => env('CLOUDNS_BASE_URL', 'https://api.cloudns.net'),
    'response_format' => env('CLOUDNS_RESPONSE_FORMAT', 'json'), // json or xml

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    |
    | Configure timeouts and retry settings for API requests.
    |
    */

    'timeout' => env('CLOUDNS_TIMEOUT', 30),
    'retry_times' => env('CLOUDNS_RETRY_TIMES', 3),
    'retry_delay' => env('CLOUDNS_RETRY_DELAY', 1000), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | IP Restrictions
    |--------------------------------------------------------------------------
    |
    | Optional IP whitelist for API access. Leave empty to allow from any IP.
    |
    */

    'allowed_ips' => env('CLOUDNS_ALLOWED_IPS', ''),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for API responses to reduce API calls.
    |
    */

    'cache_enabled' => env('CLOUDNS_CACHE_ENABLED', true),
    'cache_ttl' => env('CLOUDNS_CACHE_TTL', 300), // seconds
    'cache_prefix' => env('CLOUDNS_CACHE_PREFIX', 'cloudns_'),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for API requests and responses.
    |
    */

    'log_enabled' => env('CLOUDNS_LOG_ENABLED', true),
    'log_channel' => env('CLOUDNS_LOG_CHANNEL', 'stack'),
    'log_level' => env('CLOUDNS_LOG_LEVEL', 'info'),

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Default values for various API parameters.
    |
    */

    'defaults' => [
        'ttl' => env('CLOUDNS_DEFAULT_TTL', 3600),
        'rows_per_page' => env('CLOUDNS_DEFAULT_ROWS_PER_PAGE', 30),
        'monitoring_check_period' => env('CLOUDNS_DEFAULT_MONITORING_CHECK_PERIOD', 300),
    ],
];