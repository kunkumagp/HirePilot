<?php

namespace App\DTOs\AI;

class AICompletionRequest
{
    public function __construct(
        public readonly string $prompt,
        public readonly string $model = 'gpt-4o',
        public readonly float $temperature = 0.7,
        public readonly int $maxTokens = 2048,
        public readonly array $messages = [],
        public readonly ?string $systemPrompt = null,
    ) {}

    public static function fromPrompt(string $prompt, array $options = []): self
    {
        return new self(
            prompt: $prompt,
            model: $options['model'] ?? 'gpt-4o',
            temperature: $options['temperature'] ?? 0.7,
            maxTokens: $options['max_tokens'] ?? 2048,
            messages: $options['messages'] ?? [],
            systemPrompt: $options['system_prompt'] ?? null,
        );
    }
}
