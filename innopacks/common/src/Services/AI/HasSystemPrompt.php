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
 * Trait for handling system prompts in AI services
 */
trait HasSystemPrompt
{
    /**
     * Handle prompt with system settings
     *
     * @param  string  $prompt  Original prompt
     * @param  array  $options  Options array containing column and language info
     * @return string Processed prompt
     */
    protected function handlePrompt(string $prompt, array $options = []): string
    {
        if (! isset($options['column'])) {
            Log::info('No column specified, using original prompt');

            return $prompt;
        }

        $processedPrompt = $this->getSystemPrompt($prompt, $options['column'], $options['lang'] ?? '');

        Log::info('Prompt processed with system settings', [
            'original'  => substr($prompt, 0, 100),
            'processed' => substr($processedPrompt, 0, 100),
            'column'    => $options['column'],
            'locale'    => $options['lang'] ?? '',
        ]);

        return $processedPrompt;
    }

    /**
     * Get system prompt for specific column type
     *
     * @param  string  $origin  Original content
     * @param  string  $column  Column type
     * @param  string  $locale  Language locale
     * @return string Combined prompt
     */
    protected function getSystemPrompt(string $origin, string $column, string $locale = ''): string
    {
        $mappings  = $this->getPromptMapping();
        $promptKey = $mappings[$column] ?? '';

        Log::info('Looking for system prompt', [
            'column'     => $column,
            'prompt_key' => $promptKey,
        ]);

        if (empty($promptKey)) {
            Log::info('No prompt mapping found for column: '.$column);

            return $origin;
        }

        $systemPrompt = system_setting($promptKey);
        if (empty($systemPrompt)) {
            Log::info('System prompt not configured for key: '.$promptKey);

            return $origin;
        }

        Log::info('Found system prompt', [
            'prompt_key'    => $promptKey,
            'prompt_length' => strlen($systemPrompt),
        ]);

        $suffix = '';
        if ($locale) {
            $suffix = "并用 $locale 返回";
            Log::info('Added locale suffix', ['locale' => $locale]);
        }

        return $origin."\r\n".$systemPrompt."\r\n".$suffix;
    }

    /**
     * Get prompt mapping for different content types
     *
     * @return array Mapping of column types to system setting keys
     */
    protected function getPromptMapping(): array
    {
        return [
            'product_summary'       => 'ai_prompt_product_summary',
            'product_selling_point' => 'ai_prompt_product_selling_point',
            'product_slug'          => 'ai_prompt_product_slug',
            'product_title'         => 'ai_prompt_product_seo_title',
            'product_description'   => 'ai_prompt_product_seo_description',
            'product_keywords'      => 'ai_prompt_product_seo_keywords',

            'article_summary'     => 'ai_prompt_article_summary',
            'article_slug'        => 'ai_prompt_article_slug',
            'article_title'       => 'ai_prompt_article_seo_title',
            'article_description' => 'ai_prompt_article_seo_description',
            'article_keywords'    => 'ai_prompt_article_seo_keywords',
        ];
    }
}
