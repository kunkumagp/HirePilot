<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\NotificationChannel;
use App\Services\Notifiers\EmailNotifier;
use App\Services\Notifiers\InAppNotifier;
use App\Services\Notifiers\Notifier;
use App\Services\Notifiers\TelegramNotifier;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Throwable;

final class NotificationDispatcher
{
    /** @var array<string, class-string<Notifier>> */
    private const NOTIFIERS = [
        'telegram' => TelegramNotifier::class,
        'email' => EmailNotifier::class,
        'inapp' => InAppNotifier::class,
    ];

    public function __construct(private readonly Application $app)
    {
        //
    }

    /**
     * @param  Collection<int, UserJob>  $userJobs
     */
    public function dispatch(User $user, Collection $userJobs): int
    {
        $channels = $user->userChannels()->where('enabled', true)->get();

        if ($channels->isEmpty()) {
            Log::warning('No enabled notification channels', ['user_id' => $user->id]);

            return 0;
        }

        $maxPerRun = (int) ($user->userSetting?->max_notifications_per_run
            ?? config('jobhunter.notify.max_notifications_per_run', 10));

        $selected = $userJobs->sortByDesc('score')->take($maxPerRun)->values();
        $notified = 0;

        foreach ($selected as $userJob) {
            $succeeded = false;

            foreach ($channels as $channel) {
                $notifierClass = $this->notifierClass($channel);

                if ($notifierClass === null) {
                    continue;
                }

                [$ok, $error] = $this->sendWithRetry($this->app->make($notifierClass), $user, $channel, $userJob);

                NotificationDelivery::create([
                    'user_id' => $user->id,
                    'user_job_id' => $userJob->id,
                    'channel' => $channel->type,
                    'status' => $ok ? 'sent' : 'failed',
                    'attempts' => (int) config('jobhunter.notify.retry_attempts', 3),
                    'sent_at' => $ok ? now() : null,
                    'error' => $error,
                ]);

                if ($ok) {
                    $succeeded = true;
                }
            }

            if ($succeeded) {
                $userJob->forceFill(['status' => 'notified', 'notified_at' => now()])->save();
                $notified++;
            }
        }

        return $notified;
    }

    /**
     * @return class-string<Notifier>|null
     */
    private function notifierClass(UserChannel $channel): ?string
    {
        $type = $channel->type instanceof NotificationChannel
            ? $channel->type->value
            : (string) $channel->type;

        return self::NOTIFIERS[$type] ?? null;
    }

    /**
     * @return array{0: bool, 1: ?string}
     */
    private function sendWithRetry(Notifier $notifier, User $user, UserChannel $channel, UserJob $userJob): array
    {
        $attempts = max(1, (int) config('jobhunter.notify.retry_attempts', 3));
        $backoff = max(0, (int) config('jobhunter.notify.retry_backoff_ms', 200));
        $error = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                if ($notifier->send($user, $channel, $userJob)) {
                    return [true, null];
                }

                $error = 'notifier returned false';
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }

            if ($attempt < $attempts && $backoff > 0) {
                Sleep::usleep($backoff * 1000);
            }
        }

        Log::warning('Notification delivery failed', [
            'user_id' => $user->id,
            'user_job_id' => $userJob->id,
            'channel' => $channel->type,
            'error' => $error,
        ]);

        return [false, $error];
    }
}
