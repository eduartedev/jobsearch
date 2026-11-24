<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_ai_settings(): void
    {
        $this->get(route('settings.ai.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_ai_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.ai.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Ai')
                ->has('settings')
                ->where('settings.provider', 'ollama'));
    }

    public function test_user_can_save_ollama_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.ai.update'), [
                'provider' => 'ollama',
                'ollama_base_url' => 'http://127.0.0.1:11434',
                'ollama_model' => 'qwen2.5',
                'claude_api_key' => null,
                'claude_model' => null,
                'document_language' => 'es',
            ])
            ->assertRedirect(route('settings.ai.edit'));

        $this->assertDatabaseHas('ai_settings', [
            'user_id' => $user->id,
            'provider' => 'ollama',
            'ollama_model' => 'qwen2.5',
            'document_language' => 'es',
        ]);
    }

    public function test_claude_requires_api_key_on_first_save(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('settings.ai.edit'))
            ->put(route('settings.ai.update'), [
                'provider' => 'claude',
                'ollama_base_url' => 'http://127.0.0.1:11434',
                'ollama_model' => 'llama3.1',
                'claude_api_key' => '',
                'claude_model' => 'claude-sonnet-4-5',
                'document_language' => 'es',
            ])
            ->assertRedirect(route('settings.ai.edit'))
            ->assertSessionHasErrors('claude_api_key');
    }

    public function test_user_can_save_claude_settings_with_api_key(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.ai.update'), [
                'provider' => 'claude',
                'ollama_base_url' => 'http://127.0.0.1:11434',
                'ollama_model' => 'llama3.1',
                'claude_api_key' => 'sk-ant-test-key',
                'claude_model' => 'claude-sonnet-4-5',
                'document_language' => 'en',
            ])
            ->assertRedirect(route('settings.ai.edit'));

        $settings = $user->fresh()->aiSetting;

        $this->assertNotNull($settings);
        $this->assertSame('claude', $settings->provider);
        $this->assertSame('sk-ant-test-key', $settings->claude_api_key);
        $this->assertTrue($settings->hasClaudeApiKey());
    }
}
