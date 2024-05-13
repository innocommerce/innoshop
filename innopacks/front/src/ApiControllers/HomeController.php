<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\ApiControllers;

class HomeController extends BaseApiController
{
    /**
     * Home page data.
     *
     * @return array
     */
    public function index(): array
    {
        return [
            'file' => __FILE__,
        ];
    }
}
