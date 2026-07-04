<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly AuthService $authService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $result = $this->profileService->getProfile($request->user());

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => [
                'user' => new UserResource($result['user']),
                'profile' => $result['profile'] ? new ProfileResource($result['profile']) : null,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $result = $this->profileService->updateProfile($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'data' => [
                'user' => new UserResource($result['user']),
                'profile' => $result['profile'] ? new ProfileResource($result['profile']) : null,
            ],
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $changed = $this->authService->changePassword(
            $request->user(),
            $request->input('current_password'),
            $request->input('password')
        );

        if (!$changed) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'data' => null,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
            'data' => null,
        ]);
    }

    public function destroy(DeleteAccountRequest $request): JsonResponse
    {
        $deleted = $this->authService->deleteAccount(
            $request->user(),
            $request->input('password')
        );

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect.',
                'data' => null,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account deleted.',
            'data' => null,
        ]);
    }
}
