<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Models\UserJob;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchUserNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<int>  $userJobIds
     */
    public function __construct(
        public readonly int $userId,
        public readonly array $userJobIds,
    ) {
        //
    }

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $user = User::find($this->userId);

        if ($user === null || ! $user->is_active || $this->userJobIds === []) {
            return;
        }

        $jobs = UserJob::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $this->userJobIds)
            ->with('jobPosting')
            ->get();

        $dispatcher->dispatch($user, $jobs);
    }
}
