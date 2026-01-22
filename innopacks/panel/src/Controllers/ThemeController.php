<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Common\Repositories\SpecialPageRepo;
use InnoShop\Panel\Repositories\ThemeRepo;
use InnoShop\Panel\Services\ThemeService;
use Throwable;

class ThemeController extends BaseController
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'themes' => ThemeService::getInstance()->getListFromPath(),
        ];

        return inno_view('panel::themes.index', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function settings(): mixed
    {
        $data = [
            'categories' => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'catalogs'   => CatalogRepo::getInstance()->getTopCatalogs(),
            'brands'     => BrandRepo::getInstance()->withActive()->builder()->get(),
            'specials'   => SpecialPageRepo::getInstance()->getOptions(),
            'pages'      => PageRepo::getInstance()->withActive()->builder()->get(),
        ];

        // 允许通过 Hook 扩展数据
        $data = fire_hook_filter('panel.themes.settings.data', $data);

        return inno_view('panel::themes.settings', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function updateSettings(Request $request): mixed
    {
        $settings   = $request->all();
        $settingUrl = panel_route('themes_settings.index');

        try {
            ThemeRepo::getInstance()->updateSetting($settings);

            return redirect($settingUrl)
                ->with('instance', $settings)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect($settingUrl)->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @param  string  $themeCode
     * @return mixed
     * @throws Throwable
     */
    public function enable(Request $request, string $themeCode): mixed
    {
        try {
            $status = $request->get('status');
            if (empty($status)) {
                SettingRepo::getInstance()->updateSystemValue('theme', '');
            } else {
                SettingRepo::getInstance()->updateSystemValue('theme', $themeCode);
            }

            return json_success(panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Install demo data
     * @param  string  $code
     * @return JsonResponse
     */
    public function importDemo(string $code): JsonResponse
    {
        try {
            $dir = base_path('themes/'.$code);
            if (! is_dir($dir)) {
                throw new Exception(__('panel/themes.error_theme_not_found'));
            }

            if (! $this->themeService->hasDemo($dir)) {
                throw new Exception(__('panel/themes.error_demo_not_found'));
            }

            $this->themeService->runDemoSeeder($dir);

            return json_success(trans('panel/themes.demo_installed'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Export current data as SQL file
     * @param  string  $code
     * @return mixed
     */
    public function exportSql(string $code): mixed
    {
        try {
            $demoService = new \InnoShop\Panel\Services\ThemeDemoService;
            $sqlPath     = $demoService->exportSql($code);

            if (! file_exists($sqlPath)) {
                throw new Exception(__('panel/themes.error_export_failed'));
            }

            $fileName = basename($sqlPath);

            return response()->download($sqlPath, $fileName)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
