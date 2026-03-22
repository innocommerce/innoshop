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
use InnoShop\Common\Repositories\CurrencyRepo;
use InnoShop\Common\Resources\CurrencyItem;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Panel - Currencies')]
class CurrencyController extends BaseController
{
    /**
     * Get all enabled currencies for calculator
     *
     * @return JsonResponse
     */
    #[Endpoint('List currencies')]
    public function index(): JsonResponse
    {
        $currencies = CurrencyRepo::getInstance()->withActive()->builder()->get();

        return json_success('获取成功', CurrencyItem::collection($currencies)->toArray(request()));
    }
}
