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
use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Front\Traits\FilterSidebarTrait;

class BrandController extends Controller
{
    use FilterSidebarTrait;

    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'brands' => BrandRepo::getInstance()->withActive()->all()->groupBy('first'),
        ];

        return inno_view('brands.index', $data);
    }

    /**
     * @param  Brand  $brand
     * @return mixed
     * @throws Exception
     */
    public function show(Brand $brand): mixed
    {
        return $this->renderShow($brand);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug  = $request->slug;
        $brand = BrandRepo::getInstance()->withActive()->builder(['slug' => $slug])->firstOrFail();

        return $this->renderShow($brand);
    }

    /**
     * Render brand show page with products and filters
     * @param  $brand
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    private function renderShow($brand, ?Request $request = null): mixed
    {
        if (! $request) {
            $request = request();
        }

        // Use RequestFilterParser to handle filter logic
        $filterParser = new \InnoShop\Common\Services\RequestFilterParser;
        $filters      = $filterParser->extractFilters($request, [
            'brand_id',
            'keyword',
            'sort',
            'order',
            'per_page',
            'price_from',
            'price_to',
            'attribute_values',
            'in_stock',
        ]);

        // Add brand specific filter
        $filters['brand_id'] = $brand->id;
        $filters['active']   = true;

        $products = ProductRepo::getInstance()->getFrontList($filters);

        // Use Trait method to get filter sidebar data
        $filterData = $this->getFilterSidebarData($request);

        $data = [
            'brand'          => $brand,
            'products'       => $products,
            'categories'     => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'per_page_items' => CategoryRepo::getInstance()->getPerPageItems(),
        ];

        // Merge filter data
        $data = array_merge($data, $filterData);

        return inno_view('brands.show', $data);
    }
}
