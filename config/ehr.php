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
    'oms' => env('EHR_URL') . 'api/oms/v1/',
    'esb' => env('EHR_URL') . 'api/esb/v1/',

    /**
     * App
     */
    'app' => env('EHR_URL') . 'api/a1/',
    'ehr_app' => env('EHR_URL') . 'api/ehr/a1/',
    'wms_app' => env('EHR_URL') . 'api/wms/a1/',
    'oms_app' => env('EHR_URL') . 'api/oms/a1/',

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
