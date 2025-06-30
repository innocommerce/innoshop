<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Components\Charts;

use Illuminate\View\Component;

class Pie extends Component
{
    public string $id;

    public string $title;

    public array $labels;

    public array $items;

    public array $colors;

    /**
     * @param  string  $id
     * @param  string  $title
     * @param  array  $labels
     * @param  array  $data
     * @param  array  $colors
     */
    public function __construct(string $id, string $title, array $labels, array $data, array $colors = [])
    {
        $this->id     = $id;
        $this->title  = $title;
        $this->labels = $labels;
        $this->items  = $data;
        $this->colors = $colors ?: [
            '#3c7af7',
            '#f56c6c',
            '#67c23a',
            '#e6a23c',
            '#909399',
            '#409eff',
            '#f09329',
            '#eb4d4b',
        ];
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('panel::components.charts.pie');
    }
}
