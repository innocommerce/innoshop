<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\JsonResponse;

class HomeController extends BaseController
{
    /**
     * Home page data.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = [
            [
                'type'  => 'icon',
                'items' => [
                    ['url' => '', 'image' => ''],
                    ['url' => '', 'image' => ''],
                ],
            ],
            [
                'type'  => 'slideshow',
                'items' => [
                    ['url' => '', 'image' => ''],
                    ['url' => '', 'image' => ''],
                ],
            ],
        ];

        return read_json_success($data);
    }
}
