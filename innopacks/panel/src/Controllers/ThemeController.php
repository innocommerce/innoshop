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
use Illuminate\Support\Collection;
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
        $result = ThemeService::getInstance()->getListFromPath();
        /** @var Collection<int, array<string, mixed>> $themes */
        $themes = $result['themes'] ?? collect();

        $selected = $themes->firstWhere('selected', true);
        $data     = [
            'themes'                 => $themes,
            'themes_count'           => $themes->count(),
            'themes_with_demo_count' => $themes->filter(function (array $t): bool {
                return ! empty($t['has_demo'] ?? false);
            })->count(),
            'selected_theme_name' => data_get($selected, 'name'),
            'errors'              => $result['errors'],
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
                ->with('success', common_trans('base.updated_success'));
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

            return json_success(common_trans('base.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Install demo data
     *
     * @param  Request  $request  JSON body may include clear_default_catalog (boolean)
     */
    public function importDemo(Request $request, string $code): JsonResponse
    {
        try {
            $dir = base_path('themes/'.$code);
            if (! is_dir($dir)) {
                throw new Exception(__('panel/themes.error_theme_not_found'));
            }

            if (! $this->themeService->hasDemo($dir)) {
                throw new Exception(__('panel/themes.error_demo_not_found'));
            }

            $clearDefaultCatalog = $request->boolean('clear_default_catalog');

            $this->themeService->runDemoSeeder($dir, $clearDefaultCatalog);

            return json_success(trans('panel/themes.demo_installed'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
