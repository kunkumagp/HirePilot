<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use App\Models\JobPosting;
use App\Services\Sources\Source;

final class FetchService
{
    public function __construct(private readonly DedupeService $dedupe)
    {
        //
    }

    /**
     * @param  array<string, true>  $seenHashes
     * @return list<JobData>
     */
    public function fetchSource(Source $source, bool $persist, array &$seenHashes): array
    {
        $kept = [];

        foreach ($source->fetch() as $job) {
            $hash = $this->dedupe->contentHash($job);

            if (isset($seenHashes[$hash])) {
                // Keep the existing posting (no duplicate user jobs), but refresh
                // its content in case the source updated the description.
                if ($persist) {
                    JobPosting::query()
                        ->where('source', $job->source)
                        ->where('external_id', $job->externalId)
                        ->update(['description' => $job->description]);
                }

                continue;
            }

            $seenHashes[$hash] = true;
            $kept[] = $job;

            if (! $persist) {
                continue;
            }

            JobPosting::updateOrCreate(
                ['source' => $job->source, 'external_id' => $job->externalId],
                [
                    'title' => $job->title,
                    'company' => $job->company,
                    'url' => $job->url,
                    'description' => $job->description,
                    'location_text' => $job->locationText,
                    'tags' => $job->tags,
                    'salary_min' => $job->salaryMin,
                    'salary_max' => $job->salaryMax,
                    'salary_currency' => $job->salaryCurrency,
                    'posted_at' => $job->postedAt,
                    'first_seen_at' => now(),
                    'content_hash' => $hash,
                ],
            );
        }

        return $kept;
    }
}
