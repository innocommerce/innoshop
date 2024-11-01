<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Resources\CategorySimple;
use InnoShop\RestAPI\FrontApiControllers\BaseController;

class CategoryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $categories = CategoryRepo::getInstance()->withActive()->builder($filters)->paginate($perPage);

        return CategorySimple::collection($categories);
    }
}
