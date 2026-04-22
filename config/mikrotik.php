<?php

return [
    'routers' => [
        'mikrotik-a' => [
            'name'    => env('MIKROTIK_A_NAME', 'MikroTik A'),
            'host'    => env('MIKROTIK_A_HOST', '30.30.30.3'),
            'port'    => (int) env('MIKROTIK_A_PORT', 8728),
            'user'    => env('MIKROTIK_A_USER', 'admin'),
            'pass'    => env('MIKROTIK_A_PASS', ''),
            'timeout' => (int) env('MIKROTIK_A_TIMEOUT', 5),
        ],
        'mikrotik-b' => [
            'name'    => env('MIKROTIK_B_NAME', 'MikroTik B'),
            'host'    => env('MIKROTIK_B_HOST', '30.30.30.4'),
            'port'    => (int) env('MIKROTIK_B_PORT', 8728),
            'user'    => env('MIKROTIK_B_USER', 'admin'),
            'pass'    => env('MIKROTIK_B_PASS', ''),
            'timeout' => (int) env('MIKROTIK_B_TIMEOUT', 5),
        ],
    ],
];
