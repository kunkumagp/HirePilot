<?php

declare(strict_types=1);

namespace Tests\Feature\Sources;

use App\Services\Sources\JobicySource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobicySourceTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private function payload(): array
    {
        $contents = file_get_contents(storage_path('app/fixtures/jobicy.json'));

        return (array) json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_normalize_maps_jobicy_fields_and_salary(): void
    {
        $jobs = app(JobicySource::class)->normalize($this->payload());

        $this->assertCount(2, $jobs);
        $this->assertSame('jobicy', $jobs[0]->source);
        $this->assertSame('7001', $jobs[0]->externalId);
        $this->assertSame('React Developer', $jobs[0]->title);
        $this->assertSame('Acme Remote', $jobs[0]->company);
        $this->assertSame('Worldwide', $jobs[0]->locationText);
        $this->assertSame(70000.0, $jobs[0]->salaryMin);
        $this->assertSame(90000.0, $jobs[0]->salaryMax);
        $this->assertSame('USD', $jobs[0]->salaryCurrency);
        $this->assertSame(['Software Development', 'Full-Time'], $jobs[0]->tags);
        $this->assertNotNull($jobs[0]->postedAt);
    }

    public function test_fetch_returns_jobs_from_fake_response(): void
    {
        Http::preventStrayRequests();
        Http::fake(['jobicy.com/api/v2/remote-jobs*' => Http::response($this->payload())]);

        $jobs = app(JobicySource::class)->fetch();

        $this->assertCount(2, $jobs);
    }
}
