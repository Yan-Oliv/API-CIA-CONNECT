<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
	'http://localhost:62188',
        'http://localhost:55933',
        'https://omnialog.pages.dev',
        'https://omnia.api.br',
	'https://cia-system.up.railway.app'
    ],

    'allowed_headers' => ['*'],

    'allowed_origins_patterns' => [],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,
];
