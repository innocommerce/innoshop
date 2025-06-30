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
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Repositories\ReviewRepo;
use InnoShop\Common\Resources\ProductVariable;
use InnoShop\Common\Resources\SkuListItem;

class ProductController extends Controller
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
            'products'       => $products,
            'categories'     => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'per_page_items' => CategoryRepo::getInstance()->getPerPageItems(),
        ];

        return inno_view('products.index', $data);
    }

    /**
     * @param  Request  $request
     * @param  Product  $product
     * @return mixed
     */
    public function show(Request $request, Product $product): mixed
    {
        if (! $product->active) {
            abort(404);
        }

        $skuId = $request->get('sku_id');

        return $this->renderShow($product, $skuId);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug    = $request->slug;
        $product = ProductRepo::getInstance()->withActive()->builder(['slug' => $slug])->firstOrFail();

        $skuId = $request->get('sku_id');

        return $this->renderShow($product, $skuId);
    }

    /**
     * @param  Product  $product
     * @param  $skuId
     * @return mixed
     */
    private function renderShow(Product $product, $skuId): mixed
    {
        if ($skuId) {
            $sku = Product\Sku::query()->find($skuId);
        }

        if (empty($sku)) {
            $sku = $product->masterSku;
        }

        $product->increment('viewed');
        $reviews    = ReviewRepo::getInstance()->getListByProduct($product, 10);
        $customerID = current_customer_id();
        $variables  = ProductVariable::collection($product->variables)->jsonSerialize();

        $data = [
            'product'      => $product,
            'sku'          => (new SkuListItem($sku))->jsonSerialize(),
            'skus'         => SkuListItem::collection($product->skus)->jsonSerialize(),
            'variants'     => $variables,
            'attributes'   => $product->groupedAttributes(),
            'reviews'      => $reviews,
            'reviewed'     => ReviewRepo::productReviewed($customerID, $product->id),
            'related'      => $product->relationProducts,
            'bundle_items' => ProductRepo::getInstance()->getBundleItems($product),
        ];

        return inno_view('products.show', $data);
    }

    /**
     * @param  Request  $request
     * @param  Product  $product
     * @return mixed
     */
    public function reviews(Request $request, Product $product): mixed
    {
        $page    = $request->get('page', 1);
        $reviews = ReviewRepo::getInstance()->getListByProduct($product, 10, $page);

        $html = view('products._review_list', [
            'reviews' => $reviews,
        ])->render();

        return response()->json([
            'success' => true,
            'data'    => [
                'html'     => $html,
                'has_more' => $reviews->hasMorePages(),
            ],
        ]);
    }
}
