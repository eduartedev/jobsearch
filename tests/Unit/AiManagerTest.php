<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Ai\AiManager;
use App\Services\Ai\ClaudeProvider;
use App\Services\Ai\OllamaProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AiManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_for_defaults_to_ollama(): void
    {
        $user = User::factory()->create();
        $manager = app(AiManager::class);

        $this->assertInstanceOf(OllamaProvider::class, $manager->driverFor($user));
    }

    public function test_driver_for_claude_when_configured(): void
    {
        $user = User::factory()->create();
        $manager = app(AiManager::class);
        $settings = $manager->settingsFor($user);
        $settings->update([
            'provider' => 'claude',
            'claude_api_key' => 'sk-ant-test',
            'claude_model' => 'claude-sonnet-4-5',
        ]);

        $this->assertInstanceOf(ClaudeProvider::class, $manager->driverFor($user->fresh()));
    }

    public function test_assert_ready_blocks_claude_without_key(): void
    {
        $user = User::factory()->create();
        $manager = app(AiManager::class);
        $settings = $manager->settingsFor($user);
        $settings->update(['provider' => 'claude', 'claude_api_key' => null]);

        $this->expectException(RuntimeException::class);
        $manager->assertReady($user->fresh());
    }
}
