<?php

declare(strict_types=1);

namespace Tests\Feature\Sources;

use App\Services\Sources\RemotiveSource;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemotiveSourceTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private function payload(): array
    {
        $contents = file_get_contents(storage_path('app/fixtures/remotive.json'));

        return (array) json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_normalize_maps_remotive_fields_and_salary(): void
    {
        $jobs = app(RemotiveSource::class)->normalize($this->payload());

        $this->assertCount(2, $jobs);
        $this->assertSame('remotive', $jobs[0]->source);
        $this->assertSame('5001', $jobs[0]->externalId);
        $this->assertSame('React Developer', $jobs[0]->title);
        $this->assertSame('Acme Remote', $jobs[0]->company);
        $this->assertSame('Worldwide', $jobs[0]->locationText);
        $this->assertSame(70000.0, $jobs[0]->salaryMin);
        $this->assertSame(90000.0, $jobs[0]->salaryMax);
        $this->assertSame('USD', $jobs[0]->salaryCurrency);
        $this->assertSame(['react', 'javascript'], $jobs[0]->tags);
        $this->assertNotNull($jobs[0]->postedAt);
    }

    public function test_fetch_uses_expected_endpoint(): void
    {
        Http::preventStrayRequests();
        Http::fake(['remotive.com/api/remote-jobs*' => Http::response($this->payload())]);

        $jobs = app(RemotiveSource::class)->fetch();

        $this->assertCount(2, $jobs);
        Http::assertSent(
            fn (Request $request): bool => str_contains($request->url(), 'category=software-dev'),
        );
    }
}
