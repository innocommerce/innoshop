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

class SettingSeeder extends Seeder
{
    /**
     * @return void
     * @throws \Throwable
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
        ];
    }
}
