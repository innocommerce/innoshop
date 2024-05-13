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
use InnoShop\Common\Models\Category;
use InnoShop\Common\Repositories\CategoryRepo;
use Throwable;

class CategorySeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $items = $this->getCategories();
        if ($items) {
            Category::query()->truncate();
            foreach ($items as $item) {
                CategoryRepo::getInstance()->create($item);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getCategories(): array
    {
        return [
            [
                'slug'         => 'women-clothing',
                'position'     => 1,
                'active'       => 1,
                'translations' => [
                    [
                        'locale'  => 'zh_cn',
                        'name'    => '女装',
                        'content' => '女性时尚服装',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Women',
                        'content' => 'Fashion clothing for women',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'casual-wear',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '休闲装',
                                'content' => '休闲风格的女装',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Casual Wear',
                                'content' => 'Casual style women\'s clothing',
                            ],
                        ],
                    ],
                    [
                        'slug'         => 'formal-wear',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '正装',
                                'content' => '正式场合的女装',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Formal Wear',
                                'content' => 'Formal women\'s clothing for special occasions',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'men-clothing',
                'position'     => 2,
                'active'       => 1,
                'translations' => [
                    [
                        'locale'  => 'zh_cn',
                        'name'    => '男装',
                        'content' => '男性时尚服装',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Men',
                        'content' => 'Fashion clothing for men',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'casual-wear-men',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '休闲装',
                                'content' => '休闲风格的男装',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Casual Wear',
                                'content' => 'Casual style men\'s clothing',
                            ],
                        ],
                    ],
                    [
                        'slug'         => 'business-wear',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '商务装',
                                'content' => '商务场合的男装',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Business Wear',
                                'content' => 'Business men\'s clothing for professional occasions',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'children-clothing',
                'position'     => 3,
                'active'       => 1,
                'translations' => [
                    [
                        'locale'  => 'zh_cn',
                        'name'    => '童装',
                        'content' => '儿童时尚服装',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Children',
                        'content' => 'Fashion clothing for children',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'boys-clothing',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '男童',
                                'content' => '男童时尚服装',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Boys',
                                'content' => 'Fashion clothing for boys',
                            ],
                        ],
                    ],
                    [
                        'slug'         => 'girls-clothing',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '女童',
                                'content' => '女童时尚服装',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Girls',
                                'content' => 'Fashion clothing for girls',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'sportswear',
                'position'     => 4,
                'active'       => 1,
                'translations' => [
                    [
                        'locale'  => 'zh_cn',
                        'name'    => '运动装',
                        'content' => '运动风格的服装',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Sports',
                        'content' => 'Clothing for sports activities',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'sports-clothing',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '运动服',
                                'content' => '适合运动的服装',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Sports Clothing',
                                'content' => 'Clothing designed for sports',
                            ],
                        ],
                    ],
                    [
                        'slug'         => 'sports-accessories',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '运动配件',
                                'content' => '运动所需的配件',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Sports Accessories',
                                'content' => 'Accessories needed for sports',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'accessories',
                'position'     => 5,
                'active'       => 1,
                'translations' => [
                    [
                        'locale'  => 'zh_cn',
                        'name'    => '配饰',
                        'content' => '服装搭配的配饰',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Accessories',
                        'content' => 'Accessories for clothing',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'hats',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '帽子',
                                'content' => '各种款式的帽子',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Hats',
                                'content' => 'Various styles of hats',
                            ],
                        ],
                    ],
                    [
                        'slug'         => 'scarves',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            [
                                'locale'  => 'zh_cn',
                                'name'    => '围巾',
                                'content' => '各种款式的围巾',
                            ],
                            [
                                'locale'  => 'en',
                                'name'    => 'Scarves',
                                'content' => 'Various styles of scarves',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
