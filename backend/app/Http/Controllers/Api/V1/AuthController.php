<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account created. Please verify your email.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token']->plainTextToken,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $key = 'login:' . $request->input('email');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.'],
            ]);
        }

        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        if (!$result) {
            RateLimiter::hit($key, 60);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
                'data' => null,
            ], 401);
        }

        RateLimiter::clear($key);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token']->plainTextToken,
                'token_expires_at' => $result['token']->accessToken->expires_at?->toISOString(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
            'data' => null,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => [
                'user' => new UserResource($user),
                'profile' => $user->profile ? new \App\Http\Resources\ProfileResource($user->profile) : null,
            ],
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $key = 'forgot-password:' . $request->input('email');

        if (RateLimiter::tooManyAttempts($key, 2)) {
            throw ValidationException::withMessages([
                'email' => ['Too many requests. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.'],
            ]);
        }

        RateLimiter::hit($key, 3600);

        $this->authService->sendPasswordResetLink($request->input('email'));

        return response()->json([
            'success' => true,
            'message' => 'If that email is registered, you will receive a password reset link.',
            'data' => null,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        if ($status !== \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => __($status),
                'data' => null,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful. You can now log in.',
            'data' => null,
        ]);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $user = \App\Models\User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link.',
                'data' => null,
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
                'data' => null,
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'data' => null,
        ]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $key = 'resend-verification:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            throw ValidationException::withMessages([
                'email' => ['Too many requests. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.'],
            ]);
        }

        RateLimiter::hit($key, 60);

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
                'data' => null,
            ]);
        }

        $this->authService->resendVerificationEmail($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Verification link sent.',
            'data' => null,
        ]);
    }
}
