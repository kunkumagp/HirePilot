<?php

declare(strict_types=1);

namespace App\Services\Sources;

use App\DTO\JobData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class JsonApiSource implements Source
{
    abstract protected function endpoint(): string;

    /**
     * @param  array<mixed>  $payload
     * @return list<JobData>
     */
    abstract public function normalize(array $payload): array;

    public function fetch(): array
    {
        try {
            $response = Http::connectTimeout(3)
                ->timeout((int) config('jobhunter.http.timeout', 12))
                ->withUserAgent((string) config('jobhunter.http.user_agent', 'HirePilot/1.0'))
                ->retry((int) config('jobhunter.http.retries', 3), 100)
                ->throw()
                ->get($this->endpoint());

            $payload = $response->json();
        } catch (Throwable $exception) {
            Log::warning('Source fetch failed', [
                'source' => $this->name(),
                'error' => $exception->getMessage(),
            ]);

            return [];
        }

        if (! is_array($payload)) {
            Log::warning('Source returned an unexpected payload', ['source' => $this->name()]);

            return [];
        }

        return $this->normalize($payload);
    }
}
