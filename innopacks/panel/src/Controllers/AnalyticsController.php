<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use InnoShop\Panel\Repositories\Dashboard\OrderRepo;
use InnoShop\Panel\Repositories\Dashboard\ProductRepo;

class AnalyticsController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $data = [
            'order' => [
                'latest_week' => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            ],
            'top_sale_products' => ProductRepo::getInstance()->getTopSaleProducts(),
        ];

        return inno_view('panel::analytics.index', $data);
    }

    public function order()
    {
        $data = [
            'order' => [
                'latest_week' => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            ],
            'top_sale_products' => ProductRepo::getInstance()->getTopSaleProducts(),
        ];

        return inno_view('panel::analytics.order', $data);
    }

    public function product()
    {
        $data = [
            'order' => [
                'latest_week' => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            ],
            'top_sale_products' => ProductRepo::getInstance()->getTopSaleProducts(),
        ];

        return inno_view('panel::analytics.product', $data);
    }

    public function customer()
    {
        $data = [
            'order' => [
                'latest_week' => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            ],
            'top_sale_products' => ProductRepo::getInstance()->getTopSaleProducts(),
        ];

        return inno_view('panel::analytics.customer', $data);
    }
}
