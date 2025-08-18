<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Components;

use Illuminate\View\Component;
use InnoShop\Front\Repositories\FooterMenuRepo;

/**
 * Footer component class
 * Responsible for rendering the website footer, including link menus, copyright information, payment icons, etc.
 */
class Footer extends Component
{
    public array $footerMenus;

    /**
     * Constructor - Initialize data required for Footer component
     *
     * @throws \Exception
     */
    public function __construct()
    {
        // Get footer menu data
        $this->footerMenus = FooterMenuRepo::getInstance()->getMenus();
    }

    /**
     * Render Footer component view
     *
     * @return mixed
     */
    public function render(): mixed
    {
        return view('components.footer');
    }
}
