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
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Resources\BrandName;
use InnoShop\Common\Resources\BrandSimple;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Brands')]
class BrandController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('List brands')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $categories = BrandRepo::getInstance()->builder($filters)->paginate($perPage);

        return BrandSimple::collection($categories);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get brands by IDs')]
    #[QueryParam('ids', 'string', required: true, example: '1,2,3')]
    public function names(Request $request): AnonymousResourceCollection
    {
        $brands = BrandRepo::getInstance()->getListByBrandIDs($request->get('ids'));

        return BrandName::collection($brands);
    }

    /**
     * Fuzzy search for auto complete.
     * /api/panel/brands/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('Autocomplete brands')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $categories = BrandRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return BrandSimple::collection($categories);
    }

    /**
     * Get single brand by ID with full details.
     * GET /api/panel/brands/{id}
     *
     * @param  int  $id
     * @return mixed
     */
    #[Endpoint('Get brand detail')]
    #[UrlParam('id', 'integer', description: 'Brand ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $brand = BrandRepo::getInstance()->builder()->with('translations')->findOrFail($id);

            $data = [
                'id'           => $brand->id,
                'name'         => $brand->name,
                'slug'         => $brand->slug,
                'first'        => $brand->first,
                'logo'         => $brand->logo,
                'position'     => $brand->position,
                'active'       => (bool) $brand->active,
                'url'          => $brand->url,
                'translations' => $brand->translations->map(function ($t) {
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
     * Create new brand.
     * POST /api/panel/brands
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Create brand')]
    public function store(Request $request): mixed
    {
        try {
            $data  = $request->all();
            $brand = BrandRepo::getInstance()->create($data);

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Update brand by ID (full update).
     * PUT /api/panel/brands/{id}
     *
     * @param  Request  $request
     * @param  int  $id
     * @return mixed
     */
    #[Endpoint('Update brand')]
    #[UrlParam('id', 'integer', description: 'Brand ID', example: 1)]
    public function update(Request $request, int $id): mixed
    {
        try {
            $brand = BrandRepo::getInstance()->builder()->findOrFail($id);
            $data  = $request->all();
            BrandRepo::getInstance()->update($brand, $data);

            return json_success('Brand updated successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update brand by ID.
     * PATCH /api/panel/brands/{id}
     *
     * @param  Request  $request
     * @param  int  $id
     * @return mixed
     */
    #[Endpoint('Partial update brand')]
    #[UrlParam('id', 'integer', description: 'Brand ID', example: 1)]
    public function patch(Request $request, int $id): mixed
    {
        try {
            $brand = BrandRepo::getInstance()->builder()->findOrFail($id);
            $data  = $request->all();
            BrandRepo::getInstance()->update($brand, $data);

            return update_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Delete brand by ID.
     * DELETE /api/panel/brands/{id}
     *
     * @param  int  $id
     * @return mixed
     */
    #[Endpoint('Delete brand')]
    #[UrlParam('id', 'integer', description: 'Brand ID', example: 1)]
    public function destroy(int $id): mixed
    {
        try {
            $brand = BrandRepo::getInstance()->builder()->findOrFail($id);
            BrandRepo::getInstance()->destroy($brand);

            return json_success('Brand deleted successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
