<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->jobTitle();
        $company = $this->faker->company();

        return [
            'source' => 'remoteok',
            'external_id' => (string) $this->faker->unique()->numberBetween(1, 100000),
            'title' => $title,
            'company' => $company,
            'url' => $this->faker->url(),
            'description' => $this->faker->paragraph(),
            'location_text' => 'Worldwide',
            'tags' => ['react', 'php'],
            'salary_min' => 60000,
            'salary_max' => 90000,
            'salary_currency' => 'USD',
            'posted_at' => now()->subDay(),
            'first_seen_at' => now(),
            'content_hash' => hash('sha256', Str::lower($company.$title)),
        ];
    }
}
