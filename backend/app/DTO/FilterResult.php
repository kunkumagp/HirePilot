<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class FilterResult
{
    public function __construct(
        public bool $passed,
        public bool $locationUncertain = false,
        public bool $salaryUnknown = false,
        public ?string $rejectReason = null,
    ) {
        //
    }

    public static function pass(bool $locationUncertain, bool $salaryUnknown): self
    {
        return new self(
            passed: true,
            locationUncertain: $locationUncertain,
            salaryUnknown: $salaryUnknown,
        );
    }

    public static function reject(string $reason): self
    {
        return new self(
            passed: false,
            rejectReason: $reason,
        );
    }
}
