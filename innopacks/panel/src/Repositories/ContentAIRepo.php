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
     * @return array[]
     */
    public function getModels(): array
    {
        return [
            ['code' => 'open_ai', 'name' => 'OpenAI'],
            ['code' => 'claude', 'name' => 'Claude'],
            ['code' => 'ollama', 'name' => 'Ollama'],
            ['code' => 'llama', 'name' => 'Llama'],
        ];
    }

    /**
     * @return string[]
     */
    public function getPrompts(): array
    {
        return [
            'ai_prompt_product_summary',
            'ai_prompt_product_selling_point',
            'ai_prompt_product_seo_slug',
            'ai_prompt_product_seo_tag',
            'ai_prompt_product_seo_description',
            'ai_prompt_product_seo_keyword',
            'ai_prompt_article_summary',
            'ai_prompt_article_seo_slug',
            'ai_prompt_article_seo_tag',
            'ai_prompt_article_seo_description',
            'ai_prompt_article_seo_keyword',
            'ai_prompt_article_tags',
        ];
    }
}
