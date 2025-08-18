<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

interface AIServiceInterface
{
    /**
     * Generate content using AI service
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return string The generated content
     */
    public function generate(string $prompt, array $options = []): string;

    /**
     * Stream content generation using AI service
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return iterable Iterator yielding generated content chunks
     */
    public function stream(string $prompt, array $options = []): iterable;

    /**
     * Validate configuration for the AI service
     *
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public function validateConfig(array $config): bool;

    /**
     * Get model information
     *
     * @return array Model information including available models and capabilities
     */
    public static function getModelInfo(): array;
}
