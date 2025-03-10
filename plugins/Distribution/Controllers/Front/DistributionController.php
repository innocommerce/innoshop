<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Controllers\Front;

use Exception;
use InnoShop\Common\Models\Customer;
use InnoShop\Front\Controllers\BaseController;
use Plugin\Coupon\Models\Order;
use Plugin\Distribution\Models\Commission;
use Plugin\Distribution\Services\ReferralService;

class DistributionController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $customer   = current_customer();
        $customerID = current_customer_id();
        $refCode    = ReferralService::getInstance()->getCustomerRefCode($customer);

        $data = ReferralService::getReport($customerID);

        $data['referral_link'] = front_route('home.index', ['ref' => $refCode]);

        return view('Distribution::account.distribution_index', $data);
    }

    /**
     * @return mixed
     */
    public function members(): mixed
    {
        $customerID = current_customer_id();
        $members    = Customer::query()->where('referrer_id', $customerID)->paginate(20);

        $data = [
            'members' => $members,
        ];

        return view('Distribution::account.members_index', $data);
    }

    /**
     * @return mixed
     */
    public function commissions(): mixed
    {
        $customerID = current_customer_id();

        $commissions = Commission::query()->where('referrer_id', $customerID)->paginate(20);

        $data = [
            'commissions' => $commissions,
        ];

        return view('Distribution::account.commissions_index', $data);
    }

    /**
     * @return mixed
     */
    public function orders(): mixed
    {
        $customerID = current_customer_id();
        $orders     = Order::query()->where('referrer_id', $customerID)->paginate(20);

        $data = [
            'orders' => $orders,
        ];

        return view('Distribution::account.orders_index', $data);
    }
}
