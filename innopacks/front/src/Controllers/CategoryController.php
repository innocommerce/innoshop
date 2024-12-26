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

class CategoryController extends Controller
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters  = $request->all();
        $products = ProductRepo::getInstance()->withActive()->list($filters);

        $data = [
            'products'   => $products,
            'categories' => CategoryRepo::getInstance()->getTwoLevelCategories(),
        ];

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
        $category = CategoryRepo::getInstance()->withActive()->builder(['slug' => $request->slug])->firstOrFail();
        $keyword  = $request->get('keyword');

        return $this->renderShow($category, $keyword, $request);
    }

    /**
     * @param  Category  $category
     * @param  $keyword
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    private function renderShow(Category $category, $keyword, Request $request): mixed
    {
        $categories = CategoryRepo::getInstance()->getTwoLevelCategories();

        $filters = [
            'category_id' => $category->id,
            'keyword'     => $keyword,
            'sort'        => $request->get('sort'),
            'order'       => $request->get('order'),
            'per_page'    => $request->get('per_page'),
        ];
        $products = ProductRepo::getInstance()->getFrontList($filters);

        $data = [
            'slug'           => $category->slug ?? '',
            'category'       => $category,
            'categories'     => $categories,
            'products'       => $products,
            'per_page_items' => CategoryRepo::getInstance()->getPerPageItems(),
        ];

        return inno_view('categories.show', $data)->render();
    }
}
