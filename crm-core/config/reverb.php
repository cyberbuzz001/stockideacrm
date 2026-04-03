<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reverb Default Connection
    |--------------------------------------------------------------------------
    |
    | This option controls the default Connection that will be used by
    | Reverb when no other connection is specified.
    |
    */

    'default' => env('REVERB_CONNECTION', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Servers
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the Reverb servers for your application.
    | Each server connection must include a driver and any additional
    | configuration options that may be required by that driver.
    |
    */

    'servers' => [

        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_HOST'),
            'options' => [
                'tls' => [],
            ],
            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10000),
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', 6379),
                ],
            ],
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
            'apps' => [
                [
                    'key' => env('REVERB_APP_KEY'),
                    'secret' => env('REVERB_APP_SECRET'),
                    'app_id' => env('REVERB_APP_ID'),
                    'options' => [
                        'host' => env('REVERB_HOST'),
                        'port' => env('REVERB_PORT', 443),
                        'scheme' => env('REVERB_SCHEME', 'https'),
                        'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
                    ],
                    'allowed_origins' => ['*'],
                    'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
                    'activity_timeout' => env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
                ],
            ],
        ],

    ],

];
