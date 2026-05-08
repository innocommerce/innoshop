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
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Services\EventTrackingService;
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

        $news = HomeRepo::getInstance()->getHomeArticles();
        $data = [
            'slideshow'       => HomeRepo::getInstance()->getSlideShow(),
            'tab_products'    => $tabProducts,
            'news'            => $news,
            'hot_products'    => $this->getHotProducts(),
            'home_categories' => HomeRepo::getInstance()->getHomeCategories(),
        ];

        $data = fire_hook_filter('home.index.data', $data);

        // Track home page view event
        $eventService = new EventTrackingService;
        $eventService->trackHomeView(request());

        return inno_view('home', $data);
    }

    /**
     * Get hot products from settings, organized by floors.
     *
     * @return array Array of floors: [['name' => 'xxx', 'subtitle' => 'xxx', 'products' => [...]], ...]
     */
    private function getHotProducts(): array
    {
        $hotProductsSetting = system_setting('home_hot_products', '{}');

        if (is_array($hotProductsSetting)) {
            $hotProductsData = $hotProductsSetting;
        } else {
            $hotProductsData = json_decode($hotProductsSetting, true) ?: [];
        }

        if (empty($hotProductsData) || empty($hotProductsData['floors'])) {
            return [];
        }

        $groups = $hotProductsData['floors'];

        $result = [];

        try {
            $allProductIds = HomeRepo::getInstance()->getHomeHotProductIdsOrdered();

            if (empty($allProductIds)) {
                return [];
            }

            $products = ProductRepo::getInstance()->builder(['active' => true])
                ->whereIn('products.id', $allProductIds)
                ->with(['masterSku', 'skus', 'translation'])
                ->get();

            foreach ($groups as $group) {
                if (! isset($group['products']) || ! is_array($group['products']) || empty($group['products'])) {
                    continue;
                }

                $floorProducts = [];
                foreach ($group['products'] as $productId) {
                    $product = $products->firstWhere('id', $productId);
                    if ($product) {
                        $floorProducts[] = HomeRepo::getInstance()->formatProductData($product);
                    }
                }

                if (! empty($floorProducts)) {
                    $result[] = [
                        'name'     => $this->resolveLocaleValue($group['name'] ?? ''),
                        'subtitle' => $this->resolveLocaleValue($group['subtitle'] ?? ''),
                        'products' => $floorProducts,
                    ];
                }
            }

            return [
                'display_mode' => $hotProductsData['display_mode'] ?? 'flat',
                'title_align'  => $hotProductsData['title_align'] ?? 'left',
                'floors'       => $result,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Resolve a locale-aware value: if array (locale→value map), pick current locale;
     * if string, return as-is (backward compatibility).
     */
    private function resolveLocaleValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $currentLocale  = front_locale_code();
            $fallbackLocale = setting_locale_code() ?? 'zh_cn';

            return $value[$currentLocale] ?? $value[$fallbackLocale] ?? reset($value) ?: '';
        }

        return '';
    }
}
