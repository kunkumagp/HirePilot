<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserChannel;
use App\NotificationChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load('userSetting', 'userChannels');
        $settings = $user->userSetting;

        return response()->json([
            'user' => new UserResource($user),
            'settings' => $settings === null ? [] : [
                'skills' => $settings->skills,
                'title_include' => $settings->title_include,
                'exclude_words' => $settings->exclude_words,
                'location_accept' => $settings->location_accept,
                'location_reject' => $settings->location_reject,
                'max_age_days' => $settings->max_age_days,
                'min_salary_usd_yearly' => $settings->min_salary_usd_yearly,
                'allow_unknown_salary' => $settings->allow_unknown_salary,
                'min_score' => $settings->min_score,
                'weights' => $settings->weights,
                'max_notifications_per_run' => $settings->max_notifications_per_run,
            ],
            'channels' => $user->userChannels->map(fn (UserChannel $channel): array => $this->channelData($channel))->all(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $user->fill(Arr::only($validated, ['name', 'portfolio_url']))->save();

        $user->userSetting?->fill(Arr::only($validated, [
            'skills',
            'title_include',
            'exclude_words',
            'location_accept',
            'location_reject',
            'max_age_days',
            'min_salary_usd_yearly',
            'allow_unknown_salary',
            'min_score',
            'weights',
            'max_notifications_per_run',
        ]))->save();

        return response()->json(['user' => new UserResource($user->fresh())]);
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
