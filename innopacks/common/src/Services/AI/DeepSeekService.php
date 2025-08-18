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

class DeepSeekService implements AIServiceInterface
{
    use HasSystemPrompt;

    private $client;

    private array $config;

    /**
     * DeepSeekService constructor
     *
     * @param  array  $config  Configuration array containing API key, base URL, and timeout settings
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        Log::info('DeepSeekService constructor called', [
            'config_keys'    => array_keys($config),
            'api_key_set'    => ! empty($config['api_key']),
            'api_key_length' => isset($config['api_key']) ? strlen($config['api_key']) : 0,
            'base_url'       => $config['base_url'] ?? 'https://api.deepseek.com/v1',
            'model'          => $config['model'] ?? 'deepseek-chat',
        ]);

        if (empty($config['api_key'])) {
            Log::error('DeepSeekService: API key is empty or not provided');
            throw new \InvalidArgumentException('DeepSeek API key is required');
        }

        $this->client = OpenAI::factory()
            ->withApiKey($config['api_key'])
            ->withBaseUri($config['base_url'] ?? 'https://api.deepseek.com/v1')
            ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
            ->make();

        Log::info('DeepSeekService client created successfully');
    }

    /**
     * Generate content using DeepSeek API
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return string The generated content
     */
    public function generate(string $prompt, array $options = []): string
    {
        try {
            Log::info('DeepSeekService generate called', [
                'original_prompt' => substr($prompt, 0, 100),
                'options'         => array_keys($options),
            ]);

            $prompt = $this->handlePrompt($prompt, $options);
            Log::info('DeepSeekService prompt after processing', [
                'final_prompt' => substr($prompt, 0, 100),
            ]);

            // 映射前端模型名称到实际API模型名称
            $modelName   = $options['model'] ?? $this->config['model'] ?? 'deepseek-chat';
            $actualModel = $this->mapModelName($modelName);

            $requestData = [
                'model'    => $actualModel,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
            ];

            Log::info('DeepSeekService generate model mapping', [
                'original_model' => $modelName,
                'mapped_model'   => $actualModel,
            ]);

            Log::info('DeepSeekService request data', [
                'model'       => $requestData['model'],
                'max_tokens'  => $requestData['max_tokens'],
                'temperature' => $requestData['temperature'],
            ]);

            $response = $this->client->chat()->create($requestData);

            $content = $response->choices[0]->message->content ?? '';
            Log::info('DeepSeekService response received', [
                'content_length' => strlen($content),
            ]);

            return $content;
        } catch (Exception $e) {
            Log::error('DeepSeekService error', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Stream content generation using DeepSeek API
     *
     * @param  string  $prompt  The prompt text to generate content from
     * @param  array  $options  Additional configuration options
     * @return iterable Iterator yielding generated content chunks
     */
    public function stream(string $prompt, array $options = []): iterable
    {
        Log::info('DeepSeekService stream called', [
            'original_prompt' => substr($prompt, 0, 100),
            'options'         => array_keys($options),
        ]);

        $prompt = $this->handlePrompt($prompt, $options);
        Log::info('DeepSeekService stream prompt after processing', [
            'final_prompt' => substr($prompt, 0, 100),
        ]);

        // 映射前端模型名称到实际API模型名称
        $modelName   = $options['model'] ?? $this->config['model'] ?? 'deepseek-chat';
        $actualModel = $this->mapModelName($modelName);

        $requestData = [
            'model'    => $actualModel,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 1000,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ];

        Log::info('DeepSeekService model mapping', [
            'original_model' => $modelName,
            'mapped_model'   => $actualModel,
        ]);

        Log::info('DeepSeekService stream request data', [
            'model'          => $requestData['model'],
            'max_tokens'     => $requestData['max_tokens'],
            'temperature'    => $requestData['temperature'],
            'message_length' => strlen($requestData['messages'][0]['content']),
        ]);

        try {
            $stream = $this->client->chat()->createStreamed($requestData);
        } catch (\Exception $e) {
            Log::error('DeepSeekService stream creation failed', [
                'error'          => $e->getMessage(),
                'model'          => $requestData['model'],
                'api_key_length' => strlen($this->config['api_key'] ?? ''),
                'base_url'       => $this->config['base_url'] ?? 'https://api.deepseek.com/v1',
            ]);
            throw $e;
        }

        Log::info('DeepSeekService stream started');

        foreach ($stream as $response) {
            $content = $response->choices[0]->delta->content ?? '';
            if ($content !== '') {
                Log::debug('DeepSeekService stream chunk', ['chunk_length' => strlen($content)]);
                yield $content;
            }
        }

        Log::info('DeepSeekService stream completed');
    }

    /**
     * Validate DeepSeek configuration
     *
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public function validateConfig(array $config): bool
    {
        return ! empty($config['api_key']);
    }

    /**
     * Map frontend model names to actual API model names
     *
     * @param  string  $modelName  The frontend model name
     * @return string The actual API model name
     */
    private function mapModelName(string $modelName): string
    {
        $modelMap = [
            'DeepSeek'       => 'deepseek-chat',
            'deepseek'       => 'deepseek-chat',
            'DeepSeek-Chat'  => 'deepseek-chat',
            'DeepSeek-Coder' => 'deepseek-coder',
            'deepseek-coder' => 'deepseek-coder',
        ];

        return $modelMap[$modelName] ?? $modelName;
    }

    /**
     * Get DeepSeek model information
     *
     * @return array Model information including available models and capabilities
     */
    public static function getModelInfo(): array
    {
        return [
            'name'   => 'DeepSeek',
            'models' => [
                'deepseek-chat',
                'deepseek-coder',
            ],
            'supports_streaming' => true,
            'supports_images'    => false,
            'max_tokens'         => 128000,
        ];
    }
}
