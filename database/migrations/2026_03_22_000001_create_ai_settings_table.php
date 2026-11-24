<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32)->default('ollama');
            $table->string('ollama_base_url')->default('http://127.0.0.1:11434');
            $table->string('ollama_model')->nullable();
            $table->text('claude_api_key')->nullable();
            $table->string('claude_model')->nullable();
            $table->string('document_language', 8)->default('es');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
