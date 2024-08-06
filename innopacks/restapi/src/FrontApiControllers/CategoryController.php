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
use InnoShop\Common\Repositories\Category\TreeRepo;
use InnoShop\Common\Repositories\CategoryRepo;

class CategoryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();

        $categories = CategoryRepo::getInstance()->withActive()->builder($filters)->paginate();

        return read_json_success($categories);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function tree(): JsonResponse
    {
        $categoryTree = TreeRepo::getInstance()->getCategoryTree();

        return read_json_success($categoryTree);
    }
}
