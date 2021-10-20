<?php

return [
    /**
     * Ehr URL
     */
    'url' => env('EHR_URL', 'http://192.168.130.103:6688/'),

    /**
     * RPC Service
     */
    'service' => config('ehr.url') . 'api/service/',

    /**
     * API
     */
    'api' => config('ehr.url') . 'api/v1/',

    /**
     * OAUTH URL
     */
    'oauth' => config('ehr.url') . 'oauth/',

    /**
     * CLIENT_INFO
     */
    'client' => [
        'id' => env('EHR_CLIENT_ID', ''),
        'secret' => env('EHR_CLIENT_SECRET', ''),
    ],
];
