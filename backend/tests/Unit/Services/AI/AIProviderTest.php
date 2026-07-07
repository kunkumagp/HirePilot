<?php

namespace Tests\Unit\Services\AI;

use App\Services\AI\AIProviderManager;
use App\Services\AI\AnthropicProvider;
use App\Services\AI\GeminiProvider;
use App\Services\AI\OpenAIProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class AIProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ai.providers.openai.api_key', 'test-openai-key');
        Config::set('ai.providers.anthropic.api_key', 'test-anthropic-key');
        Config::set('ai.providers.gemini.api_key', 'test-gemini-key');
    }

    public function test_openai_provider_generates_text(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Hello from OpenAI']],
                ],
            ]),
        ]);

        $provider = new OpenAIProvider();
        $result = $provider->generateText('Say hello');

        $this->assertSame('Hello from OpenAI', $result);

        Http::assertSent(function (Request $request) {
            $body = $request->data();
            return str_contains($request->url(), 'chat/completions')
                && $body['model'] === 'gpt-4o'
                && $body['messages'][0]['role'] === 'user'
                && $body['messages'][0]['content'] === 'Say hello';
        });
    }

    public function test_openai_provider_generates_json(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"name":"John","age":30}']],
                ],
            ]),
        ]);

        $provider = new OpenAIProvider();
        $result = $provider->generateJson('Extract info');

        $this->assertSame(['name' => 'John', 'age' => 30], $result);

        Http::assertSent(function (Request $request) {
            $body = $request->data();
            return $body['response_format']['type'] === 'json_object';
        });
    }

    public function test_anthropic_provider_generates_text(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => 'Hello from Claude'],
                ],
            ]),
        ]);

        $provider = new AnthropicProvider();
        $result = $provider->generateText('Say hello');

        $this->assertSame('Hello from Claude', $result);

        Http::assertSent(function (Request $request) {
            $body = $request->data();
            return str_contains($request->url(), '/messages')
                && $body['model'] === 'claude-sonnet-4-20250514';
        });
    }

    public function test_anthropic_provider_generates_json(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => '{"name":"Alice","role":"engineer"}'],
                ],
            ]),
        ]);

        $provider = new AnthropicProvider();
        $result = $provider->generateJson('Extract info');

        $this->assertSame(['name' => 'Alice', 'role' => 'engineer'], $result);
    }

    public function test_gemini_provider_generates_text(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hello from Gemini'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $provider = new GeminiProvider();
        $result = $provider->generateText('Say hello');

        $this->assertSame('Hello from Gemini', $result);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'generateContent');
        });
    }

    public function test_gemini_provider_generates_json(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"city":"Tokyo","temp":22}'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $provider = new GeminiProvider();
        $result = $provider->generateJson('Extract weather');

        $this->assertSame(['city' => 'Tokyo', 'temp' => 22], $result);
    }

    public function test_manager_resolves_default_provider(): void
    {
        Config::set('ai.default', 'openai');

        $manager = new AIProviderManager('openai');
        $provider = $manager->provider();

        $this->assertInstanceOf(OpenAIProvider::class, $provider);
    }

    public function test_manager_resolves_named_providers(): void
    {
        $manager = new AIProviderManager('openai');

        $this->assertInstanceOf(OpenAIProvider::class, $manager->provider('openai'));
        $this->assertInstanceOf(AnthropicProvider::class, $manager->provider('anthropic'));
        $this->assertInstanceOf(GeminiProvider::class, $manager->provider('gemini'));
    }

    public function test_manager_caches_provider_instance(): void
    {
        $manager = new AIProviderManager('openai');

        $first = $manager->provider('openai');
        $second = $manager->provider('openai');

        $this->assertSame($first, $second);
    }

    public function test_manager_throws_for_unknown_provider(): void
    {
        $manager = new AIProviderManager('openai');

        $this->expectException(InvalidArgumentException::class);
        $manager->provider('unknown');
    }

    public function test_manager_delegates_generate_text(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Delegated text']],
                ],
            ]),
        ]);

        $manager = new AIProviderManager('openai');
        $result = $manager->generateText('Hello');

        $this->assertSame('Delegated text', $result);
    }

    public function test_manager_delegates_generate_json(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"key":"value"}']],
                ],
            ]),
        ]);

        $manager = new AIProviderManager('openai');
        $result = $manager->generateJson('Parse this');

        $this->assertSame(['key' => 'value'], $result);
    }

    public function test_manager_allows_provider_override_in_options(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => 'From Anthropic via manager'],
                ],
            ]),
        ]);

        $manager = new AIProviderManager('openai');
        $result = $manager->generateText('Hello', ['provider' => 'anthropic']);

        $this->assertSame('From Anthropic via manager', $result);
    }

    public function test_openai_provider_handles_api_error(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'error' => ['message' => 'Invalid API key'],
            ], 401),
        ]);

        $provider = new OpenAIProvider();

        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        $provider->generateText('Hello');
    }

    public function test_anthropic_provider_handles_empty_response(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [],
            ]),
        ]);

        $provider = new AnthropicProvider();
        $result = $provider->generateText('Hello');

        $this->assertSame('', $result);
    }

    public function test_anthropic_provider_strips_json_fences(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => "```json\n{\"key\": \"value\"}\n```"],
                ],
            ]),
        ]);

        $provider = new AnthropicProvider();
        $result = $provider->generateJson('Extract');

        $this->assertSame(['key' => 'value'], $result);
    }
}
