<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'CIA API',
        'version' => '1.0.0'
    ]);
});

