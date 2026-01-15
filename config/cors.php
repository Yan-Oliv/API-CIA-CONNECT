<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'auth/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://omnia.api.br',
        'https://omnialog.pages.dev',
        'https://cia-system.up.railway.app',
        'http://localhost:62188',
        'http://localhost:55933',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => true,
];