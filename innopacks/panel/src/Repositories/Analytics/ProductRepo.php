<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories\Analytics;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Translation;
use InnoShop\Panel\Repositories\BaseRepo;

class ProductRepo extends BaseRepo
{
    /**
     * Get date range from filter
     *
     * @param  string  $dateFilter
     * @param  string|null  $customStartDate
     * @param  string|null  $customEndDate
     * @return array
     */
    public function getDateRange(string $dateFilter, ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $now = Carbon::now();
        switch ($dateFilter) {
            case '':
            case 'all':
                $startDate = null;
                $endDate   = null;
                break;
            case 'today':
                $startDate = $now->format('Y-m-d');
                $endDate   = $now->format('Y-m-d');
                break;
            case 'yesterday':
                $startDate = $now->subDay()->format('Y-m-d');
                $endDate   = $startDate;
                break;
            case 'this_week':
                $startDate = $now->startOfWeek()->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'this_month':
                $startDate = $now->startOfMonth()->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'last_7_days':
                $startDate = Carbon::now()->subDays(6)->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'last_30_days':
                $startDate = Carbon::now()->subDays(29)->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'custom':
                $startDate = $customStartDate ?? Carbon::now()->subDays(29)->format('Y-m-d');
                $endDate   = $customEndDate ?? Carbon::now()->format('Y-m-d');
                break;
            default:
                $startDate = Carbon::now()->subDays(29)->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
        }

        return [
            'start_date' => $startDate ? Carbon::parse($startDate)->startOfDay() : null,
            'end_date'   => $endDate ? Carbon::parse($endDate)->endOfDay() : null,
            'start'      => $startDate,
            'end'        => $endDate,
        ];
    }

    /**
     * Get product statistics summary
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getProductStatistics(array $dateRange): array
    {
        $filters = [];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
        }

        $builder = \InnoShop\Common\Repositories\ProductRepo::getInstance()->builder($filters);

        $totalProducts  = $builder->count();
        $activeProducts = clone $builder;
        $activeProducts = $activeProducts->where('active', 1)->count();

        $growth = 0;
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            // Get previous period for comparison
            $daysDiff      = $dateRange['start_date']->diffInDays($dateRange['end_date']) + 1;
            $previousStart = $dateRange['start_date']->copy()->subDays($daysDiff);
            $previousEnd   = $dateRange['start_date']->copy()->subDay()->endOfDay();

            $previousFilters = [
                'created_at_start' => $previousStart,
                'created_at_end'   => $previousEnd,
            ];

            $previousBuilder  = \InnoShop\Common\Repositories\ProductRepo::getInstance()->builder($previousFilters);
            $previousProducts = $previousBuilder->count();

            $growth = $previousProducts > 0 ? (($totalProducts - $previousProducts) / $previousProducts) * 100 : 0;
        }

        return [
            'total_products'  => $totalProducts,
            'active_products' => $activeProducts,
            'growth'          => $growth,
        ];
    }

    /**
     * Get product daily trends
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getProductDailyTrends(array $dateRange): array
    {
        $filters = [];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
            $trendStart                  = $dateRange['start'];
            $trendEnd                    = $dateRange['end'];
        } else {
            $trendStart                  = Carbon::now()->subDays(89)->format('Y-m-d');
            $trendEnd                    = Carbon::now()->format('Y-m-d');
            $filters['created_at_start'] = Carbon::parse($trendStart)->startOfDay();
            $filters['created_at_end']   = Carbon::parse($trendEnd)->endOfDay();
        }

        $productTotals = \InnoShop\Common\Repositories\ProductRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, count(*) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates  = $totals = [];
        $period = CarbonPeriod::create($trendStart, $trendEnd)->toArray();
        foreach ($period as $date) {
            $dateFormat   = $date->format('Y-m-d');
            $productTotal = $productTotals[$dateFormat] ?? null;

            $dates[]  = $dateFormat;
            $totals[] = $productTotal ? $productTotal->total : 0;
        }

        return [
            'labels' => $dates,
            'totals' => $totals,
        ];
    }

    /**
     * Get top selling products
     *
     * @param  array  $dateRange
     * @param  int  $limit
     * @return array
     */
    public function getTopSellingProducts(array $dateRange, int $limit = 10): array
    {
        $prefix = DB::getTablePrefix();

        $orderItemModel   = new Item;
        $orderModel       = new Order;
        $productModel     = new Product;
        $translationModel = new Translation;

        // Get table names without prefix (Laravel adds prefix automatically for join/table)
        $orderItemTable   = $orderItemModel->getTable();
        $orderTable       = $orderModel->getTable();
        $productTable     = $productModel->getTable();
        $translationTable = $translationModel->getTable();

        // For raw SQL, we need to add prefix manually
        $rawOrderItemTable   = $prefix.$orderItemTable;
        $rawOrderTable       = $prefix.$orderTable;
        $rawProductTable     = $prefix.$productTable;
        $rawTranslationTable = $prefix.$translationTable;

        $query = $orderItemModel->newQuery()
            ->join($orderTable, "{$orderItemTable}.order_id", '=', "{$orderTable}.id")
            ->join($productTable, "{$orderItemTable}.product_id", '=', "{$productTable}.id")
            ->join($translationTable, "{$productTable}.id", '=', "{$translationTable}.product_id")
            ->where("{$orderTable}.status", '!=', 'cancelled')
            ->where("{$translationTable}.locale", locale_code());

        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $query->whereBetween("{$orderTable}.created_at", [$dateRange['start_date'], $dateRange['end_date']]);
        }

