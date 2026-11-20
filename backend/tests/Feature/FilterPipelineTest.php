<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\JobData;
use App\Models\UserSetting;
use App\Services\FilterPipeline;
use Tests\TestCase;

class FilterPipelineTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeJob(array $overrides = []): JobData
    {
        $attributes = array_merge([
            'source' => 'remoteok',
            'externalId' => '1',
            'title' => 'React Developer',
            'company' => 'Acme',
            'url' => 'https://example.com/jobs/1',
            'description' => 'Build web applications with React.',
            'locationText' => 'Worldwide',
            'tags' => ['remote'],
            'salaryMin' => 60000.0,
            'salaryMax' => 90000.0,
            'salaryCurrency' => 'USD',
            'postedAt' => now()->subHours(6),
        ], $overrides);

        return new JobData(...$attributes);
    }

    public function test_rejects_jobs_older_than_max_age_days(): void
    {
        $this->travelTo('2026-11-18 12:00:00');

        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['postedAt' => now()->subDays(15)]),
        );

        $this->assertFalse($result->passed);
        $this->assertSame('older than max_age_days', $result->rejectReason);
    }

    public function test_keeps_job_with_unknown_posted_date(): void
    {
        $result = app(FilterPipeline::class)->apply($this->makeJob(['postedAt' => null]));

        $this->assertTrue($result->passed);
    }

    public function test_keeps_job_without_field_keyword_for_scoring(): void
    {
        $result = app(FilterPipeline::class)->apply($this->makeJob(['title' => 'Office Manager']));

        $this->assertTrue($result->passed);
        $this->assertNull($result->rejectReason);
    }

    public function test_rejects_blacklisted_job(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['title' => 'Senior Staff React Engineer']),
        );

        $this->assertFalse($result->passed);
        $this->assertSame('blacklisted word', $result->rejectReason);
    }

    public function test_rejects_location_with_restriction(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['locationText' => 'US only']),
        );

        $this->assertFalse($result->passed);
        $this->assertSame('location restricted', $result->rejectReason);
    }

    public function test_marks_unclear_location_but_keeps_job(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['locationText' => 'Paris', 'tags' => []]),
        );

        $this->assertTrue($result->passed);
        $this->assertTrue($result->locationUncertain);
    }

    public function test_accepts_worldwide_from_tags(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['locationText' => '', 'tags' => ['worldwide']]),
        );

        $this->assertTrue($result->passed);
        $this->assertFalse($result->locationUncertain);
    }

    public function test_rejects_non_usd_salary(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['salaryCurrency' => 'EUR', 'description' => 'Build web apps.']),
        );

        $this->assertFalse($result->passed);
        $this->assertSame('salary currency does not match preference', $result->rejectReason);
    }

    public function test_rejects_salary_below_minimum(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['salaryMin' => 20000.0, 'salaryMax' => 20000.0]),
        );

        $this->assertFalse($result->passed);
        $this->assertSame('salary below minimum', $result->rejectReason);
    }

    public function test_normalizes_hourly_salary_to_yearly(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['salaryMin' => 20.0, 'salaryMax' => 20.0]),
        );

        $this->assertTrue($result->passed);
    }

    public function test_normalizes_monthly_salary_to_yearly(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['salaryMin' => 3000.0, 'salaryMax' => 3000.0]),
        );

        $this->assertTrue($result->passed);
    }

    public function test_rejects_missing_salary_when_not_allowed(): void
    {
        $settings = UserSetting::factory()->make(['allow_unknown_salary' => false]);

        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['salaryMin' => null, 'salaryMax' => null, 'salaryCurrency' => null]),
            $settings,
        );

        $this->assertFalse($result->passed);
        $this->assertSame('salary not listed', $result->rejectReason);
    }

    public function test_keeps_missing_salary_flagged_when_allowed(): void
    {
        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['salaryMin' => null, 'salaryMax' => null, 'salaryCurrency' => null]),
        );

        $this->assertTrue($result->passed);
        $this->assertTrue($result->salaryUnknown);
    }

    public function test_user_settings_override_config_defaults(): void
    {
        $settings = UserSetting::factory()->make(['salary_currency' => 'EUR']);

        $result = app(FilterPipeline::class)->apply($this->makeJob(), $settings);

        $this->assertFalse($result->passed);
        $this->assertSame('salary currency does not match preference', $result->rejectReason);
    }

    public function test_accepts_job_in_the_users_preferred_currency(): void
    {
        $settings = UserSetting::factory()->make(['salary_currency' => 'EUR']);

        $result = app(FilterPipeline::class)->apply(
            $this->makeJob(['salaryCurrency' => 'EUR']),
            $settings,
        );

        $this->assertTrue($result->passed);
    }
}
