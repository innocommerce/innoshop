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
     * Get page builder data
     *
     * @param  string|null  $page  Page identifier, 'home' for home page, others are page ID or slug, null means home page
     * @return array
     * @throws Exception
     */
    public function getPageData(?string $page = null): array
    {
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
            'pages'        => PageRepo::getInstance()->builder(['active' => true])->with('translation')->get(),
            'source'       => [
                'modules' => ModuleRepo::getModules(),
            ],
            'design_settings' => $design_settings,
        ];

        return fire_hook_filter('page_builder.index.data', $data);
    }

    /**
     * Get home page settings
     *
     * @return array
     */
    private function getHomePageSettings(): array
    {
        $design_settings = plugin_setting('page_builder', 'modules');

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

            SettingRepo::getInstance()->updatePluginValue('page_builder', 'modules', $design_settings);
        }

        return $design_settings;
    }

    /**
     * Get single page settings
     *
     * @param  string  $page  Page identifier
     * @return array
     * @throws Exception
     */
    private function getSinglePageSettings(string $page): array
    {
        $pageModel = $this->findPage($page);
        if (! $pageModel) {
            abort(404, 'Page not found');
        }

        $pageModule = PageModule::query()->where('page_id', $pageModel->id)->first();

        return [
            'modules' => $pageModule->module_data ?? [],
        ];
    }

    /**
     * Save page module data
     *
     * @param  array  $modules  Module data
     * @param  string|null  $page  Page identifier, null means home page
     * @return array
     * @throws Throwable
     */
    public function savePageModules(array $modules, ?string $page = null): array
    {
        if ($page === null) {
            $page = 'home';
        }

        $moduleData = DesignService::getInstance()->handleRequestModules(['modules' => $modules]);

        if ($page === 'home') {
            SettingRepo::getInstance()->updatePluginValue('page_builder', 'modules', $moduleData);
        } else {
            $pageModel = $this->findPage($page);
            if (! $pageModel) {
                throw new Exception('Page not found');
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
     * Import demo data
     *
     * @param  string|null  $page  Page identifier, null means home page
     * @return array
     * @throws Exception
     */
    public function importDemoData(?string $page = null): array
    {
        if ($page === null) {
            $page = 'home';
        }

        if ($page !== 'home') {
            throw new Exception('Demo data is only supported for home page');
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
     * Find page - support ID or slug
     *
     * @param  string  $page  Page ID or slug
     * @return Page|null
     */
    public function findPage(string $page): ?Page
    {
        if (is_numeric($page)) {
            $pageModel = Page::find($page);
            if ($pageModel) {
                return $pageModel;
            }
        }

        return Page::where('slug', $page)->first();
    }
}
