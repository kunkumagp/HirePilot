<?php

namespace App\DTOs\AI;

class AICompletionResponse
{
    public function __construct(
        public readonly string $content,
        public readonly ?int $inputTokens = null,
        public readonly ?int $outputTokens = null,
        public readonly ?string $model = null,
        public readonly ?string $finishReason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? '',
            inputTokens: $data['input_tokens'] ?? null,
            outputTokens: $data['output_tokens'] ?? null,
            model: $data['model'] ?? null,
            finishReason: $data['finish_reason'] ?? null,
        );
    }
}
