<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobHunterRunCommandTest extends TestCase
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

    public function test_dry_run_prints_fetched_jobs_and_sends_no_notifications(): void
    {
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response($this->fixturePayload())]);

        $this->artisan('jobhunter:run', ['--dry-run' => true])
            ->expectsOutputToContain('DRY RUN (no notifications will be sent)')
            ->expectsOutputToContain('Fetched: 2')
            ->expectsOutputToContain('After filters: 1')
            ->expectsOutputToContain('React Developer @ Acme Remote')
            ->expectsOutputToContain('Notified: 0')
            ->assertSuccessful();

        $this->assertSame(0, NotificationDelivery::count());
    }

    public function test_live_mode_reports_live_mode(): void
    {
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response($this->fixturePayload())]);

        $this->artisan('jobhunter:run')
            ->expectsOutputToContain('Mode: LIVE')
            ->assertSuccessful();
    }
}
