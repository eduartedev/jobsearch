<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OllamaProvider implements AiProvider
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $model,
    ) {}

    public function name(): string
    {
        return 'ollama';
    }

    public function chat(string $system, string $prompt, array $options = []): string
    {
        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->timeout((int) ($options['timeout'] ?? 120))
            ->acceptJson()
            ->post('/api/chat', [
                'model' => $options['model'] ?? $this->model,
                'stream' => false,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException('Ollama no respondió: '.$e->getMessage(), 0, $e);
        }

        $content = data_get($response->json(), 'message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('Ollama devolvió una respuesta vacía.');
        }

        return $content;
    }

    /**
     * @return list<string>
     */
    public function listModels(): array
    {
        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->timeout(10)
            ->acceptJson()
            ->get('/api/tags');

        if (! $response->successful()) {
            return [];
        }

        $models = collect($response->json('models', []))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return array_values($models);
    }
}
