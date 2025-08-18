<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;

class OpenAIService implements AIServiceInterface
{
    private Client $client;

    private array $config;

    /**
     * OpenAIService constructor
     *
     * @param  array  $config  Configuration array containing API key, base URL, and timeout settings
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = OpenAI::client($config['api_key']);
    }

    /**
     * Generate content using OpenAI API
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return string The generated content
     */
    public function generate(string $prompt, array $options = []): string
    {
        try {
            Log::info('OpenAIService generate called with prompt: '.substr($prompt, 0, 100));

            $requestData = [
                'model'       => $options['model'] ?? $this->config['model'] ?? 'gpt-3.5-turbo',
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
            ];

            Log::info('OpenAIService request data: '.json_encode($requestData));

            $response = $this->client->chat()->create($requestData);

            $content = $response->choices[0]->message->content ?? '';
            Log::info('OpenAIService response: '.substr($content, 0, 100));

            return $content;
        } catch (Exception $e) {
            Log::error('OpenAIService error: '.$e->getMessage());
            Log::error('OpenAIService stack: '.$e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Stream content generation using OpenAI API
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return iterable Iterator yielding generated content chunks
     */
    public function stream(string $prompt, array $options = []): iterable
    {
        $stream = $this->client->chat()->createStreamed([
            'model'       => $options['model'] ?? $this->config['model'] ?? 'gpt-3.5-turbo',
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 1000,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ]);

        foreach ($stream as $response) {
            $delta = $response->choices[0]->delta;
            if (isset($delta->content)) {
                yield $delta->content;
            }
        }
    }

    /**
     * Validate OpenAI configuration
     *
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public function validateConfig(array $config): bool
    {
        return ! empty($config['api_key']);
    }

    /**
     * Get OpenAI model information
     *
     * @return array Model information including available models and capabilities
     */
    public static function getModelInfo(): array
    {
        return [
            'name'   => 'OpenAI',
            'models' => [
                'gpt-3.5-turbo',
                'gpt-4',
                'gpt-4-turbo',
                'gpt-4o',
                'gpt-4o-mini',
            ],
            'supports_streaming' => true,
            'supports_images'    => true,
            'max_tokens'         => 128000,
        ];
    }
}
