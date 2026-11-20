<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\JobRunService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jobhunter:run {--dry-run : Run the whole pipeline without sending notifications or marking them as notified}')]
#[Description('Fetch, filter, score, store and notify about matching remote jobs.')]
class JobHunterRunCommand extends Command
{
    public function __construct(private readonly JobRunService $runService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $result = $this->runService->run(! $isDryRun);

        $this->info('HirePilot — job run');
        $this->line('Mode: '.($isDryRun ? 'DRY RUN (no notifications will be sent)' : 'LIVE'));

        foreach ($result['sources'] as $source => $count) {
            $this->line(sprintf('  %-14s unique %d', $source, $count));
        }

        $this->line('Fetched: '.$result['fetched']);
        $this->line('Active users: '.count($result['users']));

        foreach ($result['users'] as $user) {
            $this->line(sprintf(
                '  %-30s matched %d, new %d, notified %d',
                $user['email'],
                $user['matched'],
                $user['new'],
                $user['notified'],
            ));
        }

        $this->line('After filters: '.$result['matches']);
        $this->line('New: '.$result['new']);
        $this->line('Notified: '.$result['notified']);

        if ($result['users'] === []) {
            $this->comment('No active users. Register an account or run the DefaultUserSeeder.');
        } elseif ($result['matches'] === 0) {
            $this->comment('No jobs matched the filters. Tune config/jobhunter.php if this is unexpected.');
        }

        return self::SUCCESS;
    }
}
