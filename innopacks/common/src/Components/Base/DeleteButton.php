<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Components\Base;

use Illuminate\View\Component;

class DeleteButton extends Component
{
    /**
     * The ID of the item to delete.
     *
     * @var int|string
     */
    public $id;

    /**
     * The route to delete the item.
     *
     * @var string
     */
    public $route;

    /**
     * The confirmation message.
     *
     * @var string|null
     */
    public $confirmMessage;

    /**
     * Create a new component instance.
     *
     * @param  int|string  $id
     * @param  string  $route
     * @param  string|null  $confirmMessage
     * @return void
     */
    public function __construct($id, $route, $confirmMessage = null)
    {
        $this->id             = $id;
        $this->route          = $route;
        $this->confirmMessage = $confirmMessage;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('common::components.delete-button');
    }
}
