<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use App\DTO\ScoreResult;
use App\Models\User;

final class UserPipelineService
{
    public function __construct(
        private readonly FilterPipeline $filterPipeline,
        private readonly ScoringService $scoringService,
        private readonly JobNotificationService $jobNotifications,
    ) {
        //
    }

    /**
     * Run filtering and scoring with one user's own rules, then store and notify.
     *
     * @param  list<JobData>  $jobs
     * @return array{
     *     matches: list<array{job: JobData, score: ScoreResult, salaryUnknown: bool, locationUncertain: bool}>,
     *     new: int,
     *     notified: int
     * }
     */
    public function run(User $user, array $jobs, bool $persist): array
    {
        $settings = $user->userSetting;
        $matches = [];

        foreach ($jobs as $job) {
            $filterResult = $this->filterPipeline->apply($job, $settings);

            if (! $filterResult->passed) {
                continue;
            }

            $matches[] = [
                'job' => $job,
                'score' => $this->scoringService->score($job, $settings, $filterResult->locationUncertain),
                'salaryUnknown' => $filterResult->salaryUnknown,
                'locationUncertain' => $filterResult->locationUncertain,
            ];
        }

        $counts = $this->jobNotifications->process($user, $matches, $persist);

        return [
            'matches' => $matches,
            'new' => $counts['new'],
            'notified' => $counts['notified'],
        ];
    }
}
