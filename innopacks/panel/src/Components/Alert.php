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

class Alert extends Component
{
    public string $type;

    public string $msg;

    public function __construct(?string $type, string $msg)
    {
        $this->type = $type ?? 'success';
        $this->msg  = $msg;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('panel::components.alert');
    }
}
