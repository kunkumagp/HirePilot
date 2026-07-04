<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SessionController;
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

Route::name('auth.')->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::name('auth.')->prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
            ->name('verification.send');
    });

    Route::name('profile.')->prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    Route::name('password.')->prefix('password')->group(function () {
        Route::put('/', [ProfileController::class, 'changePassword'])->name('change');
    });

    Route::name('sessions.')->prefix('sessions')->group(function () {
        Route::get('/', [SessionController::class, 'index'])->name('index');
        Route::delete('/{tokenId}', [SessionController::class, 'destroy'])->name('destroy');
    });

    Route::name('account.')->prefix('account')->group(function () {
        Route::delete('/', [ProfileController::class, 'destroy'])->name('delete');
    });
});
