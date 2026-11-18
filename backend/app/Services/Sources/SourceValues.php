<?php

declare(strict_types=1);

namespace App\Services\Sources;

use Illuminate\Support\Carbon;
use Throwable;

final class SourceValues
{
    /**
     * @return list<string>
     */
    public function toTags(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $tags = [];

        foreach ($value as $tag) {
            if (is_scalar($tag) && trim((string) $tag) !== '') {
                $tags[] = trim((string) $tag);
            }
        }

        return $tags;
    }

    public function toPositiveFloat(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return $number > 0 ? $number : null;
    }

    public function toDate(mixed $value): ?Carbon
    {
        if (is_int($value)) {
            try {
                return Carbon::createFromTimestamp($value);
            } catch (Throwable) {
                return null;
            }
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
