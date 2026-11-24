<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAiSettingsRequest;
use App\Services\Ai\AiManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiSettingsController extends Controller
{
    public function __construct(private readonly AiManager $ai) {}

    public function edit(Request $request): Response
    {
        $settings = $this->ai->settingsFor($request->user());

        return Inertia::render('Settings/Ai', [
            'settings' => $settings->toPublicArray(),
            'ollamaModels' => $this->ai->availableOllamaModels($request->user()),
            'claudeModels' => config('ai.claude_models'),
        ]);
    }

    public function update(UpdateAiSettingsRequest $request): RedirectResponse
    {
        $settings = $this->ai->settingsFor($request->user());
        $data = $request->validated();

        if ($data['provider'] === 'claude') {
            if (filled($data['claude_api_key'] ?? null)) {
                $settings->claude_api_key = $data['claude_api_key'];
            }
        }

        $settings->fill([
            'provider' => $data['provider'],
            'ollama_base_url' => $data['ollama_base_url'] ?? $settings->ollama_base_url,
            'ollama_model' => $data['ollama_model'] ?? $settings->ollama_model,
            'claude_model' => $data['claude_model'] ?? $settings->claude_model,
            'document_language' => $data['document_language'],
        ]);
        $settings->save();

        return redirect()
            ->route('settings.ai.edit')
            ->with('status', 'Configuración de IA guardada.');
    }

    public function refreshOllamaModels(Request $request): RedirectResponse
    {
        return redirect()
            ->route('settings.ai.edit')
            ->with('status', 'Modelos de Ollama actualizados.');
    }
}
