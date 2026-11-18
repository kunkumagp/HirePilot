<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTO\JobData;
use App\Services\Sources\Source;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('jobhunter:run {--dry-run : Run the whole pipeline without sending notifications or marking them as notified}')]
#[Description('Fetch, filter, score, store and notify about matching remote jobs.')]
class JobHunterRunCommand extends Command
{
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        /** @var array<string, array{enabled: bool, adapter?: class-string}> $sources */
        $sources = config('jobhunter.sources', []);
        $enabledSources = collect($sources)
            ->filter(fn (array $settings): bool => (bool) ($settings['enabled'] ?? false))
            ->keys()
            ->all();

        $this->info('HirePilot — job run');
        $this->line('Mode: '.($isDryRun ? 'DRY RUN (no notifications will be sent)' : 'LIVE'));
        $this->line('Sources enabled: '.count($enabledSources).' ('.implode(', ', $enabledSources).')');

        /** @var list<JobData> $allJobs */
        $allJobs = [];

        foreach ($sources as $key => $settings) {
            if (! ($settings['enabled'] ?? false)) {
                continue;
            }

            $adapterClass = $settings['adapter'] ?? null;

            if (! is_string($adapterClass) || ! class_exists($adapterClass)) {
                Log::warning('Source has no adapter yet; skipping', ['source' => $key]);

                continue;
            }

            $source = $this->laravel->make($adapterClass);

            if (! $source instanceof Source) {
                Log::warning('Source adapter does not implement the Source interface', ['source' => $key]);

                continue;
            }

            $jobs = $source->fetch();
            $allJobs = array_merge($allJobs, $jobs);
            $this->line(sprintf('  %-14s fetched %d', $source->name(), count($jobs)));
        }

        $this->line('Fetched: '.count($allJobs));
        $this->line('After filters: '.count($allJobs));
        $this->line('New: 0');
        $this->line('Notified: 0');

        foreach (collect($allJobs)->groupBy(fn (JobData $job): string => $job->source) as $sourceName => $jobs) {
            $this->newLine();
            $this->line($sourceName.' ('.$jobs->count().'):');

            foreach ($jobs as $job) {
                $salary = $job->salaryCurrency !== null
                    ? sprintf('%s %s-%s', $job->salaryCurrency, $job->salaryMin ?? '?', $job->salaryMax ?? '?')
                    : 'salary not listed';

                $this->line(sprintf(
                    '  - %s @ %s | %s | %s | %s',
                    $job->title,
                    $job->company,
                    $job->locationText !== '' ? $job->locationText : '(location unclear)',
                    $salary,
                    $job->url,
                ));
            }
        }

        if ($allJobs === []) {
            $this->comment('No jobs fetched. Source adapters for the remaining sources arrive in Milestone 4.');
        }

        return self::SUCCESS;
    }
}
