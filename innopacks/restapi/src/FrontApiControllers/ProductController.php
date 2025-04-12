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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\Product\FilterRepo;
use InnoShop\Common\Repositories\Product\SkuRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Repositories\ReviewRepo;
use InnoShop\Common\Resources\ProductDetail;
use InnoShop\Common\Resources\ProductSimple;
use InnoShop\Common\Resources\ReviewListItem;
use InnoShop\Common\Resources\SkuSimple;

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

        $sort  = $filters['sort']  ?? 'id';
        $order = $filters['order'] ?? 'desc';

        $builder = ProductRepo::getInstance()->withActive()->builder($filters);

        $productBuilder = clone $builder;

        if ($sort == 'price') {
            $productBuilder->select(['products.*', 'ps.price']);
            $productBuilder->join('product_skus as ps', function ($query) {
                $query->on('ps.product_id', '=', 'products.id')
                    ->where('is_default', true);
            });
            $productBuilder->orderBy('ps.price', $order);
        } elseif (in_array($sort, ['id', 'sales', 'viewed'])) {
            $productBuilder->orderBy($sort, $order);
        }

        $products = $productBuilder->paginate($perPage);

        return ProductSimple::collection($products)->additional([
            'filters' => FilterRepo::getInstance($builder)->getCurrentFilters(),
        ]);
    }

    /**
     * @param  Product  $product
     * @return mixed
     */
    public function show(Product $product): mixed
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

    /**
     * Summary of searchSku
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function skuList(Request $request)
    {
        $keyword = $request->get('keyword');
        $skus    = SkuRepo::getInstance()->searchByKeyword($keyword);

        return read_json_success(SkuSimple::collection($skus));
    }
}
