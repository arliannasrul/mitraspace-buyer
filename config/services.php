<?php

return [

    /*
    |----------------------------------------------------------------------
    | Seller Center API (Backend Internal via Ngrok)
    |----------------------------------------------------------------------
    */
    'seller_api' => [
        'base_url' => env('SELLER_API_BASE_URL', 'http://localhost'),
    ],

    /*
    |----------------------------------------------------------------------
    | DOKU Payment Gateway
    |----------------------------------------------------------------------
    */
    'doku' => [
        'client_id'     => env('DOKU_CLIENT_ID', ''),
        'secret_key'    => env('DOKU_SECRET_KEY', ''),
        'is_production' => env('DOKU_IS_PRODUCTION', false),
        'success_url'   => env('DOKU_SUCCESS_URL', env('APP_URL') . '/payment/success'),
        'failed_url'    => env('DOKU_FAILED_URL', env('APP_URL') . '/payment/failed'),
        'notify_url'    => env('DOKU_NOTIFY_URL', env('APP_URL') . '/webhook/doku'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URL'),
    ],

    /*
    |----------------------------------------------------------------------
    | KiriminAja Shipping API
    |----------------------------------------------------------------------
    */
    'kiriminaja' => [
        'api_key'     => env('KIRIMINAJA_API_KEY', ''),
        'base_url'    => env('KIRIMINAJA_BASE_URL', 'https://tdev.kiriminaja.com'),
        'origin_city' => env('KIRIMINAJA_ORIGIN_CITY', 'Jakarta'),
        'use_mock'    => env('KIRIMINAJA_USE_MOCK', true),
    ],

    /*
    |----------------------------------------------------------------------
    | RajaOngkir Shipping API
    |----------------------------------------------------------------------
    */
    'rajaongkir' => [
        'api_key'     => env('RAJAONGKIR_API_KEY', ''),
        'base_url'    => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'origin_city' => env('RAJAONGKIR_ORIGIN_CITY', 'Jakarta'),
        'use_mock'    => env('RAJAONGKIR_USE_MOCK', true),
    ],

    /*
    |----------------------------------------------------------------------
    | Third Party Services
    |----------------------------------------------------------------------
    */
    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
