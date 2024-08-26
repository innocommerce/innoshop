<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Panel\Repositories\ThemeRepo;

class ThemeController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'themes' => ThemeRepo::getInstance()->getListFromPath(),
        ];

        return inno_view('panel::themes.index', $data);
    }

    /**
     * @return mixed
     */
    public function settings(): mixed
    {
        $data = [
            'categories' => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'catalogs'   => CatalogRepo::getInstance()->getTopCatalogs(),
            'pages'      => PageRepo::getInstance()->withActive()->builder()->get(),
            'themes'     => ThemeRepo::getInstance()->getListFromPath(),
        ];

        return inno_view('panel::themes.settings', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Throwable
     */
    public function updateSettings(Request $request): mixed
    {
        $settings   = $request->all();
        $settingUrl = panel_route('themes_settings.index');

        try {
            SettingRepo::getInstance()->updateValues($settings);

            return redirect($settingUrl)
                ->with('instance', $settings)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect($settingUrl)->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  string  $themeCode
     * @return JsonResponse
     * @throws \Throwable
     */
    public function enable(string $themeCode): JsonResponse
    {
        try {
            SettingRepo::getInstance()->updateSystemValue('theme', $themeCode);

            return json_success(panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
