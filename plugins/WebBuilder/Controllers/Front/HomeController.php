<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder\Controllers\Front;

use Exception;
use InnoShop\Front\Controllers\BaseController;
use Plugin\WebBuilder\Services\ModuleService;

class HomeController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $modules = plugin_setting('web_builder', 'modules');

        if (empty($modules) || empty($modules['modules'])) {
            return view('WebBuilder::front.home', []);
        }

        $processedModules = ModuleService::getInstance()->parseModules($modules['modules']);

        $data = [
            'modules' => $processedModules,
        ];

        return view('WebBuilder::front.home', $data);
    }
}
