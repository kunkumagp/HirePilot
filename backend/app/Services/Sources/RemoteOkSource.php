<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;
use App\Services\TextNormalizer;

final class RemoteOkSource extends JsonApiSource
{
    private const ENDPOINT = 'https://remoteok.com/api';

    public function __construct(
        private readonly TextNormalizer $textNormalizer,
        private readonly SourceValues $values,
    ) {
        //
    }

    public function name(): string
    {
        return 'remoteok';
    }

    protected function endpoint(): string
    {
        return self::ENDPOINT;
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

        $salaryMin = $this->values->toPositiveFloat($item['salary_min'] ?? null);
        $salaryMax = $this->values->toPositiveFloat($item['salary_max'] ?? null);

        return new JobData(
            source: $this->name(),
            externalId: $externalId,
            title: $title,
            company: trim((string) ($item['company'] ?? '')),
            url: $url,
            description: $this->textNormalizer->toPlainText((string) ($item['description'] ?? '')),
            locationText: trim((string) ($item['location'] ?? '')),
            tags: $this->values->toTags($item['tags'] ?? null),
            salaryMin: $salaryMin,
            salaryMax: $salaryMax,
            salaryCurrency: ($salaryMin !== null || $salaryMax !== null) ? 'USD' : null,
            postedAt: $this->values->toDate($item['date'] ?? null),
        );
    }
}
