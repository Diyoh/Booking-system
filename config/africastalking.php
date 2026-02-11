<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Africa's Talking Configuration
    |--------------------------------------------------------------------------
    */

    'username' => env('AT_USERNAME', 'sandbox'),
    'api_key' => env('AT_API_KEY'),
    'sender_id' => env('AT_SENDER_ID', 'BOOKING'),
    'environment' => env('AT_ENVIRONMENT', 'sandbox'),
    'currency_code' => env('AT_CURRENCY_CODE', 'KES'),
];
