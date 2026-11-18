<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ScoreResult
{
    /**
     * @param  list<string>  $reasons
     */
    public function __construct(
        public int $score,
        public array $reasons,
    ) {
        //
    }
}
