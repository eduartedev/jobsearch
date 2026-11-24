<?php

return [

    'defaults' => [
        'provider' => env('AI_PROVIDER', 'ollama'),
        'document_language' => env('AI_DOCUMENT_LANGUAGE', 'es'),
        'ollama_base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'ollama_model' => env('OLLAMA_MODEL', 'llama3.1'),
        'claude_model' => env('CLAUDE_MODEL', 'claude-sonnet-4-5'),
    ],

    'claude_models' => [
        'claude-sonnet-4-5',
        'claude-opus-4-5',
        'claude-haiku-4-5',
        'claude-3-5-sonnet-latest',
        'claude-3-5-haiku-latest',
    ],

];
