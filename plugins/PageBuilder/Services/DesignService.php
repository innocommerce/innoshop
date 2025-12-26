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
     * Handle and normalize request modules data
     *
     * @param  array  $modulesData  Modules data from request
     * @return array
     */
    public function handleRequestModules(array $modulesData): array
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
     * Handle module content based on module code
     *
     * @param  string  $moduleCode  Module code identifier
     * @param  array  $content  Module content data
     * @return array
     * @throws Exception
     */
    public function handleModuleContent(string $moduleCode, array $content): array
    {
        $content['module_code'] = $moduleCode;

        $handlerMap = $this->getModuleHandlerMap();

        if (isset($handlerMap[$moduleCode])) {
            $handlerMethod = $handlerMap[$moduleCode];
            if (method_exists($this, $handlerMethod)) {
                $content = $this->$handlerMethod($content);
            }
        }

        $content                = $this->normalizeMultilingualFields($content);
        $content['width_class'] = pb_get_width_class($content['width'] ?? 'wide');

        return fire_hook_filter('service.design.module.content', $content);
    }

    /**
     * Get module handler method mapping
     *
     * @return array
     */
    private function getModuleHandlerMap(): array
    {
        return [
            'slideshow'         => 'handleSlideShow',
            'image401'          => 'handleImage401',
            'image402'          => 'handleImage401',
            'single-image'      => 'handleImage401',
            'image200'          => 'handleImage401',
            'image300'          => 'handleImage401',
            'image301'          => 'handleImage401',
            'brand'             => 'handleBrand',
            'image-text-list'   => 'handleImageTextList',
            'brands'            => 'handleBrands',
            'tab_product'       => 'handleTabProducts',
            'custom-products'   => 'handleProducts',
            'category-products' => 'handleCategoryProducts',
            'brand-products'    => 'handleBrandProducts',
            'latest-products'   => 'handleLatest',
            'icons'             => 'handleIcons',
            'rich_text'         => 'handleRichText',
            'page'              => 'handlePage',
            'article'           => 'handleArticle',
        ];
    }

    /**
     * Normalize multilingual fields, convert array format to current language string
     *
     * @param  array  $content
     * @return array
     */
    private function normalizeMultilingualFields(array $content): array
    {
        $topFields = ['title', 'subtitle', 'floor', 'description', 'content', 'button_text', 'text', 'sub_text'];
        foreach ($topFields as $field) {
            if (isset($content[$field])) {
                $content[$field] = $this->getMultilingualValue($content[$field]);
            }
        }

        $content = $this->normalizeNestedArrays($content);

        $content = $this->normalizeCountFields($content);

        return $content;
    }

    /**
     * Get multilingual field value
     *
     * @param  mixed  $value
     * @return string
     */
    private function getMultilingualValue($value): string
    {
        if (is_array($value) && ! $this->isNumericArray($value)) {
            $locale      = locale_code();
            $frontLocale = front_locale_code();

            return $value[$locale] ?? ($value[$frontLocale] ?? ($value[array_key_first($value)] ?? ''));
        }

        if (! is_string($value) && ! is_object($value)) {
            return (string) $value;
        }

        return $value ?? '';
    }

    /**
     * Normalize multilingual fields in nested arrays
     *
     * @param  array  $content
     * @return array
     */
    private function normalizeNestedArrays(array $content): array
    {
        $locale      = locale_code();
        $frontLocale = front_locale_code();

        if (isset($content['images']) && is_array($content['images'])) {
            foreach ($content['images'] as $index => $image) {
                if (is_array($image)) {
                    if (isset($image['image'])) {
                        $content['images'][$index]['image'] = $this->getMultilingualValue($image['image']);
                    }
                    foreach (['title', 'subtitle', 'button_text', 'text', 'sub_text', 'description'] as $field) {
                        if (isset($image[$field])) {
                            $content['images'][$index][$field] = $this->getMultilingualValue($image[$field]);
                        }
                    }
                }
            }
        }

        if (isset($content['imageTextItems']) && is_array($content['imageTextItems'])) {
            foreach ($content['imageTextItems'] as $index => $item) {
                if (is_array($item)) {
                    foreach (['title', 'subtitle', 'text', 'sub_text', 'description'] as $field) {
                        if (isset($item[$field])) {
                            $content['imageTextItems'][$index][$field] = $this->getMultilingualValue($item[$field]);
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Normalize count fields for collections and arrays
     *
     * @param  array  $content
     * @return array
     */
    private function normalizeCountFields(array $content): array
    {
        if (isset($content['products'])) {
            $products = $content['products'];
            if (is_object($products) && method_exists($products, 'count')) {
                $content['products_count'] = $products->count();
            } elseif (is_array($products)) {
                $content['products']       = collect($products);
                $content['products_count'] = count($products);
            } else {
                $content['products']       = collect();
                $content['products_count'] = 0;
            }
        } else {
            $content['products']       = collect();
            $content['products_count'] = 0;
        }

        $content['images_count'] = isset($content['images']) && is_array($content['images'])
            ? count($content['images'])
            : 0;

        $content['imageTextItems_count'] = isset($content['imageTextItems']) && is_array($content['imageTextItems'])
            ? count($content['imageTextItems'])
            : 0;

        return $content;
    }

    /**
     * Check if array is numeric indexed
     *
     * @param  array  $array
     * @return bool
     */
    private function isNumericArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Handle slideshow module
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleSlideShow(array $content): array
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
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleBrand(array $content): array
    {
        $brandIds   = $content['brands'] ?? [];
        $brandItems = BrandRepo::getInstance()->getListByBrandIDs($brandIds);
        $brands     = BrandSimple::collection($brandItems)->jsonSerialize();

        $content['brands'] = $brands;

        return $content;
    }

    /**
     * Handle image text list module
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleImageTextList(array $content): array
    {
        $imageTextItems = $content['imageTextItems'] ?? [];
        if (! empty($imageTextItems)) {
            foreach ($imageTextItems as $index => $item) {
                if (isset($item['image'])) {
                    $imageTextItems[$index]['image'] = image_origin($item['image']);
                }

                if (isset($item['link']) && ! empty($item['link']['value'])) {
                    $imageTextItems[$index]['url'] = $this->handleLink($item['link']['type'] ?? '', $item['link']['value'] ?? '');
                }
            }
        }

        $content['imageTextItems'] = $imageTextItems;

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
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleImage401(array $content): array
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
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleIcons(array $content): array
    {
        $images = $content['images'] ?? [];
        if (empty($images)) {
            $content['images'] = [];

            return $content;
        }

        $processedImages = [];
        foreach ($images as $image) {
            $processedImages[] = [
                'image'    => image_origin($image['image'] ?? ''),
                'text'     => $image['text'] ?? '',
                'sub_text' => $image['sub_text'] ?? '',
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
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleRichText(array $content): array
    {
        if (isset($content['text'])) {
            if (is_array($content['text']) && ! $this->isNumericArray($content['text'])) {
                $locale          = locale_code();
                $frontLocale     = front_locale_code();
                $content['data'] = $content['text'][$locale] ?? ($content['text'][$frontLocale] ?? ($content['text'][array_key_first($content['text'])] ?? ''));
            } else {
                $content['data'] = is_string($content['text']) ? $content['text'] : '';
            }
        } else {
            $content['data'] = '';
        }

        return $content;
    }

    /**
     * Handle tab products
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleTabProducts(array $content): array
    {
        $tabs = $content['tabs'] ?? [];
        if (empty($tabs)) {
            return [];
        }

        foreach ($tabs as $index => $tab) {
            $productIds = $this->extractIds($tab['products'] ?? []);
            if ($productIds) {
                $productItems             = ProductRepo::getInstance()->getListByProductIDs($productIds);
                $tabs[$index]['products'] = $productItems->filter(fn ($product) => $product->active);
            }
        }

        $content['tabs'] = $tabs;

        return $content;
    }

    /**
     * Handle article
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleArticle(array $content): array
    {
        $articleIds          = $this->extractIds($content['articles'] ?? []);
        $content['articles'] = $articleIds
            ? ArticleRepo::getInstance()->getListByArticleIDs($articleIds)
            : collect();

        return $content;
    }

    /**
     * Handle page
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handlePage(array $content): array
    {
        $pageIds = $content['items'] ?? [];
        if (! empty($pageIds)) {
            $content['items'] = PageRepo::getInstance()->getListByPageIDs($pageIds)->jsonSerialize();
        } else {
            $content['items'] = [];
        }

        return $content;
    }

    /**
     * Handle category products - dynamically fetch latest data based on category ID and sort conditions
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleCategoryProducts(array $content): array
    {
        $categoryId = $content['category_id'] ?? 0;
        if (! $categoryId) {
            $content['products'] = collect();

            return $content;
        }

        $filters = $this->buildProductFilters([
            'category_id' => $categoryId,
            'limit'       => $content['limit'] ?? 8,
            'sort'        => $content['sort'] ?? 'sales',
            'order'       => $content['order'] ?? 'asc',
        ]);

        $content['products'] = ProductRepo::getInstance()->withActive()->list($filters);

        return $content;
    }

    /**
     * Handle brand products - get product data by brand ID
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleBrandProducts(array $content): array
    {
        $brandId = $content['brand_id'] ?? 0;
        if (! $brandId) {
            $content['products'] = collect();

            return $content;
        }

        $filters = $this->buildProductFilters([
            'brand_id' => $brandId,
            'limit'    => $content['limit'] ?? 8,
            'sort'     => $content['sort'] ?? 'sales_desc',
        ]);

        $content['products'] = ProductRepo::getInstance()->withActive()->list($filters);

        return $content;
    }

    /**
     * Handle latest products - dynamically fetch latest product data
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleLatest(array $content): array
    {
        $limit   = $content['limit'] ?? 8;
        $columns = $content['columns'] ?? 4;

        $filters = [
            'active'   => 1,
            'per_page' => $limit,
            'page'     => 1,
            'sort'     => 'created_at',
            'order'    => 'desc',
        ];

        $productItems = ProductRepo::getInstance()->withActive()->list($filters);

        $content['products'] = $productItems;

        return $content;
    }

    /**
     * Handle products
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleProducts(array $content): array
    {
        $productIds = $this->extractIds($content['products'] ?? []);
        if ($productIds) {
            $productItems        = ProductRepo::getInstance()->getListByProductIDs($productIds);
            $content['products'] = $productItems->filter(fn ($product) => $product->active);
        } else {
            $content['products'] = collect();
        }

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

        $locale      = locale_code();
        $frontLocale = front_locale_code();

        foreach ($images as $index => $image) {
            if (isset($image['image'])) {
                if (is_array($image['image']) && ! $this->isNumericArray($image['image'])) {
                    $imagePath = $image['image'][$locale] ?? ($image['image'][$frontLocale] ?? ($image['image'][array_key_first($image['image'])] ?? ''));
                } else {
                    $imagePath = is_string($image['image']) ? $image['image'] : '';
                }
                $images[$index]['image'] = image_origin($imagePath);
            }

            $link = $image['link'] ?? null;
            if (! empty($link)) {
                $type  = $link['type'] ?? '';
                $value = $link['type'] == 'custom' ? ($link['value'] ?? '') : ((int) ($link['value'] ?? 0));

                $images[$index]['link']['link'] = $this->handleLink($type, $value);
            }

            $buttonLink = $image['button_link'] ?? null;
            if (! empty($buttonLink)) {
                $type  = $buttonLink['type'] ?? '';
                $value = $buttonLink['type'] == 'custom' ? ($buttonLink['value'] ?? '') : ((int) ($buttonLink['value'] ?? 0));

                $images[$index]['button_link']['link'] = $this->handleLink($type, $value);
            }
        }

        return $images;
    }

    /**
     * Handle brands module
     *
     * @param  array  $content
     * @return array
     * @throws Exception
     */
    private function handleBrands(array $content): array
    {
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
     * @param  string  $type
     * @param  mixed  $value
     * @return string
     * @throws Exception
     */
    private function handleLink(string $type, $value): string
    {
        return Link::getInstance()->link($type, $value);
    }

    /**
     * Build product query filters
     *
     * @param  array  $params
     * @return array
     */
    private function buildProductFilters(array $params): array
    {
        $filters = [
            'active'   => 1,
            'per_page' => $params['limit'] ?? 8,
            'page'     => 1,
        ];

        if (isset($params['category_id'])) {
            $filters['category_id'] = $params['category_id'];
        }
        if (isset($params['brand_id'])) {
            $filters['brand_id'] = $params['brand_id'];
        }

        if (! empty($params['sort'])) {
            $sortInfo         = $this->parseSortField($params['sort'], $params['order'] ?? 'desc');
            $filters['sort']  = $sortInfo['field'];
            $filters['order'] = $sortInfo['order'];
        }

        return $filters;
    }

    /**
     * Parse sort field, return mapped field name and sort direction
     *
     * @param  string  $sort
     * @param  string  $defaultOrder
     * @return array
     */
    private function parseSortField(string $sort, string $defaultOrder = 'desc'): array
    {
        $sortParts = explode('_', $sort);
        $sortField = $sortParts[0];
        $sortOrder = $sortParts[1] ?? $defaultOrder;

        $fieldMap = [
            'price'    => 'ps.price',
            'created'  => 'created_at',
            'updated'  => 'updated_at',
            'sales'    => 'sales_count',
            'viewed'   => 'viewed_count',
            'position' => 'position',
        ];

        // If field is not in map or is 'rating' (removed field), use default sales sorting
        if (! isset($fieldMap[$sortField]) || $sortField === 'rating') {
            return [
                'field' => 'sales_count',
                'order' => 'desc',
            ];
        }

        return [
            'field' => $fieldMap[$sortField],
            'order' => $sortOrder,
        ];
    }

    /**
     * Extract ID array from mixed format
     *
     * @param  mixed  $items
     * @return array
     */
    private function extractIds($items): array
    {
        if (empty($items)) {
            return [];
        }

        $ids = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $ids[] = $item['id'];
                } elseif (is_numeric($item)) {
                    $ids[] = $item;
                }
            }
        } elseif (is_numeric($items)) {
            $ids = [$items];
        }

        return $ids;
    }
}
