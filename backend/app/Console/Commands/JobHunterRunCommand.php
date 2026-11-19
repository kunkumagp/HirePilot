<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTO\JobData;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\FetchService;
use App\Services\Sources\Source;
use App\Services\UserPipelineService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('jobhunter:run {--dry-run : Run the whole pipeline without sending notifications or marking them as notified}')]
#[Description('Fetch, filter, score, store and notify about matching remote jobs.')]
class JobHunterRunCommand extends Command
{
    public function __construct(
        private readonly FetchService $fetchService,
        private readonly UserPipelineService $userPipeline,
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

        /** @var array<string, true> $seenHashes */
        $seenHashes = array_fill_keys(JobPosting::query()->pluck('content_hash')->all(), true);

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

            $jobs = $this->fetchService->fetchSource($source, ! $isDryRun, $seenHashes);
            $allJobs = array_merge($allJobs, $jobs);
            $this->line(sprintf('  %-14s unique %d', $source->name(), count($jobs)));
        }

        $users = User::query()
            ->where('is_active', true)
            ->with('userSetting')
            ->orderBy('id')
            ->get();

        /** @var list<array{user: User, result: array{matches: array, new: int, notified: int}}> $results */
        $results = [];
        $totals = ['matches' => 0, 'new' => 0, 'notified' => 0];

        foreach ($users as $user) {
            $result = $this->userPipeline->run($user, $allJobs, ! $isDryRun);
            $results[] = ['user' => $user, 'result' => $result];

            $totals['matches'] += count($result['matches']);
            $totals['new'] += $result['new'];
            $totals['notified'] += $result['notified'];
        }

        $this->line('Fetched: '.count($allJobs));
        $this->line('Active users: '.$users->count());

        foreach ($results as $entry) {
            $this->line(sprintf(
                '  %-30s matched %d, new %d, notified %d',
                $entry['user']->email,
                count($entry['result']['matches']),
                $entry['result']['new'],
                $entry['result']['notified'],
            ));
        }

        $this->line('After filters: '.$totals['matches']);
        $this->line('New: '.$totals['new']);
        $this->line('Notified: '.$totals['notified']);

        foreach ($results as $entry) {
            $matches = $entry['result']['matches'];

            if ($matches === []) {
                continue;
            }

            $minScore = (int) ($entry['user']->userSetting?->min_score ?? config('jobhunter.scoring.min_score', 5));

            $this->newLine();
            $this->line($entry['user']->email.' — top matches:');

            $topMatches = collect($matches)
                ->sortByDesc(fn (array $match): int => $match['score']->score)
                ->take(10);

            foreach ($topMatches as $item) {
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

        if ($users->isEmpty()) {
            $this->comment('No active users. Register an account or run the DefaultUserSeeder.');
        } elseif ($totals['matches'] === 0) {
            $this->comment('No jobs matched the filters. Tune config/jobhunter.php if this is unexpected.');
        }

        return self::SUCCESS;
    }
}
