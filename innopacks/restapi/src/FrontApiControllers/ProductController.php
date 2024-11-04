<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\Product\FilterRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Repositories\ReviewRepo;
use InnoShop\Common\Resources\ProductDetail;
use InnoShop\Common\Resources\ProductSimple;
use InnoShop\Common\Resources\ReviewListItem;

class ProductController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $builder  = ProductRepo::getInstance()->withActive()->builder($filters);
        $products = $builder->paginate($perPage);

        return ProductSimple::collection($products)->additional([
            'filters' => FilterRepo::getInstance($builder)->getCurrentFilters(),
        ]);
    }

    /**
     * @param  Product  $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $single = new ProductDetail($product);

        return read_json_success($single);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function filters(Request $request): mixed
    {
        $builder = ProductRepo::getInstance()->builder($request->all());

        return FilterRepo::getInstance($builder)->getCurrentFilters();
    }

    /**
     * @param  Product  $product
     * @return AnonymousResourceCollection
     */
    public function productReviews(Product $product): AnonymousResourceCollection
    {
        $filters = [
            'product_id' => $product->id,
        ];

        $list = ReviewRepo::getInstance()->builder($filters)->paginate();

        return ReviewListItem::collection($list);
    }
}
