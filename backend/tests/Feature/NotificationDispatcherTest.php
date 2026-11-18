<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\JobMatchMail;
use App\Models\JobPosting;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\Models\UserSetting;
use App\NotificationChannel as ChannelType;
use App\Notifications\JobMatchNotification;
use App\Services\NotificationDispatcher;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationDispatcherTest extends TestCase
{
    private function userWithChannel(ChannelType $type, bool $enabled = true, ?string $target = null): User
    {
        $user = User::factory()->create();
        UserSetting::factory()->for($user)->create();
        UserChannel::factory()->for($user)->create([
            'type' => $type,
            'enabled' => $enabled,
            'target' => $target,
        ]);

        return $user;
    }

    private function userJobFor(User $user): UserJob
    {
        $posting = JobPosting::factory()->create();

        return UserJob::factory()->for($user)->create([
            'job_posting_id' => $posting->id,
            'status' => 'new',
            'score' => 9,
            'reasons' => ['title skill: react'],
            'cover_letter' => 'Dear Acme Remote Hiring Team,',
        ]);
    }

    public function test_dispatches_in_app_notification_and_marks_job_notified(): void
    {
        Notification::fake();
        $user = $this->userWithChannel(ChannelType::InApp);
        $userJob = $this->userJobFor($user);

        $notified = app(NotificationDispatcher::class)->dispatch($user, collect([$userJob]));

        $this->assertSame(1, $notified);
        $this->assertSame('notified', $userJob->fresh()->status);
        $this->assertSame(1, NotificationDelivery::count());
        $this->assertSame('sent', NotificationDelivery::first()->status);
        Notification::assertSentTo($user, JobMatchNotification::class);
    }

    public function test_skips_disabled_channels(): void
    {
        Notification::fake();
        $user = $this->userWithChannel(ChannelType::InApp, false);
        $userJob = $this->userJobFor($user);

        $notified = app(NotificationDispatcher::class)->dispatch($user, collect([$userJob]));

        $this->assertSame(0, $notified);
        $this->assertSame('new', $userJob->fresh()->status);
        $this->assertSame(0, NotificationDelivery::count());
        Notification::assertNothingSent();
    }

    public function test_sends_email_through_default_mailer(): void
    {
        Mail::fake();
        $user = $this->userWithChannel(ChannelType::Email, true, 'candidate@example.test');
        $userJob = $this->userJobFor($user);

        $notified = app(NotificationDispatcher::class)->dispatch($user, collect([$userJob]));

        $this->assertSame(1, $notified);
        Mail::assertSent(JobMatchMail::class, fn (JobMatchMail $mail): bool => $mail->hasTo('candidate@example.test'));
    }

    public function test_plan_limit_caps_notifications_per_run(): void
    {
        Notification::fake();
        config(['jobhunter.plans.free.max_notifications_per_run' => 2]);

        $user = $this->userWithChannel(ChannelType::InApp);
        $user->update(['plan' => 'free']);

        $jobs = collect([
            $this->userJobFor($user),
            $this->userJobFor($user),
            $this->userJobFor($user),
            $this->userJobFor($user),
        ]);

        $notified = app(NotificationDispatcher::class)->dispatch($user, $jobs);

        $this->assertSame(2, $notified);
        $this->assertSame(2, NotificationDelivery::where('status', 'sent')->count());
    }

    public function test_marks_job_notified_when_one_channel_succeeds(): void
    {
        Notification::fake();
        config(['services.telegram.bot_token' => null, 'services.telegram.chat_id' => null]);

        $user = $this->userWithChannel(ChannelType::Telegram);
        UserChannel::factory()->for($user)->create(['type' => ChannelType::InApp]);
        $userJob = $this->userJobFor($user);

        $notified = app(NotificationDispatcher::class)->dispatch($user, collect([$userJob]));

        $this->assertSame(1, $notified);
        $this->assertSame('notified', $userJob->fresh()->status);
        $this->assertSame(2, NotificationDelivery::count());
        $this->assertSame(1, NotificationDelivery::where('status', 'sent')->count());
        $this->assertSame(1, NotificationDelivery::where('status', 'failed')->count());
    }
}
