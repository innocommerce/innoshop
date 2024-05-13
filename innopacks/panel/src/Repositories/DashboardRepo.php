<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\Tag;
use InnoShop\Common\Repositories\ArticleRepo;

class DashboardRepo
{
    /**
     * Get card instance.
     *
     * @return DashboardRepo
     */
    public static function getInstance(): DashboardRepo
    {
        return new self;
    }

    /**
     * @return array[]
     */
    public function getCards(): array
    {
        return [
            [
                'title'    => '文章数量',
                'icon'     => 'bi bi-file-earmark-text',
                'quantity' => Article::query()->count(),
            ],
            [
                'title'    => '分类数量',
                'icon'     => 'bi bi-journal',
                'quantity' => Catalog::query()->count(),
            ],
            [
                'title'    => '单页数量',
                'icon'     => 'bi bi-tags',
                'quantity' => Page::query()->count(),
            ],
            [
                'title'    => '标签数量',
                'icon'     => 'bi bi-chat-left-text',
                'quantity' => Tag::query()->count(),
            ],
        ];
    }

    /**
     * 获取最近一周每日新增文章数量
     */
    public function getArticleTotalLatestWeek(): array
    {
        $filters = [
            'start' => today()->subWeek(),
            'end'   => today(),
        ];
        $articleTotals = ArticleRepo::getInstance()->builder($filters)
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
     * 获取浏览量最高的7篇文章
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
