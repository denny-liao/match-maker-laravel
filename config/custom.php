<?php

return [
    'payment' => [
        'ecpay' => [
            'merchant_id' => env('MERCHANT_ID', 'default_value'),
            'hash_key' => env('HASH_KEY', 'default_value'),
            'hash_iv' => env('HASH_IV', 'default_value'),
            'url' => env('ECPAY_URL', 'default_value'),
        ],
    ],
];
