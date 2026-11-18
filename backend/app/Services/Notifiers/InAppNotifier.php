<?php

declare(strict_types=1);

namespace App\Services\Notifiers;

use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\NotificationChannel;
use App\Notifications\JobMatchNotification;
use App\Services\NotificationMessage;

final class InAppNotifier implements Notifier
{
    public function __construct(private readonly NotificationMessage $message)
    {
        //
    }

    public function name(): NotificationChannel
    {
        return NotificationChannel::InApp;
    }

    public function send(User $user, UserChannel $channel, UserJob $userJob): bool
    {
        $data = $this->message->build($userJob);

        $user->notify(new JobMatchNotification([
            'title' => $data['title'],
            'body' => $data['body'],
            'cover_letter' => (string) $userJob->cover_letter,
            'url' => $userJob->jobPosting->url,
            'user_job_id' => $userJob->id,
        ]));

        return true;
    }
}
