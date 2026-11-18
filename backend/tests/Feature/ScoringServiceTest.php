<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\JobData;
use App\DTO\ScoreResult;
use App\Models\UserSetting;
use App\Services\ScoringService;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeJob(array $overrides = []): JobData
    {
        $attributes = array_merge([
            'source' => 'remoteok',
            'externalId' => '1',
            'title' => 'Developer',
            'company' => 'Acme',
            'url' => 'https://example.com/jobs/1',
            'description' => 'A plain role.',
            'locationText' => 'Paris',
            'tags' => [],
            'salaryMin' => null,
            'salaryMax' => null,
            'salaryCurrency' => null,
            'postedAt' => null,
        ], $overrides);

        return new JobData(...$attributes);
    }

    public function test_adds_title_and_description_skill_points(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => ['react'],
            'weights' => ['title_skill' => 3, 'desc_skill' => 1],
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob(['title' => 'React Developer', 'description' => 'We use React daily.']),
            $settings,
        );

        $this->assertInstanceOf(ScoreResult::class, $result);
        $this->assertSame(4, $result->score);
        $this->assertSame(['title skill: react (+3)', 'description skill: react (+1)'], $result->reasons);
    }

    public function test_adds_worldwide_salary_and_fresh_bonuses(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [],
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob([
                'locationText' => 'Worldwide',
                'salaryMin' => 60000.0,
                'salaryMax' => 90000.0,
                'salaryCurrency' => 'USD',
                'postedAt' => now()->subHours(2),
            ]),
            $settings,
        );

        $this->assertSame(5, $result->score);
        $this->assertSame([
            'worldwide (+2)',
            'USD salary above target (+2)',
            'posted in last 24h (+1)',
        ], $result->reasons);
    }

    public function test_applies_location_uncertain_penalty(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [],
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score($this->makeJob(), $settings, true);

        $this->assertSame(-2, $result->score);
        $this->assertSame(['location uncertain (-2)'], $result->reasons);
    }

    public function test_uses_custom_weights_from_settings(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => ['react'],
            'weights' => ['title_skill' => 7, 'desc_skill' => 0],
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob(['title' => 'React Developer']),
            $settings,
        );

        $this->assertSame(7, $result->score);
    }

    public function test_ignores_blank_skills(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [''],
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob(['title' => 'React Developer']),
            $settings,
        );

        $this->assertSame(0, $result->score);
        $this->assertSame([], $result->reasons);
    }
}
