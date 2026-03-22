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
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Resources\CatalogName;
use InnoShop\Panel\Requests\CatalogRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Panel - Catalogs')]
class CatalogController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('List catalogs')]
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        return CatalogRepo::getInstance()->list($filters);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get catalogs by IDs')]
    #[QueryParam('ids', 'string', required: true)]
    public function names(Request $request): AnonymousResourceCollection
    {
        $catalogs = CatalogRepo::getInstance()->getListByCatalogIDs($request->get('ids'));

        return CatalogName::collection($catalogs);
    }

    /**
     * @param  CatalogRequest  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create catalog')]
    public function store(CatalogRequest $request): mixed
    {
        try {
            $data    = $request->all();
            $catalog = CatalogRepo::getInstance()->create($data);

            return json_success(common_trans('base.updated_success'), $catalog);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  CatalogRequest  $request
     * @param  Catalog  $catalog
     * @return mixed
     */
    #[Endpoint('Update catalog')]
    #[UrlParam('catalog', 'integer', description: 'Catalog ID')]
    public function update(CatalogRequest $request, Catalog $catalog): mixed
    {
        try {
            $data = $request->all();
            CatalogRepo::getInstance()->update($catalog, $data);

            return json_success(common_trans('base.updated_success'), $catalog);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update a catalog.
     * PATCH /api/panel/catalogs/{catalog}
     *
     * @param  CatalogRequest  $request
     * @param  Catalog  $catalog
     * @return mixed
     */
    #[Endpoint('Partial update catalog')]
    #[UrlParam('catalog', 'integer', description: 'Catalog ID')]
    public function patch(CatalogRequest $request, Catalog $catalog): mixed
    {
        try {
            $data = $request->validated();
            CatalogRepo::getInstance()->patch($catalog, $data);

            return update_json_success($catalog);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Catalog  $catalog
     * @return mixed
     */
    #[Endpoint('Delete catalog')]
    #[UrlParam('catalog', 'integer', description: 'Catalog ID')]
    public function destroy(Catalog $catalog): mixed
    {
        try {
            CatalogRepo::getInstance()->destroy($catalog);

            return json_success(common_trans('base.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Fuzzy search for auto complete.
     * /api/panel/catalogs/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Autocomplete catalogs')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $catalogs = CatalogRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return CatalogName::collection($catalogs);
    }
}
