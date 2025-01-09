<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder\FrontControllers;

use Exception;
use InnoShop\Common\Libraries\Link;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Front\Controllers\BaseController;

class HomeController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $modules = plugin_setting('web_builder', 'modules');

        if (empty($modules) || empty($modules['modules'])) {
            return view('WebBuilder::front.home', []);
        }

        $processedModules = [];
        foreach ($modules['modules'] as $module) {
            $moduleCode = $module['code']    ?? '';
            $content    = $module['content'] ?? [];

            if ($moduleCode && $content) {
                switch ($moduleCode) {
                    case 'product':
                    case 'latest':
                    case 'category':
                        $moduleCode = 'product';
                        if (! empty($content['products'])) {
                            $productIds = array_map(function ($product) {
                                return is_array($product) ? ($product['id'] ?? null) : $product;
                            }, $content['products']);

                            $productIds = array_filter(array_map('intval', $productIds));

                            if (! empty($productIds)) {
                                $content['products'] = ProductRepo::getInstance()->getListByProductIDs($productIds);
                            }
                        }
                        break;

                    case 'article':
                        if (! empty($content['articles'])) {
                            $articleIds = array_map(function ($article) {
                                return is_array($article) ? ($article['id'] ?? null) : $article;
                            }, $content['articles']);

                            $articleIds = array_filter(array_map('intval', $articleIds));

                            if (! empty($articleIds)) {
                                $content['articles'] = ArticleRepo::getInstance()->getListByArticleIDs($articleIds);
                            }
                        }
                        break;

                    case 'rich_text':
                        break;

                    case 'four_image':
                    case 'slideshow':
                    case 'four_image-plus':
                        if (! empty($content['images'])) {
                            foreach ($content['images'] as &$image) {
                                if (! empty($image['link'])) {
                                    $type                  = $image['link']['type']  ?? '';
                                    $value                 = $image['link']['value'] ?? '';
                                    $image['link']['link'] = Link::getInstance()->link($type, $value);
                                }
                                if (! empty($image['image'])) {
                                    foreach ($image['image'] as $locale => $path) {
                                        if ($path) {
                                            $image['image'][$locale] = image_origin($path);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }

                $processedModules[] = [
                    'code'    => $moduleCode,
                    'content' => $content,
                ];
            }
        }

        $data = [
            'modules' => $processedModules,
        ];

        $data = fire_hook_filter('home.index.data', $data);

        return view('WebBuilder::front.home', $data);
    }
}
