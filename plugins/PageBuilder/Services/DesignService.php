<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Services;

use Exception;
use Illuminate\Support\Str;
use InnoShop\Common\Libraries\Link;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Resources\BrandSimple;

class DesignService
{
    /**
     * @return self
     */
    public static function getInstance(): DesignService
    {
        return new self;
    }

    /**
     * @param  $modulesData
     * @return array
     */
    public function handleRequestModules($modulesData): array
    {
        $modulesData = $modulesData['modules'];
        if (empty($modulesData)) {
            return [];
        }

        foreach ($modulesData as $index => $moduleData) {
            $moduleId = $moduleData['module_id'] ?? '';
            if (empty($moduleId)) {
                $moduleData['module_id'] = Str::random(16);
            }

            $viewPath = $moduleData['view_path'] ?? '';
            if ($viewPath == 'design.') {
                $moduleData['view_path'] = '';
            }

            $modulesData[$index] = $moduleData;
        }

        return ['modules' => $modulesData];
    }

    /**
     * @throws Exception
     */
    public function handleModuleContent($moduleCode, $content)
    {
        $content['module_code'] = $moduleCode;
        if ($moduleCode == 'slideshow') {
            $content = $this->handleSlideShow($content);
        } elseif (in_array($moduleCode, ['image401', 'image402', 'single-image', 'image200', 'image300', 'image301'])) {
            $content = $this->handleImage401($content);
        } elseif ($moduleCode == 'brand') {
            $content = $this->handleBrand($content);
        } elseif ($moduleCode == 'image-text-list') {
            $content = $this->handleImageTextList($content);
        } elseif ($moduleCode == 'brands') {
            $content = $this->handleBrands($content);
        } elseif ($moduleCode == 'tab_product') {
            $content = $this->handleTabProducts($content);
        } elseif ($moduleCode == 'custom-products') {
            $content = $this->handleProducts($content);
        } elseif ($moduleCode == 'category-products') {
            $content = $this->handleCategoryProducts($content);
        } elseif ($moduleCode == 'brand-products') {
            $content = $this->handleBrandProducts($content);
        } elseif ($moduleCode == 'latest-products') {
            $content = $this->handleLatest($content);
        } elseif ($moduleCode == 'icons') {
            $content = $this->handleIcons($content);
        } elseif ($moduleCode == 'rich_text') {
            $content = $this->handleRichText($content);
        } elseif ($moduleCode == 'page') {
            $content = $this->handlePage($content);
        } elseif ($moduleCode == 'article') {
            $content = $this->handleArticle($content);
        }

        $content['width_class'] = pb_get_width_class($content['width'] ?? 'wide');

        return fire_hook_filter('service.design.module.content', $content);
    }