        $products = $query->select(
            "{$productTable}.id",
            "{$translationTable}.name",
            DB::raw("SUM({$rawOrderItemTable}.quantity) as total_quantity"),
            DB::raw("SUM({$rawOrderItemTable}.price * {$rawOrderItemTable}.quantity) as total_amount"),
            DB::raw("COUNT(DISTINCT {$rawOrderTable}.id) as order_count")
        )
            ->groupBy("{$productTable}.id", "{$translationTable}.name")
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'id'             => $item->id,
                'name'           => $item->name,
                'total_quantity' => $item->total_quantity,
                'total_amount'   => $item->total_amount,
                'order_count'    => $item->order_count,
            ])
            ->toArray();

        return $products;
    }

    /**
     * Get product category distribution
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getProductCategoryDistribution(array $dateRange): array
    {
        // DB::table() automatically adds the prefix, so use plain table names
        $categoryData = DB::table('product_categories')
            ->select(DB::raw('category_id, count(*) as total'))
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        // Get category names from translations table
        $categoryIds   = $categoryData->keys()->toArray();
        $categoryNames = DB::table('category_translations')
            ->whereIn('category_id', $categoryIds)
            ->where('locale', front_locale_code())
            ->pluck('name', 'category_id')
            ->toArray();

        $labels = $data = [];
        foreach ($categoryData as $categoryId => $item) {
            $labels[] = $categoryNames[$categoryId] ?? __('panel/common.uncategorized');
            $data[]   = $item->total;
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }

    /**
     * @return array
     */
    public function getProductCountLatestWeek(): array
    {
        $dateRange = $this->getDateRange('last_7_days');
        $trends    = $this->getProductDailyTrends($dateRange);

        return [
            'period' => $trends['labels'],
            'totals' => $trends['totals'],
        ];
    }

    /**
     * Get top sale products for dashboard.
     *
     * @param  int  $limit
     * @return array
     */
    public function getTopSaleProducts(int $limit = 8): array
    {
        $products = \InnoShop\Common\Repositories\ProductRepo::getInstance()->getBestSellerProducts($limit);

        // Get total amount for each product from order_items
        $productIds   = $products->pluck('id')->toArray();
        $amountData   = [];
        $quantityData = [];

        if (! empty($productIds)) {
            $prefix         = DB::getTablePrefix();
            $orderItemTable = (new Item)->getTable();
            $orderTable     = (new Order)->getTable();

            // For raw SQL, we need to add prefix manually
            $rawOrderItemTable = $prefix.$orderItemTable;
            $rawOrderTable     = $prefix.$orderTable;

            $salesData = DB::table($orderItemTable)
                ->join($orderTable, "{$orderItemTable}.order_id", '=', "{$orderTable}.id")
                ->whereIn("{$orderItemTable}.product_id", $productIds)
                ->where("{$orderTable}.status", '!=', 'cancelled')
                ->select(
                    "{$orderItemTable}.product_id",
                    DB::raw("SUM({$rawOrderItemTable}.quantity) as total_quantity"),
                    DB::raw("SUM({$rawOrderItemTable}.price * {$rawOrderItemTable}.quantity) as total_amount")
                )
                ->groupBy("{$orderItemTable}.product_id")
                ->get();

            foreach ($salesData as $item) {
                $amountData[$item->product_id]   = $item->total_amount;
                $quantityData[$item->product_id] = $item->total_quantity;
            }
        }

        $items = [];
        foreach ($products as $product) {
            if (empty($product->order_items_count)) {
                continue;
            }

            $name      = $product->translation->name ?? '';
            $productId = $product->id;
            $items[]   = [
                'product_id'     => $productId,
                'image'          => image_resize($product->image ?? ''),
                'name'           => $name,
                'summary'        => sub_string($name, 50),
                'order_count'    => $product->order_items_count,
                'total_quantity' => $quantityData[$productId] ?? 0,
                'total_amount'   => $amountData[$productId] ?? 0,
            ];
        }

        return $items;
    }

    /**
     * Get top sale products for pie chart.
     *
     * @return array
     */
    public function getTopSaleProductsForPieChart(): array
    {
        $products = \InnoShop\Common\Repositories\ProductRepo::getInstance()->getBestSellerProducts();

        $names = $viewed = [];
        foreach ($products as $product) {
            $names[]  = sub_string($product->translation->name, 64);
            $viewed[] = $product->order_items_count;
        }

        return [
            'period' => $names,
            'totals' => $viewed,
        ];
    }
}
