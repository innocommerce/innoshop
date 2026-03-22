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
}
