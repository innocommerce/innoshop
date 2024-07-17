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
use InnoShop\Common\Models\Address;
use InnoShop\Common\Models\Customer\Favorite;
use InnoShop\Common\Models\Order;

class AccountController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $customer   = current_customer();
        $customerID = $customer->id;
        $data       = [
            'customer'      => $customer,
            'order_total'   => Order::query()->where('customer_id', $customerID)->count(),
            'fav_total'     => Favorite::query()->where('customer_id', $customerID)->count(),
            'address_total' => Address::query()->where('customer_id', $customerID)->count(),
            'latest_orders' => Order::query()->where('customer_id', $customerID)->orderByDesc('id')->limit(3)->get(),
        ];

        return inno_view('account.home', $data);
    }
}
