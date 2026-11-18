<?php

declare(strict_types=1);

namespace App\DTO;

use Illuminate\Support\Carbon;

final readonly class JobData
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public string $source,
        public string $externalId,
        public string $title,
        public string $company,
        public string $url,
        public string $description,
        public string $locationText,
        public array $tags,
        public ?float $salaryMin,
        public ?float $salaryMax,
        public ?string $salaryCurrency,
        public ?Carbon $postedAt,
    ) {
        //
    }
}
