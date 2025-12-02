<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Front\Repositories\HomeRepo;

class HomeController extends Controller
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function index(): mixed
    {
        $bestSeller  = ProductRepo::getInstance()->getBestSellerProducts();
        $newArrivals = ProductRepo::getInstance()->getLatestProducts();
        $tabProducts = [
            ['tab_title' => trans('front/home.bestseller'), 'products' => $bestSeller],
            ['tab_title' => trans('front/home.new_arrival'), 'products' => $newArrivals],
        ];

        $news = ArticleRepo::getInstance()->getLatestArticles();
        $data = [
            'slideshow'       => HomeRepo::getInstance()->getSlideShow(),
            'tab_products'    => $tabProducts,
            'news'            => $news,
            'hot_products'    => $this->getHotProducts(),
            'home_categories' => HomeRepo::getInstance()->getHomeCategories(),
        ];

        $data = fire_hook_filter('home.index.data', $data);

        return inno_view('home', $data);
    }

    /**
     * Get hot products from settings, organized by category
     * Returns array of category groups with their products
     *
     * @return array Array of category groups: [['category_id' => 1, 'category_name' => 'xxx', 'products' => [...]], ...]
     */
    private function getHotProducts(): array
    {
        $hotProductsSetting = system_setting('home_hot_products', '{}');

        // Handle both string and array return types from system_setting
        if (is_array($hotProductsSetting)) {
            $hotProductsData = $hotProductsSetting;
        } else {
            $hotProductsData = json_decode($hotProductsSetting, true) ?: [];
        }

        if (empty($hotProductsData) || ! isset($hotProductsData['categories']) || ! is_array($hotProductsData['categories'])) {
            return [];
        }

        $categoryGroups = [];

        try {
            $allProductIds = [];
            foreach ($hotProductsData['categories'] as $categoryGroup) {
                if (isset($categoryGroup['products']) && is_array($categoryGroup['products'])) {
                    $allProductIds = array_merge($allProductIds, $categoryGroup['products']);
                }
            }

            if (empty($allProductIds)) {
                return [];
            }

            $products = ProductRepo::getInstance()->builder(['active' => true])
                ->whereIn('products.id', array_unique($allProductIds))
                ->with(['masterSku', 'skus', 'translation'])
                ->get();

            // 获取所有分类ID，用于批量获取分类名称
            $categoryIds = [];
            foreach ($hotProductsData['categories'] as $categoryGroup) {
                if (isset($categoryGroup['category_id'])) {
                    $categoryIds[] = $categoryGroup['category_id'];
                }
            }

            // 批量获取分类信息
            $categories = [];
            if (! empty($categoryIds)) {
                $categoryModels = CategoryRepo::getInstance()
                    ->builder(['category_ids' => array_unique($categoryIds)])
                    ->with(['translation'])
                    ->get();
                foreach ($categoryModels as $category) {
                    $categories[$category->id] = $category->fallbackName();
                }
            }

            foreach ($hotProductsData['categories'] as $categoryGroup) {
                if (! isset($categoryGroup['products']) || ! is_array($categoryGroup['products']) || empty($categoryGroup['products'])) {
                    continue;
                }

                $categoryId = $categoryGroup['category_id'] ?? 0;
                // 优先使用从数据库查询的多语言分类名称，如果不存在则使用配置中的名称
                $categoryName = $categories[$categoryId] ?? ($categoryGroup['category_name'] ?? "分类 ID: {$categoryId}");

                $categoryProducts = [];
                foreach ($categoryGroup['products'] as $productId) {
                    $product = $products->firstWhere('id', $productId);
                    if ($product) {
                        $categoryProducts[] = HomeRepo::getInstance()->formatProductData($product);
                    }
                }

                if (! empty($categoryProducts)) {
                    $categoryGroups[] = [
                        'category_id'   => $categoryId,
                        'category_name' => $categoryName,
                        'products'      => $categoryProducts,
                    ];
                }
            }

            return $categoryGroups;
        } catch (\Exception $e) {
            return [];
        }
    }
}
