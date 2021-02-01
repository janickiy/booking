<?php
return [
    'im' => [
        'login' => env('IM_API_LOGIN', ''),
        'password' => env('IM_API_PASSWORD', ''),
        'apiUrl' => env('IM_API_ENDPOINT'),
        'code' => 'im',
    ],
    'payture' => [
        'login' => env('PAYTURE_API_LOGIN', ''),
        'password' => env('PAYTURE_API_PASSWORD', ''),
        'apiUrl' => env('PAYTURE_API_HOST', 'https://sandbox3.payture.com/api/'),
    ]
];