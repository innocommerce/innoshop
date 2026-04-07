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

/**
 * Adapter bridging AIServiceInterface to Laravel AI SDK Agents.
 */
class LaravelAIService implements AIServiceInterface
{
    use HasSystemPrompt;

    /**
     * Generate text from a single prompt (backward compatible).
     */
    public function generate(string $prompt, array $options = []): string
    {
        try {
            $systemPrompt = $this->handlePrompt($prompt, $options);
            $agent        = new SimpleAgent($systemPrompt);

            $response = $agent->prompt(
                $prompt,
                provider: $options['provider'] ?? null,
                model: $options['model'] ?? null,
            );

            return $response->text;
        } catch (\Throwable $e) {
            Log::error('LaravelAIService generate failed: '.$e->getMessage());

            return '';
        }
    }

    /**
     * Stream text generation.
     */
    public function stream(string $prompt, array $options = []): iterable
    {
        try {
            $systemPrompt = $this->handlePrompt($prompt, $options);
            $agent        = new SimpleAgent($systemPrompt);

            $stream = $agent->stream(
                $prompt,
                provider: $options['provider'] ?? null,
                model: $options['model'] ?? null,
            );

            foreach ($stream as $chunk) {
                yield $chunk;
            }
        } catch (\Throwable $e) {
            Log::error('LaravelAIService stream failed: '.$e->getMessage());
        }
    }

    /**
     * Validate provider config.
     */
    public function validateConfig(array $config): bool
    {
        return ! empty($config['key']);
    }

    /**
     * Get model info.
     */
    public static function getModelInfo(): array
    {
        return [
            'name'    => 'Laravel AI SDK',
            'drivers' => ['openai', 'anthropic', 'deepseek', 'openai-compatible'],
        ];
    }

    /**
     * Chat with multi-turn messages.
     */
    public function chat(array $messages, array $options = []): string
    {
        try {
            $systemPrompt = $options['system_prompt'] ?? 'You are a helpful assistant.';
            $agent        = new ChatAgent($messages, $systemPrompt);
            $lastMessage  = end($messages) ?: [];

            $response = $agent->prompt(
                $lastMessage['content'] ?? '',
                provider: $options['provider'] ?? null,
                model: $options['model'] ?? null,
            );

            return $response->text;
        } catch (\Throwable $e) {
            Log::error('LaravelAIService chat failed: '.$e->getMessage());

            return '';
        }
    }

    /**
     * Chat with streaming.
     */
    public function chatStream(array $messages, array $options = []): iterable
    {
        try {
            $systemPrompt = $options['system_prompt'] ?? 'You are a helpful assistant.';
            $agent        = new ChatAgent($messages, $systemPrompt);
            $lastMessage  = end($messages) ?: [];

            $stream = $agent->stream(
                $lastMessage['content'] ?? '',
                provider: $options['provider'] ?? null,
                model: $options['model'] ?? null,
            );

            foreach ($stream as $chunk) {
                yield $chunk;
            }
        } catch (\Throwable $e) {
            Log::error('LaravelAIService chatStream failed: '.$e->getMessage());
        }
    }

    /**
     * Structured output (TODO).
     */
    public function structured(string $prompt, string $schemaClass, array $options = []): mixed
    {
        return null;
    }
}
