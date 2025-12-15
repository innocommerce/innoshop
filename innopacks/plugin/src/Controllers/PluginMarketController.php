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

class PluginMarketController
{
    /**
     * Get plugins market categories and items.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            $categorySlug = $request->get('category');
            $search       = $request->get('search');
            $tab          = $request->get('tab', 'all');

            $marketService = MarketplaceService::getInstance()
                ->setPage($request->get('page', 1))
                ->setPerPage($request->get('per_page', 12));

            // Build query parameters
            // Use parent_slug=plugins to filter only plugins
            // The server-side MarketplaceService will handle the conversion
            $params = ['parent_slug' => 'plugins'];
            if ($categorySlug) {
                $params['category_slug'] = $categorySlug;
            }
            if ($search) {
                $params['search'] = $search;
            }
            if ($tab && $tab !== 'all') {
                $params['tab'] = $tab;
            }

            // Always use getMarketProductsWithParams to ensure type filtering
            $products = $marketService->getMarketProductsWithParams($params);

            $data = [
                'categories' => $marketService->getPluginCategories(),
                'products'   => $products,
            ];

            return inno_view('plugin::plugin_market.index', $data);
        } catch (\Exception $e) {
            return inno_view('plugin::plugin_market.index', [
                'categories' => ['data' => []],
                'products'   => ['data' => []],
                'error'      => $e->getMessage(),
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
                'categories' => $marketService->getPluginCategories(),
                'product'    => $marketService->getProductDetail($slug),
            ];

            return inno_view('plugin::plugin_market.detail', $data);
        } catch (\Exception $e) {
            return inno_view('plugin::plugin_market.detail', [
                'categories' => ['data' => []],
                'product'    => null,
                'error'      => $e->getMessage(),
            ])->with('error', $e->getMessage());
        }
    }
}
