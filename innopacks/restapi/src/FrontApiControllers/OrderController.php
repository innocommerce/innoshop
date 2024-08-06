<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\JsonResponse;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\OrderRepo;

class OrderController extends BaseController
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $filters = [
            'customer_id' => token_customer_id(),
        ];
        $orders = OrderRepo::getInstance()->list($filters);

        return read_json_success($orders);
    }

    /**
     * Order detail
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        if ($order->customer_id != token_customer_id()) {
            return json_fail('Unauthorized', null, 403);
        }
        $order->load(['items', 'fees']);

        return read_json_success($order);
    }

    /**
     * Order detail
     *
     * @param  int  $number
     * @return JsonResponse
     */
    public function numberShow(int $number): JsonResponse
    {
        $order = OrderRepo::getInstance()->getOrderByNumber($number);
        if ($order->customer_id != token_customer_id()) {
            return json_fail('Unauthorized', null, 403);
        }

        $order->load(['items', 'fees']);

        return read_json_success($order);
    }
}
