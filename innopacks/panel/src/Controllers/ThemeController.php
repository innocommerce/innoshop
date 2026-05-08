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
use InnoShop\Common\Repositories\ArticleRepo;
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
            'articles'   => ArticleRepo::getInstance()->withActive()->builder()
                ->with(['translation', 'catalog.translation'])
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
            'product_floors'    => $this->getProductFloorsForEdit(),
            'home_category_ids' => $this->getHomeCategoryIds(),
            'hp_display_mode'   => $this->getHotProductsSetting('display_mode', 'flat'),
            'hp_title_align'    => $this->getHotProductsSetting('title_align', 'left'),
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
            // Assemble hot products floor data from form fields
            $settings['home_hot_products'] = $this->assembleProductFloors($request);

            // Parse home categories from JSON hidden input
            $catIdsJson                  = $request->input('home_categories_ids', '[]');
            $settings['home_categories'] = json_decode($catIdsJson, true) ?: [];

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

    /**
     * Parse existing hot_products setting into edit-ready floor data.
     * Returns array of floors with name/subtitle translations and product IDs.
     */
    private function getProductFloorsForEdit(): array
    {
        $raw  = system_setting('home_hot_products', '{}');
        $data = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);

        if (empty($data)) {
            return [];
        }

        if (empty($data['floors']) || ! is_array($data['floors'])) {
            return [];
        }

        $floors = $data['floors'];

        $result = [];
        foreach ($floors as $floor) {
            // Ensure name/subtitle are {locale: value} objects
            $name     = $floor['name'] ?? '';
            $subtitle = $floor['subtitle'] ?? '';

            // If name is a plain string (old format), wrap it
            if (is_string($name)) {
                $name = [panel_locale_code() => $name];
            }
            if (is_string($subtitle)) {
                $subtitle = ! empty($subtitle) ? [panel_locale_code() => $subtitle] : [];
            }

            $result[] = [
                'name_translations'     => $name,
                'subtitle_translations' => $subtitle,
                'products'              => $floor['products'] ?? [],
            ];
        }

        return $result;
    }

    /**
     * Assemble product floors from form submission into JSON-ready structure.
     */
    private function assembleProductFloors(Request $request): array
    {
        $floorsInput   = $request->input('floors', []);
        $productsInput = $request->input('floors_products', []);

        if (empty($floorsInput)) {
            return ['floors' => []];
        }

        $floors = [];
        foreach ($floorsInput as $index => $floorFields) {
            $products = $productsInput[$index] ?? [];
            if (is_string($products)) {
                $products = json_decode($products, true) ?: [];
            }
            $products = array_map('intval', array_filter((array) $products));

            $floors[] = [
                'name'     => $floorFields['name'] ?? [],
                'subtitle' => $floorFields['subtitle'] ?? [],
                'products' => $products,
            ];
        }

        return [
            'display_mode' => in_array($request->input('hp_display_mode'), ['tab', 'flat']) ? $request->input('hp_display_mode') : 'flat',
            'title_align'  => in_array($request->input('hp_title_align'), ['left', 'center']) ? $request->input('hp_title_align') : 'left',
            'floors'       => $floors,
        ];
    }

    /**
     * Get a single hot products setting value.
     */
    private function getHotProductsSetting(string $key, string $default = ''): string
    {
        $raw  = system_setting('home_hot_products', '{}');
        $data = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);

        return $data[$key] ?? $default;
    }

    /**
     * Get home category IDs from settings.
     */
    private function getHomeCategoryIds(): array
    {
        $ids = system_setting('home_categories', []);

        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?: [];
        }

        return is_array($ids) ? array_map('intval', $ids) : [];
    }
}
