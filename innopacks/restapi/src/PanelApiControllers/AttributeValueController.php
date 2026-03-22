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
use InnoShop\Common\Repositories\Attribute\ValueRepo;
use InnoShop\Common\Resources\AttributeValueSimple;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Panel - Attributes')]
class AttributeValueController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('List attribute values')]
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $values  = ValueRepo::getInstance()->all($filters);
        $items   = AttributeValueSimple::collection($values);

        return read_json_success($items);
    }
}
