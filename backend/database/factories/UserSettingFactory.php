<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSetting>
 */
class UserSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'skills' => ['react', 'php'],
            'title_include' => ['web', 'react'],
            'exclude_words' => ['intern', 'unpaid'],
            'location_accept' => ['worldwide', 'asia'],
            'location_reject' => ['us only'],
            'max_age_days' => 14,
            'min_salary_usd_yearly' => 24000,
            'allow_unknown_salary' => true,
            'min_score' => 5,
            'weights' => ['title_skill' => 3, 'desc_skill' => 1],
            'max_notifications_per_run' => 10,
        ];
    }
}
