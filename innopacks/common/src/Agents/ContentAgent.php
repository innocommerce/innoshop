<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class ContentAgent implements Agent
{
    use Promptable;

    public function __construct(
        private readonly string $column = '',
        private readonly string $locale = '',
    ) {}

    public function instructions(): \Stringable|string
    {
        $basePrompt = 'You are an expert e-commerce content writer. Generate high-quality, SEO-optimized content.';

        if ($this->column) {
            $mappings = [
                'product_summary'       => 'ai_prompt_product_summary',
                'product_selling_point' => 'ai_prompt_product_selling_point',
                'product_slug'          => 'ai_prompt_product_slug',
                'product_title'         => 'ai_prompt_product_seo_title',
                'product_description'   => 'ai_prompt_product_seo_description',
                'product_keywords'      => 'ai_prompt_product_seo_keywords',
                'article_summary'       => 'ai_prompt_article_summary',
                'article_slug'          => 'ai_prompt_article_slug',
                'article_title'         => 'ai_prompt_article_seo_title',
                'article_description'   => 'ai_prompt_article_seo_description',
                'article_keywords'      => 'ai_prompt_article_seo_keywords',
            ];

            $systemPrompt = system_setting($mappings[$this->column] ?? '') ?: '';
            if ($systemPrompt) {
                $basePrompt .= "\n\n".$systemPrompt;
            }
        }

        if ($this->locale) {
            $basePrompt .= "\n\nRespond in {$this->locale}.";
        }

        return $basePrompt;
    }
}
