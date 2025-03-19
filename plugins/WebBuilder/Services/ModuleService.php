<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder\Services;

use Exception;
use InnoShop\Common\Libraries\Link;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\ProductRepo;

class ModuleService
{
    /**
     * @return self
     */
    public static function getInstance(): ModuleService
    {
        return new self;
    }

    /**
     * @param  $modules
     * @return array
     * @throws Exception
     */
    public function parseModules($modules): array
    {
        $processedModules = [];
        foreach ($modules as $module) {
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

        return $processedModules;
    }
}
