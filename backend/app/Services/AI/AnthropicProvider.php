<?php

namespace App\Services\AI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AnthropicProvider implements AIProviderInterface
{
    private readonly string $apiKey;
    private readonly string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.anthropic.api_key', '');
        $this->model = config('ai.providers.anthropic.model', 'claude-sonnet-4-20250514');
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $response = $this->client()->post('/messages', [
            'model' => $options['model'] ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'temperature' => $options['temperature'] ?? 0.7,
            'system' => $options['system_prompt'] ?? '',
            'messages' => $this->buildMessages($prompt, $options),
        ]);

        $data = $response->throw()->json();

        return $data['content'][0]['text'] ?? '';
    }

    public function generateJson(string $prompt, array $options = []): array
    {
        $options['system_prompt'] = ($options['system_prompt'] ?? '')
            . "\n\nYou must respond with valid JSON only. No markdown, no code fences.";

        $response = $this->client()->post('/messages', [
            'model' => $options['model'] ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 0.3,
            'system' => $options['system_prompt'] ?? '',
            'messages' => $this->buildMessages($prompt, $options),
        ]);

        $data = $response->throw()->json();

        $text = $data['content'][0]['text'] ?? '{}';
        $text = preg_replace('/```(?:json)?\s*|\s*```/', '', $text);

        return json_decode($text, true) ?? [];
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ])
            ->baseUrl('https://api.anthropic.com/v1')
            ->acceptJson()
            ->timeout(60);
    }

    private function buildMessages(string $prompt, array $options): array
    {
        $messages = [];

        if (!empty($options['messages'])) {
            foreach ($options['messages'] as $msg) {
                if ($msg instanceof \App\DTOs\AI\AIMessage) {
                    $messages[] = $msg->toArray();
                } elseif (is_array($msg) && isset($msg['role'], $msg['content'])) {
                    $messages[] = $msg;
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $messages;
    }
}
