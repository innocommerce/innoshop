<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Panel\Repositories\MenuSearchRepo;

class SearchController extends BaseController
{
    /**
     * Search panel menus by keyword.
     */
    public function menus(Request $request): JsonResponse
    {
        $keyword = mb_substr(trim($request->get('keyword', '')), 0, 100);
        $results = MenuSearchRepo::getInstance()->search($keyword);

        return response()->json($results);
    }
}
