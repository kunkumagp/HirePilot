<?php

declare(strict_types=1);

namespace Tests\Feature\Sources;

use App\Services\Sources\WeWorkRemotelySource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeWorkRemotelySourceTest extends TestCase
{
    private function rss(): string
    {
        return (string) file_get_contents(storage_path('app/fixtures/weworkremotely.xml'));
    }

    public function test_normalize_parses_rss_items(): void
    {
        $jobs = app(WeWorkRemotelySource::class)->normalize($this->rss());

        $this->assertCount(2, $jobs);
        $this->assertSame('weworkremotely', $jobs[0]->source);
        $this->assertSame('React Developer', $jobs[0]->title);
        $this->assertSame('Acme Remote', $jobs[0]->company);
        $this->assertSame('Anywhere in the World', $jobs[0]->locationText);
        $this->assertSame('Build web applications with React.', $jobs[0]->description);
        $this->assertSame('https://weworkremotely.com/remote-jobs/acme-react-developer', $jobs[0]->url);
        $this->assertNull($jobs[0]->salaryMin);
        $this->assertNotNull($jobs[0]->postedAt);
    }

    public function test_fetch_returns_jobs_from_fake_rss_response(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'weworkremotely.com/categories/remote-programming-jobs.rss' => Http::response($this->rss()),
        ]);

        $jobs = app(WeWorkRemotelySource::class)->fetch();

        $this->assertCount(2, $jobs);
    }
}
