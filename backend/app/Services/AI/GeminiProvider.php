<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class GeminiProvider implements AIProviderInterface
{
    private readonly string $apiKey;
    private readonly string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.gemini.api_key', '');
        $this->model = config('ai.providers.gemini.model', 'gemini-2.0-flash');
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $options['model'] ?? $this->model,
            $this->apiKey,
        );

        $response = Http::timeout(60)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 2048,
            ],
        ]);

        $data = $response->throw()->json();

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function generateJson(string $prompt, array $options = []): array
    {
        $jsonPrompt = $prompt . "\n\nRespond with valid JSON only. No markdown, no code fences.";

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $options['model'] ?? $this->model,
            $this->apiKey,
        );

        $response = Http::timeout(60)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $jsonPrompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.3,
                'maxOutputTokens' => $options['max_tokens'] ?? 4096,
            ],
        ]);

        $data = $response->throw()->json();

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $text = preg_replace('/```(?:json)?\s*|\s*```/', '', $text);

        return json_decode($text, true) ?? [];
    }
}
