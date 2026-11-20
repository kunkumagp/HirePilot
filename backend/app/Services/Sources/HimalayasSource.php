<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;
use App\Services\TextNormalizer;

final class HimalayasSource extends JsonApiSource
{
    private const ENDPOINT = 'https://himalayas.app/jobs/api?limit=50';

    public function __construct(
        private readonly TextNormalizer $textNormalizer,
        private readonly SourceValues $values,
    ) {
        //
    }

    public function name(): string
    {
        return 'himalayas';
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

        foreach ($payload['jobs'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $url = trim((string) ($item['applicationLink'] ?? ''));

            if ($title === '' || $url === '') {
                continue;
            }

            $externalId = trim((string) ($item['guid'] ?? ''));

            if ($externalId === '') {
                $externalId = md5($url);
            }

            $salaryMin = $this->values->toPositiveFloat($item['minSalary'] ?? null);
            $salaryMax = $this->values->toPositiveFloat($item['maxSalary'] ?? null);
            $location = $this->values->toTags($item['locationRestrictions'] ?? null);
            $tags = array_merge(
                $this->values->toTags($item['categories'] ?? null),
                $this->values->toTags($item['parentCategories'] ?? null),
            );

            $jobs[] = new JobData(
                source: $this->name(),
                externalId: $externalId,
                title: $title,
                company: trim((string) ($item['companyName'] ?? '')),
                url: $url,
                description: $this->textNormalizer->toPlainText((string) ($item['description'] ?? $item['excerpt'] ?? '')),
                locationText: implode(', ', $location),
                tags: $tags,
                salaryMin: $salaryMin,
                salaryMax: $salaryMax,
                salaryCurrency: ($salaryMin !== null || $salaryMax !== null)
                    ? (trim((string) ($item['currency'] ?? '')) ?: 'USD')
                    : null,
                postedAt: $this->values->toDate($item['pubDate'] ?? null),
            );
        }

        return $jobs;
    }
}
