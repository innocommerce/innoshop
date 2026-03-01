<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Components\Data;

use Illuminate\View\Component;

class DataSearch extends Component
{
    /**
     * Form action URL
     */
    public string $action;

    /**
     * Search field options
     */
    public array $searchFields;

    /**
     * Filter button options
     */
    public array $filters;

    /**
     * Enable date range filter
     */
    public bool $enableDateRange;

    /**
     * Current keyword
     */
    public string $currentKeyword;

    /**
     * Current search field
     */
    public string $currentSearchField;

    /**
     * Date filter value
     */
    public string $dateFilter;

    /**
     * Start date
     */
    public string $startDate;

    /**
     * End date
     */
    public string $endDate;

    /**
     * Date range options
     */
    public array $dateRangeOptions;

    /**
     * Active button filters
     */
    public array $activeFilters;

    /**
     * Active range filters
     */
    public array $activeRangeFilters;

    /**
     * Has active filters
     */
    public bool $hasActiveFilters;

    /**
     * Create a new component instance.
     */
    public function __construct(string $action = '', array $searchFields = [], array $filters = [], bool $enableDateRange = false)
    {
        $this->action          = $action;
        $this->searchFields    = $searchFields;
        $this->filters         = $filters;
        $this->enableDateRange = $enableDateRange;

        $this->currentKeyword     = request('keyword', '');
        $this->currentSearchField = request('search_field', '');

        $this->initDateFilters();
        $this->initActiveFilters();
        $this->initHasActiveFilters();
    }

    /**
     * Initialize date filter related data
     */
    protected function initDateFilters(): void
    {
        $this->startDate  = request('start_date', '');
        $this->endDate    = request('end_date', '');
        $this->dateFilter = request('date_filter', 'all');

        if (($this->startDate || $this->endDate) && $this->dateFilter === 'all') {
            $this->dateFilter = 'custom';
        }

        $this->dateRangeOptions = [
            ['value' => 'all', 'label' => trans('panel/common.all')],
            ['value' => 'today', 'label' => trans('panel/analytics.filter_today')],
            ['value' => 'this_week', 'label' => trans('panel/analytics.filter_this_week')],
            ['value' => 'this_month', 'label' => trans('panel/analytics.filter_this_month')],
            ['value' => 'custom', 'label' => trans('panel/analytics.filter_custom')],
        ];
    }

    /**
     * Initialize active filters
     */
    protected function initActiveFilters(): void
    {
        $this->activeFilters      = [];
        $this->activeRangeFilters = [];

        foreach ($this->filters as $filterGroup) {
            $name = $filterGroup['name'];
            $type = $filterGroup['type'] ?? 'button';

            if ($type === 'range') {
                $this->processRangeFilter($name, $filterGroup);
            } else {
                $this->processButtonFilter($name, $filterGroup);
            }
        }
    }

    /**
     * Process range type filter
     */
    protected function processRangeFilter(string $name, array $filterGroup): void
    {
        $startName  = $name.'_start';
        $endName    = $name.'_end';
        $startValue = request($startName, '');
        $endValue   = request($endName, '');

        if ($startValue !== '' || $endValue !== '') {
            $this->activeRangeFilters[$name] = [
                'label' => $filterGroup['label'],
                'start' => $startValue,
                'end'   => $endValue,
            ];
        }
    }

    /**
     * Process button type filter
     */
    protected function processButtonFilter(string $name, array $filterGroup): void
    {
        $value = request($name, '');
        if ($value === '') {
            return;
        }

        $label = '';
        foreach ($filterGroup['options'] ?? [] as $option) {
            if (($option['value'] ?? '') === $value) {
                $label = $option['label'];
                break;
            }
        }

        if ($label) {
            $this->activeFilters[$name] = [
                'label'      => $filterGroup['label'],
                'valueLabel' => $label,
                'value'      => $value,
            ];
        }
    }

    /**
     * Initialize hasActiveFilters flag
     */
    protected function initHasActiveFilters(): void
    {
        $this->hasActiveFilters = count($this->activeFilters) > 0
            || count($this->activeRangeFilters) > 0
            || $this->currentKeyword !== ''
            || ($this->enableDateRange && $this->dateFilter !== 'all');
    }

    /**
     * Get range filter values
     */
    public function getRangeValues(string $name): array
    {
        return [
            'start' => request($name.'_start', ''),
            'end'   => request($name.'_end', ''),
        ];
    }

    /**
     * Get button filter current value
     */
    public function getButtonValue(string $name): string
    {
        return request($name, '');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('panel::components.data.data_search');
    }
}
