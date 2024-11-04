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
use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\OrderRepo;

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
     * @param  Order  $order
     * @return mixed
     */
    public function show(Order $order): mixed
    {
        $order->load(['items', 'fees']);
        $data = [
            'order' => $order,
        ];

        return inno_view('account.order_info', $data);
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
        $order->load(['items', 'fees']);
        $data = [
            'order' => $order,
        ];

        return inno_view('account.order_info', $data);
    }
}
