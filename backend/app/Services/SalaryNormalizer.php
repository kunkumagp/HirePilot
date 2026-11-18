<?php

declare(strict_types=1);

namespace App\Services;

final class SalaryNormalizer
{
    public function toYearly(float $amount): float
    {
        $hourlyThreshold = (float) config('jobhunter.filters.salary.hourly_threshold', 1000);
        $monthlyThreshold = (float) config('jobhunter.filters.salary.monthly_threshold', 12000);

        if ($amount < $hourlyThreshold) {
            return $amount * (float) config('jobhunter.filters.salary.hours_per_year', 2080);
        }

        if ($amount < $monthlyThreshold) {
            return $amount * (float) config('jobhunter.filters.salary.months_per_year', 12);
        }

        return $amount;
    }
}
