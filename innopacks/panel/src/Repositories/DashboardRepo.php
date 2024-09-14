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
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;

class DashboardRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public function getCards(): array
    {
        $filters = [
            'statuses' => StateMachineService::getValidStatuses(),
        ];

        $validOrderBuilder = OrderRepo::getInstance()->builder($filters);

        return [
            [
                'title'    => panel_trans('dashboard.order_quantity'),
                'icon'     => 'bi bi-cart',
                'quantity' => Order::query()->count(),
                'url'      => panel_route('orders.index'),
            ],
            [
                'title'    => panel_trans('dashboard.product_quantity'),
                'icon'     => 'bi bi-bag',
                'quantity' => Product::query()->count(),
                'url'      => panel_route('products.index'),
            ],
            [
                'title'    => panel_trans('dashboard.customer_quantity'),
                'icon'     => 'bi bi-person',
                'quantity' => Customer::query()->count(),
                'url'      => panel_route('customers.index'),
            ],
            [
                'title'    => panel_trans('dashboard.order_amount'),
                'icon'     => 'bi bi-gem',
                'quantity' => currency_format($validOrderBuilder->sum('total')),
                'url'      => panel_route('orders.index'),
            ],
        ];
    }
}
