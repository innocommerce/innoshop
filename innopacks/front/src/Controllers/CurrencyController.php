<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function switch(Request $request): RedirectResponse
    {
        $destCode   = $request->code;
        $refererUrl = $request->headers->get('referer');

        session(['currency' => $destCode]);

        return redirect()->to($refererUrl);
    }
}
