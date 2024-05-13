<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories\Dashboard;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InnoShop\Panel\Repositories\BaseRepo;

class ArticleRepo extends BaseRepo
{
    /**
     * Retrieve the number of new articles added each day in the past week.
     *
     * @return array
     */
    public function getArticleTotalLatestWeek(): array
    {
        $filters = [
            'start' => today()->subWeek(),
            'end'   => today(),
        ];
        $articleTotals = \InnoShop\Common\Repositories\ArticleRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, count(*) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates  = $totals = [];
        $period = CarbonPeriod::create(today()->subWeek(), today()->subDay())->toArray();
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
     * Retrieve the 7 articles with the highest views.
     *
     * @return array
     */
    public function getArticleViewedLatestWeek(): array
    {
        $topArticleArticles = ArticleRepo::getInstance()->builder()->orderByDesc('viewed')->limit(5)->get();
        $names              = $viewed = [];
        foreach ($topArticleArticles as $article) {
            $names[]  = sub_string($article->translation->title, 8);
            $viewed[] = $article->viewed;
        }

        return [
            'period' => $names,
            'totals' => $viewed,
        ];
    }
}
