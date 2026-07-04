<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'HirePilot API is running.',
        'data' => [
            'service' => 'HirePilot API',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});
