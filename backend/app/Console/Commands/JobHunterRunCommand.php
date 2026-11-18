<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTO\JobData;
use App\DTO\ScoreResult;
use App\Models\User;
use App\Services\FilterPipeline;
use App\Services\ScoringService;
use App\Services\Sources\Source;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('jobhunter:run {--dry-run : Run the whole pipeline without sending notifications or marking them as notified}')]
#[Description('Fetch, filter, score, store and notify about matching remote jobs.')]
class JobHunterRunCommand extends Command
{
    public function __construct(
        private readonly FilterPipeline $filterPipeline,
        private readonly ScoringService $scoringService,
    ) {
        parent::__construct();
    }

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

        $user = User::where('email', config('jobhunter.profile.email'))->first();
        $settings = $user?->userSetting;
        $minScore = (int) ($settings?->min_score ?? config('jobhunter.scoring.min_score', 5));

        /** @var list<array{job: JobData, score: ScoreResult, salaryUnknown: bool}> $matches */
        $matches = [];

        foreach ($allJobs as $job) {
            $filterResult = $this->filterPipeline->apply($job, $settings);

            if (! $filterResult->passed) {
                continue;
            }

            $matches[] = [
                'job' => $job,
                'score' => $this->scoringService->score($job, $settings, $filterResult->locationUncertain),
                'salaryUnknown' => $filterResult->salaryUnknown,
            ];
        }

        $this->line('Fetched: '.count($allJobs));
        $this->line('After filters: '.count($matches));
        $this->line('New: 0');
        $this->line('Notified: 0');

        foreach (collect($matches)->groupBy(fn (array $match): string => $match['job']->source) as $sourceName => $items) {
            $this->newLine();
            $this->line($sourceName.' ('.$items->count().'):');

            foreach ($items as $item) {
                $job = $item['job'];
                $score = $item['score'];
                $belowThreshold = $score->score < $minScore ? ' (below min_score)' : '';

                $salary = $item['salaryUnknown']
                    ? 'salary not listed'
                    : sprintf('%s %s-%s', $job->salaryCurrency ?? '?', $job->salaryMin ?? '?', $job->salaryMax ?? '?');

                $this->line(sprintf(
                    '  - [%d%s] %s @ %s | %s | %s | %s',
                    $score->score,
                    $belowThreshold,
                    $job->title,
                    $job->company,
                    $job->locationText !== '' ? $job->locationText : '(location unclear)',
                    $salary,
                    $job->url,
                ));

                if ($score->reasons !== []) {
                    $this->line('      '.implode('; ', $score->reasons));
                }
            }
        }

        if ($matches === []) {
            $this->comment('No jobs matched the filters. Tune config/jobhunter.php if this is unexpected.');
        }

        return self::SUCCESS;
    }
}
