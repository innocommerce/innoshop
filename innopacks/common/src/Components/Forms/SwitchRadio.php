<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Components\Forms;

use Illuminate\View\Component;

class SwitchRadio extends Component
{
    public string $name;

    public bool $value;

    public string $title;

    public string $description;

    public function __construct(string $name, ?bool $value, string $title, string $description = '')
    {
        $this->name        = $name;
        $this->title       = $title;
        $this->value       = (bool) $value;
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('common::components.form.switch-radio');
    }
}
