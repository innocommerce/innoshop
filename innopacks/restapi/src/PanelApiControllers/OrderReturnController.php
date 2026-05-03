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
use InnoShop\Common\Repositories\OrderReturnRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Order Returns')]
class OrderReturnController extends BaseController
{
    #[Endpoint('List order returns')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): mixed
    {
        try {
            $filters      = $request->all();
            $perPage      = $request->get('per_page', 15);
            $orderReturns = OrderReturnRepo::getInstance()->builder($filters)->paginate($perPage);

            return read_json_success($orderReturns);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Get order return detail')]
    #[UrlParam('id', 'integer', description: 'Order Return ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $orderReturn = OrderReturnRepo::getInstance()->builder()->findOrFail($id);

            return read_json_success($orderReturn);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Update order return')]
    #[UrlParam('id', 'integer', description: 'Order Return ID', example: 1)]
    public function update(Request $request, int $id): mixed
    {
        try {
            $orderReturn = OrderReturnRepo::getInstance()->builder()->findOrFail($id);
            OrderReturnRepo::getInstance()->update($orderReturn, $request->all());

            return update_json_success($orderReturn->fresh());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
