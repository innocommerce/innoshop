<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Resources\OrderItemSimple;
use InnoShop\Common\Services\CartService;
use Throwable;

class OrderController extends Controller
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        $filters['customer_id'] = current_customer_id();

        $orders = OrderRepo::getInstance()->list($filters);

        $data = [
            'orders'          => $orders,
            'filter_statuses' => OrderRepo::getInstance()->getFilterStatuses(),
        ];

        return inno_view('account.order_index', $data);
    }

    /**
     * Order detail
     *
     * @param  int  $number
     * @return mixed
     */
    public function numberShow(int $number): mixed
    {
        $order = OrderRepo::getInstance()->getOrderByNumber($number);

        if ($order->customer_id !== current_customer_id()) {
            abort(403, 'Unauthorized access to order details');
        }

        $order->load(['items', 'fees']);
        $data = [
            'order'       => $order,
            'order_items' => OrderItemSimple::collection($order->items)->jsonSerialize(),
        ];

        return inno_view('account.order_info', $data);
    }

    /**
     * Order detail
     *
     * @param  int  $number
     * @return mixed
     * @throws Throwable
     */
    public function recart(int $number): mixed
    {
        $order = OrderRepo::getInstance()->getOrderByNumber($number);

        if ($order->customer_id !== current_customer_id()) {
            abort(403, 'Unauthorized access to order');
        }

        foreach ($order->items as $item) {
            CartService::getInstance()->addCart([
                'sku_code' => $item->product_sku,
                'quantity' => $item->quantity,
            ]);
        }

        return create_json_success();
    }
}
