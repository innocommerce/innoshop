<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use InnoShop\Panel\Repositories\DashboardRepo;

class HomeController extends BaseController
{
    /**
     * Dashboard for panel home page.
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $dashboard = DashboardRepo::getInstance();
        $data      = [
            'cards'   => $dashboard->getCards(),
            'article' => [
                'latest_week' => $dashboard->getArticleTotalLatestWeek(),
                'top_viewed'  => $dashboard->getArticleViewedLatestWeek(),
            ],
        ];

        return view('panel::home', $data);
    }
}
