<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Components;

use Illuminate\View\Component;

class Criteria extends Component
{
    public string $action;

    public array $criteria;

    /**
     * Types: [input, select, date, range, date_range]
     *
     * @param  string  $action
     * @param  array  $criteria
     */
    public function __construct(string $action, array $criteria)
    {
        $this->action   = $action;
        $this->criteria = $criteria;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('panel::components.criteria');
    }
}
