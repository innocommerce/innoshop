<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Services;

use Exception;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\PageModule;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\SettingRepo;
use Plugin\PageBuilder\Repositories\DemoRepo;
use Plugin\PageBuilder\Repositories\ModuleRepo;
use Throwable;

class PageBuilderService
{
    /**
     * 获取页面构建器数据
     *
     * @param  string|null  $page  页面标识，'home' 表示首页，其他为页面ID或slug，null表示首页
     * @return array
     * @throws Exception
     */
    public function getPageData(?string $page = null): array
    {
        // 如果没有传入page参数或page为null，默认为首页
        if ($page === null) {
            $page = 'home';
        }

        $isHomePage = ($page === 'home');

        if ($isHomePage) {
            $design_settings = $this->getHomePageSettings();
        } else {
            $design_settings = $this->getSinglePageSettings($page);
        }

        $data = [
            'page'         => $page,
            'is_home_page' => $isHomePage,
            'demo_data'    => DemoRepo::getHomeDemoData(),
            'pages'        => PageRepo::getInstance()->all(),
            'source'       => [
                'modules' => ModuleRepo::getModules(),
            ],
            'design_settings' => $design_settings,
        ];

        return fire_hook_filter('page_builder.index.data', $data);
    }

    /**
     * 获取首页设置
     *
     * @return array
     */
    private function getHomePageSettings(): array
    {
        $design_settings = plugin_setting('page_builder', 'modules');

        // 如果没有模块数据，使用演示数据
        if (empty($design_settings) || empty($design_settings['modules'])) {
            $demo_data       = DemoRepo::getHomeDemoData();
            $design_settings = [
                'modules' => array_map(function ($demo) {
                    return [
                        'code'      => $demo['code'],
                        'content'   => $demo['content'],
                        'module_id' => $demo['module_id'] ?? uniqid(),
                        'name'      => $demo['title'],
                        'view_path' => '',
                    ];
                }, $demo_data),
            ];

            // 保存演示数据
            SettingRepo::getInstance()->updatePluginValue('page_builder', 'modules', $design_settings);
        }

        return $design_settings;
    }

    /**
     * 获取单页设置
     *
     * @param  string  $page  页面标识
     * @return array
     * @throws Exception
     */
    private function getSinglePageSettings(string $page): array
    {
        $pageModel = $this->findPage($page);
        if (! $pageModel) {
            abort(404, '页面不存在');
        }

        // 获取单页的模块数据
        $pageModule = PageModule::query()->where('page_id', $pageModel->id)->first();

        return [
            'modules' => $pageModule->module_data ?? [],
        ];
    }

    /**
     * 保存页面模块数据
     *
     * @param  array  $modules  模块数据
     * @param  string|null  $page  页面标识，null表示首页
     * @return array
     * @throws Throwable
     */
    public function savePageModules(array $modules, ?string $page = null): array
    {
        // 如果没有传入page参数或page为null，默认为首页
        if ($page === null) {
            $page = 'home';
        }

        $moduleData = DesignService::getInstance()->handleRequestModules(['modules' => $modules]);

        if ($page === 'home') {
            // 首页数据保存到插件设置
            SettingRepo::getInstance()->updatePluginValue('page_builder', 'modules', $moduleData);
        } else {
            // 单页数据保存到 PageModule
            $pageModel = $this->findPage($page);
            if (! $pageModel) {
                throw new Exception('页面不存在');
            }

            $pageModule = PageModule::query()->where('page_id', $pageModel->id)->first();
            if (empty($pageModule)) {
                $pageModule = new PageModule;
            }

            $pageModule->fill([
                'page_id'     => $pageModel->id,
                'module_data' => $moduleData['modules'] ?? [],
            ]);
            $pageModule->saveOrFail();
        }

        fire_hook_action('admin.design.update.after', $moduleData);

        return $moduleData;
    }

    /**
     * 导入演示数据
     *
     * @param  string|null  $page  页面标识，null表示首页
     * @return array
     * @throws Exception
     */
    public function importDemoData(?string $page = null): array
    {
        // 如果没有传入page参数或page为null，默认为首页
        if ($page === null) {
            $page = 'home';
        }

        if ($page !== 'home') {
            throw new Exception('演示数据仅支持首页');
        }

        $demo_data  = DemoRepo::getHomeDemoData();
        $moduleData = [
            'modules' => array_map(function ($demo) {
                return [
                    'code'      => $demo['code'],
                    'content'   => $demo['content'],
                    'module_id' => $demo['module_id'] ?? uniqid(),
                    'name'      => $demo['title'],
                    'view_path' => '',
                ];
            }, $demo_data),
        ];

        SettingRepo::getInstance()->updatePluginValue('page_builder', 'modules', $moduleData);

        fire_hook_action('admin.design.import_demo.after', $moduleData);

        return $moduleData;
    }

    /**
     * 查找页面 - 支持ID或slug
     *
     * @param  string  $page  页面ID或slug
     * @return Page|null
     */
    private function findPage(string $page): ?Page
    {
        // 先尝试按ID查找
        if (is_numeric($page)) {
            $pageModel = Page::find($page);
            if ($pageModel) {
                return $pageModel;
            }
        }

        // 再尝试按slug查找
        return Page::where('slug', $page)->first();
    }
}
