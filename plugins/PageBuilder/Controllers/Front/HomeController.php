<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Controllers\Front;

use Exception;
use InnoShop\Front\Controllers\BaseController;
use Plugin\PageBuilder\Services\DesignService;

class HomeController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $modules = plugin_setting('page_builder', 'modules');
        $device  = request()->get('device', 'pc'); // 获取设备类型参数

        if (empty($modules) || empty($modules['modules'])) {
            // 如果没有模块数据，使用演示数据
            $demoData         = \Plugin\PageBuilder\Repositories\DemoRepo::getHomeDemoData();
            $processedModules = array_map(function ($demo) {
                return [
                    'code'      => $demo['code'],
                    'content'   => $demo['content'],
                    'module_id' => $demo['module_id'] ?? uniqid(),
                    'name'      => $demo['title'],
                    'view_path' => '',
                ];
            }, $demoData);
        } else {
            // 使用 DesignService 处理每个模块，确保与预览一致
            $processedModules = [];
            foreach ($modules['modules'] as $module) {
                $moduleCode = $module['code'] ?? '';
                $content    = $module['content'] ?? [];

                if ($moduleCode && $content) {
                    $processedContent = DesignService::getInstance()->handleModuleContent($moduleCode, $content);

                    $processedModules[] = [
                        'code'      => $moduleCode,
                        'content'   => $processedContent,
                        'module_id' => $module['module_id'] ?? 'module-'.uniqid(),
                        'name'      => $module['name'] ?? '',
                        'view_path' => $module['view_path'] ?? '',
                    ];
                }
            }
        }

        $data = [
            'modules' => $processedModules,
            'device'  => $device,
        ];

        return view('PageBuilder::front.home', $data);
    }
}
