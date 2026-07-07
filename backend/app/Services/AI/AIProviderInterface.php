<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    public function generateText(string $prompt, array $options = []): string;

    public function generateJson(string $prompt, array $options = []): array;
}
