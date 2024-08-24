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

class BrandController extends Controller
{
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
     * @param  $brand
     * @return mixed
     * @throws Exception
     */
    private function renderShow($brand): mixed
    {
        $products = ProductRepo::getInstance()->list(['active' => true, 'brand_id' => $brand->id]);

        $data = [
            'brand'      => $brand,
            'products'   => $products,
            'categories' => CategoryRepo::getInstance()->getTwoLevelCategories(),
        ];

        return inno_view('brands.show', $data);
    }
}
