<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserSetting;

final class UserSettingsService
{
    public function createDefaults(User $user): UserSetting
    {
        $profile = config('jobhunter.profile');
        $filters = config('jobhunter.filters');
        $scoring = config('jobhunter.scoring');
        $notify = config('jobhunter.notify');

        return UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'skills' => $profile['skills'],
                'title_include' => $filters['title_include'],
                'exclude_words' => $filters['exclude_words'],
                'location_accept' => $filters['location_accept'],
                'location_reject' => $filters['location_reject'],
                'max_age_days' => $filters['max_age_days'],
                'min_salary_usd_yearly' => $filters['min_salary_usd_yearly'],
                'allow_unknown_salary' => $filters['allow_unknown_salary'],
                'min_score' => $scoring['min_score'],
                'weights' => $scoring['weights'],
                'max_notifications_per_run' => $notify['max_notifications_per_run'],
            ],
        );
    }
}
