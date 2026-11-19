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
            'listed salary above target (+2)',
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
            'field' => 'sales',
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob(['title' => 'React Developer']),
            $settings,
        );

        $this->assertSame(0, $result->score);
        $this->assertSame([], $result->reasons);
    }

    public function test_field_preset_and_custom_keywords_score_a_different_role_family(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [],
            'field' => 'sales',
            'field_keywords' => ['laravel'],
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $salesJob = app(ScoringService::class)->score(
            $this->makeJob([
                'title' => 'Enterprise Account Executive',
                'description' => 'Own the sales cycle with HubSpot.',
            ]),
            $settings,
        );
        $customJob = app(ScoringService::class)->score(
            $this->makeJob(['title' => 'Laravel Developer']),
            $settings,
        );

        $this->assertSame(6, $salesJob->score);
        $this->assertContains('field: sales (+6)', $salesJob->reasons);
        $this->assertContains('field: sales (+6)', $customJob->reasons);
    }

    public function test_free_text_job_query_scores_phrase_and_tokens(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [],
            'field' => 'sales',
            'job_query' => 'React Developer, HubSpot Marketing',
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob(['title' => 'React Developer']),
            $settings,
        );

        $this->assertSame(7, $result->score);
        $this->assertContains('query phrase: React Developer (+6)', $result->reasons);
        $this->assertContains('query token: react (+1)', $result->reasons);
    }

    public function test_free_text_query_caps_token_points(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [],
            'field' => 'sales',
            'job_query' => 'HubSpot Marketing Manager',
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob([
                'title' => 'Senior HubSpot Marketing Manager',
                'description' => 'Own campaigns and pipeline.',
            ]),
            $settings,
        );

        $this->assertSame(9, $result->score);
        $this->assertContains('query phrase: HubSpot Marketing Manager (+6)', $result->reasons);
    }

    public function test_taxonomy_subcategory_keywords_score_a_specific_role(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [],
            'field' => 'web_development',
            'job_category' => 'engineering',
            'job_subcategory' => 'frontend',
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob([
                'title' => 'React Developer',
                'description' => 'Build interfaces with TypeScript and Vue.',
            ]),
            $settings,
        );

        $this->assertSame(6, $result->score);
        $this->assertContains('field: Frontend Development (+6)', $result->reasons);
    }

    public function test_experience_arrangement_and_country_preferences_add_points(): void
    {
        $settings = UserSetting::factory()->make([
            'skills' => [],
            'field' => 'web_development',
            'experience_level' => 'senior',
            'min_years_experience' => 3,
            'work_arrangement' => 'remote',
            'preferred_countries' => ['Germany'],
            'weights' => config('jobhunter.scoring.weights'),
        ]);

        $result = app(ScoringService::class)->score(
            $this->makeJob([
                'title' => 'Senior React Developer',
                'description' => '5+ years experience. Remote role, work from anywhere.',
                'locationText' => 'Germany',
            ]),
            $settings,
        );

        $this->assertSame(14, $result->score);
        $this->assertContains('experience level: senior (+2)', $result->reasons);
        $this->assertContains('experience >= 3 years (+2)', $result->reasons);
        $this->assertContains('arrangement: remote (+2)', $result->reasons);
        $this->assertContains('preferred country/region (+2)', $result->reasons);
    }
}
