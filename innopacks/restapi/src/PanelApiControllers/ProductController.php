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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Repositories\Product\SkuRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Resources\ProductSimple;
use InnoShop\Common\Resources\SkuSimple;
use InnoShop\RestAPI\Services\ProductImportService;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Panel - Products')]
class ProductController extends BaseController
{
    /**
     * List products.
     * GET /api/panel/products
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('List products')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    #[QueryParam('keyword', 'string', required: false)]
    #[QueryParam('category_id', 'integer', required: false)]
    public function index(Request $request): mixed
    {
        $filters  = $request->all();
        $products = ProductRepo::getInstance()->list($filters);

        return ProductSimple::collection($products);
    }

    /**
     * Get products by IDs.
     * GET /api/panel/products/names?ids=1,2,3
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get products by IDs')]
    #[QueryParam('ids', 'string', description: 'Comma-separated product IDs', required: true, example: '1,2,3')]
    public function names(Request $request): AnonymousResourceCollection
    {
        $products = ProductRepo::getInstance()->getListByProductIDs($request->get('ids'));

        return ProductSimple::collection($products);
    }

    /**
     * Autocomplete by keyword.
     * GET /api/panel/products/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('Autocomplete products')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $products = ProductRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return ProductSimple::collection($products);
    }

    /**
     * SKU autocomplete by keyword.
     * GET /api/panel/products/sku_autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('Autocomplete SKUs')]
    #[QueryParam('keyword', 'string', required: false)]
    #[QueryParam('limit', 'integer', required: false, example: 10)]
    public function skuAutocomplete(Request $request): AnonymousResourceCollection
    {
        $keyword = $request->get('keyword') ?? '';
        $limit   = $request->get('limit', 10);

        $skus = SkuRepo::getInstance()->searchByKeyword($keyword, $limit);

        return SkuSimple::collection($skus);
    }

    /**
     * Get single product by ID with full details.
     * GET /api/panel/products/{id}
     *
     * @param  int  $id
     * @return mixed
     */
    #[Endpoint('Get product detail')]
    #[UrlParam('id', 'integer', description: 'Product ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $product = ProductRepo::getInstance()->builder()->with('translations')->findOrFail($id);

            $sku  = $product->masterSku;
            $data = [
                'id'           => $product->id,
                'spu_code'     => $product->spu_code,
                'slug'         => $product->slug,
                'brand_id'     => $product->brand_id,
                'price'        => $product->price,
                'images'       => $product->images,
                'active'       => (bool) $product->active,
                'position'     => $product->position,
                'translations' => $product->translations->map(function ($t) {
                    return [
                        'locale'           => $t->locale,
                        'name'             => $t->name,
                        'summary'          => $t->summary,
                        'content'          => $t->content,
                        'meta_title'       => $t->meta_title,
                        'meta_description' => $t->meta_description,
                        'meta_keywords'    => $t->meta_keywords,
                    ];
                }),
            ];

            return json_success('Success', $data);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Create new product.
     * POST /api/panel/products
     *
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create product')]
    public function store(Request $request): mixed
    {
        try {
            $data = $request->all();
            $this->validateSkus($data);

            $product = ProductImportService::getInstance()->import($data);

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Update product by ID (full update).
     * PUT /api/panel/products/{id}
     *
     * @param  Request  $request
     * @param  int  $id
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Update product')]
    #[UrlParam('id', 'integer', description: 'Product ID', example: 1)]
    public function update(Request $request, int $id): mixed
    {
        try {
            $product = ProductRepo::getInstance()->builder()->findOrFail($id);
            $data    = $request->all();
            $this->validateSkus($data);

            ProductImportService::getInstance()->import($data, $product);

            return json_success('Product updated successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update product by ID.
     * PATCH /api/panel/products/{id}
     *
     * @param  Request  $request
     * @param  string  $spuCode
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Partial update product')]
    #[UrlParam('spuCode', 'string', description: 'Product SPU code', example: 'SPU-001')]
    public function patch(Request $request, string $spuCode): mixed
    {
        try {
            $product = ProductRepo::getInstance()->builder()->findOrFail($spuCode);
            $data    = $request->all();

            ProductImportService::getInstance()->patch($product, $data);

            return update_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Delete product by ID.
     * DELETE /api/panel/products/{id}
     *
     * @param  int  $id
     * @return mixed
     */
    #[Endpoint('Delete product')]
    #[UrlParam('id', 'integer', description: 'Product ID', example: 1)]
    public function destroy(int $id): mixed
    {
        try {
            $product = ProductRepo::getInstance()->builder()->findOrFail($id);
            ProductRepo::getInstance()->destroy($product);

            return json_success('Product deleted successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Bulk import products.
     * POST /api/panel/products/import
     *
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Bulk import products')]
    public function import(Request $request): mixed
    {
        try {
            $data = $request->all();
            foreach ($data['products'] as $productData) {
                $product = null;
                $spuCode = $productData['spu_code'] ?? '';
                if (empty($spuCode)) {
                    throw new Exception('Empty SPU code!');
                }

                $this->validateSkus($productData);

                $product = ProductRepo::getInstance()->findBySpuCode($spuCode);
                ProductImportService::getInstance()->import($productData, $product);
            }

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Validate that skus field is present and is an array.
     *
     * @param  array  $data
     * @throws Exception
     */
    private function validateSkus(array $data): void
    {
        if (! isset($data['skus'])) {
            throw new Exception('The skus field is required.');
        }
        if (! is_array($data['skus'])) {
            throw new Exception('The skus field must be an array.');
        }
    }
}
