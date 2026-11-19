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
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function options(): JsonResponse
    {
        /** @var array<string, list<string>> $fields */
        $fields = config('jobhunter.fields', []);

        return response()->json([
            'fields' => collect($fields)
                ->map(fn (array $keywords, string $key): array => [
                    'key' => $key,
                    'label' => Str::headline($key),
                ])
                ->values()
                ->all(),
            'experience_levels' => array_keys(config('jobhunter.experience_levels', [])),
            'arrangements' => array_keys(config('jobhunter.arrangements', [])),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load('userSetting', 'userChannels');
        $settings = $user->userSetting;

        return response()->json([
            'user' => new UserResource($user),
            'settings' => $settings === null ? [] : [
                'skills' => $settings->skills,
                'field' => $settings->field,
                'field_keywords' => $settings->field_keywords,
                'experience_level' => $settings->experience_level,
                'min_years_experience' => $settings->min_years_experience,
                'work_arrangement' => $settings->work_arrangement,
                'preferred_countries' => $settings->preferred_countries,
                'salary_currency' => $settings->salary_currency,
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
            'field',
            'field_keywords',
            'experience_level',
            'min_years_experience',
            'work_arrangement',
            'preferred_countries',
            'salary_currency',
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
