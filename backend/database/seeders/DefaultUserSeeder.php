<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, mixed> $profile */
        $profile = config('jobhunter.profile');

        $user = User::firstOrCreate(
            ['email' => $profile['email']],
            [
                'name' => $profile['name'],
                'portfolio_url' => $profile['portfolio_url'],
                // The local default user is not a login account yet; auth arrives in Milestone 7.
                'password' => Hash::make(Str::random(32)),
                'is_active' => true,
            ],
        );

        $filters = config('jobhunter.filters');
        $scoring = config('jobhunter.scoring');
        $notify = config('jobhunter.notify');

        UserSetting::firstOrCreate(
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
