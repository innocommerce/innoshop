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
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Product;
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
     */
    public function show(Request $request, Category $category): mixed
    {
        $slug    = $category->slug;
        $keyword = $request->get('keyword');

        return $this->renderShow($slug, $keyword);
    }

    /**
     * Display the product list under the current category
     *
     * @param  Request  $request
     * @return mixed
     */
    public function slugShow(Request $request): mixed
    {
        $slug    = $request->slug;
        $keyword = $request->get('keyword');

        return $this->renderShow($slug, $keyword);
    }

    /**
     * @param  $slug
     * @param  $keyword
     * @return mixed
     */
    private function renderShow($slug, $keyword): mixed
    {
        $cacheKey = cache_key('category', ['slug' => $slug, 'keyword' => $keyword]);

        return Cache::remember($cacheKey, 0, function () use ($slug, $keyword) {
            $category   = CategoryRepo::getInstance()->withActive()->builder(['slug' => $slug])->firstOrFail();
            $categories = CategoryRepo::getInstance()->getTwoLevelCategories();

            $filters = [
                'category_id' => $category->id,
                'keyword'     => $keyword,
            ];
            $products = ProductRepo::getInstance()->withActive()->builder($filters)->paginate();

            $data = [
                'slug'       => $slug,
                'category'   => $category,
                'categories' => $categories,
                'products'   => $products,
            ];

            return inno_view('categories.show', $data)->render();
        });
    }
}
