<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use App\Models\JobPosting;

final class JobDataFactory
{
    public function fromPosting(JobPosting $posting): JobData
    {
        return new JobData(
            source: $posting->source,
            externalId: $posting->external_id,
            title: $posting->title,
            company: $posting->company,
            url: $posting->url,
            description: (string) $posting->description,
            locationText: (string) $posting->location_text,
            tags: is_array($posting->tags) ? $posting->tags : [],
            salaryMin: $posting->salary_min !== null ? (float) $posting->salary_min : null,
            salaryMax: $posting->salary_max !== null ? (float) $posting->salary_max : null,
            salaryCurrency: $posting->salary_currency,
            postedAt: $posting->posted_at,
        );
    }
}
