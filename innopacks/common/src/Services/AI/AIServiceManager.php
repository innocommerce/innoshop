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

class AIServiceManager
{
    private static ?AIServiceManager $instance = null;

    private array $config;

    private function __construct()
    {
        $this->config = $this->loadConfig();
    }

    /**
     * Get singleton instance of AIServiceManager
     *
     * @return AIServiceManager The singleton instance
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
     * @throws \RuntimeException When generation fails
     */
    public function generate(string $prompt, ?string $purpose = null, array $options = []): string
    {
        try {
            Log::info('AIServiceManager generate started');
            Log::info('Prompt: '.substr($prompt, 0, 100));
            Log::info('Purpose: '.($purpose ?? 'null'));
            Log::info('Options: '.json_encode($options));

            $model  = $this->getModelForPurpose($purpose);
            $config = $this->getModelConfig($model);

            Log::info('Selected model: '.$model);
            Log::info('Config: '.json_encode($config));

            $service = AIServiceFactory::make($model, $config);
            Log::info('Service created successfully');

            // Allow modification of request via hooks
            $prompt  = apply_filters('ai.generate_prompt', $prompt, $purpose, $model);
            $options = apply_filters('ai.generate_options', $options, $purpose, $model);

            Log::info('After filters - Prompt: '.substr($prompt, 0, 100));
            Log::info('After filters - Options: '.json_encode($options));

            try {
                $result = $service->generate($prompt, $options);
                Log::info('Generation successful: '.substr($result, 0, 100));

                return apply_filters('ai.generate_result', $result, $prompt, $purpose, $model);
            } catch (\Exception $e) {
                Log::error('Primary model failed: '.$e->getMessage());

                // Try fallback model if primary model fails
                $fallbackModel = $this->getFallbackModel($model);
                if ($fallbackModel && $fallbackModel !== $model) {
                    Log::info('Trying fallback model: '.$fallbackModel);
                    $config  = $this->getModelConfig($fallbackModel);
                    $service = AIServiceFactory::make($fallbackModel, $config);

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
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  string|null  $purpose  Purpose of generation
     * @param  array  $options  Additional options for generation
     * @return iterable Iterator yielding generated content chunks
     */
    public function stream(string $prompt, ?string $purpose = null, array $options = []): iterable
    {
        $model  = $this->getModelForPurpose($purpose);
        $config = $this->getModelConfig($model);

        $service = AIServiceFactory::make($model, $config);

        $prompt  = apply_filters('ai.generate_prompt', $prompt, $purpose, $model);
        $options = apply_filters('ai.generate_options', $options, $purpose, $model);

        foreach ($service->stream($prompt, $options) as $chunk) {
            yield apply_filters('ai.generate_result', $chunk, $prompt, $purpose, $model);
        }
    }

    /**
     * Create AI service instance
     *
     * @param  string  $model  The model name
     * @param  array  $config  Optional configuration override
     * @return AIServiceInterface The AI service instance
     * @throws \InvalidArgumentException When model is not supported or disabled
     */
    public function make(string $model, array $config = []): AIServiceInterface
    {
        if (! isset($this->config['models'][$model])) {
            throw new \InvalidArgumentException("Unsupported AI model: {$model}");
        }

        // Check if model is enabled
        if (! $this->isModelEnabled($model)) {
            throw new \InvalidArgumentException("AI model is disabled: {$model}");
        }

        $modelConfig = array_merge($this->config['models'][$model], $config);

        return AIServiceFactory::make($model, $modelConfig);
    }

    /**
     * Get model for specified purpose
     *
     * @param  string|null  $purpose  The purpose for which to get the model
     * @return string The model name
     */
    public function getModelForPurpose(?string $purpose): string
    {
        // 优先使用后台设置的默认模型
        $userDefaultModel = system_setting('ai_model');
        if ($userDefaultModel && isset($this->config['models'][$userDefaultModel])) {
            return $userDefaultModel;
        }

        // 如果用户没有设置，使用配置中的默认模型
        return $this->config['default_model'] ?? 'openai';
    }

    /**
     * Get model configuration
     *
     * @param  string  $model  The model name
     * @return array The model configuration
     */
    private function getModelConfig(string $model): array
    {
        return $this->config['models'][$model] ?? [];
    }

    /**
     * Get fallback model for specified model
     *
     * @param  string  $model  The current model name
     * @return string|null The fallback model name, or null if no fallback
     */
    private function getFallbackModel(string $model): ?string
    {
        return $this->config['fallback_models'][$model] ?? null;
    }

    /**
     * Load AI configuration from system settings
     *
     * @return array The loaded configuration
     */
    private function loadConfig(): array
    {
        // Load AI configuration from system settings
        $config = system_setting('ai_config', []);

        // Default configuration
        $defaultConfig = [
            'default_model' => 'openai',
            'models'        => [
                'openai' => [
                    'enabled'     => system_setting('openai_enabled', false),
                    'api_key'     => system_setting('openai_api_key', ''),
                    'base_url'    => 'https://api.openai.com',
                    'model'       => 'gpt-3.5-turbo',
                    'max_tokens'  => 1000,
                    'temperature' => 0.7,
                ],
                'deepseek' => [
                    'enabled'     => system_setting('deepseek_enabled', false),
                    'api_key'     => system_setting('deepseek_api_key', ''),
                    'base_url'    => 'https://api.deepseek.com/v1',
                    'model'       => 'deepseek-chat',
                    'max_tokens'  => 1000,
                    'temperature' => 0.7,
                ],
                'kimi' => [
                    'enabled'     => system_setting('kimi_enabled', false),
                    'api_key'     => system_setting('kimi_api_key', ''),
                    'base_url'    => 'https://api.moonshot.cn',
                    'model'       => 'moonshot-v1-8k',
                    'max_tokens'  => 1000,
                    'temperature' => 0.7,
                ],
                'doubao' => [
                    'enabled'     => system_setting('doubao_enabled', false),
                    'api_key'     => system_setting('doubao_api_key', ''),
                    'base_url'    => 'https://ark.cn-beijing.volces.com/api/v3',
                    'model'       => 'doubao-lite-4k',
                    'max_tokens'  => 1000,
                    'temperature' => 0.7,
                ],
                'qianwen' => [
                    'enabled'     => system_setting('qianwen_enabled', false),
                    'api_key'     => system_setting('qianwen_api_key', ''),
                    'base_url'    => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
                    'model'       => 'qwen-turbo',
                    'max_tokens'  => 1000,
                    'temperature' => 0.7,
                ],
                'hunyuan' => [
                    'enabled'     => system_setting('hunyuan_enabled', false),
                    'api_key'     => system_setting('hunyuan_api_key', ''),
                    'base_url'    => 'https://api.hunyuan.cloud.tencent.com/v1',
                    'model'       => 'hunyuan-standard',
                    'max_tokens'  => 1000,
                    'temperature' => 0.7,
                ],
                'anthropic' => [
                    'enabled'     => system_setting('anthropic_enabled', false),
                    'api_key'     => system_setting('anthropic_api_key', ''),
                    'base_url'    => 'https://api.anthropic.com',
                    'model'       => 'claude-3-sonnet-20240229',
                    'max_tokens'  => 1000,
                    'temperature' => 0.7,
                ],
            ],
            'purpose_mapping' => [
                'tdk'   => 'openai',
                'chat'  => 'openai',
                'image' => 'openai',
            ],
            'fallback_models' => [
                'openai'    => 'deepseek',
                'deepseek'  => 'kimi',
                'kimi'      => 'openai',
                'doubao'    => 'qianwen',
                'qianwen'   => 'hunyuan',
                'hunyuan'   => 'anthropic',
                'anthropic' => 'openai',
            ],
        ];

        return array_merge($defaultConfig, $config);
    }

    /**
     * Get all available models information
     *
     * @return array Available models information
     */
    public function getAvailableModels(): array
    {
        return AIServiceFactory::getAvailableModels();
    }

    /**
     * Get formatted AI models for select options
     *
     * @return array Formatted models for frontend select
     */
    public function getModelsForSelect(): array
    {
        $models    = $this->getAvailableModels();
        $formatted = [];

        foreach ($models as $key => $info) {
            // Only include enabled models
            $modelConfig = $this->config['models'][$key] ?? [];
            if (isset($modelConfig['enabled']) && $modelConfig['enabled']) {
                $formatted[] = [
                    'code' => $key,
                    'name' => $info['name'] ?? $key,
                ];
            }
        }

        return $formatted;
    }

    /**
     * Validate model configuration
     *
     * @param  string  $model  The model name
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public function validateModelConfig(string $model, array $config): bool
    {
        return AIServiceFactory::validateConfig($model, $config);
    }

    /**
     * Get only enabled models
     *
     * @return array Enabled models configuration
     */
    public function getEnabledModels(): array
    {
        $enabledModels = [];

        foreach ($this->config['models'] as $key => $config) {
            if (isset($config['enabled']) && $config['enabled']) {
                $enabledModels[$key] = $config;
            }
        }

        return $enabledModels;
    }

    /**
     * Check if a model is enabled
     *
     * @param  string  $model  The model name
     * @return bool Whether the model is enabled
     */
    public function isModelEnabled(string $model): bool
    {
        $modelConfig = $this->config['models'][$model] ?? [];

        return isset($modelConfig['enabled']) && $modelConfig['enabled'];
    }

    /**
     * 重新加载配置
     */
    public function reloadConfig(): void
    {
        $this->config = $this->loadConfig();
        AIServiceFactory::clearCache();
    }
}
