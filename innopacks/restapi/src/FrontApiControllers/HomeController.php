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
        $content = file_get_contents(inno_path('restapi/src/Repositories/app_home_data.json'));
        $data    = json_decode($content, true);

        return read_json_success($data['data']);
    }
}
