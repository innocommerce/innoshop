<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MobileBuilder\Controllers;

use InnoShop\Panel\Controllers\BaseController;

class MobileBuilderController extends BaseController
{
    public function index()
    {
        return view('MobileBuilder::index');
    }
}
