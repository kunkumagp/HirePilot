<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;
use App\Services\TextNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class WeWorkRemotelySource implements Source
{
    private const ENDPOINT = 'https://weworkremotely.com/categories/remote-programming-jobs.rss';

    public function __construct(
        private readonly TextNormalizer $textNormalizer,
        private readonly SourceValues $values,
    ) {
        //
    }

    public function name(): string
    {
        return 'weworkremotely';
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
            $body = $response->body();
        } catch (Throwable $exception) {
            Log::warning('WWR fetch failed', [
                'source' => $this->name(),
                'error' => $exception->getMessage(),
            ]);

            return [];
        }

        return $this->normalize($body);
    }

    public function normalize(string $xml): array
    {
        $previous = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($feed === false || ! isset($feed->channel->item)) {
            return [];
        }

        $jobs = [];

        foreach ($feed->channel->item as $item) {
            $title = trim((string) $item->title);

            if ($title === '') {
                continue;
            }

            $parts = explode(':', $title, 2);
            $company = trim($parts[0]);
            $role = trim($parts[1] ?? $title);
            $url = trim((string) ($item->link ?: $item->guid));

            if ($url === '') {
                continue;
            }

            $description = html_entity_decode((string) $item->description, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $jobs[] = new JobData(
                source: $this->name(),
                externalId: md5((string) ($item->guid ?: $url)),
                title: $role,
                company: $company,
                url: $url,
                description: $this->textNormalizer->toPlainText($description),
                locationText: trim((string) ($item->region ?? '')),
                tags: $this->values->toTags([(string) ($item->category ?? '')]),
                salaryMin: null,
                salaryMax: null,
                salaryCurrency: null,
                postedAt: $this->values->toDate((string) ($item->pubDate ?? '')),
            );
        }

        return $jobs;
    }
}
