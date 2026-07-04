<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => SessionResource::collection($tokens),
        ]);
    }

    public function destroy(Request $request, string $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()
            ->where('id', $tokenId)
            ->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found.',
                'data' => null,
            ], 404);
        }

        if ($token->id === $request->user()->currentAccessToken()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot revoke current session. Use logout instead.',
                'data' => null,
            ], 422);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session revoked.',
            'data' => null,
        ]);
    }
}
