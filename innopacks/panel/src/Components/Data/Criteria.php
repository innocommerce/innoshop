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

class Criteria extends Component
{
    public string $action;

    public array $criteria;

    public bool $export;

    public bool $hasFilters;

    /**
     * Types: [input, select, date, range, date_range]
     *
     * @param  string  $action
     * @param  array  $criteria
     */
    public function __construct(string $action, array $criteria, bool $export = false)
    {
        $this->action     = $action;
        $this->criteria   = $criteria;
        $this->export     = $export;
        $this->hasFilters = has_set_value(request()->except(['page', 'export']));
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('panel::components.data.criteria');
    }

    /**
     * Generate export url, include all query parameters (except page)
     *
     * @return string
     */
    public function getExportUrl(): string
    {
        $query = request()->except(['page', 'export']);

        $exportAction = $this->action.'/export';

        return $exportAction.'?'.http_build_query($query);
    }
}
