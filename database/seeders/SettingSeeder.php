<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use InnoShop\Common\Models\Setting;
use InnoShop\Common\Repositories\SettingRepo;
use Throwable;

class SettingSeeder extends Seeder
{
    /**
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        $items = $this->getSettings();
        if ($items) {
            Setting::query()->truncate();
            foreach ($items as $item) {
                SettingRepo::getInstance()->updateSystemValue($item['name'], $item['value']);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getSettings(): array
    {
        return [
            ['space' => 'system', 'name' => 'front_logo', 'value' => 'images/logo.png'],
            ['space' => 'system', 'name' => 'panel_logo', 'value' => 'images/logo-panel.svg'],
            ['space' => 'system', 'name' => 'placeholder', 'value' => 'images/placeholder.png'],
            ['space' => 'system', 'name' => 'favicon', 'value' => 'images/favicon.png'],
            ['space' => 'system', 'name' => 'country_code', 'value' => 'US'],
            ['space' => 'system', 'name' => 'state_code', 'value' => 'CA'],
            ['space' => 'system', 'name' => 'front_locale', 'value' => 'en'],
            ['space' => 'system', 'name' => 'expand', 'value' => '0'],
            ['space' => 'system', 'name' => 'address', 'value' => 'TF Software Park'],
            ['space' => 'system', 'name' => 'telephone', 'value' => '13688886666'],
            ['space' => 'system', 'name' => 'email', 'value' => 'team@innoshop.com'],
            ['space' => 'system', 'name' => 'currency', 'value' => 'usd'],
            ['space' => 'system', 'name' => 'menu_header_categories', 'value' => ['1', '4', '7', '10', '13']],
            ['space' => 'system', 'name' => 'menu_header_pages', 'value' => ['3']],
            ['space' => 'system', 'name' => 'menu_footer_categories', 'value' => ['1', '4', '7']],
            ['space' => 'system', 'name' => 'menu_footer_catalogs', 'value' => ['1', '2']],
            ['space' => 'system', 'name' => 'menu_footer_pages', 'value' => ['1', '2', '3']],
            [
                'space' => 'system',
                'name'  => 'meta_title',
                'value' => [
                    'zh_cn' => 'InnoShop - 创新开源电商系统 - Laravel 11，多语言和多货币支持，基于Hook的强大插件架构电商系统',
                    'en'    => 'InnoShop - Innovative Open Source E-commerce System - Built on Laravel 11, with multi-language and multi-currency support, a powerful e-commerce system based on a Hook-based plugin architecture.',
                ],
            ],
            [
                'space' => 'system',
                'name'  => 'meta_keywords',
                'value' => [
                    'zh_cn' => 'InnoShop, 创新, 开源, 电子商务, Laravel 11, 多语言, 多货币, Hook, 插件架构, 灵活, 强大',
                    'en'    => 'InnoShop, Innovation, Open Source, E-commerce, Laravel 11, Multi-language, Multi-currency, Hook, Plugin architecture, Flexible, Powerful',
                ],
            ],
            [
                'space' => 'system',
                'name'  => 'meta_description',
                'value' => [
                    'zh_cn' => 'InnoShop 是一款创新的开源电子商务平台，基于 Laravel 11 开发，具有多语言和多货币支持的特性。它采用了基于 Hook 的强大而灵活的插件架构，为用户提供了丰富的定制和扩展功能。欢迎体验 InnoShop, 打造属于您自己的电子商务平台！',
                    'en'    => 'InnoShop is an innovative open-source e-commerce platform developed based on Laravel 11, featuring multi-language and multi-currency support. It adopts a powerful and flexible plugin architecture based on Hooks, providing users with a wealth of customization and extension capabilities. Welcome to experience InnoShop and create your own e-commerce platform!',
                ],
            ],
            [
                'space' => 'system',
                'name'  => 'slideshow',
                'value' => [
                    [
                        'image' => [
                            'en'    => 'images/demo/banner/banner-1-en.jpg',
                            'zh_cn' => 'images/demo/banner/banner-1-cn.jpg',
                        ],
                        'link' => '/en/category-women-clothing',
                    ],
                    [
                        'image' => [
                            'en'    => 'images/demo/banner/banner-2-en.jpg',
                            'zh_cn' => 'images/demo/banner/banner-2-cn.jpg',
                        ],
                        'link' => '/en/category-women-clothing',
                    ],
                ],
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_article_seo_description',
                'value' => '请根据关键字为该文章生成一个优化的文章SEO描述。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_article_seo_keywords',
                'value' => '请根据关键字为该文章生成一个优化的文章SEO关键词。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_article_seo_title',
                'value' => '请根据关键字为该文章生成一个有效的文章SEO标题。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_article_slug',
                'value' => '请根据关键字为该文章生成一个简洁、明确的文章slug。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_article_summary',
                'value' => '请根据关键字为该文章撰写一份简洁而引人注目的产品摘要。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_product_selling_point',
                'value' => '请为该商品生成一份简洁有力的产品卖点描述。请突出产品的核心优势和独特功能，明确其与竞争产品的不同之处。描述应能引发目标用户的兴趣，强调产品如何为他们带来特定好处或解决问题。语言应具有吸引力，并能清晰传达产品的价值和使用场景，请用1.2.3.....段落形式输出方便用快速阅读，并加上适当的表情\'',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_product_seo_description',
                'value' => '请为该商品生成一个优化的产品SEO描述。描述应包含主要关键词，概述产品的核心功能和优势，并吸引潜在客户点击。请确保描述简洁且具吸引力，能够清楚传达产品的价值主张，并符合搜索引擎的最佳实践。字数应控制在150-160个字符之间，以确保在搜索引擎结果页上完整显示。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_product_seo_keywords',
                'value' => '请为该商品生成一个优化的产品SEO关键词。应包含主要关键词，概述产品的核心功能和优势，并吸引潜在客户点击。请确保描述简洁且具吸引力，能够清楚传达产品的价值主张，并符合搜索引擎的最佳实践。字数应控制在150-160个字符之间，以确保在搜索引擎结果页上完整显示。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_product_seo_title',
                'value' => '请为该商品生成一个有效的产品SEO标题。标题应包含主要的关键词，以优化搜索引擎排名，并且吸引潜在客户的注意。请确保标题简洁且描述性强，突出产品的核心优势和独特之处，同时与用户的搜索意图紧密相关。标题字数应控制在60个字符以内，以便在搜索引擎结果页上完整显示。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_product_slug',
                'value' => '请为该商品生成一个简洁、明确的产品slug。slug应简短且易于记忆，包含产品的主要关键词，以便于搜索引擎优化（SEO）和用户识别。请确保slug使用小写字母、连字符连接关键词，并避免使用特殊字符。slug应能够在简洁的同时准确传达产品名称或类型，使用户能够一目了然地理解产品的核心特征。',
            ],
            [
                'space' => 'system',
                'name'  => 'ai_prompt_product_summary',
                'value' => '请为该商品撰写一份简洁而引人注目的产品摘要。摘要应突出产品的核心功能、主要特点和独特卖点，根据目标用户，并且能够激发他们的购买欲望。请确保摘要语言简洁明了，同时能清楚地传达产品的价值和优势。输出为1-2句吸引用户的精炼内容。',
            ],
        ];
    }
}
