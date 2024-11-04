<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\Request;

class SNSController extends BaseController
{
    public function index()
    {
        return inno_view('panel::sns.index');
    }

    public function store(Request $request)
    {
        dd($request->all());
    }
}
