<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use App\DTO\ScoreResult;
use App\Jobs\DispatchUserNotifications;
use App\Models\JobPosting;
use App\Models\User;
use App\Models\UserJob;

final class JobNotificationService
{
    public function __construct(
        private readonly CoverLetterService $coverLetters,
        private readonly NotificationDispatcher $dispatcher,
    ) {
        //
    }

    /**
     * @param  list<array{job: JobData, score: ScoreResult, salaryUnknown: bool, locationUncertain: bool}>  $matches
     * @return array{new: int, notified: int}
     */
    public function process(User $user, array $matches, bool $persist): array
    {
        $minScore = (int) ($user->userSetting?->min_score ?? config('jobhunter.scoring.min_score', 5));
        $eligible = collect();
        $new = 0;

        foreach ($matches as $match) {
            if ($match['score']->score < $minScore) {
                continue;
            }

            if (! $persist) {
                continue;
            }

            $job = $match['job'];
            $posting = JobPosting::where('source', $job->source)
                ->where('external_id', $job->externalId)
                ->first();

            if ($posting === null) {
                continue;
            }

            $userJob = UserJob::firstOrCreate(
                ['user_id' => $user->id, 'job_posting_id' => $posting->id],
                [
                    'score' => $match['score']->score,
                    'reasons' => $match['score']->reasons,
                    'status' => 'new',
                    'location_uncertain' => $match['locationUncertain'],
                    'salary_unknown' => $match['salaryUnknown'],
                    'cover_letter' => $this->coverLetters->generate($user, $job),
                ],
            );

            if ($userJob->wasRecentlyCreated) {
                $new++;
            }

            $eligible->push($userJob);
        }

        if (! $persist || $eligible->isEmpty()) {
            return ['new' => $new, 'notified' => 0];
        }

        if ((bool) config('jobhunter.notify.use_queue', false)) {
            DispatchUserNotifications::dispatch($user->id, $eligible->pluck('id')->all());

            return ['new' => $new, 'notified' => 0];
        }

        return [
            'new' => $new,
            'notified' => $this->dispatcher->dispatch($user, $eligible),
        ];
    }
}
