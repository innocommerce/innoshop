<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    public string $type;

    public string $msg;

    public bool $close;

    /**
     * @param  string  $msg
     * @param  string|null  $type
     * @param  bool  $close
     */
    public function __construct(string $msg, ?string $type = 'success', bool $close = false)
    {
        $this->type  = $type;
        $this->msg   = $msg;
        $this->close = $close;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('common::components.alert');
    }
}
