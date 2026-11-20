<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\JobData;
use App\DTO\ScoreResult;
use App\Jobs\DispatchUserNotifications;
use App\Models\JobPosting;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\NotificationChannel;
use App\Notifications\JobMatchNotification;
use App\Services\JobNotificationService;
use App\Services\UserSettingsService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class JobNotificationServiceTest extends TestCase
{
    public function test_queue_mode_defers_dispatch_and_still_creates_user_job(): void
    {
        config(['jobhunter.notify.use_queue' => true]);
        Queue::fake();

        $user = User::factory()->create();
        app(UserSettingsService::class)->createDefaults($user);
        $posting = JobPosting::factory()->create();

        $job = new JobData(
            source: $posting->source,
            externalId: $posting->external_id,
            title: $posting->title,
            company: $posting->company,
            url: $posting->url,
            description: $posting->description ?? '',
            locationText: $posting->location_text ?? '',
            tags: [],
            salaryMin: 70000.0,
            salaryMax: 90000.0,
            salaryCurrency: 'USD',
            postedAt: now(),
        );

        $result = app(JobNotificationService::class)->process($user, [[
            'job' => $job,
            'score' => new ScoreResult(9, ['title skill: react']),
            'salaryUnknown' => false,
            'locationUncertain' => false,
        ]], true);

        $this->assertSame(['new' => 1, 'notified' => 0], $result);
        $this->assertSame(1, UserJob::count());
        $this->assertSame(0, NotificationDelivery::count());
        Queue::assertPushed(DispatchUserNotifications::class);
    }

    public function test_queued_job_dispatches_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        UserChannel::factory()->for($user)->create(['type' => NotificationChannel::InApp]);
        $userJob = UserJob::factory()->for($user)->create([
            'job_posting_id' => JobPosting::factory()->create()->id,
            'status' => 'new',
            'cover_letter' => 'Dear Acme,',
        ]);

        DispatchUserNotifications::dispatchSync($user->id, [$userJob->id]);

        Notification::assertSentTo($user, JobMatchNotification::class);
        $this->assertSame('notified', $userJob->fresh()->status);
    }
}
