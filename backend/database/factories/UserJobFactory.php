<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JobPosting;
use App\Models\User;
use App\Models\UserJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserJob>
 */
class UserJobFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_posting_id' => JobPosting::factory(),
            'score' => 6,
            'reasons' => ['title skill: react'],
            'status' => 'new',
            'location_uncertain' => false,
            'salary_unknown' => false,
            'cover_letter' => null,
            'notified_at' => null,
        ];
    }
}
