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
use InnoShop\Common\Models\Article;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getArticles();
        if ($items) {
            Article::query()->truncate();
            foreach ($items as $item) {
                Article::query()->create($item);
            }
        }

        $items = $this->getArticleTranslations();
        if ($items) {
            Article\Translation::query()->truncate();
            foreach ($items as $item) {
                Article\Translation::query()->create($item);
            }
        }

        $items = $this->getArticleTags();
        if ($items) {
            Article\Tag::query()->truncate();
            foreach ($items as $item) {
                Article\Tag::query()->create($item);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getArticles(): array
    {
        return [
            [
                'id'         => 1,
                'catalog_id' => 1,
                'slug'       => 'innoshop-innovative-open-source-ecommerce',
                'position'   => 0,
                'viewed'     => 16,
                'author'     => 'InnoShop',
                'active'     => 1,
            ],
            [
                'id'         => 2,
                'catalog_id' => 1,
                'slug'       => null,
                'position'   => 0,
                'viewed'     => 16,
                'author'     => 'InnoShop',
                'active'     => 1,
            ],
            [
                'id'         => 3,
                'catalog_id' => 2,
                'slug'       => null,
                'position'   => 0,
                'viewed'     => 16,
                'author'     => null,
                'active'     => 1,
            ],
            [
                'id'         => 4,
                'catalog_id' => 2,
                'slug'       => null,
                'position'   => 0,
                'viewed'     => 16,
                'author'     => 'InnoShop',
                'active'     => 1,
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getArticleTranslations(): array
    {
        return [
            [
                'article_id'       => 1,
                'locale'           => 'zh_cn',
                'title'            => 'InnoShop - 创新电商，智选未来',
                'summary'          => 'InnoShop，一个以创新为核心的开源电商平台，致力于提供灵活、可扩展的电商解决方案。我们的产品管理功能全面，包括产品分类、添加、库存及价格管理等，旨在帮助商家轻松管理商品，提升运营效率。',
                'image'            => 'images/demo/news/1.jpg',
                'content'          => '欢迎访问 <a href="https://www.innoshop.cn/" target="_blank" rel="noopener">InnoShop 中文官方网站</a> 了解更多信息。',
                'meta_title'       => 'InnoShop - 创新电商，智选未来',
                'meta_description' => 'InnoShop - 创新电商，智选未来',
                'meta_keywords'    => 'InnoShop - 创新电商，智选未来',
            ],
            [
                'article_id'       => 1,
                'locale'           => 'en',
                'title'            => 'InnoShop - Innovative ecommerce',
                'summary'          => 'InnoShop，An open-source e-commerce platform with innovation',
                'image'            => 'images/demo/news/1.jpg',
                'content'          => 'This is english test article',
                'meta_title'       => 'InnoShop - Innovative ecommerce',
                'meta_description' => 'InnoShop - Innovative ecommerce',
                'meta_keywords'    => 'InnoShop - Innovative ecommerce',
            ],
            [
                'article_id'       => 2,
                'locale'           => 'zh_cn',
                'title'            => 'InnoShop - 创新电商，智选未来',
                'summary'          => 'InnoShop，一个以创新为核心的开源电商平台，致力于提供灵活、可扩展的电商解决方案。我们的产品管理功能全面，包括产品分类、添加、库存及价格管理等，旨在帮助商家轻松管理商品，提升运营效率。',
                'image'            => 'images/demo/news/2.jpg',
                'content'          => '欢迎访问 <a href="https://www.innoshop.cn/" target="_blank" rel="noopener">InnoShop 中文官方网站</a> 了解更多信息。',
                'meta_title'       => 'InnoShop - 创新电商，智选未来',
                'meta_description' => 'InnoShop - 创新电商，智选未来',
                'meta_keywords'    => 'InnoShop - 创新电商，智选未来',
            ],
            [
                'article_id'       => 2,
                'locale'           => 'en',
                'title'            => 'New generation ecommerce system',
                'summary'          => 'InnoShop，An open-source e-commerce platform with innovation',
                'image'            => 'images/demo/news/2.jpg',
                'content'          => 'This is english test article',
                'meta_title'       => 'InnoShop - Innovative ecommerce',
                'meta_description' => 'InnoShop - Innovative ecommerce',
                'meta_keywords'    => 'InnoShop - Innovative ecommerce',
            ],
            [
                'article_id'       => 3,
                'locale'           => 'zh_cn',
                'title'            => 'InnoShop - 创新电商，智选未来',
                'summary'          => 'InnoShop，一个以创新为核心的开源电商平台，致力于提供灵活、可扩展的电商解决方案。我们的产品管理功能全面，包括产品分类、添加、库存及价格管理等，旨在帮助商家轻松管理商品，提升运营效率。',
                'image'            => 'images/demo/news/3.jpg',
                'content'          => '欢迎访问 <a href="https://www.innoshop.cn/" target="_blank" rel="noopener">InnoShop 中文官方网站</a> 了解更多信息。',
                'meta_title'       => 'InnoShop - 创新电商，智选未来',
                'meta_description' => 'InnoShop - 创新电商，智选未来',
                'meta_keywords'    => 'InnoShop - 创新电商，智选未来',
            ],
            [
                'article_id'       => 3,
                'locale'           => 'en',
                'title'            => 'Ecommerce integrated with AI',
                'summary'          => 'InnoShop，An open-source e-commerce platform with innovation',
                'image'            => 'images/demo/news/3.jpg',
                'content'          => 'This is english test article',
                'meta_title'       => 'InnoShop - Innovative ecommerce',
                'meta_description' => 'InnoShop - Innovative ecommerce',
                'meta_keywords'    => 'InnoShop - Innovative ecommerce',
            ],
            [
                'article_id'       => 4,
                'locale'           => 'zh_cn',
                'title'            => 'InnoShop - 创新电商，智选未来',
                'summary'          => 'InnoShop，一个以创新为核心的开源电商平台，致力于提供灵活、可扩展的电商解决方案。我们的产品管理功能全面，包括产品分类、添加、库存及价格管理等，旨在帮助商家轻松管理商品，提升运营效率。',
                'image'            => 'images/demo/news/4.jpg',
                'content'          => '欢迎访问 <a href="https://www.innoshop.cn/" target="_blank" rel="noopener">InnoShop 中文官方网站</a> 了解更多信息。',
                'meta_title'       => 'InnoShop - 创新电商，智选未来',
                'meta_description' => 'InnoShop - 创新电商，智选未来',
                'meta_keywords'    => 'InnoShop - 创新电商，智选未来',
            ],
            [
                'article_id'       => 4,
                'locale'           => 'en',
                'title'            => 'New version released!',
                'summary'          => 'InnoShop，An open-source e-commerce platform with innovation',
                'image'            => 'images/demo/news/4.jpg',
                'content'          => 'This is english test article',
                'meta_title'       => 'InnoShop - Innovative ecommerce',
                'meta_description' => 'InnoShop - Innovative ecommerce',
                'meta_keywords'    => 'InnoShop - Innovative ecommerce',
            ],
        ];

    }

    /**
     * @return \int[][]
     */
    private function getArticleTags(): array
    {
        return [
            [
                'id'         => 1,
                'article_id' => 1,
                'tag_id'     => 1,
            ],
            [
                'id'         => 2,
                'article_id' => 1,
                'tag_id'     => 2,
            ],
        ];
    }
}
