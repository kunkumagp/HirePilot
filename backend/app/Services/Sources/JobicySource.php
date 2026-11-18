<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;
use App\Services\TextNormalizer;

final class JobicySource extends JsonApiSource
{
    private const ENDPOINT = 'https://jobicy.com/api/v2/remote-jobs?count=50';

    public function __construct(
        private readonly TextNormalizer $textNormalizer,
        private readonly SourceValues $values,
    ) {
        //
    }

    public function name(): string
    {
        return 'jobicy';
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

            $title = trim((string) ($item['jobTitle'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));

            if ($title === '' || $url === '') {
                continue;
            }

            $externalId = trim((string) ($item['id'] ?? ''));

            if ($externalId === '') {
                $externalId = md5($url);
            }

            $salaryMin = $this->values->toPositiveFloat($item['annualSalaryMin'] ?? null);
            $salaryMax = $this->values->toPositiveFloat($item['annualSalaryMax'] ?? null);
            $tags = array_merge(
                $this->values->toTags($item['jobIndustry'] ?? null),
                $this->values->toTags($item['jobType'] ?? null),
            );
            $description = trim((string) ($item['jobDescription'] ?? '')) !== ''
                ? (string) $item['jobDescription']
                : (string) ($item['jobExcerpt'] ?? '');

            $jobs[] = new JobData(
                source: $this->name(),
                externalId: $externalId,
                title: $title,
                company: trim((string) ($item['companyName'] ?? '')),
                url: $url,
                description: $this->textNormalizer->toPlainText($description),
                locationText: trim((string) ($item['jobGeo'] ?? '')),
                tags: $tags,
                salaryMin: $salaryMin,
                salaryMax: $salaryMax,
                salaryCurrency: ($salaryMin !== null || $salaryMax !== null)
                    ? (trim((string) ($item['salaryCurrency'] ?? '')) ?: 'USD')
                    : null,
                postedAt: $this->values->toDate($item['pubDate'] ?? null),
            );
        }

        return $jobs;
    }
}
