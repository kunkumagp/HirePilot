<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTO\JobData;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\JobDataFactory;
use App\Services\UserPipelineService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jobhunter:backfill')]
#[Description('Apply current user rules to every stored job and create missing user jobs.')]
class BackfillUserJobs extends Command
{
    public function __construct(
        private readonly JobDataFactory $jobDataFactory,
        private readonly UserPipelineService $userPipeline,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var list<JobData> $jobs */
        $jobs = JobPosting::query()->orderBy('id')->get()->map(
            fn (JobPosting $posting) => $this->jobDataFactory->fromPosting($posting),
        )->all();

        $this->line('Stored jobs: '.count($jobs));

        foreach (User::query()->where('is_active', true)->orderBy('id')->get() as $user) {
            $result = $this->userPipeline->run($user, $jobs, true);

            $this->line(sprintf(
                '  %-30s total %d, new %d, notified %d',
                $user->email,
                $user->userJobs()->count(),
                $result['new'],
                $result['notified'],
            ));
        }

        return self::SUCCESS;
    }
}
