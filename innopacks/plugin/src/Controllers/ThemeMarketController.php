<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Controllers;

use Illuminate\Http\Request;
use InnoShop\Plugin\Services\MarketplaceService;

class ThemeMarketController
{
    /**
     * Get search field options for theme market
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        return [
            ['value' => 'all', 'label' => trans('panel/plugin.search_field_all')],
            ['value' => 'name', 'label' => trans('panel/plugin.search_field_name')],
            ['value' => 'author', 'label' => trans('panel/plugin.search_field_author')],
            ['value' => 'description', 'label' => trans('panel/plugin.search_field_description')],
        ];
    }

    /**
     * Get filter button options for theme market
     *
     * @param  array  $categories
     * @return array
     */
    public static function getFilterButtonOptions(array $categories = []): array
    {
        $filters = [
            [
                'name'    => 'tab',
                'label'   => trans('panel/plugin.filter_type'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => 'featured', 'label' => trans('panel/plugin.featured')],
                    ['value' => 'popular', 'label' => trans('panel/plugin.popular')],
                    ['value' => 'recommended', 'label' => trans('panel/plugin.recommended')],
                ],
            ],
        ];

        // Add category filter if categories available
        if (! empty($categories)) {
            $categoryOptions = [['value' => '', 'label' => trans('panel/common.all')]];
            foreach ($categories as $category) {
                $categoryOptions[] = [
                    'value' => $category['slug'] ?? '',
                    'label' => $category['translation']['name'] ?? $category['name'] ?? '',
                ];
            }
            $filters[] = [
                'name'    => 'category',
                'label'   => trans('panel/common.category'),
                'type'    => 'button',
                'options' => $categoryOptions,
            ];
        }

        return $filters;
    }

    /**
     * Get themes market categories and items.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            $categorySlug = $request->get('category');
            $search       = $request->get('search');
            $searchField  = $request->get('search_field', 'all');
            $tab          = $request->get('tab', 'all');

            $marketService = MarketplaceService::getInstance()
                ->setPage($request->get('page', 1))
                ->setPerPage($request->get('per_page', 12));

            // Build query parameters
            // Use parent_slug=themes to filter only themes
            $params = ['parent_slug' => 'themes'];
            if ($categorySlug) {
                $params['category_slug'] = $categorySlug;
            }
            if ($search) {
                $params['search'] = $search;
            }
            if ($searchField && $searchField !== 'all') {
                $params['search_field'] = $searchField;
            }
            if ($tab && $tab !== 'all') {
                $params['tab'] = $tab;
            }

            // Get categories and products
            // Limit categories to 4 for cleaner UI
            $categories = $marketService->getThemeCategories(10);
            $products   = $marketService->getMarketProductsWithParams($params);

            $data = [
                'categories'    => $categories,
                'products'      => $products,
                'searchFields'  => self::getSearchFieldOptions(),
                'filterButtons' => self::getFilterButtonOptions($categories['data'] ?? []),
            ];

            return inno_view('plugin::theme_market.index', $data);
        } catch (\Exception $e) {
            return inno_view('plugin::theme_market.index', [
                'categories'    => ['data' => []],
                'products'      => ['data' => []],
                'searchFields'  => self::getSearchFieldOptions(),
                'filterButtons' => [],
                'error'         => $e->getMessage(),
            ])->with('error', $e->getMessage());
        }
    }

    /**
     * @param  int  $slug
     * @return mixed
     */
    public function show(int $slug): mixed
    {
        try {
            $marketService = MarketplaceService::getInstance();

            $data = [
                'categories' => $marketService->getThemeCategories(),
                'product'    => $marketService->getProductDetail($slug),
            ];

            return inno_view('plugin::theme_market.detail', $data);
        } catch (\Exception $e) {
            return inno_view('plugin::theme_market.detail', [
                'categories' => ['data' => []],
                'product'    => null,
                'error'      => $e->getMessage(),
            ])->with('error', $e->getMessage());
        }
    }
}
