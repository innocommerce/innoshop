<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Front\Services\SitemapService;

class SitemapController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        try {
            return SitemapService::getInstance()->response($request);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
