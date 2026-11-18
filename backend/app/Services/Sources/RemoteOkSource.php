<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RemoteOkSource implements Source
{
    private const ENDPOINT = 'https://remoteok.com/api';

    public function name(): string
    {
        return 'remoteok';
    }

    public function fetch(): array
    {
        try {
            $response = Http::connectTimeout(3)
                ->timeout((int) config('jobhunter.http.timeout', 12))
                ->withUserAgent((string) config('jobhunter.http.user_agent', 'HirePilot/1.0'))
                ->retry((int) config('jobhunter.http.retries', 3), 100)
                ->throw()
                ->get(self::ENDPOINT);

            $payload = $response->json();
        } catch (Throwable $exception) {
            Log::warning('RemoteOK fetch failed', [
                'source' => $this->name(),
                'error' => $exception->getMessage(),
            ]);

            return [];
        }

        if (! is_array($payload)) {
            Log::warning('RemoteOK returned an unexpected payload', ['source' => $this->name()]);

            return [];
        }

        return $this->normalize($payload);
    }

    /**
     * @param  array<mixed>  $payload
     * @return list<JobData>
     */
    public function normalize(array $payload): array
    {
        $jobs = [];

        foreach ($payload as $index => $item) {
            // The first array item is legal/metadata, not a job.
            if ($index === 0 || ! is_array($item)) {
                continue;
            }

            $job = $this->toJobData($item);

            if ($job !== null) {
                $jobs[] = $job;
            }
        }

        return $jobs;
    }

    /**
     * @param  array<mixed>  $item
     */
    private function toJobData(array $item): ?JobData
    {
        $title = trim((string) ($item['position'] ?? ''));
        $url = trim((string) ($item['url'] ?? $item['apply_url'] ?? ''));
        $externalId = trim((string) ($item['id'] ?? ''));

        if ($externalId === '') {
            $externalId = trim((string) ($item['slug'] ?? ''));
        }

        if ($title === '' || $externalId === '' || $url === '') {
            return null;
        }

        $salaryMin = $this->toPositiveFloat($item['salary_min'] ?? null);
        $salaryMax = $this->toPositiveFloat($item['salary_max'] ?? null);
        $rawDescription = (string) ($item['description'] ?? '');
        $spacedDescription = (string) preg_replace('#</?(?:h[1-6]|p|li|ul|ol|div|br)>#i', ' ', $rawDescription);
        $description = (string) preg_replace(
            '/\s+/u',
            ' ',
            html_entity_decode(strip_tags($spacedDescription), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        );

        return new JobData(
            source: $this->name(),
            externalId: $externalId,
            title: $title,
            company: trim((string) ($item['company'] ?? '')),
            url: $url,
            description: trim($description),
            locationText: trim((string) ($item['location'] ?? '')),
            tags: $this->toTags($item['tags'] ?? null),
            salaryMin: $salaryMin,
            salaryMax: $salaryMax,
            salaryCurrency: ($salaryMin !== null || $salaryMax !== null) ? 'USD' : null,
            postedAt: $this->toPostedAt($item['date'] ?? null),
        );
    }

    private function toPositiveFloat(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return $number > 0 ? $number : null;
    }

    /**
     * @return list<string>
     */
    private function toTags(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $tags = [];

        foreach ($value as $tag) {
            if (is_scalar($tag) && trim((string) $tag) !== '') {
                $tags[] = trim((string) $tag);
            }
        }

        return $tags;
    }

    private function toPostedAt(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
