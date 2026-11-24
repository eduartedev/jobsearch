<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'provider',
    'ollama_base_url',
    'ollama_model',
    'claude_api_key',
    'claude_model',
    'document_language',
])]
#[Hidden(['claude_api_key'])]
class AiSetting extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'claude_api_key' => 'encrypted',
        ];
    }

    public function hasClaudeApiKey(): bool
    {
        return filled($this->claude_api_key);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'provider' => $this->provider,
            'ollama_base_url' => $this->ollama_base_url,
            'ollama_model' => $this->ollama_model,
            'claude_model' => $this->claude_model,
            'document_language' => $this->document_language,
            'has_claude_api_key' => $this->hasClaudeApiKey(),
        ];
    }
}
