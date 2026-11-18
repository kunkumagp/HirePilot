<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use Illuminate\Support\Str;

final class DedupeService
{
    public function contentHash(JobData $job): string
    {
        $key = Str::lower($job->company.' '.$job->title);
        $key = (string) preg_replace('/[^\pL\pN]+/u', ' ', $key);
        $key = trim((string) preg_replace('/\s+/u', ' ', $key));

        return hash('sha256', $key);
    }
}
