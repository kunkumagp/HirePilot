<?php

namespace App\Services\AI;

use App\Enums\AIProviderType;
use InvalidArgumentException;

class AIProviderManager implements AIProviderInterface
{
    private array $providers = [];

    public function __construct(
        private readonly string $defaultDriver,
    ) {}

    public function provider(?string $name = null): AIProviderInterface
    {
        $name = $name ?? $this->defaultDriver;

        if (!isset($this->providers[$name])) {
            $this->providers[$name] = $this->resolve($name);
        }

        return $this->providers[$name];
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $driver = $options['provider'] ?? null;

        return $this->provider($driver)->generateText($prompt, $options);
    }

    public function generateJson(string $prompt, array $options = []): array
    {
        $driver = $options['provider'] ?? null;

        return $this->provider($driver)->generateJson($prompt, $options);
    }

    private function resolve(string $name): AIProviderInterface
    {
        $provider = AIProviderType::tryFrom($name);

        return match ($provider) {
            AIProviderType::OpenAI => app(OpenAIProvider::class),
            AIProviderType::Anthropic => app(AnthropicProvider::class),
            AIProviderType::Gemini => app(GeminiProvider::class),
            null => throw new InvalidArgumentException("Unknown AI provider: {$name}"),
        };
    }
}
