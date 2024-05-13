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
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function switch(Request $request): RedirectResponse
    {
        $currentCode = App::getLocale();
        $destCode    = $request->code;
        $refererUrl  = $request->headers->get('referer');
        $baseUrl     = url('/').'/';

        $newUrl = str_replace($baseUrl.$currentCode, $baseUrl.$destCode, $refererUrl);
        App::setLocale($destCode);
        session(['locale' => $destCode]);

        return redirect()->to($newUrl);
    }
}
