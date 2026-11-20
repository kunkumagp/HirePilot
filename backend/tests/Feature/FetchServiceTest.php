<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\JobData;
use App\Models\JobPosting;
use App\Services\DedupeService;
use App\Services\FetchService;
use App\Services\Sources\Source;
use Tests\TestCase;

class FetchServiceTest extends TestCase
{
    private function job(string $source, string $externalId): JobData
    {
        return new JobData(
            source: $source,
            externalId: $externalId,
            title: 'React Developer',
            company: 'Acme Inc.',
            url: 'https://example.com/jobs/'.$externalId,
            description: '',
            locationText: 'Worldwide',
            tags: [],
            salaryMin: 70000.0,
            salaryMax: 90000.0,
            salaryCurrency: 'USD',
            postedAt: now(),
        );
    }

    private function stubSource(array $jobs): Source
    {
        return new class($jobs) implements Source
        {
            /**
             * @param  list<JobData>  $jobs
             */
            public function __construct(private readonly array $jobs)
            {
                //
            }

            public function name(): string
            {
                return 'stub';
            }

            public function fetch(): array
            {
                return $this->jobs;
            }
        };
    }

    public function test_keeps_one_job_when_same_posting_comes_from_two_sources(): void
    {
        $source = $this->stubSource([
            $this->job('remoteok', '1'),
            $this->job('remotive', '2'),
        ]);
        $seen = [];

        $kept = app(FetchService::class)->fetchSource($source, true, $seen);

        $this->assertCount(1, $kept);
        $this->assertSame(1, JobPosting::count());
    }

    public function test_dry_run_does_not_persist_jobs(): void
    {
        $source = $this->stubSource([$this->job('remoteok', '1')]);
        $seen = [];

        $kept = app(FetchService::class)->fetchSource($source, false, $seen);

        $this->assertCount(1, $kept);
        $this->assertSame(0, JobPosting::count());
    }

    public function test_skips_job_already_known_from_database(): void
    {
        $job = $this->job('remoteok', '1');
        $hash = app(DedupeService::class)->contentHash($job);
        JobPosting::factory()->create(['content_hash' => $hash]);
        $seen = array_fill_keys(JobPosting::query()->pluck('content_hash')->all(), true);

        $kept = app(FetchService::class)->fetchSource($this->stubSource([$job]), true, $seen);

        $this->assertSame([], $kept);
        $this->assertSame(1, JobPosting::count());
    }
}
