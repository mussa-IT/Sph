<?php

namespace App\Services\OpenAI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OpenAIClient
{
    public function isConfigured(): bool
    {
        return (string) config('services.openai.key', '') !== '';
    }

    /**
     * @param array<int, array<string, string>> $messages
     */
    public function createChatCompletion(array $messages, ?string $model = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $this->guardCircuitBreaker();

        try {
            $response = Http::baseUrl((string) config('services.openai.base_url', 'https://api.openai.com/v1'))
                ->acceptJson()
                ->withToken((string) config('services.openai.key'))
                ->connectTimeout((float) config('services.openai.connect_timeout', 5))
                ->timeout((float) config('services.openai.timeout', 20))
                ->retry(
                    (int) config('services.openai.retry_attempts', 2),
                    (int) config('services.openai.retry_sleep_ms', 250),
                    throw: false
                )
                ->post('/chat/completions', [
                    'model' => $model ?: (string) config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => $messages,
                    'temperature' => 0.5,
                ]);

            if (! $response->successful()) {
                throw new RuntimeException('OpenAI request failed with status ' . $response->status());
            }

            $data = $response->json();
            $content = trim((string) data_get($data, 'choices.0.message.content', ''));

            if ($content === '') {
                throw new RuntimeException('OpenAI returned an empty response.');
            }

            $this->resetCircuitBreaker();

            return [
                'reply' => $content,
                'model' => (string) data_get($data, 'model', $model ?: config('services.openai.model')),
                'raw' => $data,
            ];
        } catch (Throwable $exception) {
            $this->recordCircuitFailure();
            throw $exception;
        }
    }

    private function guardCircuitBreaker(): void
    {
        $openUntil = (int) Cache::get($this->openUntilKey(), 0);

        if ($openUntil > now()->timestamp) {
            $seconds = max(1, $openUntil - now()->timestamp);
            throw new RuntimeException('OpenAI circuit breaker is open. Retry in ' . $seconds . ' seconds.');
        }
    }

    private function recordCircuitFailure(): void
    {
        $failures = (int) Cache::get($this->failuresKey(), 0) + 1;
        Cache::put($this->failuresKey(), $failures, now()->addMinutes(30));

        $threshold = (int) config('services.openai.circuit_breaker.threshold', 5);
        if ($failures < $threshold) {
            return;
        }

        $cooldownSeconds = (int) config('services.openai.circuit_breaker.cooldown_seconds', 60);
        Cache::put($this->openUntilKey(), now()->addSeconds($cooldownSeconds)->timestamp, now()->addSeconds($cooldownSeconds));
        Cache::put($this->failuresKey(), 0, now()->addMinutes(30));
    }

    private function resetCircuitBreaker(): void
    {
        Cache::forget($this->failuresKey());
        Cache::forget($this->openUntilKey());
    }

    private function failuresKey(): string
    {
        return 'openai:circuit:failures';
    }

    private function openUntilKey(): string
    {
        return 'openai:circuit:open_until';
    }
}

