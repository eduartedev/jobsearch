<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $provider = $this->input('provider');

        return [
            'provider' => ['required', Rule::in(['ollama', 'claude'])],
            'ollama_base_url' => [
                Rule::requiredIf($provider === 'ollama'),
                'nullable',
                'url',
                'max:255',
            ],
            'ollama_model' => [
                Rule::requiredIf($provider === 'ollama'),
                'nullable',
                'string',
                'max:120',
            ],
            'claude_api_key' => [
                'nullable',
                'string',
                'max:500',
            ],
            'claude_model' => [
                Rule::requiredIf($provider === 'claude'),
                'nullable',
                'string',
                'max:120',
            ],
            'document_language' => ['required', Rule::in(['es', 'en'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('provider') !== 'claude') {
                return;
            }

            $user = $this->user();
            $existing = $user?->aiSetting;
            $incomingKey = $this->input('claude_api_key');

            if (blank($incomingKey) && (! $existing || ! $existing->hasClaudeApiKey())) {
                $validator->errors()->add(
                    'claude_api_key',
                    'La API key de Claude es obligatoria la primera vez que eliges ese proveedor.',
                );
            }
        });
    }
}
