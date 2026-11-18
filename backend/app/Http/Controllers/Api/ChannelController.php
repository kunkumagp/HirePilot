<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChannelRequest;
use App\Models\User;
use App\Models\UserChannel;
use App\NotificationChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

class ChannelController extends Controller
{
    public function store(StoreChannelRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $credentials = null;

        if (isset($validated['credentials']) && $validated['credentials'] !== []) {
            $credentials = Crypt::encryptString(json_encode($validated['credentials'], JSON_THROW_ON_ERROR));
        }

        $values = [
            'enabled' => $validated['enabled'] ?? true,
            'target' => $validated['target'] ?? null,
        ];

        if (isset($validated['credentials'])) {
            $values['credentials'] = $credentials;
        }

        $channel = UserChannel::updateOrCreate(
            ['user_id' => $user->id, 'type' => $validated['type']],
            $values,
        );

        return response()->json(['channel' => $this->channelData($channel)], 201);
    }

    public function destroy(Request $request, UserChannel $channel): Response
    {
        /** @var User $user */
        $user = $request->user();

        if ($channel->user_id !== $user->id) {
            abort(404);
        }

        $channel->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function channelData(UserChannel $channel): array
    {
        $type = $channel->type instanceof NotificationChannel
            ? $channel->type->value
            : (string) $channel->type;

        return [
            'id' => $channel->id,
            'type' => $type,
            'enabled' => $channel->enabled,
            'target' => $channel->target,
        ];
    }
}
