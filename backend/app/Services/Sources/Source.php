<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;

interface Source
{
    public function name(): string;

    /**
     * Fetch and normalise jobs. Must never throw: log the failure and return an empty list.
     *
     * @return list<JobData>
     */
    public function fetch(): array;
}
