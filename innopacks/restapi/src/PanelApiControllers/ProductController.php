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
use InnoShop\RestAPI\Services\ProductImportService;
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
                $spuCode = $productData['spu_code'] ?? '';
                if (empty($spuCode)) {
                    throw new Exception('Empty SPU code!');
                }

                $product = ProductRepo::getInstance()->findBySpuCode($spuCode);
                ProductImportService::getInstance()->import($productData, $product);
            }

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  string  $spuCode
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request, string $spuCode): JsonResponse
    {
        try {
            $data    = $request->all();
            $product = ProductRepo::getInstance()->findBySpuCode($spuCode);
            if (! $product) {
                throw new Exception('Product not found!');
            }

            ProductImportService::getInstance()->import($data, $product);

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  string  $spuCode
     * @return JsonResponse
     * @throws Throwable
     */
    public function patch(Request $request, string $spuCode): JsonResponse
    {
        try {
            $data    = $request->all();
            $product = ProductRepo::getInstance()->findBySpuCode($spuCode);
            if (! $product) {
                throw new Exception('Product not found!');
            }

            $data['spu_code'] = $spuCode;
            ProductImportService::getInstance()->patch($product, $data);

            return update_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
