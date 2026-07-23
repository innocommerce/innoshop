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
use InnoShop\Common\Resources\SkuListItem;
use InnoShop\Common\Services\EventTrackingService;
use InnoShop\Common\Services\RequestFilterParser;
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
        $filterParser = new RequestFilterParser;
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
        if (! $product->active) {
            abort(404);
        }

        if ($skuId) {
            $sku = Product\Sku::query()->find($skuId);
        }

        if (empty($sku)) {
            $sku = $product->masterSku;
        }

        $product->increment('viewed');

        // Track product view event
        $eventService = new EventTrackingService;
        $eventService->trackProductView($product->id, request());

        $reviews    = ReviewRepo::getInstance()->getListByProduct($product, 10);
        $customerID = current_customer_id();

        // Structured variant dimensions carrying stable value IDs, consumed
        // by the front-end blade for ID-based SKU matching.
        $variantDimensions = $this->buildVariantDimensions($product);
        $sku->loadMissing([
            'variantValues.translation',
            'variantValues.variant.translation',
            'product.variants',
        ]);

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
            'variant_dimensions'  => $variantDimensions,
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
     * Build the structured variant_dimensions shape for the front-end blade.
     * Each value carries a stable DB id used by JS for ID-based SKU matching,
     * eliminating the positional `variables` index fragility.
     */
    private function buildVariantDimensions(Product $product): array
    {
        $product->loadMissing(['variants.translations', 'variants.values.translations']);

        $out = [];
        foreach ($product->variants as $variant) {
            $names = [];
            foreach ($variant->translations as $t) {
                if ($t->name !== null && $t->name !== '') {
                    $names[$t->locale] = $t->name;
                }
            }

            $values = [];
            foreach ($variant->values as $value) {
                $valueNames = [];
                foreach ($value->translations as $vt) {
                    if ($vt->name !== null && $vt->name !== '') {
                        $valueNames[$vt->locale] = $vt->name;
                    }
                }
                $values[] = [
                    'id'    => (string) $value->id,
                    'image' => $value->image ?? '',
                    'name'  => $valueNames,
                ];
            }

            $out[] = [
                'id'       => (string) $variant->id,
                'is_image' => (bool) $variant->is_image,
                'name'     => $names,
                'values'   => $values,
            ];
        }

        return $out;
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
