<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    public function generateText(string $prompt): string;
}
