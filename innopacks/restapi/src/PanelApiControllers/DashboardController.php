<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use InnoShop\Panel\Repositories\DashboardRepo;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Dashboard')]
class DashboardController extends BaseController
{
    /**
     * Get yesterday's report.
     *
     * @return mixed
     */
    #[Endpoint('Get dashboard report')]
    public function index(): mixed
    {
        $dashboardRepo = new DashboardRepo;
        $report        = $dashboardRepo->getDailyReport();

        return read_json_success($report);
    }

    /**
     * Get report for a specific date.
     *
     * @param  string  $date
     * @return mixed
     */
    #[Endpoint('Get daily report')]
    #[UrlParam('date', 'string', description: 'Date in Y-m-d format', example: '2024-01-15')]
    public function daily(string $date): mixed
    {
        $dashboardRepo = new DashboardRepo;
        $report        = $dashboardRepo->getDailyReport($date);

        return read_json_success($report);
    }
}
