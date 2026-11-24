<?php

namespace App\Services\Ai;

interface AiProvider
{
    public function name(): string;

    /**
     * @param  array<string, mixed>  $options
     */
    public function chat(string $system, string $prompt, array $options = []): string;
}
