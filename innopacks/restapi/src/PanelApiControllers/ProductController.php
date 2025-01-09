<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Resources\ProductSimple;
use Throwable;

class ProductController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters  = $request->all();
        $products = ProductRepo::getInstance()->list($filters);

        return ProductSimple::collection($products);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function names(Request $request): AnonymousResourceCollection
    {
        $products = ProductRepo::getInstance()->getListByProductIDs($request->get('ids'));

        return ProductSimple::collection($products);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $products = ProductRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return ProductSimple::collection($products);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            foreach ($data['products'] as $productData) {
                $product = null;
                $slug    = $productData['slug'] ?? '';
                if ($slug) {
                    $product = ProductRepo::getInstance()->findBySlug($slug);
                }
                if ($product) {
                    ProductRepo::getInstance()->update($product, $productData);
                } else {
                    ProductRepo::getInstance()->create($productData);
                }
            }

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
