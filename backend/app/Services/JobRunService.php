<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\Sources\Source;
use Illuminate\Support\Facades\Log;

final class JobRunService
{
    public function __construct(
        private readonly FetchService $fetchService,
        private readonly UserPipelineService $userPipeline,
    ) {
        //
    }

    /**
     * Fetch every enabled source once, then run each active user's rules.
     *
     * @return array{
     *     fetched: int,
     *     sources: array<string, int>,
     *     users: list<array{email: string, matched: int, new: int, notified: int}>,
     *     matches: int,
     *     new: int,
     *     notified: int
     * }
     */
    public function run(bool $persist = true): array
    {
        /** @var array<string, array{enabled: bool, adapter?: class-string}> $sources */
        $sources = config('jobhunter.sources', []);

        /** @var list<JobData> $allJobs */
        $allJobs = [];

        /** @var array<string, true> $seenHashes */
        $seenHashes = array_fill_keys(JobPosting::query()->pluck('content_hash')->all(), true);

        /** @var array<string, int> $sourceCounts */
        $sourceCounts = [];

        foreach ($sources as $key => $settings) {
            if (! ($settings['enabled'] ?? false)) {
                continue;
            }

            $adapterClass = $settings['adapter'] ?? null;

            if (! is_string($adapterClass) || ! class_exists($adapterClass)) {
                Log::warning('Source has no adapter yet; skipping', ['source' => $key]);

                continue;
            }

            $source = app()->make($adapterClass);

            if (! $source instanceof Source) {
                Log::warning('Source adapter does not implement the Source interface', ['source' => $key]);

                continue;
            }

            $jobs = $this->fetchService->fetchSource($source, $persist, $seenHashes);
            $sourceCounts[$source->name()] = count($jobs);
            $allJobs = array_merge($allJobs, $jobs);
        }

        $users = User::query()
            ->where('is_active', true)
            ->with('userSetting')
            ->orderBy('id')
            ->get();

        /** @var list<array{email: string, matched: int, new: int, notified: int}> $userResults */
        $userResults = [];
        $totals = ['matches' => 0, 'new' => 0, 'notified' => 0];

        foreach ($users as $user) {
            $result = $this->userPipeline->run($user, $allJobs, $persist);
            $matched = count($result['matches']);

            $userResults[] = [
                'email' => $user->email,
                'matched' => $matched,
                'new' => $result['new'],
                'notified' => $result['notified'],
            ];

            $totals['matches'] += $matched;
            $totals['new'] += $result['new'];
            $totals['notified'] += $result['notified'];
        }

        return [
            'fetched' => count($allJobs),
            'sources' => $sourceCounts,
            'users' => $userResults,
            'matches' => $totals['matches'],
            'new' => $totals['new'],
            'notified' => $totals['notified'],
        ];
    }
}
