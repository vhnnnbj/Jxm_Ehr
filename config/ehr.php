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
    'ehr' => env('EHR_URL') . 'api/ehr/v1/',
    'wms' => env('EHR_URL') . 'api/wms/v1/',
    'order' => env('EHR_URL') . 'api/order/v1/',

    /**
     * App
     */
    'app' => env('EHR_URL') . 'api/a1/',

    /**
     * OAUTH URL
     */
    'oauth' => env('EHR_URL') . 'oauth/',

    /**
     * CLIENT_INFO
     */
    'bg_id' => env('EHR_BG_ID', '0'),
    'app_id' => env('EHR_APP_ID', '0'),
];
