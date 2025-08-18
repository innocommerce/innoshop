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

class DoubaoService implements AIServiceInterface
{
    private $client;

    private array $config;

    /**
     * DoubaoService constructor
     *
     * @param  array  $config  Configuration array containing API key, base URL, and timeout settings
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = OpenAI::factory()
            ->withApiKey($config['api_key'])
            ->withBaseUri($config['base_url'] ?? 'https://ark.cn-beijing.volces.com/api/v3')
            ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
            ->make();
    }

    /**
     * Generate content using AI service
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return string The generated content
     */
    public function generate(string $prompt, array $options = []): string
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->client->chat()->create([
            'model'       => $options['model'] ?? $this->config['model'] ?? 'doubao-pro-128k',
            'messages'    => $messages,
            'max_tokens'  => $options['max_tokens'] ?? 2048,
            'temperature' => $options['temperature'] ?? 0.7,
        ]);

        return $response->choices[0]->message->content;
    }

    /**
     * Stream content generation using AI service
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return iterable Iterator yielding generated content chunks
     */
    public function stream(string $prompt, array $options = []): iterable
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        $stream = $this->client->chat()->createStreamed([
            'model'       => $options['model'] ?? $this->config['model'] ?? 'doubao-pro-128k',
            'messages'    => $messages,
            'max_tokens'  => $options['max_tokens'] ?? 2048,
            'temperature' => $options['temperature'] ?? 0.7,
        ]);

        foreach ($stream as $response) {
            $delta = $response->choices[0]->delta;
            if (isset($delta->content)) {
                yield $delta->content;
            }
        }
    }

    /**
     * Validate configuration for the AI service
     *
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public function validateConfig(array $config): bool
    {
        return ! empty($config['api_key']);
    }

    /**
     * Get model information
     *
     * @return array Model information including capabilities and limits
     */
    public static function getModelInfo(): array
    {
        return [
            'name'        => 'Doubao',
            'provider'    => 'ByteDance',
            'description' => '豆包大模型，字节跳动出品',
            'models'      => [
                'doubao-pro-128k',
                'doubao-lite-128k',
                'doubao-pro-4k',
                'doubao-lite-4k',
            ],
            'max_tokens'             => 128000,
            'supports_streaming'     => true,
            'supports_function_call' => true,
            'base_url'               => 'https://ark.cn-beijing.volces.com/api/v3',
        ];
    }
}
