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
use InnoShop\Common\Models\OrderReturn;
use InnoShop\Common\Repositories\Order\ItemRepo;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Repositories\OrderReturnRepo;
use InnoShop\Common\Resources\OrderReturnHistory;
use InnoShop\Front\Controllers\BaseController;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Front - Order Returns')]
#[Authenticated]
class OrderReturnController extends BaseController
{
    #[Endpoint('List order returns')]
    public function index(Request $request)
    {
        $filters = $request->all();

        $filters['customer_id'] = token_customer_id();

        $orderReturns = OrderReturnRepo::getInstance()->list($filters);

        return read_json_success($orderReturns);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Get order info for return')]
    #[QueryParam('order_number', type: 'string', required: true)]
    public function orderInfo(Request $request): mixed
    {
        $number = $request->get('order_number');
        if (empty($number)) {
            return read_json_success([]);
        }

        $filters = [
            'number'      => $number,
            'customer_id' => token_customer_id(),
        ];
        $order   = OrderRepo::getInstance()->builder($filters)->firstOrFail();
        $options = ItemRepo::getInstance()->getOptions($order);

        $data = [
            'number'  => $number,
            'order'   => $order,
            'options' => $options,
        ];

        return read_json_success($data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception|Throwable
     */
    #[Endpoint('Create order return')]
    public function store(Request $request): mixed
    {
        $data = $request->all();
        try {
            $data['customer_id'] = token_customer_id();
            $orderReturn         = OrderReturnRepo::getInstance()->create($data);

            return create_json_success($orderReturn);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  OrderReturn  $orderReturn
     * @return mixed
     */
    #[Endpoint('Get order return detail')]
    #[UrlParam('orderReturn', type: 'integer', description: 'Order return ID')]
    public function show(OrderReturn $orderReturn): mixed
    {
        if ($orderReturn->customer_id !== token_customer_id()) {
            return json_fail('Unauthorized', null, 403);
        }

        $data = [
            'order_return' => $orderReturn,
            'histories'    => OrderReturnHistory::collection($orderReturn->histories()->get()),
        ];

        return read_json_success($data);
    }

    /**
     * @param  OrderReturn  $order_return
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('Delete order return')]
    #[UrlParam('order_return', type: 'integer', description: 'Order return ID')]
    public function destroy(OrderReturn $order_return): mixed
    {
        if ($order_return->customer_id !== token_customer_id()) {
            return json_fail('Unauthorized', null, 403);
        }

        $order_return->delete();

        return delete_json_success();
    }
}
