<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;
use App\Services\TextNormalizer;

final class RemotiveSource extends JsonApiSource
{
    private const ENDPOINT = 'https://remotive.com/api/remote-jobs?category=software-dev';

    public function __construct(
        private readonly TextNormalizer $textNormalizer,
        private readonly SourceValues $values,
    ) {
        //
    }

    public function name(): string
    {
        return 'remotive';
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
            $url = trim((string) ($item['url'] ?? ''));

            if ($title === '' || $url === '') {
                continue;
            }

            $externalId = trim((string) ($item['id'] ?? ''));

            if ($externalId === '') {
                $externalId = md5($url);
            }

            [$salaryMin, $salaryMax, $currency] = $this->parseSalary((string) ($item['salary'] ?? ''));

            $jobs[] = new JobData(
                source: $this->name(),
                externalId: $externalId,
                title: $title,
                company: trim((string) ($item['company_name'] ?? '')),
                url: $url,
                description: $this->textNormalizer->toPlainText((string) ($item['description'] ?? '')),
                locationText: trim((string) ($item['candidate_required_location'] ?? '')),
                tags: $this->values->toTags($item['tags'] ?? null),
                salaryMin: $salaryMin,
                salaryMax: $salaryMax,
                salaryCurrency: $currency,
                postedAt: $this->values->toDate($item['publication_date'] ?? null),
            );
        }

        return $jobs;
    }

    /**
     * @return array{0: ?float, 1: ?float, 2: ?string}
     */
    private function parseSalary(string $text): array
    {
        if (! preg_match('/([0-9]+(?:\.[0-9]+)?)\s*([kK])?\s*-\s*\$?\s*([0-9]+(?:\.[0-9]+)?)\s*([kK])?/', $text, $matches)) {
            return [null, null, null];
        }

        $min = (float) $matches[1];
        $max = (float) $matches[3];

        if (($matches[2] ?? '') !== '') {
            $min *= 1000;
        }

        if (($matches[4] ?? '') !== '') {
            $max *= 1000;
        }

        $currency = str_contains($text, '$') || str_contains(strtoupper($text), 'USD') ? 'USD' : null;

        return [$min, $max, $currency];
    }
}
