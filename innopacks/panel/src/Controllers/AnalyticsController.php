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
use InnoShop\Panel\Repositories\Analytics\CustomerRepo;
use InnoShop\Panel\Repositories\Analytics\ProductRepo;
use InnoShop\Panel\Repositories\Dashboard\OrderRepo;

class AnalyticsController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $data = [
            'order_latest_week'    => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            'product_latest_week'  => ProductRepo::getInstance()->getProductCountLatestWeek(),
            'customer_latest_week' => CustomerRepo::getInstance()->getCustomerCountLatestWeek(),
        ];

        return inno_view('panel::analytics.index', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function order(): mixed
    {
        $data = [
            'order_quantity_latest_month' => OrderRepo::getInstance()->getOrderCountLatestMonth(),
            'order_quantity_latest_week'  => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            'order_total_latest_month'    => \InnoShop\Panel\Repositories\Analytics\OrderRepo::getInstance()->getOrderTotalLatestMonth(),
            'order_total_latest_week'     => \InnoShop\Panel\Repositories\Analytics\OrderRepo::getInstance()->getOrderTotalLatestWeek(),
            'top_sale_products'           => \InnoShop\Panel\Repositories\Dashboard\ProductRepo::getInstance()->getTopSaleProducts(),
        ];

        return inno_view('panel::analytics.order', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function product(): mixed
    {
        $data = [
            'product_latest_week' => ProductRepo::getInstance()->getProductCountLatestWeek(),
        ];

        return inno_view('panel::analytics.product', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function customer(): mixed
    {
        $data = [
            'customer_latest_week' => CustomerRepo::getInstance()->getCustomerCountLatestWeek(),
        ];

        return inno_view('panel::analytics.customer', $data);
    }
}
