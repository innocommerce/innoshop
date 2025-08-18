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

class QianwenService implements AIServiceInterface
{
    private $client;

    private array $config;

    /**
     * QianwenService constructor
     *
     * @param  array  $config  Configuration array containing API key, base URL, and timeout settings
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = OpenAI::factory()
            ->withApiKey($config['api_key'])
            ->withBaseUri($config['base_url'] ?? 'https://dashscope.aliyuncs.com/compatible-mode/v1')
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
            'model'       => $options['model'] ?? $this->config['model'] ?? 'qwen-max',
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
            'model'       => $options['model'] ?? $this->config['model'] ?? 'qwen-max',
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
            'name'        => '通义千问',
            'provider'    => 'Alibaba',
            'description' => '阿里云通义千问大模型',
            'models'      => [
                'qwen-max',
                'qwen-plus',
                'qwen-turbo',
                'qwen-long',
            ],
            'max_tokens'             => 1000000,
            'supports_streaming'     => true,
            'supports_function_call' => true,
            'base_url'               => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
        ];
    }
}
