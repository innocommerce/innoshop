<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories;

class ContentAIRepo extends BaseRepo
{
    /**
     * AI Models, such as OpenAI, Claude, Ollama, Llama
     * @return array[]
     */
    public function getModels(): array
    {
        $plugins = app('plugin')->getPlugins()->where('type', 'intelli');

        $models = [];
        foreach ($plugins as $code => $plugin) {
            $models[] = [
                'code' => $code,
                'name' => $plugin->getDirName(),
            ];
        }

        return $models;
    }

    /**
     * @return string[]
     */
    public function getPrompts(): array
    {
        return [
            'ai_prompt_product_summary',
            'ai_prompt_product_selling_point',
            'ai_prompt_product_slug',
            'ai_prompt_product_seo_title',
            'ai_prompt_product_seo_description',
            'ai_prompt_product_seo_keywords',

            'ai_prompt_article_summary',
            'ai_prompt_article_slug',
            'ai_prompt_article_seo_title',
            'ai_prompt_article_seo_description',
            'ai_prompt_article_seo_keywords',
        ];
    }
}
