<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\NotificationChannel;
use App\Notifications\JobMatchNotification;
use App\Services\Sources\RemoteOkSource;
use Database\Seeders\DefaultUserSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
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
        config([
            'jobhunter.sources' => [
                'remoteok' => ['enabled' => true, 'adapter' => RemoteOkSource::class],
            ],
        ]);
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
        $this->assertSame(0, JobPosting::count());
    }

    public function test_live_mode_reports_live_mode(): void
    {
        config([
            'jobhunter.sources' => [
                'remoteok' => ['enabled' => true, 'adapter' => RemoteOkSource::class],
            ],
        ]);
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response($this->fixturePayload())]);

        $this->artisan('jobhunter:run')
            ->expectsOutputToContain('Mode: LIVE')
            ->assertSuccessful();
    }

    public function test_live_run_persists_unique_jobs(): void
    {
        config([
            'jobhunter.sources' => [
                'remoteok' => ['enabled' => true, 'adapter' => RemoteOkSource::class],
            ],
        ]);
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response($this->fixturePayload())]);

        $this->artisan('jobhunter:run')->assertSuccessful();

        $this->assertSame(2, JobPosting::count());
    }

    public function test_live_run_notifies_default_user(): void
    {
        config([
            'jobhunter.sources' => [
                'remoteok' => ['enabled' => true, 'adapter' => RemoteOkSource::class],
            ],
        ]);
        $this->seed(DefaultUserSeeder::class);
        $user = User::where('email', config('jobhunter.profile.email'))->firstOrFail();
        UserChannel::factory()->for($user)->create(['type' => NotificationChannel::InApp]);
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response($this->fixturePayload())]);
        Notification::fake();

        $this->artisan('jobhunter:run')
            ->expectsOutputToContain('Notified: 1')
            ->assertSuccessful();

        $this->assertSame(1, UserJob::count());
        Notification::assertSentTo($user, JobMatchNotification::class);
    }
}
