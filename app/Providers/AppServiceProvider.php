<?php

namespace App\Providers;

use App\Contracts\AIServiceInterface;
use App\Services\GeminiService;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AIServiceInterface::class, function () {
            return config('ai.provider') === 'openai'
                ? new OpenAIService()
                : new GeminiService();
        });
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
