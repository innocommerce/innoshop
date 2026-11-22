<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        if (env('APP_FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Register OpenAI-compatible providers that use the DeepSeek
        // chat/completions gateway instead of OpenAiGateway (/responses)
        if (class_exists(\Laravel\Ai\AiManager::class)) {
            \Laravel\Ai\Ai::extend('glm', function ($app, array $config) {
                $config['driver'] = 'deepseek'; // Uses /chat/completions
                return new \Laravel\Ai\Providers\DeepSeekProvider(
                    $config,
                    $app->make(\Illuminate\Contracts\Events\Dispatcher::class)
                );
            });

            \Laravel\Ai\Ai::extend('minimax', function ($app, array $config) {
                $config['driver'] = 'deepseek'; // Uses /chat/completions
                return new \Laravel\Ai\Providers\DeepSeekProvider(
                    $config,
                    $app->make(\Illuminate\Contracts\Events\Dispatcher::class)
                );
            });
        }
    }
}
