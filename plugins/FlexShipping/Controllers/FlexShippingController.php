<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlexShipping\Controllers;

use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;

class FlexShippingController extends BaseController
{
    public function update(Request $request)
    {
        return $request->all();
    }
}
