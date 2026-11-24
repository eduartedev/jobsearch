<?php

use App\Http\Controllers\AiSettingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings/ai', [AiSettingsController::class, 'edit'])->name('settings.ai.edit');
    Route::put('/settings/ai', [AiSettingsController::class, 'update'])->name('settings.ai.update');
    Route::post('/settings/ai/ollama/refresh', [AiSettingsController::class, 'refreshOllamaModels'])
        ->name('settings.ai.ollama.refresh');
});

require __DIR__.'/auth.php';
