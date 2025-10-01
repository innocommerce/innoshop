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
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\ProductRepo;

class ProductSeeder extends Seeder
{
    /**
     * @throws \Exception|\Throwable
     */
    public function run(): void
    {
        $items = $this->getProducts();
        if ($items) {
            Product::query()->truncate();
            Product\Translation::query()->truncate();
            Product\Category::query()->truncate();
            Product\Sku::query()->truncate();
            foreach ($items as $item) {
                ProductRepo::getInstance()->create($item);
            }
        }
    }

    private function getProducts(): array
    {
        return [
            [
                'brand_id' => 1,
                'spu_code' => 'galaxy-glow-evening-gown',
                'slug'     => 'galaxy-glow-evening-gown',
                'images'   => [
                    'images/demo/product/1.png',
                    'images/demo/product/3.png',
                    'images/demo/product/4.png',
                    'images/demo/product/5.png',
                    'images/demo/product/6.png',
                ],
                'hover_image'  => 'images/demo/product/7.png',
                'active'       => true,
                'translations' => [
                    [
                        'locale'           => 'zh-cn',
                        'name'             => '银河流光璀璨晚礼服闪耀全场',
                        'summary'          => '灵感来源于星河流光，细腻亮片织就璀璨光泽，优雅版型衬托气质。',
                        'content'          => '以银河为灵感的晚礼服，采用细密亮片面料与柔和垂坠剪裁，行走间光影流转，尽显高雅与自信。适合宴会、典礼等正式场合。',
                        'selling_point'    => '星光流动质感、轻盈垂坠剪裁、灵动闪耀气场',
                        'meta_title'       => '银河流光璀璨晚礼服 | 典雅闪耀',
                        'meta_description' => '以星河为灵感的璀璨晚礼服，亮片质感与优雅剪裁，闪耀全场。',
                        'meta_keywords'    => '晚礼服, 亮片, 高定, 银河, 闪耀',
                    ],
                    [
                        'locale'           => 'en',
                        'name'             => 'Galaxy Glittering Evening Gown Shines Everywhere',
                        'summary'          => 'Inspired by the galaxy glow, shimmering sequins and elegant silhouette elevate your presence.',
                        'content'          => 'A galaxy-inspired evening gown crafted with fine shimmering sequins and a soft draping cut. With light flowing as you move, it exudes grace and confidence—perfect for banquets and ceremonies.',
                        'selling_point'    => 'Shimmering texture, airy drape, radiant aura',
                        'meta_title'       => 'Galaxy Glow Evening Gown | Elegant & Radiant',
                        'meta_description' => 'Galaxy-inspired shimmering evening gown with refined sequins and elegant tailoring to shine everywhere.',
                        'meta_keywords'    => 'evening gown, glitter, galaxy, sequins, elegant',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'GGE001',
                        'image'        => 'images/demo/product/1.png',
                        'price'        => 111.00,
                        'origin_price' => 112.00,
                        'quantity'     => 50,
                        'variants'     => [0, 0],
                        'is_default'   => true,
                    ],
                    [
                        'code'         => 'GGE002',
                        'image'        => 'images/demo/product/1.png',
                        'price'        => 222.00,
                        'origin_price' => 223.00,
                        'quantity'     => 50,
                        'variants'     => [0, 1],
                    ],
                    [
                        'code'         => 'GGE003',
                        'image'        => 'images/demo/product/7.png',
                        'price'        => 333.00,
                        'origin_price' => 334.00,
                        'quantity'     => 50,
                        'variants'     => [1, 0],
                    ],
                    [
                        'code'         => 'GGE004',
                        'image'        => 'images/demo/product/7.png',
                        'price'        => 444.00,
                        'origin_price' => 445.00,
                        'quantity'     => 50,
                        'variants'     => [1, 1],
                    ],
                    [
                        'code'         => 'GGE005',
                        'image'        => 'images/demo/product/3.png',
                        'price'        => 555.00,
                        'origin_price' => 556.00,
                        'quantity'     => 50,
                        'variants'     => [2, 0],
                    ],
                    [
                        'code'         => 'GGE006',
                        'image'        => 'images/demo/product/3.png',
                        'price'        => 666.00,
                        'origin_price' => 667.00,
                        'quantity'     => 50,
                        'variants'     => [2, 1],
                    ],
                ],
                'variables' => [
                    [
                        'name'   => ['en' => 'Color', 'zh-cn' => '颜色'],
                        'values' => [
                            ['image' => 'images/demo/product/1.png', 'name' => ['en' => 'Red', 'zh-cn' => '红色']],
                            ['image' => 'images/demo/product/7.png', 'name' => ['en' => 'White', 'zh-cn' => '白色']],
                            ['image' => 'images/demo/product/3.png', 'name' => ['en' => 'Pink', 'zh-cn' => '粉色']],
                        ],
                    ],
                    [
                        'name'   => ['en' => 'Size', 'zh-cn' => '尺寸'],
                        'values' => [
                            ['image' => '', 'name' => ['en' => 'Big', 'zh-cn' => '大']],
                            ['image' => '', 'name' => ['en' => 'Small', 'zh-cn' => '小']],
                        ],
                    ],
                ],

                'product_options' => [
                    [
                        'option_id' => 1, // Gift Wrap
                        'values'    => [
                            ['option_value_id' => 1, 'price_adjustment' => 5.00,  'stock_quantity' => 100],  // Standard Wrap
                            ['option_value_id' => 2, 'price_adjustment' => 20.00, 'stock_quantity' => 100],  // Premium Gift Box
                        ],
                    ],
                    [
                        'option_id' => 2, // Accessories
                        'values'    => [
                            ['option_value_id' => 3, 'price_adjustment' => 15.00, 'stock_quantity' => 100], // Brooch
                            ['option_value_id' => 4, 'price_adjustment' => 10.00, 'stock_quantity' => 100], // Belt
                        ],
                    ],
                    [
                        'option_id' => 3, // Express Customization
                        'values'    => [
                            ['option_value_id' => 5, 'price_adjustment' => 0.00,  'stock_quantity' => 100],   // No Rush
                            ['option_value_id' => 6, 'price_adjustment' => 50.00, 'stock_quantity' => 100],   // Next-day Shipping
                        ],
                    ],
                ],

                'categories' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 1,
                'spu_code'     => 'urban-elite-suit-jacket',
                'slug'         => 'urban-elite-suit-jacket',
                'images'       => ['images/demo/product/2.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '都市精英风尚西装外套经典剪裁'],
                    [
                        'locale' => 'en',
                        'name'   => 'Urban Elite Fashion Suit Jacket Classic Cut',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'UES002',
                        'image'        => 'images/demo/product/2.png',
                        'price'        => 599.99,
                        'origin_price' => null,
                        'quantity'     => 30,
                    ],
                ],
                'categories' => [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 1,
                'slug'         => 'dawn-stroll-light-trench',
                'images'       => ['images/demo/product/3.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '晨曦漫步轻盈薄款风衣春意盎然',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => 'Dawn Stroll Lightweight Spring Trench Coat',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'DSLT003',
                        'image'        => 'images/demo/product/3.png',
                        'price'        => 399.99,
                        'origin_price' => 2199.99,
                        'quantity'     => 40,
                    ],
                ],
                'categories' => [1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 1,
                'slug'         => 'star-orbit-casual-sweater',
                'images'       => ['images/demo/product/4.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '星辰轨迹个性休闲卫衣夜空星辰',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => 'Starry Track Personalized Casual Sweater Night Sky Stars',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'SOCS004',
                        'image'        => 'images/demo/product/4.png',
                        'price'        => 299.99,
                        'origin_price' => 2199.99,
                        'quantity'     => 60,
                    ],
                ],
                'categories' => [1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 1,
                'slug'         => 'rainbow-fringe-scarf',
                'images'       => ['images/demo/product/5.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '幻彩流苏时尚个性围巾绚丽多彩',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => 'Colorful Tassel Fashion Personalized Scarf Bright and Colorful',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'RFS005',
                        'image'        => 'images/demo/product/5.png',
                        'price'        => 199.99,
                        'origin_price' => 2199.99,
                        'quantity'     => 70,
                    ],
                ],
                'categories' => [1, 2, 3,  5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 2,
                'slug'         => 'minimalist-classic-shirt',
                'images'       => ['images/demo/product/6.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '极简风格主义经典衬衫简约不简单',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => 'Minimalist Style Classic Shirt Simple but Not Simple',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'MSCS006',
                        'image'        => 'images/demo/product/6.png',
                        'price'        => 99.66,
                        'origin_price' => null,
                        'quantity'     => 80,
                    ],
                ],
                'categories' => [1, 2, 3, 4,  6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 2,
                'slug'         => 'retro-high-waist-jeans',
                'images'       => ['images/demo/product/7.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '摩登复古风高腰牛仔裤经典再现',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => 'Modern Retro Style High-Waist Jeans Classic Reappearance',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'MRHWJ007',
                        'image'        => 'images/demo/product/7.png',
                        'price'        => 69.66,
                        'origin_price' => null,
                        'quantity'     => 90,
                    ],
                ],
                'categories' => [1, 2, 3, 4, 5,  7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 2,
                'slug'         => 'elegant-lace-sexy-top',
                'images'       => ['images/demo/product/8.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '优雅蕾丝边透视性感上衣女性魅力',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => 'Elegant Lace Transparent Sexy Top Female Charm',
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'ELSXT008',
                        'image'        => 'images/demo/product/8.png',
                        'price'        => 49.66,
                        'origin_price' => null,
                        'quantity'     => 100,
                    ],
                ],
                'categories' => [1, 2, 3, 4, 5,  6, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 2,
                'slug'         => 'men-white-sweatsuit',
                'images'       => ['images/demo/product/9.png'],
                'active'       => true,
                'translations' => [
                    [
                        'locale' => 'zh-cn',
                        'name'   => '男士白色卫衣套装',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => "Men's White Sweatsuit",
                    ],
                ],
                'skus' => [
                    [
                        'code'         => 'MWS009',
                        'image'        => 'images/demo/product/9.png',
                        'price'        => 49.66,
                        'origin_price' => null,
                        'quantity'     => 110,
                    ],
                ],
                'categories' => [1, 2, 3, 4, 5,  7, 9, 10, 11, 12, 13, 14, 15],
            ],
        ];
    }
}
