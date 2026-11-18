<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use App\DTO\ScoreResult;
use App\Models\UserSetting;
use Illuminate\Support\Str;

final class ScoringService
{
    public function __construct(private readonly SalaryNormalizer $salaryNormalizer)
    {
        //
    }

    public function score(JobData $job, ?UserSetting $settings = null, bool $locationUncertain = false): ScoreResult
    {
        /** @var array<string, int> $weights */
        $weights = $settings?->weights ?? config('jobhunter.scoring.weights', []);

        /** @var list<string> $skills */
        $skills = $settings?->skills ?? config('jobhunter.profile.skills', []);

        $score = 0;
        $reasons = [];

        foreach ($skills as $skill) {
            if ($skill === '') {
                continue;
            }

            if (Str::contains($job->title, $skill, true)) {
                $points = (int) ($weights['title_skill'] ?? 0);
                $score += $points;
                $reasons[] = "title skill: {$skill} (+{$points})";
            }

            if (Str::contains($job->description, $skill, true)) {
                $points = (int) ($weights['desc_skill'] ?? 0);
                $score += $points;
                $reasons[] = "description skill: {$skill} (+{$points})";
            }
        }

        $locationText = $job->locationText.' '.implode(' ', $job->tags);

        if (Str::contains($locationText, 'worldwide', true)) {
            $points = (int) ($weights['worldwide'] ?? 0);
            $score += $points;
            $reasons[] = "worldwide (+{$points})";
        }

        $minimum = (float) ($settings?->min_salary_usd_yearly
            ?? config('jobhunter.filters.min_salary_usd_yearly', 24000));

        if ($job->salaryCurrency === 'USD') {
            $yearlyMax = $this->salaryNormalizer->toYearly($job->salaryMax ?? $job->salaryMin ?? 0.0);

            if ($yearlyMax > $minimum) {
                $points = (int) ($weights['usd_salary'] ?? 0);
                $score += $points;
                $reasons[] = "USD salary above target (+{$points})";
            }
        }

        if ($job->postedAt !== null && $job->postedAt->gt(now()->subDay())) {
            $points = (int) ($weights['fresh_24h'] ?? 0);
            $score += $points;
            $reasons[] = "posted in last 24h (+{$points})";
        }

        if ($locationUncertain) {
            $points = (int) ($weights['location_uncertain'] ?? 0);
            $score += $points;
            $reasons[] = "location uncertain ({$points})";
        }

        return new ScoreResult(score: $score, reasons: $reasons);
    }
}
