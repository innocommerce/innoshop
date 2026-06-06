<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Repositories;

class DemoRepo
{
    /**
     * Get home page demo data
     */
    public static function getHomeDemoData(): array
    {
        return [
            [
                'title'     => trans('PageBuilder::modules.slideshow'),
                'code'      => 'slideshow',
                'module_id' => 'module-slideshow-demo-001',
                'icon'      => '<i class="bi bi-images"></i>',
                'content'   => [
                    'images' => [
                        [
                            'image'       => asset('images/demo/banner/hero-1.webp'),
                            'show'        => false,
                            'title'       => ['zh-cn' => '新品首发 限时特惠', 'en' => 'New Arrivals Limited Offer'],
                            'subtitle'    => ['zh-cn' => '精选好物低至5折，错过再等一年', 'en' => 'Up to 50% off selected items'],
                            'button_text' => ['zh-cn' => '立即抢购', 'en' => 'Shop Now'],
                            'title_color'       => '#ffffff',
                            'subtitle_color'    => '#ffffff',
                            'button_color'      => '#C9A87C',
                            'button_text_color' => '#ffffff',
                            'title_size'        => 36,
                            'subtitle_size'     => 20,
                            'title_align'       => 'left',
                            'link'  => [
                                'type'  => 'product',
                                'value' => 1,
                                'link'  => '',
                            ],
                        ],
                        [
                            'image'       => asset('images/demo/banner/hero-2.webp'),
                            'show'        => true,
                            'title'       => ['zh-cn' => '夏日焕新 潮流来袭', 'en' => 'Summer Collection'],
                            'subtitle'    => ['zh-cn' => '探索本季最热门的单品', 'en' => 'Discover the hottest items this season'],
                            'button_text' => ['zh-cn' => '查看更多', 'en' => 'Explore'],
                            'title_color'       => '#ffffff',
                            'subtitle_color'    => '#ffffff',
                            'button_color'      => '#C9A87C',
                            'button_text_color' => '#ffffff',
                            'title_size'        => 36,
                            'subtitle_size'     => 20,
                            'title_align'       => 'center',
                            'link'  => [
                                'type'  => 'category',
                                'value' => 1,
                                'link'  => '',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'     => trans('PageBuilder::modules.single_image'),
                'code'      => 'single-image',
                'module_id' => 'module-single-image-demo-002',
                'icon'      => '<i class="bi bi-image"></i>',
                'content'   => [
                    'style' => [
                        'background_color' => '',
                    ],
                    'images' => [
                        [
                            'image' => asset('images/demo/banner/hero-2.webp'),
                            'show'  => true,
                            'link'  => [
                                'type'  => 'category',
                                'value' => 5,
                                'link'  => '',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'     => trans('PageBuilder::modules.custom_products'),
                'code'      => 'custom-products',
                'module_id' => 'module-custom-products-demo-003',
                'icon'      => '<i class="bi bi-box"></i>',
                'content'   => [
                    'style' => [
                        'background_color' => '',
                    ],
                    'floor' => [
                        'zh-cn' => '',
                        'en'    => '',
                    ],
                    'products' => [1, 2, 3, 4],
                    'title'    => [
                        'zh-cn' => '推荐商品',
                        'en'    => 'Hot Items',
                    ],
                ],
            ],
            [
                'title'     => trans('PageBuilder::modules.category_products'),
                'code'      => 'category-products',
                'module_id' => 'module-category-products-demo-004',
                'icon'      => '<i class="bi bi-collection"></i>',
                'content'   => [
                    'style' => [
                        'background_color' => '',
                    ],
                    'limit'         => '4',
                    'order'         => 'asc',
                    'category_id'   => 1,
                    'category_name' => '时尚潮流',
                    'sort'          => 'sales',
                    'floor'         => [
                        'zh-cn' => '',
                        'en'    => '',
                    ],
                    'products' => [
                        [
                            'id'           => 1,
                            'name'         => '摩登复风高腰牛仔裤经典再现',
                            'image_big'    => asset('images/demo/product/5.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                        [
                            'id'           => 2,
                            'name'         => '幻彩流苏时尚个性围巾绚丽多彩',
                            'image_big'    => asset('images/demo/product/6.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                        [
                            'id'           => 3,
                            'name'         => '男士白色卫衣套装',
                            'image_big'    => asset('images/demo/product/7.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                        [
                            'id'           => 4,
                            'name'         => '优雅蕾丝边透视性感上衣女性魅力',
                            'image_big'    => asset('images/demo/product/8.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                    ],
                    'title' => [
                        'zh-cn' => '分类商品',
                        'en'    => 'New Summer',
                    ],
                ],
            ],
            [
                'title'     => trans('PageBuilder::modules.latest_products'),
                'code'      => 'latest-products',
                'module_id' => 'module-latest-products-demo-005',
                'icon'      => '<i class="bi bi-star"></i>',
                'content'   => [
                    'style' => [
                        'background_color' => '',
                    ],
                    'limit' => '4',
                    'floor' => [
                        'zh-cn' => '',
                        'en'    => '',
                    ],
                    'products' => [
                        [
                            'id'           => 1,
                            'name'         => '都市精英风尚西装外套经典剪裁',
                            'image_big'    => asset('images/demo/product/1.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                        [
                            'id'           => 2,
                            'name'         => '银河流光璀璨晚礼服闪耀全场',
                            'image_big'    => asset('images/demo/product/2.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                        [
                            'id'           => 3,
                            'name'         => '晨曦漫步轻盈薄款风衣春意盎然',
                            'image_big'    => asset('images/demo/product/3.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                        [
                            'id'           => 4,
                            'name'         => '极简风格主义经典衬衫简约不简单',
                            'image_big'    => asset('images/demo/product/4.webp'),
                            'image_format' => '',
                            'price_format' => '$123.50',
                            'active'       => true,
                        ],
                    ],
                    'title' => [
                        'zh-cn' => '最新商品',
                        'en'    => 'New Products',
                    ],
                ],
            ],
        ];
    }
}
