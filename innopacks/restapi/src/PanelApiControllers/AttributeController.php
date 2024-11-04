<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\AttributeRepo;
use InnoShop\Common\Resources\AttributeSimple;

class AttributeController extends BaseController
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters    = $request->all();
        $attributes = AttributeRepo::getInstance()->all($filters);
        $items      = AttributeSimple::collection($attributes);

        return read_json_success($items);
    }
}
