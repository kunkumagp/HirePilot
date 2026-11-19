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
        $preferences = config('jobhunter.preferences');
        $scoring = config('jobhunter.scoring');
        $notify = config('jobhunter.notify');

        return UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'skills' => $profile['skills'],
                'field' => $preferences['field'],
                'job_category' => $preferences['job_category'],
                'job_subcategory' => $preferences['job_subcategory'],
                'field_keywords' => $preferences['field_keywords'],
                'experience_level' => $preferences['experience_level'],
                'min_years_experience' => $preferences['min_years_experience'],
                'work_arrangement' => $preferences['work_arrangement'],
                'preferred_countries' => $preferences['preferred_countries'],
                'salary_currency' => $preferences['salary_currency'],
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
