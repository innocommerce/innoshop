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
            Product\Image::query()->truncate();
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
                'brand_id'     => 1,
                'slug'         => 'galaxy-glow-evening-gown',
                'translations' => [
                    [
                        'locale' => 'zh_cn',
                        'name'   => '银河流光璀璨晚礼服闪耀全场',
                    ],
                    [
                        'locale' => 'en',
                        'name'   => 'Galaxy Glittering Evening Gown Shines Everywhere',
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
                        'image'        => 'images/demo/product/1.png',
                        'price'        => 333.00,
                        'origin_price' => 334.00,
                        'quantity'     => 50,
                        'variants'     => [1, 0],
                    ],
                    [
                        'code'         => 'GGE004',
                        'image'        => 'images/demo/product/1.png',
                        'price'        => 444.00,
                        'origin_price' => 445.00,
                        'quantity'     => 50,
                        'variants'     => [1, 1],
                    ],
                    [
                        'code'         => 'GGE005',
                        'image'        => 'images/demo/product/1.png',
                        'price'        => 555.00,
                        'origin_price' => 556.00,
                        'quantity'     => 50,
                        'variants'     => [2, 0],
                    ],
                    [
                        'code'         => 'GGE006',
                        'image'        => 'images/demo/product/1.png',
                        'price'        => 666.00,
                        'origin_price' => 667.00,
                        'quantity'     => 50,
                        'variants'     => [2, 1],
                    ],
                ],
                'variables' => [
                    [
                        'name'   => ['en' => 'Color', 'zh_cn' => '颜色'],
                        'values' => [
                            ['image' => '', 'name' => ['en' => 'Red', 'zh_cn' => '红色']],
                            ['image' => '', 'name' => ['en' => 'Green', 'zh_cn' => '绿色']],
                            ['image' => '', 'name' => ['en' => 'Blue', 'zh_cn' => '蓝色']],
                        ],
                    ],
                    [
                        'name'   => ['en' => 'Size', 'zh_cn' => '尺寸'],
                        'values' => [
                            ['image' => '', 'name' => ['en' => 'Big', 'zh_cn' => '大']],
                            ['image' => '', 'name' => ['en' => 'Small', 'zh_cn' => '小']],
                        ],
                    ],
                ],

                'categories' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            ],
            [
                'brand_id'     => 1,
                'slug'         => 'urban-elite-suit-jacket',
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
                'translations' => [
                    [
                        'locale' => 'zh_cn',
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
