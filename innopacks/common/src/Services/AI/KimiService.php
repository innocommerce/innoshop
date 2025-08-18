<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

use OpenAI;

class KimiService implements AIServiceInterface
{
    private $client;

    private array $config;

    /**
     * KimiService constructor
     *
     * @param  array  $config  Configuration array containing API key, base URL, and timeout settings
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = OpenAI::factory()
            ->withApiKey($config['api_key'])
            ->withBaseUri($config['base_url'] ?? 'https://api.moonshot.cn/v1')
            ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
            ->make();
    }

    /**
     * Generate content using Kimi API
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return string The generated content
     */
    public function generate(string $prompt, array $options = []): string
    {
        $response = $this->client->chat()->create([
            'model'    => $options['model'] ?? $this->config['model'] ?? 'moonshot-v1-8k',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 1000,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ]);

        return $response->choices[0]->message->content ?? '';
    }

    /**
     * Stream content generation using Kimi API
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return iterable Iterator yielding generated content chunks
     */
    public function stream(string $prompt, array $options = []): iterable
    {
        $stream = $this->client->chat()->createStreamed([
            'model'    => $options['model'] ?? $this->config['model'] ?? 'moonshot-v1-8k',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 1000,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ]);

        foreach ($stream as $response) {
            $content = $response->choices[0]->delta->content ?? '';
            if ($content !== '') {
                yield $content;
            }
        }
    }

    /**
     * Validate Kimi configuration
     *
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public function validateConfig(array $config): bool
    {
        return ! empty($config['api_key']);
    }

    /**
     * Get Kimi model information
     *
     * @return array Model information including available models and capabilities
     */
    public static function getModelInfo(): array
    {
        return [
            'name'   => 'Kimi',
            'models' => [
                'moonshot-v1-8k',
                'moonshot-v1-32k',
                'moonshot-v1-128k',
            ],
            'supports_streaming' => true,
            'supports_images'    => true,
            'max_tokens'         => 128000,
        ];
    }
}
