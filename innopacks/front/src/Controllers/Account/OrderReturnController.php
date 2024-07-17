<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use Illuminate\Http\Request;
use InnoShop\Common\Models\OrderReturn;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Repositories\OrderReturnRepo;
use InnoShop\Front\Controllers\BaseController;

class OrderReturnController extends BaseController
{
    public function index(Request $request)
    {
        $data = [
            'order_returns' => OrderReturnRepo::getInstance()->list(),
        ];

        return inno_view('account.order_return_index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function create(Request $request): mixed
    {
        $number  = $request->get('order_number');
        $filters = [
            'number'      => $number,
            'customer_id' => current_customer_id(),
        ];
        $data = [
            'number' => $number,
            'order'  => OrderRepo::getInstance()->builder($filters)->firstOrFail(),
        ];

        return inno_view('account.order_return_create', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        OrderReturnRepo::getInstance()->create($request->all());

        return redirect(account_route('order_returns.index'));
    }

    /**
     * @param  OrderReturn  $order_return
     * @return mixed
     */
    public function show(OrderReturn $order_return): mixed
    {
        $order_return->delete();

        return redirect(account_route('order_returns.index'));
    }
}
