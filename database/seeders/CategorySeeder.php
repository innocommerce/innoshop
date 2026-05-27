<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use InnoShop\Common\Models\Category;
use InnoShop\Common\Repositories\CategoryRepo;
use Throwable;

class CategorySeeder extends BaseSeeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $items = $this->getCategories();
        if ($items) {
            $this->safeTruncate(Category::class);
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
                'image'        => 'images/demo/categories/women-fashion.webp',
                'translations' => [
                    [
                        'locale'  => 'zh-cn',
                        'name'    => '女装',
                        'summary' => '潮流女装，精致设计，彰显优雅品味',
                        'content' => '<div class="category-description">
                            <h3>精选女性时尚服饰</h3>
                            <p>我们为现代女性精心挑选各种风格的时尚服装，包含：</p>
                            <ul>
                                <li><strong>连衣裙系列</strong> - 优雅迷人，适合各种场合穿着</li>
                                <li><strong>上衣衬衫</strong> - 简约时尚，日常通勤百搭之选</li>
                                <li><strong>外套风衣</strong> - 经典剪裁，四季必备时尚单品</li>
                                <li><strong>牛仔裤装</strong> - 修身显瘦，百搭经典之选</li>
                            </ul>
                            <p>我们坚持使用<em>优质面料</em>，注重每一个<em>剪裁细节</em>，为您打造舒适、时尚、优雅的着装体验。</p>
                            <blockquote>
                                <p>"时尚不仅是外表，更是内在自信的体现"</p>
                            </blockquote>
                        </div>',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Women',
                        'summary' => 'Trendy women\'s fashion with exquisite design and elegant taste',
                        'content' => '<div class="category-description">
                            <h3>Curated Women\'s Fashion Collection</h3>
                            <p>We carefully select fashionable clothing for modern women, featuring:</p>
                            <ul>
                                <li><strong>Dress Collection</strong> - Elegant and charming, perfect for every occasion</li>
                                <li><strong>Tops & Blouses</strong> - Simple and stylish, ideal for daily wear and office</li>
                                <li><strong>Coats & Jackets</strong> - Classic tailoring, essential pieces for all seasons</li>
                                <li><strong>Denim Collection</strong> - Slim fit, timeless and versatile</li>
                            </ul>
                            <p>We are committed to using <em>premium fabrics</em> and paying attention to every <em>tailoring detail</em>.</p>
                            <blockquote>
                                <p>"Fashion is not just about appearance, but the embodiment of inner confidence"</p>
                            </blockquote>
                        </div>',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'dresses',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '连衣裙', 'content' => '优雅迷人的各式连衣裙'],
                            ['locale' => 'en', 'name' => 'Dresses', 'content' => 'Elegant and charming dresses for all occasions'],
                        ],
                    ],
                    [
                        'slug'         => 'tops-blouses',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '上衣衬衫', 'content' => '简约时尚的上衣与衬衫'],
                            ['locale' => 'en', 'name' => 'Tops', 'content' => 'Simple and stylish tops and blouses'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'men-clothing',
                'position'     => 2,
                'active'       => 1,
                'image'        => 'images/demo/categories/men-fashion.webp',
                'translations' => [
                    [
                        'locale'  => 'zh-cn',
                        'name'    => '男装',
                        'summary' => '精品男装，绅士风度，尽显成熟魅力',
                        'content' => '<div class="category-description">
                            <h3>精选男性时尚服饰</h3>
                            <p>为现代绅士精选各种风格的男装，涵盖：</p>
                            <ul>
                                <li><strong>西装外套</strong> - 精致剪裁，商务与正式场合首选</li>
                                <li><strong>休闲衬衫</strong> - 舒适面料，日常穿搭必备</li>
                                <li><strong>卫衣套装</strong> - 休闲运动，舒适自在</li>
                            </ul>
                            <p>精选<em>高品质面料</em>，打造舒适得体的男士着装体验。</p>
                        </div>',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Men',
                        'summary' => 'Premium menswear with gentleman style and mature charm',
                        'content' => '<div class="category-description">
                            <h3>Curated Men\'s Fashion Collection</h3>
                            <p>Selected menswear for the modern gentleman:</p>
                            <ul>
                                <li><strong>Suits & Blazers</strong> - Fine tailoring for business and formal occasions</li>
                                <li><strong>Casual Shirts</strong> - Comfortable fabrics for everyday wear</li>
                                <li><strong>Sweatsuits</strong> - Casual and sporty, comfortable all day</li>
                            </ul>
                            <p>Selected <em>high-quality fabrics</em> for a comfortable and stylish menswear experience.</p>
                        </div>',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'suits-blazers',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '西装外套', 'content' => '精致剪裁的西装与外套'],
                            ['locale' => 'en', 'name' => 'Suits', 'content' => 'Fine tailored suits and blazers'],
                        ],
                    ],
                    [
                        'slug'         => 'casual-shirts',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '休闲衬衫', 'content' => '舒适百搭的休闲衬衫'],
                            ['locale' => 'en', 'name' => 'Shirts', 'content' => 'Comfortable and versatile casual shirts'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'kids-clothing',
                'position'     => 3,
                'active'       => 1,
                'image'        => 'images/demo/categories/kids-fashion.webp',
                'translations' => [
                    [
                        'locale'  => 'zh-cn',
                        'name'    => '童装',
                        'summary' => '活泼可爱，舒适安全的儿童服饰',
                        'content' => '为孩子们精心挑选的时尚童装，安全面料，活泼设计。',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Kids',
                        'summary' => 'Playful, comfortable and safe children\'s clothing',
                        'content' => 'Carefully selected fashionable kids clothing with safe fabrics and playful designs.',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'boys',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '男童', 'content' => '男童时尚服装'],
                            ['locale' => 'en', 'name' => 'Boys', 'content' => 'Fashion clothing for boys'],
                        ],
                    ],
                    [
                        'slug'         => 'girls',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '女童', 'content' => '女童时尚服装'],
                            ['locale' => 'en', 'name' => 'Girls', 'content' => 'Fashion clothing for girls'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'shoes-bags',
                'position'     => 4,
                'active'       => 1,
                'image'        => 'images/demo/categories/shoes-bags.webp',
                'translations' => [
                    [
                        'locale'  => 'zh-cn',
                        'name'    => '鞋履箱包',
                        'summary' => '时尚鞋履与品质箱包，步履间的优雅',
                        'content' => '精选时尚鞋履与品质箱包，从高跟鞋到运动鞋，从手提包到双肩包，满足您的各种搭配需求。',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Footwear',
                        'summary' => 'Stylish footwear and quality bags for every step',
                        'content' => 'Selected stylish footwear and quality bags, from heels to sneakers, handbags to backpacks.',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'womens-shoes',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '女鞋', 'content' => '高跟鞋、平底鞋、运动鞋等'],
                            ['locale' => 'en', 'name' => 'Heels', 'content' => 'Heels, flats, sneakers and more'],
                        ],
                    ],
                    [
                        'slug'         => 'bags',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '箱包', 'content' => '手提包、双肩包、钱包等'],
                            ['locale' => 'en', 'name' => 'Bags', 'content' => 'Handbags, backpacks, wallets and more'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'sportswear',
                'position'     => 5,
                'active'       => 1,
                'image'        => 'images/demo/categories/sportswear.webp',
                'translations' => [
                    [
                        'locale'  => 'zh-cn',
                        'name'    => '运动服饰',
                        'summary' => '专业运动装备，激发无限运动潜能',
                        'content' => '为运动爱好者准备的专业装备，涵盖运动服装、运动鞋等，舒适透气，助力运动表现。',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Sportswear',
                        'summary' => 'Professional sports gear to unleash your athletic potential',
                        'content' => 'Professional sportswear for active lifestyle, including athletic clothing and sports shoes.',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'sports-clothing',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '运动服装', 'content' => '卫衣、运动套装、瑜伽服等'],
                            ['locale' => 'en', 'name' => 'Activewear', 'content' => 'Hoodies, tracksuits, yoga wear and more'],
                        ],
                    ],
                    [
                        'slug'         => 'sports-shoes',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '运动鞋', 'content' => '跑步鞋、训练鞋等'],
                            ['locale' => 'en', 'name' => 'Sneakers', 'content' => 'Running shoes, training shoes and more'],
                        ],
                    ],
                ],
            ],
            [
                'slug'         => 'jewelry-accessories',
                'position'     => 6,
                'active'       => 1,
                'image'        => 'images/demo/categories/jewelry-accessories.webp',
                'translations' => [
                    [
                        'locale'  => 'zh-cn',
                        'name'    => '珠宝配饰',
                        'summary' => '精致珠宝与时尚配饰，点亮整体造型',
                        'content' => '精心挑选的珠宝首饰与时尚配饰，包含项链、耳环、围巾、帽子等，为您的穿搭增添亮点。',
                    ],
                    [
                        'locale'  => 'en',
                        'name'    => 'Jewelry',
                        'summary' => 'Exquisite jewelry and fashion accessories to elevate your look',
                        'content' => 'Carefully curated jewelry and fashion accessories, including necklaces, earrings, scarves, hats and more.',
                    ],
                ],
                'children' => [
                    [
                        'slug'         => 'necklaces',
                        'position'     => 1,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '项链首饰', 'content' => '项链、耳环、手链等'],
                            ['locale' => 'en', 'name' => 'Necklaces', 'content' => 'Necklaces, earrings, bracelets and more'],
                        ],
                    ],
                    [
                        'slug'         => 'scarves-hats',
                        'position'     => 2,
                        'active'       => 1,
                        'translations' => [
                            ['locale' => 'zh-cn', 'name' => '围巾帽子', 'content' => '丝巾、围巾、帽子等'],
                            ['locale' => 'en', 'name' => 'Scarves', 'content' => 'Silk scarves, winter scarves, hats and more'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
