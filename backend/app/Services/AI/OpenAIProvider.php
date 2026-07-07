<?php

namespace App\Services\AI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProviderInterface
{
    private readonly string $apiKey;
    private readonly string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.openai.api_key', '');
        $this->model = config('ai.providers.openai.model', 'gpt-4o');
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $response = $this->client()->post('/chat/completions', [
            'model' => $options['model'] ?? $this->model,
            'messages' => $this->buildMessages($prompt, $options),
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2048,
        ]);

        $data = $response->throw()->json();

        return $data['choices'][0]['message']['content'] ?? '';
    }

    public function generateJson(string $prompt, array $options = []): array
    {
        $options['response_format'] = ['type' => 'json_object'];

        $response = $this->client()->post('/chat/completions', [
            'model' => $options['model'] ?? $this->model,
            'messages' => $this->buildMessages($prompt, $options),
            'temperature' => $options['temperature'] ?? 0.3,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'response_format' => ['type' => 'json_object'],
        ]);

        $data = $response->throw()->json();

        $content = $data['choices'][0]['message']['content'] ?? '{}';

        return json_decode($content, true) ?? [];
    }

    private function client(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->baseUrl('https://api.openai.com/v1')
            ->acceptJson()
            ->timeout(60);
    }

    private function buildMessages(string $prompt, array $options): array
    {
        $messages = [];

        if (!empty($options['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $options['system_prompt']];
        }

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
