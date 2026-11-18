<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\JobData;
use App\Services\DedupeService;
use Tests\TestCase;

class DedupeServiceTest extends TestCase
{
    private function job(string $company, string $title): JobData
    {
        return new JobData(
            source: 'remoteok',
            externalId: '1',
            title: $title,
            company: $company,
            url: 'https://example.com/jobs/1',
            description: '',
            locationText: 'Worldwide',
            tags: [],
            salaryMin: null,
            salaryMax: null,
            salaryCurrency: null,
            postedAt: null,
        );
    }

    public function test_same_company_and_title_produce_same_hash(): void
    {
        $service = app(DedupeService::class);

        $first = $service->contentHash($this->job('Acme Inc.', 'React Developer'));
        $second = $service->contentHash($this->job('  acme inc  ', 'react  developer'));

        $this->assertSame($first, $second);
    }

    public function test_different_titles_produce_different_hashes(): void
    {
        $service = app(DedupeService::class);

        $first = $service->contentHash($this->job('Acme Inc.', 'React Developer'));
        $second = $service->contentHash($this->job('Acme Inc.', 'Backend Developer'));

        $this->assertNotSame($first, $second);
    }
}
