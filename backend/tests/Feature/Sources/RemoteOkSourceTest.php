<?php

declare(strict_types=1);

namespace Tests\Feature\Sources;

use App\DTO\JobData;
use App\Services\Sources\RemoteOkSource;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemoteOkSourceTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private function fixturePayload(): array
    {
        $contents = file_get_contents(storage_path('app/fixtures/remoteok.json'));
        $decoded = json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    public function test_normalize_skips_legal_metadata_and_maps_fields(): void
    {
        $jobs = (new RemoteOkSource)->normalize($this->fixturePayload());

        $this->assertCount(2, $jobs);

        $first = $jobs[0];
        $this->assertInstanceOf(JobData::class, $first);
        $this->assertSame('remoteok', $first->source);
        $this->assertSame('1001', $first->externalId);
        $this->assertSame('React Developer', $first->title);
        $this->assertSame('Acme Remote', $first->company);
        $this->assertSame('Worldwide', $first->locationText);
        $this->assertSame(['react', 'javascript', 'typescript', 'remote'], $first->tags);
        $this->assertSame(60000.0, $first->salaryMin);
        $this->assertSame(90000.0, $first->salaryMax);
        $this->assertSame('USD', $first->salaryCurrency);
        $this->assertSame('About the Role Build web interfaces with React and TypeScript.', $first->description);
        $this->assertNotNull($first->postedAt);
    }

    public function test_normalize_handles_missing_optional_fields(): void
    {
        $jobs = (new RemoteOkSource)->normalize([
            ['legal' => 'terms'],
            ['id' => '2', 'position' => 'Developer', 'url' => 'https://remoteok.com/jobs/2'],
        ]);

        $this->assertCount(1, $jobs);

        $job = $jobs[0];
        $this->assertSame('', $job->description);
        $this->assertSame('', $job->locationText);
        $this->assertSame([], $job->tags);
        $this->assertNull($job->salaryMin);
        $this->assertNull($job->salaryMax);
        $this->assertNull($job->salaryCurrency);
        $this->assertNull($job->postedAt);
    }

    public function test_fetch_uses_configured_user_agent_and_returns_jobs(): void
    {
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response($this->fixturePayload())]);

        $jobs = (new RemoteOkSource)->fetch();

        $this->assertCount(2, $jobs);
        Http::assertSent(
            fn (Request $request): bool => $request->hasHeader('User-Agent', config('jobhunter.http.user_agent')),
        );
    }

    public function test_fetch_returns_empty_list_on_connection_failure(): void
    {
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::failedConnection()]);

        $this->assertSame([], (new RemoteOkSource)->fetch());
    }

    public function test_fetch_returns_empty_list_on_server_error(): void
    {
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response('server error', 500)]);

        $this->assertSame([], (new RemoteOkSource)->fetch());
    }
}
