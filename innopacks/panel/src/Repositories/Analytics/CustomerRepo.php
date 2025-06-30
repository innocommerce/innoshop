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

    /**
     * 获取客户来源数据
     *
     * @return array
     */
    public function getCustomerSourceData(): array
    {
        $sourceData = \InnoShop\Common\Repositories\CustomerRepo::getInstance()->builder()
            ->select(DB::raw('`from`, count(*) as total'))
            ->groupBy('from')
            ->get()
            ->keyBy('from');

        $labels      = [];
        $data        = [];
        $fromOptions = \InnoShop\Common\Repositories\CustomerRepo::getFromList();

        // 遍历所有可能的来源类型
        foreach ($fromOptions as $option) {
            $key   = $option['key'];
            $label = $option['value'];

            // 基本数据
            $total    = isset($sourceData[$key]) ? $sourceData[$key]->total : 0;
            $labels[] = $label;
            $data[]   = $total;
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }
}
