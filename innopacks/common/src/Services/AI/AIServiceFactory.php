<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

class AIServiceFactory
{
    private static array $services = [];

    private static array $modelMap = [
        'openai'   => OpenAIService::class,
        'deepseek' => DeepSeekService::class,
        'kimi'     => KimiService::class,
        'doubao'   => DoubaoService::class,
        'qianwen'  => QianwenService::class,
        'hunyuan'  => HunyuanService::class,
    ];

    /**
     * Get AI service instance
     *
     * @param  string  $model  The model name
     * @param  array  $config  Configuration array for the service
     * @return AIServiceInterface The AI service instance
     * @throws \InvalidArgumentException When model is not supported or class not found
     */
    public static function make(string $model, array $config = []): AIServiceInterface
    {
        $cacheKey = $model.'_'.md5(serialize($config));

        if (isset(self::$services[$cacheKey])) {
            return self::$services[$cacheKey];
        }

        // Allow extension of model mapping via hooks
        $modelMap = apply_filters('ai.available_models', self::$modelMap);

        if (! isset($modelMap[$model])) {
            throw new \InvalidArgumentException("Unsupported AI model: {$model}");
        }

        $serviceClass = $modelMap[$model];

        if (! class_exists($serviceClass)) {
            throw new \InvalidArgumentException("AI service class not found: {$serviceClass}");
        }

        // Allow modification of configuration via hooks
        $config = apply_filters("ai.model_config.{$model}", $config);

        $service = new $serviceClass($config);

        if (! $service instanceof AIServiceInterface) {
            throw new \InvalidArgumentException('Service class must implement AIServiceInterface');
        }

        self::$services[$cacheKey] = $service;

        return $service;
    }

    /**
     * Get all available models information
     *
     * @return array Array of available models with their information
     */
    public static function getAvailableModels(): array
    {
        $modelMap = apply_filters('ai.available_models', self::$modelMap);
        $models   = [];

        foreach ($modelMap as $key => $class) {
            if (class_exists($class) && method_exists($class, 'getModelInfo')) {
                try {
                    $models[$key] = $class::getModelInfo();
                } catch (\Exception $e) {
                    // Ignore services that cannot provide model info
                }
            }
        }

        return $models;
    }

    /**
     * Validate model configuration
     *
     * @param  string  $model  The model name
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public static function validateConfig(string $model, array $config): bool
    {
        try {
            $service = self::make($model, $config);

            return $service->validateConfig($config);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear cached service instances
     */
    public static function clearCache(): void
    {
        self::$services = [];
    }
}
