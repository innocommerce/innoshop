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
use InnoShop\Common\Repositories\Category\TreeRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Resources\CategoryFrontend;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Front - Categories')]
class CategoryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('List categories')]
    #[Unauthenticated]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $categories = CategoryRepo::getInstance()->withActive()->builder($filters)->paginate($perPage);

        return CategoryFrontend::collection($categories);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('Get category tree')]
    #[Unauthenticated]
    public function tree(): mixed
    {
        $categoryTree = TreeRepo::getInstance()->getCategoryTree();

        return read_json_success($categoryTree);
    }
}
