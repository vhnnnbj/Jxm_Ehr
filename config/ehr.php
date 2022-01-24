<?php

return [
    /**
     * Ehr URL
     */
    'url' => env('EHR_URL'),

    /**
     * RPC Service
     */
    'service' => env('EHR_URL') . 'api/service/',

    /**
     * API
     */
    'api' => env('EHR_URL') . 'api/v1/',

    /**
     * OAUTH URL
     */
    'oauth' => env('EHR_URL') . 'oauth/',

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
