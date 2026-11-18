<?php

declare(strict_types=1);

namespace App\Services\Notifiers;

use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\NotificationChannel;

interface Notifier
{
    public function name(): NotificationChannel;

    public function send(User $user, UserChannel $channel, UserJob $userJob): bool;
}