    /**
     * Handle slideshow module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleSlideShow($content): array
    {
        $images = $content['images'] ?? [];
        if (empty($images)) {
            $content['images'] = [];

            return $content;
        }

        $content['images'] = $this->handleImages($images);

        return $content;
    }

    /**
     * Handle brand module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleBrand($content): array
    {
        $brandIds   = $content['brands'] ?? [];
        $brandItems = BrandRepo::getInstance()->getListByBrandIDs($brandIds);
        $brands     = BrandSimple::collection($brandItems)->jsonSerialize();

        $content['brands'] = $brands;
        $content['title']  = $content['title'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle image text list module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleImageTextList($content): array
    {
        $content['title'] = $content['title'][locale_code()] ?? '';

        // 处理图文项数据
        $imageTextItems = $content['imageTextItems'] ?? [];
        if (! empty($imageTextItems)) {
            foreach ($imageTextItems as $index => $item) {
                // 处理图片
                if (isset($item['image'])) {
                    $imageTextItems[$index]['image'] = image_origin($item['image']);
                }

                // 处理链接
                if (isset($item['link']) && ! empty($item['link']['value'])) {
                    $imageTextItems[$index]['url'] = $this->handleLink($item['link']['type'] ?? '', $item['link']['value'] ?? '');
                }
            }
        }

        $content['imageTextItems'] = $imageTextItems;

        // 确保样式设置存在
        $content['itemHeight']   = $content['itemHeight'] ?? 120;
        $content['padding']      = $content['padding'] ?? 16;
        $content['borderRadius'] = $content['borderRadius'] ?? 8;
        $content['borderWidth']  = $content['borderWidth'] ?? 1;
        $content['borderColor']  = $content['borderColor'] ?? '#f0f0f0';
        $content['borderStyle']  = $content['borderStyle'] ?? 'solid';

        return $content;
    }

    /**
     * Handle image four in line module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleImage401($content): array
    {
        $images = $content['images'] ?? [];
        if (empty($images)) {
            $content['images'] = [];
            $content['full']   = $content['full'] ?? false;

            return $content;
        }

        $content['images'] = $this->handleImages($images);
        $content['full']   = $content['full'] ?? false;

        return $content;
    }

    /**
     * Handle icons module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleIcons($content): array
    {
        $content['title'] = $content['title'][locale_code()] ?? '';

        $images = $content['images'] ?? [];
        if (empty($images)) {
            $content['images'] = [];

            return $content;
        }

        $processedImages = [];
        foreach ($images as $image) {
            $processedImages[] = [
                'image'    => image_origin($image['image'] ?? ''),
                'text'     => $image['text'][locale_code()] ?? '',
                'sub_text' => $image['sub_text'][locale_code()] ?? '',
                'link'     => $image['link'] ?? [],
                'url'      => $this->handleLink($image['link']['type'] ?? '', $image['link']['value'] ?? ''),
            ];
        }

        $content['images'] = $processedImages;

        return $content;
    }

    /**
     * Handle rich text module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleRichText($content): array
    {
        $content['data'] = $content['text'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle tab products
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleTabProducts($content): array
    {
        $tabs = $content['tabs'] ?? [];
        if (empty($tabs)) {
            return [];
        }

        foreach ($tabs as $index => $tab) {
            $tabs[$index]['title'] = $tab['title'][locale_code()] ?? '';
            $productsIds           = $tab['products'] ?? [];

            if ($productsIds) {
                // 处理产品ID数组，确保是一维数组
                $productIds = [];
                if (is_array($productsIds)) {
                    foreach ($productsIds as $product) {
                        if (is_array($product) && isset($product['id'])) {
                            $productIds[] = $product['id'];
                        } elseif (is_numeric($product)) {
                            $productIds[] = $product;
                        }
                    }
                } else {
                    $productIds = [$productsIds];
                }

                $productItems = ProductRepo::getInstance()->getListByProductIDs($productIds);

                // 过滤掉非活跃状态的商品
                $productItems = $productItems->filter(function ($product) {
                    return $product->active;
                });

                // 保持为 Product 对象集合，不转换为数组
                $tabs[$index]['products'] = $productItems;
            }
        }
        $content['tabs']  = $tabs;
        $content['title'] = $content['title'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle article
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleArticle($content): array
    {
        $content['title']    = $content['title'][locale_code()] ?? '';
        $content['subtitle'] = $content['subtitle'][locale_code()] ?? '';

        // 从 articles 字段获取文章ID数组
        $articleIds = [];
        if (! empty($content['articles'])) {
            foreach ($content['articles'] as $article) {
                if (is_array($article) && isset($article['id'])) {
                    $articleIds[] = $article['id'];
                } elseif (is_numeric($article)) {
                    $articleIds[] = $article;
                }
            }
        }

        if (! empty($articleIds)) {
            // 获取完整的文章对象，保持为对象集合而不是数组
            $articleItems        = ArticleRepo::getInstance()->getListByArticleIDs($articleIds);
            $content['articles'] = $articleItems;
        } else {
            $content['articles'] = collect();
        }

        return $content;
    }

    /**
     * Handle page
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handlePage($content): array
    {
        $content['title'] = $content['title'][locale_code()] ?? '';

        // 检查 items 键是否存在，如果不存在则初始化为空数组
        $pageIds = $content['items'] ?? [];
        if (! empty($pageIds)) {
            $content['items'] = PageRepo::getInstance()->getListByPageIDs($pageIds)->jsonSerialize();
        } else {
            $content['items'] = [];
        }

        return $content;
    }

    /**
     * Handle category products - 根据分类ID和排序条件动态获取最新数据
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleCategoryProducts($content): array
    {
        $categoryId = $content['category_id'] ?? 0;
        $limit      = $content['limit'] ?? 8;
        $sort       = $content['sort'] ?? 'sales';
        $order      = $content['order'] ?? 'asc';

        if ($categoryId) {
            // 构建查询条件
            $filters = [
                'category_id' => $categoryId,
                'active'      => 1,
                'per_page'    => $limit,
                'page'        => 1,
            ];

            // 添加排序条件
            if ($sort) {
                $sortParts = explode('_', $sort);
                $sortField = $sortParts[0];
                $sortOrder = $sortParts[1] ?? 'desc';

                // 特殊字段映射
                $mappedField = $sortField;
                if ($sortField === 'price') {
                    $mappedField = 'ps.price';
                } elseif ($sortField === 'created') {
                    $mappedField = 'created_at';
                } elseif ($sortField === 'updated') {
                    $mappedField = 'updated_at';
                }

                $filters['sort']  = $mappedField;
                $filters['order'] = $sortOrder;
            }

            // 根据分类ID和排序条件获取最新数据
            $productItems = ProductRepo::getInstance()->withActive()->list($filters);

            // 保持为 Product 对象集合，不转换为数组
            $content['products'] = $productItems;
        } else {
            $content['products'] = collect();
        }

        $content['title']    = $content['title'][locale_code()] ?? '';
        $content['subtitle'] = $content['subtitle'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle brand products - 根据品牌ID获取商品数据
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleBrandProducts($content): array
    {
        $brandId = $content['brand_id'] ?? 0;
        $limit   = $content['limit'] ?? 8;
        $sort    = $content['sort'] ?? 'sales_desc';

        if ($brandId) {
            // 构建查询条件
            $filters = [
                'brand_id' => $brandId,
                'active'   => 1,
                'per_page' => $limit,
                'page'     => 1,
            ];

            // 添加排序条件
            if ($sort) {
                $sortParts = explode('_', $sort);
                $sortField = $sortParts[0];
                $sortOrder = $sortParts[1] ?? 'desc';

                // 特殊字段映射
                $mappedField = $sortField;
                if ($sortField === 'price') {
                    $mappedField = 'ps.price';
                } elseif ($sortField === 'created') {
                    $mappedField = 'created_at';
                } elseif ($sortField === 'updated') {
                    $mappedField = 'updated_at';
                } elseif ($sortField === 'sales') {
                    $mappedField = 'sales_count';
                } elseif ($sortField === 'rating') {
                    $mappedField = 'rating';
                } elseif ($sortField === 'viewed') {
                    $mappedField = 'viewed_count';
                } elseif ($sortField === 'position') {
                    $mappedField = 'position';
                }

                $filters['sort']  = $mappedField;
                $filters['order'] = $sortOrder;
            }

            // 根据品牌ID和排序条件获取商品数据
            $productItems = ProductRepo::getInstance()->withActive()->list($filters);

            // 保持为 Product 对象集合，不转换为数组
            $content['products'] = $productItems;
        } else {
            $content['products'] = collect();
        }

        $content['title']    = $content['title'][locale_code()] ?? '';
        $content['subtitle'] = $content['subtitle'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle latest products - 动态获取最新商品数据
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleLatest($content): array
    {
        $limit   = $content['limit'] ?? 8;
        $columns = $content['columns'] ?? 4;

        // 构建查询条件
        $filters = [
            'active'   => 1,
            'per_page' => $limit,
            'page'     => 1,
            'sort'     => 'created_at',
            'order'    => 'desc',
        ];

        // 获取最新商品数据
        $productItems = ProductRepo::getInstance()->withActive()->list($filters);

        // 保持为 Product 对象集合，不转换为数组
        $content['products'] = $productItems;
        $content['title']    = $content['title'][locale_code()] ?? '';
        $content['subtitle'] = $content['subtitle'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle products
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleProducts($content): array
    {
        // 处理产品ID数组，确保是一维数组
        $productIds = [];
        if (! empty($content['products'])) {
            if (is_array($content['products'])) {
                foreach ($content['products'] as $product) {
                    if (is_array($product) && isset($product['id'])) {
                        $productIds[] = $product['id'];
                    } elseif (is_numeric($product)) {
                        $productIds[] = $product;
                    }
                }
            } else {
                $productIds = [$content['products']];
            }
        }

        $productItems = ProductRepo::getInstance()->getListByProductIDs($productIds);

        // 过滤掉非活跃状态的商品
        $productItems = $productItems->filter(function ($product) {
            return $product->active;
        });

        // 保持为 Product 对象集合，不转换为数组
        $content['products'] = $productItems;
        $content['title']    = $content['title'][locale_code()] ?? '';
        $content['subtitle'] = $content['subtitle'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle images
     *
     * @throws Exception
     */
    private function handleImages($images): array
    {
        if (empty($images)) {
            return [];
        }

        foreach ($images as $index => $image) {
            $imagePath = is_array($image['image']) ? $image['image'][locale_code()] ?? '' : $image['image'] ?? '';

            $images[$index]['image'] = image_origin($imagePath);

            // 处理图片链接
            $link = $image['link'];
            if (! empty($link)) {
                $type  = $link['type'] ?? '';
                $value = $link['type'] == 'custom' ? $link['value'] : ((int) $link['value'] ?? 0);

                $images[$index]['link']['link'] = $this->handleLink($type, $value);
            }

            // 处理按钮链接
            $buttonLink = $image['button_link'] ?? null;
            if (! empty($buttonLink)) {
                $type  = $buttonLink['type'] ?? '';
                $value = $buttonLink['type'] == 'custom' ? $buttonLink['value'] : ((int) $buttonLink['value'] ?? 0);

                $images[$index]['button_link']['link'] = $this->handleLink($type, $value);
            }
        }

        return $images;
    }

    /**
     * Handle brands module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleBrands($content): array
    {
        $content['title'] = $content['title'][locale_code()] ?? '';

        // 确保样式设置存在
        $content['itemHeight']   = $content['itemHeight'] ?? 80;
        $content['padding']      = $content['padding'] ?? 12;
        $content['borderRadius'] = $content['borderRadius'] ?? 8;
        $content['borderWidth']  = $content['borderWidth'] ?? 1;
        $content['borderColor']  = $content['borderColor'] ?? '#f0f0f0';
        $content['borderStyle']  = $content['borderStyle'] ?? 'solid';

        return $content;
    }

    /**
     * Handle links
     *
     * @param  $type
     * @param  $value
     * @return string
     * @throws Exception
     */
    private function handleLink($type, $value): string
    {
        return Link::getInstance()->link($type, $value);
    }
}
