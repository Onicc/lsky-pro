<?php

return [
    'version' => '3.2.3',
    'install' => [
        'admin_email' => env('LSKY_ADMIN_EMAIL'),
        'admin_password' => env('LSKY_ADMIN_PASSWORD'),
    ],
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_PROXIES', ''))))),
    'trusted_hosts' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_HOSTS', ''))))),
];
