<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Compatibility shim for health check during early bootstrap/testing
Route::get('/api/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'HirePilot API is running.',
        'data' => [
            'service' => 'HirePilot API',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});
