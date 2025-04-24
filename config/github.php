<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => 'main',

    /*
    |--------------------------------------------------------------------------
    | GitHub Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Example
    | configuration has been included, but you may add as many connections as
    | you would like. Note that the 3 supported authentication methods are:
    | "application", "jwt" and "token".
    |
    */

    'connections' => [
        'main' => [
            'token'      => env('GITHUB_TOKEN'),
            'method'     => 'token',
            // 'backoff'    => false,
            // 'cache'      => false,
            // 'version'    => 'v3',
            // 'enterprise' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Cache
    |--------------------------------------------------------------------------
    |
    | Here are each of the cache configurations setup for your application.
    | Only the "illuminate" driver is provided out of the box. Example
    | configuration has been included.
    |
    */

    'cache' => [
        'main' => [
            'driver'    => 'illuminate',
            'connector' => null, // null means use default driver
            // 'min'       => 43200,
            // 'max'       => 172800
        ],
    ],
]; 