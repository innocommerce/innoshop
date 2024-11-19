<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MobileBuilder\ApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Plugin\MobileBuilder\Services\DesignService;

class HomeController extends BaseController
{
    /**
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $appHomeData = plugin_setting('mobile_builder', 'modules');
        $modules     = $appHomeData['modules'] ?? [];

        $productCodes = ['product', 'category', 'latest'];
        $moduleItems  = [];
        foreach ($modules as $module) {
            $code    = $module['code'];
            $content = $module['content'];
            if (in_array($code, $productCodes)) {
                $content['products'] = collect($content['products'])->pluck('id')->toArray();
            }

            $moduleItems[] = [
                'code'    => $code,
                'content' => DesignService::getInstance()->handleModuleContent($code, $content),
            ];
        }

        return read_json_success($moduleItems);
    }
}
