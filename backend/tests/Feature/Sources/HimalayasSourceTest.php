<?php

declare(strict_types=1);

namespace Tests\Feature\Sources;

use App\Services\Sources\HimalayasSource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HimalayasSourceTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private function payload(): array
    {
        $contents = file_get_contents(storage_path('app/fixtures/himalayas.json'));

        return (array) json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_normalize_maps_himalayas_fields_and_salary(): void
    {
        $jobs = app(HimalayasSource::class)->normalize($this->payload());

        $this->assertCount(2, $jobs);
        $this->assertSame('himalayas', $jobs[0]->source);
        $this->assertSame('https://himalayas.app/companies/acme/jobs/react-developer', $jobs[0]->externalId);
        $this->assertSame('React Developer', $jobs[0]->title);
        $this->assertSame('Acme Remote', $jobs[0]->company);
        $this->assertSame('Worldwide', $jobs[0]->locationText);
        $this->assertSame(70000.0, $jobs[0]->salaryMin);
        $this->assertSame(90000.0, $jobs[0]->salaryMax);
        $this->assertSame('USD', $jobs[0]->salaryCurrency);
        $this->assertContains('React-Developer', $jobs[0]->tags);
        $this->assertNotNull($jobs[0]->postedAt);
    }

    public function test_fetch_returns_jobs_from_fake_response(): void
    {
        Http::preventStrayRequests();
        Http::fake(['himalayas.app/jobs/api*' => Http::response($this->payload())]);

        $jobs = app(HimalayasSource::class)->fetch();

        $this->assertCount(2, $jobs);
    }
}
