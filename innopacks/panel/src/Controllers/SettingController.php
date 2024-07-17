<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\Request;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\CurrencyRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Panel\Repositories\ThemeRepo;

class SettingController
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function index(): mixed
    {
        $data = [
            'locales'    => locales()->toArray(),
            'currencies' => CurrencyRepo::getInstance()->enabledList()->toArray(),
            'categories' => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'catalogs'   => CatalogRepo::getInstance()->getTopCatalogs(),
            'pages'      => PageRepo::getInstance()->withActive()->builder()->get(),
            'themes'     => ThemeRepo::getInstance()->getListFromPath(),
        ];

        return inno_view('panel::settings.index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Throwable
     */
    public function update(Request $request): mixed
    {
        $settings = $request->all();

        try {
            SettingRepo::getInstance()->updateValues($settings);
            $oldAdminName = panel_name();
            $newAdminName = $settings['panel_name'] ?? 'panel';
            $settingUrl   = str_replace($oldAdminName, $newAdminName, panel_route('settings.index'));

            return redirect($settingUrl)->with('success', trans('panel::common.updated_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('settings.index'))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
