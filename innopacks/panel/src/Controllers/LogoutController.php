<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Support\Facades\Auth;

class LogoutController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $admin = Auth::guard('admin')->user();
        Auth::guard('admin')->logout();

        return redirect(panel_route('login.index'))
            ->with('instance', $admin);
    }
}
