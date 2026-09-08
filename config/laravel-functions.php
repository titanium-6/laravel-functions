<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Force SSL
    |--------------------------------------------------------------------------
    |
    | Read via config(), not env(), so it survives `php artisan config:cache`.
    |
    */

    'disable_ssl' => env('DISABLE_SSL', false),

    /*
    |--------------------------------------------------------------------------
    | Blacklisted email domains
    |--------------------------------------------------------------------------
    |
    | Comma separated. mailinator.com is always appended by LaravelEmail.
    |
    */

    'blacklist_email_domains' => env('BLACKLIST_EMAIL_DOMAINS', ''),

    /*
    |--------------------------------------------------------------------------
    | Route logging
    |--------------------------------------------------------------------------
    |
    | mode:
    |   'sync'           write the row inline (default)
    |   'after_response' write it in terminate(); no queue needed, but the
    |                    worker is held until it completes
    |   'queue'          dispatch WriteRouteLog; requires a worker on the
    |                    configured connection/queue
    |
    | connection is set separately from the application default so the log can
    | go to Redis without moving every other queue.
    |
    | redact_headers adds to RouteLogWriter::ALWAYS_REDACT_HEADERS; max_request_bytes and
    | max_response_bytes cap each stored body, 0 disables. All of it applies to both the
    | log.route middleware and the log_route() helper.
    |
    */

    'log_route' => [
        'mode' => env('LOG_ROUTE_MODE', 'sync'),
        'connection' => env('LOG_ROUTE_QUEUE_CONNECTION'),
        'queue' => env('LOG_ROUTE_QUEUE', 'log_route'),
        'store_response_body' => env('LOG_ROUTE_STORE_RESPONSE_BODY', true),
        'max_request_bytes' => (int) env('LOG_ROUTE_MAX_REQUEST_BYTES', 10000),
        'max_response_bytes' => (int) env('LOG_ROUTE_MAX_RESPONSE_BYTES', 10000),
        'redact_headers' => [],
    ],

];
