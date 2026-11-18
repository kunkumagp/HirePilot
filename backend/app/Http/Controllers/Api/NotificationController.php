<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return NotificationResource::collection(
            $user->notifications()->latest()->paginate(20),
        );
    }

    public function markRead(Request $request, string $id): Response
    {
        /** @var User $user */
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->noContent();
    }
}
