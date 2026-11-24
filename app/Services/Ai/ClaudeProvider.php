<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function name(): string
    {
        return 'claude';
    }

    public function chat(string $system, string $prompt, array $options = []): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Falta la API key de Claude.');
        }

        $response = Http::baseUrl('https://api.anthropic.com')
            ->timeout((int) ($options['timeout'] ?? 120))
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ])
            ->acceptJson()
            ->post('/v1/messages', [
                'model' => $options['model'] ?? $this->model,
                'max_tokens' => (int) ($options['max_tokens'] ?? 4096),
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException('Claude no respondió: '.$e->getMessage(), 0, $e);
        }

        $blocks = $response->json('content', []);
        $text = collect($blocks)
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        if ($text === '') {
            throw new RuntimeException('Claude devolvió una respuesta vacía.');
        }

        return $text;
    }
}
