<?php

return [
    /**
     * Ehr URL
     */
    'url' => env('EHR_URL', 'http://192.168.130.103:6688/'),

    /**
     * RPC Service
     */
    'service' => env('EHR_URL', 'http://192.168.130.103:6688/') . 'api/service/',

    /**
     * API
     */
    'api' => env('EHR_URL', 'http://192.168.130.103:6688/') . 'api/v1/',

    /**
     * OAUTH URL
     */
    'oauth' => env('EHR_URL', 'http://192.168.130.103:6688/') . 'oauth/',

    /**
     * CLIENT_INFO
     */
    'client' => [
        'id' => env('EHR_CLIENT_ID', ''),
        'secret' => env('EHR_CLIENT_SECRET', ''),
    ],
    'bg_id' => env('EHR_BG_ID', 0),
    'app_id' => env('EHR_APP_ID', '0'),

    'broadcast' => [
        'port' => env('EHR_BROADCAST_PORT', 1980),
    ]
];
