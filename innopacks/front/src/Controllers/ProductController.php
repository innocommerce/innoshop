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
use InnoShop\Front\Traits\FilterSidebarTrait;

class ProductController extends Controller
{
    use FilterSidebarTrait;

    /**
     * Product list page with filter support
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        // Use RequestFilterParser to extract filter conditions
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

        // Get product list
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

        $product->load([
            'productOptions' => function ($query) {
                $query->join('options', 'product_options.option_id', '=', 'options.id')
                    ->orderBy('options.position');
            },
            'productOptionValues' => function ($query) {
                $query->join('option_values', 'product_option_values.option_value_id', '=', 'option_values.id')
                    ->orderBy('option_values.position');
            },
        ]);
        $productOptions      = $product->productOptions;
        $productOptionValues = $product->productOptionValues;

        $data = [
            'product'             => $product,
            'sku'                 => (new SkuListItem($sku))->jsonSerialize(),
            'skus'                => SkuListItem::collection($product->skus)->jsonSerialize(),
            'variants'            => $variables,
            'attributes'          => $product->groupedAttributes(),
            'reviews'             => $reviews,
            'reviewed'            => ReviewRepo::productReviewed($customerID, $product->id),
            'related'             => $product->relationProducts,
            'bundle_items'        => ProductRepo::getInstance()->getBundleItems($product),
            'productOptions'      => $productOptions,
            'productOptionValues' => $productOptionValues,
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

        $html = view('products.components._review_list', [
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
