<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use InnoShop\Plugin\Services\MarketplaceService;
use InnoShop\Plugin\Services\PaginatorService;

class ThemeMarketController
{
    /**
     * Get plugins market categories and items.
     *
     * @param  Request  $request
     * @param  PaginatorService  $paginatorService
     * @return mixed
     * @throws ConnectionException
     */
    public function index(Request $request, PaginatorService $paginatorService): mixed
    {
        $categorySlug  = $request->get('category');
        $marketService = MarketplaceService::getInstance();

        if ($categorySlug) {
            $products = $marketService->getMarketProducts($categorySlug);
        } else {
            $products = $marketService->getThemeProducts();
        }
        $paginator = $paginatorService->makePaginator($products['data']);

        $data = [
            'categories' => $marketService->getThemeCategories(),
            'products'   => $paginator,
        ];

        return inno_view('plugin::theme_market.index', $data);
    }

    /**
     * @param  int  $slug
     * @return mixed
     * @throws ConnectionException
     */
    public function show(int $slug): mixed
    {
        $marketService = MarketplaceService::getInstance();
        $data          = [
            'categories' => $marketService->getThemeCategories(),
            'product'    => $marketService->getProductDetail($slug),
        ];

        return inno_view('plugin::theme_market.detail', $data);
    }
}
