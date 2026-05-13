<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

use Illuminate\Support\Facades\Log;
use InnoShop\Common\Services\StorageService;
use Laravel\Ai\Image;

class AIServiceManager
{
    private static ?AIServiceManager $instance = null;

    private function __construct() {}

    /**
     * Get singleton instance of AIServiceManager
     */
    public static function getInstance(): AIServiceManager
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Generate content using AI service
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  string|null  $purpose  Purpose of generation (e.g., tdk, chat, image)
     * @param  array  $options  Additional options for generation
     * @return string The generated content
     *
     * @throws \RuntimeException When generation fails
     */
    public function generate(string $prompt, ?string $purpose = null, array $options = []): string
    {
        try {
            // Rebuild config to ensure plugin providers are included
            $this->reloadConfig();

            Log::info('AIServiceManager generate started');
            Log::info('Prompt: '.substr($prompt, 0, 100));
            Log::info('Purpose: '.($purpose ?? 'null'));
            Log::info('Options: '.json_encode($options));

            $service = new LaravelAIService;

            // Allow modification of request via hooks
            $prompt  = fire_hook_filter('ai.generate_prompt', $prompt);
            $options = fire_hook_filter('ai.generate_options', $options);

            Log::info('After filters - Prompt: '.substr($prompt, 0, 100));
            Log::info('After filters - Options: '.json_encode($options));

            try {
                $result = $service->generate($prompt, $options);
                Log::info('Generation successful: '.substr($result, 0, 100));

                return fire_hook_filter('ai.generate_result', $result);
            } catch (\Exception $e) {
                Log::error('Primary model failed: '.$e->getMessage());

                // Try fallback model if primary model fails
                $fallbackModel = $this->getFallbackModel();
                if ($fallbackModel) {
                    Log::info('Trying fallback model: '.$fallbackModel);
                    $service = new LaravelAIService;

                    return $service->generate($prompt, $options);
                }

                throw new \RuntimeException('AI generation failed: '.$e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('AIServiceManager final error: '.$e->getMessage());
            Log::error('AIServiceManager stack: '.$e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Stream content generation using AI service
     */
    public function stream(string $prompt, ?string $purpose = null, array $options = []): iterable
    {
        $service = new LaravelAIService;

        $prompt  = fire_hook_filter('ai.generate_prompt', $prompt);
        $options = fire_hook_filter('ai.generate_options', $options);

        foreach ($service->stream($prompt, $options) as $chunk) {
            yield fire_hook_filter('ai.generate_result', $chunk);
        }
    }

    /**
     * Create AI service instance
     *
     * @throws \InvalidArgumentException When model is not supported or disabled
     */
    public function make(string $model, array $config = []): AIServiceInterface
    {
        return new LaravelAIService;
    }

    /**
     * Chat with multi-turn messages.
     */
    public function chat(array $messages, array $options = []): string
    {
        $model  = $options['model'] ?? config('ai.default');
        $config = array_merge(config("ai.providers.{$model}", []), $options);

        try {
            return $this->make($model)->chat($messages, $config);
        } catch (\Throwable $e) {
            $fallback = $this->getFallbackModel();
            if ($fallback) {
                try {
                    return $this->make($fallback)->chat($messages, $config);
                } catch (\Throwable $e2) {
                    throw new \RuntimeException('AI chat failed: '.$e2->getMessage());
                }
            }
            throw new \RuntimeException('AI chat failed: '.$e->getMessage());
        }
    }

    /**
     * Get the fallback provider for the current default.
     */
    private function getFallbackModel(): ?string
    {
        $current = config('ai.default');

        $fallbackMap = [
            'openai'    => 'deepseek',
            'deepseek'  => 'kimi',
            'kimi'      => 'openai',
            'doubao'    => 'qianwen',
            'qianwen'   => 'hunyuan',
            'hunyuan'   => 'anthropic',
            'anthropic' => 'glm',
            'glm'       => 'minimax',
            'minimax'   => 'openai',
        ];

        $fallback = $fallbackMap[$current] ?? null;

        if ($fallback && config("ai.providers.{$fallback}")) {
            return $fallback;
        }

        return null;
    }

    /**
     * Get all available models information.
     */
    public function getAvailableModels(): array
    {
        $providers = app(ProviderRegistry::class)->getProviders();
        $result    = [];

        foreach ($providers as $provider) {
            $code = $provider['code'] ?? '';
            if (empty($code)) {
                continue;
            }
            $result[$code] = [
                'name'    => $provider['name'] ?? ucfirst($code),
                'driver'  => $provider['driver'] ?? 'openai',
                'enabled' => ! empty($provider['api_key']),
            ];
        }

        return fire_hook_filter('ai.available_models', $result);
    }

    /**
     * Get formatted AI models for select options.
     */
    public function getModelsForSelect(): array
    {
        return app(ProviderRegistry::class)->getConfiguredProviders();
    }

    /**
     * Validate model configuration
     */
    public function validateModelConfig(string $model, array $config): bool
    {
        return ! empty($config['api_key']);
    }

    /**
     * Get only enabled models
     */
    public function getEnabledModels(): array
    {
        $providers = app(ProviderRegistry::class)->getProviders();
        $enabled   = [];

        foreach ($providers as $provider) {
            if (! empty($provider['api_key'])) {
                $enabled[$provider['code']] = $provider;
            }
        }

        return $enabled;
    }

    /**
     * Check if a model is enabled
     */
    public function isModelEnabled(string $model): bool
    {
        $providerConfig = config("ai.providers.{$model}", []);

        return ! empty($providerConfig['key']);
    }

    /**
     * Generate image using AI service.
     *
     * @param  string  $prompt  The prompt text for image generation
     * @param  array  $options  Additional options (size, quality, attachments, provider, model)
     * @return array Generated image info with url, path, origin_url
     *
     * @throws \RuntimeException When generation fails
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        try {
            // Rebuild config to ensure plugin providers are included
            $this->reloadConfig();

            Log::info('AIServiceManager generateImage started', ['prompt' => substr($prompt, 0, 100)]);

            // Allow plugins to provide custom image generation driver (e.g., async APIs)
            $imageDriver = fire_hook_filter('ai.image_generate_driver', null);
            if ($imageDriver) {
                $result = (new $imageDriver)->generate($prompt, $options);

                return fire_hook_filter('ai.image_result', $result);
            }

            $prompt = fire_hook_filter('ai.image_prompt', $prompt);

            $provider = $options['provider'] ?? config('ai.default_for_images', 'openai');
            $model    = $options['model'] ?? config("ai.providers.{$provider}.models.image.default");
            $size     = $options['size'] ?? null;
            $quality  = $options['quality'] ?? null;

            $pending = Image::of($prompt);

            if ($size) {
                $pending->size($size);
            }
            if ($quality) {
                $pending->quality($quality);
            }
            if (! empty($options['reference_image']) && file_exists($options['reference_image'])) {
                $pending->attachments([
                    \Laravel\Ai\Files\Image::fromPath($options['reference_image']),
                ]);
            }

            $response = $pending->generate($provider, $model);

            $savePath   = $options['save_path'] ?? 'ai-images';
            $filename   = 'ai_'.uniqid().'.png';
            $storedPath = $response->storePubliclyAs($savePath, $filename, 'media');

            if (! $storedPath) {
                throw new \RuntimeException('Failed to store generated image');
            }

            $storageKey = StorageService::storageKey($storedPath);
            $result     = [
                'name'       => $filename,
                'path'       => $storageKey,
                'url'        => storage_url($storageKey),
                'origin_url' => storage_url($storageKey),
            ];

            return fire_hook_filter('ai.image_result', $result);
        } catch (\Exception $e) {
            Log::error('AIServiceManager generateImage failed: '.$e->getMessage());
            throw new \RuntimeException('AI image generation failed: '.$e->getMessage());
        }
    }

    /**
     * 重新加载配置
     */
    public function reloadConfig(): void
    {
        app(ProviderRegistry::class)->buildLaravelAiConfig();
    }
}
