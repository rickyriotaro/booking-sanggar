<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Server key dari Midtrans Dashboard
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Client key dari Midtrans Dashboard
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Production Mode
    |--------------------------------------------------------------------------
    |
    | Set true untuk production, false untuk sandbox
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Sanitization
    |--------------------------------------------------------------------------
    |
    | Enable sanitization untuk keamanan
    |
    */
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    /*
    |--------------------------------------------------------------------------
    | Midtrans 3DS
    |--------------------------------------------------------------------------
    |
    | Enable 3D Secure untuk keamanan transaksi
    |
    */
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];
