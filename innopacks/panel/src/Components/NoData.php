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

class NoData extends Component
{
    public string $text;

    public function __construct(?string $text = '')
    {
        $this->text = $text;
    }

    public function render()
    {
        return view('panel::components.no-data');
    }
}
