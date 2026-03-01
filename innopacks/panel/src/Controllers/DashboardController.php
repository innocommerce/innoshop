<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use InnoShop\Panel\Repositories\Analytics\CustomerRepo;
use InnoShop\Panel\Repositories\Analytics\OrderRepo as AnalyticsOrderRepo;
use InnoShop\Panel\Repositories\Analytics\ProductRepo as AnalyticsProductRepo;
use InnoShop\Panel\Repositories\DashboardRepo;

class DashboardController extends BaseController
{
    /**
     * Dashboard for panel home page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function index(): mixed
    {
        // Get date range for last 30 days
        $dateRange = (new AnalyticsOrderRepo)->getDateRange('last_30_days');

        // Get order trends and status distribution
        $orderTrends = (new AnalyticsOrderRepo)->getOrderDailyTrends($dateRange);
        $orderStatus = (new AnalyticsOrderRepo)->getOrderStatusDistribution($dateRange);

        // Get top customers
        $customerRepo = new CustomerRepo;
        $topCustomers = $customerRepo->getTopCustomers($dateRange, 7);

        // Get visit trends
        $visitStats = (new \InnoShop\Common\Repositories\VisitRepo)->getDailyStatistics([
            'start_date' => $dateRange['start_date'],
            'end_date'   => $dateRange['end_date'],
        ]);

        // Get customer trends
        $customerTrends = $customerRepo->getCustomerDailyTrends($dateRange);

        $data = [
            'cards' => DashboardRepo::getInstance()->getCards(),
            'order' => [
                'latest_month' => [
                    'period' => $orderTrends['labels'],
                    'counts' => $orderTrends['counts'],
                    'totals' => $orderTrends['totals'],
                ],
                'status_dist' => $orderStatus,
            ],
            'visits' => [
                'latest_month' => $visitStats,
            ],
            'customers' => [
                'latest_month' => $customerTrends,
            ],
            'top_sale_products' => (new AnalyticsProductRepo)->getTopSaleProducts(7),
            'top_customers'     => $topCustomers,
        ];

        return inno_view('panel::dashboard', $data);
    }
}
