<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories;

use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Product;

class DashboardRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public function getCards(): array
    {
        return [
            [
                'title'    => trans('panel::dashboard.order_quantity'),
                'icon'     => 'bi bi-cart',
                'quantity' => Order::query()->count(),
                'url'      => panel_route('orders.index'),
            ],
            [
                'title'    => trans('panel::dashboard.product_quantity'),
                'icon'     => 'bi bi-bag',
                'quantity' => Product::query()->count(),
                'url'      => panel_route('products.index'),
            ],
            [
                'title'    => trans('panel::dashboard.customer_quantity'),
                'icon'     => 'bi bi-person',
                'quantity' => Customer::query()->count(),
                'url'      => panel_route('customers.index'),
            ],
            [
                'title'    => trans('panel::dashboard.order_amount'),
                'icon'     => 'bi bi-gem',
                'quantity' => currency_format(Order::query()->sum('total')),
                'url'      => panel_route('orders.index'),
            ],
        ];
    }
}
