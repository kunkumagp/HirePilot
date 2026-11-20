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
                $this->award($reasons, $score, $weights, 'title_skill', "title skill: {$skill}");
            }

            if (Str::contains($job->description, $skill, true)) {
                $this->award($reasons, $score, $weights, 'desc_skill', "description skill: {$skill}");
            }
        }

        $text = $job->title.' '.$job->description;
        $locationText = $job->locationText.' '.implode(' ', $job->tags);

        // Free-text query typed by the user (e.g. "React Developer, HubSpot Marketing").
        $query = $settings !== null
            ? trim((string) ($settings->job_query ?? ''))
            : trim((string) config('jobhunter.preferences.job_query', ''));

        if ($query !== '') {
            /** @var list<string> $stopWords */
            $stopWords = config('jobhunter.job_query.stop_words', []);
            $tokens = [];
            $matchedPhrase = null;

            foreach (explode(',', $query) as $rawPhrase) {
                $phrase = trim($rawPhrase);

                if ($phrase === '') {
                    continue;
                }

                if ($matchedPhrase === null && Str::contains($text, $phrase, true)) {
                    $matchedPhrase = $phrase;
                }

                foreach (preg_split('/\s+/', Str::lower($phrase)) ?: [] as $word) {
                    $word = trim($word, '.,-/()');

                    if (strlen($word) < 3 || in_array($word, $stopWords, true)) {
                        continue;
                    }

                    $tokens[$word] = true;
                }
            }

            if ($matchedPhrase !== null) {
                $this->award($reasons, $score, $weights, 'query_match', "query phrase: {$matchedPhrase}");
            }

            $maxTokens = (int) config('jobhunter.job_query.max_token_points', 3);
            $awarded = 0;

            foreach (array_keys($tokens) as $token) {
                if ($awarded >= $maxTokens) {
                    break;
                }

                if (Str::contains($text, (string) $token, true)) {
                    $this->award($reasons, $score, $weights, 'query_token', "query token: {$token}");
                    $awarded++;
                }
            }
        }

        // Job role from the two-level taxonomy, with the legacy field as fallback.
        $category = $settings?->job_category ?? config('jobhunter.preferences.job_category');
        $subcategory = $settings?->job_subcategory ?? config('jobhunter.preferences.job_subcategory');
        $field = (string) ($settings?->field ?? config('jobhunter.preferences.field', 'web_development'));

        /** @var list<string> $fieldKeywords */
        $fieldKeywords = $settings?->field_keywords ?? config('jobhunter.preferences.field_keywords', []);

        /** @var list<string> $presetKeywords */
        $presetKeywords = [];

        if ($category !== null && $category !== '') {
            $presetKeywords = $subcategory !== null && $subcategory !== ''
                ? (array) config("job_taxonomy.categories.{$category}.subcategories.{$subcategory}.keywords", [])
                : (array) config("job_taxonomy.categories.{$category}.keywords", []);
        } else {
            $presetKeywords = (array) config("jobhunter.fields.{$field}", []);
        }

        $allFieldKeywords = array_values(array_unique([...$presetKeywords, ...$fieldKeywords]));
        $fieldLabel = $subcategory !== null && $subcategory !== ''
            ? (string) config("job_taxonomy.categories.{$category}.subcategories.{$subcategory}.label", $subcategory)
            : ($category !== null && $category !== ''
                ? (string) config("job_taxonomy.categories.{$category}.label", $category)
                : $field);

        if ($this->containsAny($text, $allFieldKeywords)) {
            $this->award($reasons, $score, $weights, 'field_match', "field: {$fieldLabel}");
        }

        // Experience level from title/description keywords.
        $level = (string) ($settings?->experience_level ?? config('jobhunter.preferences.experience_level', 'any'));

        if ($level !== 'any') {
            /** @var list<string> $levelKeywords */
            $levelKeywords = config("jobhunter.experience_levels.{$level}", []);

            if ($this->containsAny($text, $levelKeywords)) {
                $this->award($reasons, $score, $weights, 'experience_match', "experience level: {$level}");
            }
        }

        // Minimum years, read from phrases like "5+ years".
        $minYears = $settings?->min_years_experience ?? config('jobhunter.preferences.min_years_experience');

        if ($minYears !== null && preg_match_all('/(\d{1,2})\+?\s*years?/i', $text, $matches) > 0) {
            $maxFound = max(array_map('intval', $matches[1]));

            if ($maxFound >= (int) $minYears) {
                $this->award($reasons, $score, $weights, 'years_match', "experience >= {$minYears} years");
            }
        }

        // Work arrangement detected from job wording.
        $arrangement = (string) ($settings?->work_arrangement ?? config('jobhunter.preferences.work_arrangement', 'any'));

        if ($arrangement !== 'any') {
            /** @var list<string> $arrangementKeywords */
            $arrangementKeywords = config("jobhunter.arrangements.{$arrangement}", []);

            if ($this->containsAny($text.' '.$locationText, $arrangementKeywords)) {
                $this->award($reasons, $score, $weights, 'arrangement_match', "arrangement: {$arrangement}");
            }
        }

        // Preferred countries / regions.
        /** @var list<string> $countries */
        $countries = $settings?->preferred_countries ?? config('jobhunter.preferences.preferred_countries', []);

        if ($this->containsAny($locationText, $countries)) {
            $this->award($reasons, $score, $weights, 'country_match', 'preferred country/region');
        }

        if (Str::contains($locationText, 'worldwide', true)) {
            $this->award($reasons, $score, $weights, 'worldwide', 'worldwide');
        }

        $currency = (string) ($settings?->salary_currency
            ?? config('jobhunter.preferences.salary_currency', 'USD'));
        $minimum = (float) ($settings?->min_salary_usd_yearly
            ?? config('jobhunter.filters.min_salary_usd_yearly', 24000));

        if ($job->salaryCurrency === $currency) {
            $yearlyMax = $this->salaryNormalizer->toYearly($job->salaryMax ?? $job->salaryMin ?? 0.0);

            if ($yearlyMax > $minimum) {
                $this->award($reasons, $score, $weights, 'usd_salary', 'listed salary above target');
            }
        }

        if ($job->postedAt !== null && $job->postedAt->gt(now()->subDay())) {
            $this->award($reasons, $score, $weights, 'fresh_24h', 'posted in last 24h');
        }

        if ($locationUncertain) {
            $this->award($reasons, $score, $weights, 'location_uncertain', 'location uncertain');
        }

        return new ScoreResult(score: $score, reasons: $reasons);
    }

    /**
     * @param  list<string>  $reasons
     * @param  array<string, int>  $weights
     */
    private function award(array &$reasons, int &$score, array $weights, string $key, string $label): void
    {
        $points = (int) ($weights[$key] ?? 0);

        if ($points === 0) {
            return;
        }

        $score += $points;
        $reasons[] = sprintf('%s (%+d)', $label, $points);
    }

    /**
     * @param  list<string>  $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && Str::contains($haystack, $needle, true)) {
                return true;
            }
        }

        return false;
    }
}
