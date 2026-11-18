<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use Tests\TestCase;

class JobHunterRunCommandTest extends TestCase
{
    public function test_dry_run_reports_dry_mode_and_sends_no_notifications(): void
    {
        $this->artisan('jobhunter:run', ['--dry-run' => true])
            ->expectsOutputToContain('DRY RUN (no notifications will be sent)')
            ->expectsOutputToContain('Notified: 0')
            ->assertSuccessful();

        $this->assertSame(0, NotificationDelivery::count());
    }

    public function test_live_mode_reports_live_mode(): void
    {
        $this->artisan('jobhunter:run')
            ->expectsOutputToContain('Mode: LIVE')
            ->assertSuccessful();
    }
}
