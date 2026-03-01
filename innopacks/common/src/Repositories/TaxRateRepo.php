<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\TaxRate;

class TaxRateRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'regions', 'type' => 'input', 'label' => trans('panel/menu.regions')],
            ['name' => 'taxes', 'type' => 'input', 'label' => trans('panel/tax_classes.taxes')],
            ['name' => 'type', 'type' => 'input', 'label' => trans('panel/tax_classes.type')],
            ['name' => 'tax_rate', 'type' => 'input', 'label' => trans('panel/tax_classes.tax_rate')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('common/base.created_at')],
        ];
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'name', 'label' => trans('common/base.name')],
            ['value' => 'type', 'label' => trans('panel/tax_classes.type')],
        ];

        return fire_hook_filter('common.repo.tax_rate.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $filters = [];

        return fire_hook_filter('common.repo.tax_rate.filter_button_options', $filters);
    }

    /**
     * @param  $taxRateId
     * @return string
     */
    public function getNameByRateId($taxRateId): string
    {
        $taxRate = TaxRate::query()->find($taxRateId);

        return $taxRate->name ?? '';
    }

    /**
     * @param  $filters
     * @return Builder
     */
    public function builder($filters = []): Builder
    {
        $builder = TaxRate::query();

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            $builder->where($searchField, 'like', "%{$keyword}%");
        } elseif ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('type', 'like', "%{$keyword}%");
            });
        }

        // Handle date range filter
        $dateFilter = $filters['date_filter'] ?? '';
        $startDate  = $filters['start_date'] ?? '';
        $endDate    = $filters['end_date'] ?? '';

        if ($dateFilter === 'today') {
            $builder->whereDate('created_at', today());
        } elseif ($dateFilter === 'this_week') {
            $builder->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter === 'this_month') {
            $builder->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($dateFilter === 'custom' && $startDate && $endDate) {
            $builder->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        }

        return $builder;
    }
}
