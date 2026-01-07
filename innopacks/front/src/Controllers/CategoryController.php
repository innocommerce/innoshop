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
use InnoShop\Common\Models\Category;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Front\Traits\FilterSidebarTrait;

class CategoryController extends Controller
{
    use FilterSidebarTrait;

    /**
     * Category index page with product list and filters
     * If keyword is provided, search categories by name and display category list
     * Otherwise, display product list
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $keyword = $request->get('keyword');

        // If keyword is provided, search categories by name
        if ($keyword) {
            $filters = [
                'keyword' => $keyword,
                'active'  => true,
            ];

            $categories = CategoryRepo::getInstance()
                ->builder($filters)
                ->orderBy('position')
                ->paginate(12);

            $data = [
                'categories' => $categories,
                'keyword'    => $keyword,
            ];

            return inno_view('categories.index', $data);
        }

        // Otherwise, display product list (original behavior)
        $filterParser = new \InnoShop\Common\Services\RequestFilterParser;
        $filters      = $filterParser->extractFilters($request, [
            'keyword',
            'sort',
            'order',
            'per_page',
            'price_from',
            'price_to',
            'brand_ids',
            'attribute_values',
            'in_stock',
        ]);

        $products = ProductRepo::getInstance()->getFrontList($filters);

        // Use Trait method to get filter sidebar data
        $filterData = $this->getFilterSidebarData($request);

        $data = [
            'products'       => $products,
            'categories'     => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'per_page_items' => CategoryRepo::getInstance()->getPerPageItems(),
        ];

        // Merge filter data
        $data = array_merge($data, $filterData);

        return inno_view('products.index', $data);
    }

    /**
     * Display the product list under the current category
     *
     * @param  Request  $request
     * @param  Category  $category
     * @return mixed
     * @throws Exception
     */
    public function show(Request $request, Category $category): mixed
    {
        $category->load('activeChildren');
        $keyword = $request->get('keyword');

        return $this->renderShow($category, $keyword, $request);
    }

    /**
     * Display the product list under the current category
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $category = CategoryRepo::getInstance()->withActive()->builder(['slug' => $request->slug])->with('activeChildren')->firstOrFail();
        $keyword  = $request->get('keyword');

        return $this->renderShow($category, $keyword, $request);
    }

    /**
     * Render category show page with products and filters
     * @param  Category  $category
     * @param  $keyword
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    private function renderShow(Category $category, $keyword, Request $request): mixed
    {
        if (! $category->active) {
            abort(404);
        }

        $categories = CategoryRepo::getInstance()->getTwoLevelCategories();

        // Use RequestFilterParser to handle filter logic
        $filterParser = new \InnoShop\Common\Services\RequestFilterParser;
        $filters      = $filterParser->extractFilters($request, [
            'keyword',
            'sort',
            'order',
            'per_page',
            'price_from',
            'price_to',
            'brand_ids',
            'attribute_values',
            'in_stock',
        ]);

        // Add category specific filters
        $filters['category_id'] = $category->id;
        if ($keyword) {
            $filters['keyword'] = $keyword;
        }

        $products = ProductRepo::getInstance()->getFrontList($filters);

        // Use Trait method to get filter sidebar data
        $filterData = $this->getFilterSidebarData($request);

        $data = [
            'slug'           => $category->slug ?? '',
            'category'       => $category,
            'categories'     => $categories,
            'products'       => $products,
            'per_page_items' => CategoryRepo::getInstance()->getPerPageItems(),
        ];

        // Merge filter data
        $data = array_merge($data, $filterData);

        return inno_view('categories.show', $data)->render();
    }
}
