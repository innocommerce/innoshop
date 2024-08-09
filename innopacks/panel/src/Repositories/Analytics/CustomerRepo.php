<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories\Analytics;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InnoShop\Panel\Repositories\BaseRepo;

class CustomerRepo extends BaseRepo
{
    /**
     * @return array
     */
    public function getCustomerCountLatestWeek(): array
    {
        $filters = [
            'start' => today()->subWeek(),
            'end'   => today()->endOfDay(),
        ];
        $articleTotals = \InnoShop\Common\Repositories\CustomerRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, count(*) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates  = $totals = [];
        $period = CarbonPeriod::create(today()->subWeek(), today())->toArray();
        foreach ($period as $date) {
            $dateFormat   = $date->format('Y-m-d');
            $articleTotal = $articleTotals[$dateFormat] ?? null;

            $dates[]  = $dateFormat;
            $totals[] = $articleTotal ? $articleTotal->total : 0;
        }

        return [
            'period' => $dates,
            'totals' => $totals,
        ];
    }
}
