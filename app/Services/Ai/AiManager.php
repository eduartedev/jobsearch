<?php

namespace App\Services\Ai;

use App\Models\AiSetting;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

class AiManager
{
    public function settingsFor(User $user): AiSetting
    {
        $defaults = config('ai.defaults');

        return AiSetting::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'provider' => $defaults['provider'],
                'ollama_base_url' => $defaults['ollama_base_url'],
                'ollama_model' => $defaults['ollama_model'],
                'claude_model' => $defaults['claude_model'],
                'document_language' => $defaults['document_language'],
            ],
        );
    }

    public function driverFor(User $user): AiProvider
    {
        $settings = $this->settingsFor($user);

        return match ($settings->provider) {
            'ollama' => new OllamaProvider(
                $settings->ollama_base_url ?: config('ai.defaults.ollama_base_url'),
                $settings->ollama_model ?: config('ai.defaults.ollama_model'),
            ),
            'claude' => new ClaudeProvider(
                (string) $settings->claude_api_key,
                $settings->claude_model ?: config('ai.defaults.claude_model'),
            ),
            default => throw new InvalidArgumentException("Proveedor AI desconocido: {$settings->provider}"),
        };
    }

    /**
     * @return list<string>
     */
    public function availableOllamaModels(User $user): array
    {
        $settings = $this->settingsFor($user);
        $provider = new OllamaProvider(
            $settings->ollama_base_url ?: config('ai.defaults.ollama_base_url'),
            $settings->ollama_model ?: config('ai.defaults.ollama_model'),
        );

        return $provider->listModels();
    }

    public function assertReady(User $user): void
    {
        $settings = $this->settingsFor($user);

        if ($settings->provider === 'claude' && ! $settings->hasClaudeApiKey()) {
            throw new RuntimeException('Configura la API key de Claude antes de usarlo.');
        }

        if ($settings->provider === 'ollama' && blank($settings->ollama_model)) {
            throw new RuntimeException('Elige un modelo de Ollama antes de usarlo.');
        }
    }
}
