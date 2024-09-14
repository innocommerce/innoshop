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
use InnoShop\Common\Repositories\Attribute\ValueRepo;
use InnoShop\Common\Resources\AttributeValueSimple;

class AttributeValueController extends BaseController
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $values  = ValueRepo::getInstance()->all($filters);
        $items   = AttributeValueSimple::collection($values);

        return read_json_success($items);
    }
}
