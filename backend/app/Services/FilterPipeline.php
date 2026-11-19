<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\FilterResult;
use App\DTO\JobData;
use App\Models\UserSetting;
use Illuminate\Support\Str;

final class FilterPipeline
{
    public function __construct(private readonly SalaryNormalizer $salaryNormalizer)
    {
        //
    }

    public function apply(JobData $job, ?UserSetting $settings = null): FilterResult
    {
        $maxAgeDays = (int) ($settings?->max_age_days ?? config('jobhunter.filters.max_age_days', 14));

        if ($job->postedAt !== null && $job->postedAt->lt(now()->subDays($maxAgeDays))) {
            return FilterResult::reject('older than max_age_days');
        }

        /** @var list<string> $excludeWords */
        $excludeWords = $settings?->exclude_words ?? config('jobhunter.filters.exclude_words', []);

        if ($excludeWords !== [] && Str::contains($job->title.' '.$job->description, $excludeWords, true)) {
            return FilterResult::reject('blacklisted word');
        }

        $locationText = Str::lower($job->locationText.' '.implode(' ', $job->tags));

        /** @var list<string> $locationReject */
        $locationReject = $settings?->location_reject ?? config('jobhunter.filters.location_reject', []);

        if ($this->containsAny($locationText, $locationReject)) {
            return FilterResult::reject('location restricted');
        }

        /** @var list<string> $locationAccept */
        $locationAccept = $settings?->location_accept ?? config('jobhunter.filters.location_accept', []);
        $locationUncertain = ! $this->containsAny($locationText, $locationAccept);

        $salaryUnknown = $job->salaryMin === null && $job->salaryMax === null;

        if ($salaryUnknown) {
            $allowUnknown = (bool) ($settings?->allow_unknown_salary
                ?? config('jobhunter.filters.allow_unknown_salary', true));

            if (! $allowUnknown) {
                return FilterResult::reject('salary not listed');
            }

            return FilterResult::pass(locationUncertain: $locationUncertain, salaryUnknown: true);
        }

        $currency = (string) ($settings?->salary_currency
            ?? config('jobhunter.preferences.salary_currency', 'USD'));
        $currencySymbols = $currency === 'USD' ? ['USD', '$'] : [$currency];

        $currencyMatches = $job->salaryCurrency === $currency
            || Str::contains($job->title.' '.$job->description, $currencySymbols, true);

        if (! $currencyMatches) {
            return FilterResult::reject('salary currency does not match preference');
        }

        $yearlyMax = $this->salaryNormalizer->toYearly($job->salaryMax ?? $job->salaryMin ?? 0.0);
        $minimum = (float) ($settings?->min_salary_usd_yearly
            ?? config('jobhunter.filters.min_salary_usd_yearly', 24000));

        if ($yearlyMax < $minimum) {
            return FilterResult::reject('salary below minimum');
        }

        return FilterResult::pass(locationUncertain: $locationUncertain, salaryUnknown: false);
    }

    /**
     * @param  list<string>  $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, Str::lower((string) $needle))) {
                return true;
            }
        }

        return false;
    }
}
