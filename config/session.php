<?php

use Illuminate\Support\Str;

return [
    'driver' => env('SESSION_DRIVER', 'database'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'secure' => env('SESSION_SECURE_COOKIE', true), 
    'http_only' => true,
    'same_site' => 'lax',
];
