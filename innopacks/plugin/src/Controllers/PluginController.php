<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use InnoShop\Plugin\Core\Plugin;
use InnoShop\Plugin\Repositories\PluginRepo;
use InnoShop\Plugin\Repositories\PluginTypeRepo;
use InnoShop\Plugin\Repositories\SettingRepo;
use InnoShop\Plugin\Resources\PluginResource;
use InnoShop\Plugin\Services\PluginService;
use Throwable;

class PluginController
{
    /**
     * Get all plugins.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $allPlugins = app('plugin')->getPlugins();
        $type       = $request->get('type');

        // The blade renders the drag handle for any truthy $type, and the
        // reorder endpoint rejects unknown types, so an unknown or malformed
        // type must fall back to the "all" tab instead of showing a handle
        // that could never save.
        if (! is_string($type) || ! PluginTypeRepo::getInstance()->isValidType($type)) {
            $type = null;
        }

        $typeCounts = [];
        foreach (Plugin::TYPES as $pluginType) {
            $typeCounts[$pluginType] = $allPlugins->where('type', $pluginType)->count();
        }
        $typeCounts['all'] = $allPlugins->count();

        $plugins = $this->sortPlugins($allPlugins, $type);

        $data = [
            'types'      => Plugin::TYPES,
            'type'       => $type,
            'plugins'    => array_values(PluginResource::collection($plugins)->jsonSerialize()),
            'typeCounts' => $typeCounts,
        ];

        return inno_view('plugin::plugins.index', $data);
    }

    /**
     * Persist the display order of one plugin type tab.
     *
     * The posted list is the new order of every card currently visible on that
     * tab. Codes already stored but absent from the payload stay at the tail so
     * that a plugin whose directory disappears comes back at the end of the
     * list instead of being dropped.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function reorder(Request $request): mixed
    {
        $type  = (string) $request->get('type', '');
        $codes = $request->get('codes');

        if (! PluginTypeRepo::getInstance()->isValidType($type)) {
            return json_fail(__('common/base.error'));
        }

        if (! is_array($codes)) {
            return json_fail(__('common/base.error'));
        }

        try {
            PluginRepo::getInstance()->saveTypeOrder($type, $codes);

            return json_success(common_trans('base.updated_success'));
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Order the plugin list for display.
     *
     * A single type tab is sorted by the operator's stored order. The "all" tab
     * groups by Plugin::TYPES and applies each type's own order inside its group,
     * so both tabs always agree with each other.
     *
     * @param  Collection<int, Plugin>  $allPlugins
     * @return Collection<int, Plugin>
     */
    private function sortPlugins(Collection $allPlugins, mixed $type): Collection
    {
        $repo   = PluginRepo::getInstance();
        $orders = $repo->getAllTypeOrders();

        if ($type && in_array($type, Plugin::TYPES, true)) {
            return PluginRepo::applyOrder(
                $allPlugins->where('type', $type)->values(),
                $orders[$type] ?? []
            );
        }

        $plugins = collect();
        foreach (Plugin::TYPES as $pluginType) {
            $plugins = $plugins->concat(
                PluginRepo::applyOrder(
                    $allPlugins->where('type', $pluginType)->values(),
                    $orders[$pluginType] ?? []
                )
            );
        }

        // Types outside the canonical list are rare but must not be dropped.
        return $plugins->concat($allPlugins->whereNotIn('type', Plugin::TYPES)->values());
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $code   = $request->get('code');
            $plugin = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->installPlugin($plugin);
            Artisan::call('view:clear');

            $data = $this->getPluginResourceData($code, true, false);

            return json_success(common_trans('base.installed_success'), $data);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  $code
     * @return mixed
     */
    public function destroy($code): mixed
    {
        try {
            $plugin = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->uninstallPlugin($plugin);
            Artisan::call('view:clear');

            $data = $this->getPluginResourceData($code, false, false);

            return json_success(common_trans('base.uninstalled_success'), $data);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Show plugin details (redirects to edit page).
     *
     * @param  $code
     * @return View
     */
    public function show($code): View
    {
        return $this->edit($code);
    }

    /**
     * @param  $code
     * @return View
     */
    public function edit($code): View
    {
        try {
            $plugin     = app('plugin')->getPluginOrFail($code);
            $customView = $plugin->getFieldView();
            $data       = [
                'plugin'     => $plugin,
                'fields'     => $plugin->getFields(),
                'customView' => $customView,
            ];

            return inno_view('plugin::plugins.form', $data);
        } catch (Exception $e) {
            $plugin = app('plugin')->getPlugin($code);
            $data   = [
                'error'       => $e->getMessage(),
                'plugin_code' => $code,
                'plugin'      => $plugin,
            ];

            return inno_view('plugin::plugins.error', $data);
        }
    }

    /**
     * @param  Request  $request
     * @param  string  $code
     * @return mixed
     * @throws Throwable
     */
    public function update(Request $request, string $code): mixed
    {
        $fields = $request->all();
        $plugin = app('plugin')->getPluginOrFail($code);
        if (method_exists($plugin, 'validateFields')) {
            $validator = $plugin->validateFields($fields);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        }
        SettingRepo::getInstance()->updateValues($fields, $code);
        Artisan::call('view:clear');
        $currentUrl = panel_route('plugins.edit', [$code]);

        return redirect($currentUrl)
            ->with('instance', $plugin)
            ->with('success', common_trans('base.updated_success'));
    }

    /**
     * Run plugin seeders.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function runSeeders(Request $request): mixed
    {
        try {
            $code      = $request->get('code');
            $clearData = (bool) $request->get('clear_data', false);
            $plugin    = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->runSeeders($plugin, $clearData);

            return json_success(common_trans('base.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }
    }

    /**
     * Reset plugin database: rollback → migrate → seed.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function reset(Request $request): mixed
    {
        try {
            $code      = $request->get('code');
            $clearData = (bool) $request->get('clear_data', false);
            $plugin    = app('plugin')->getPluginOrFail($code);
            PluginService::getInstance()->resetPlugin($plugin, $clearData);
            Artisan::call('view:clear');

            return json_success(common_trans('base.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function updateStatus(Request $request): mixed
    {
        try {
            $code    = $request->get('code');
            $enabled = $request->get('enabled');
            $plugin  = app('plugin')->getPluginOrFail($code);
            SettingRepo::getInstance()->updatePluginValue($code, 'active', $enabled);
            Artisan::call('view:clear');

            if ($enabled) {
                $this->loadPluginRoutesOnDemand($plugin->getDirname());
            }

            $data = $this->getPluginResourceData($code, true, (bool) $enabled);

            return json_success(common_trans('base.updated_success'), $data);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Dynamically load plugin routes after enabling,
     * so menu_url is correct in the AJAX response.
     */
    private function loadPluginRoutesOnDemand(string $pluginCode): void
    {
        $pluginBasePath = base_path('plugins');

        $routes = [
            ['path' => "$pluginBasePath/$pluginCode/Routes/panel.php", 'prefix' => panel_name(), 'name' => 'panel.', 'middleware' => ['panel', 'admin_auth:admin']],
            ['path' => "$pluginBasePath/$pluginCode/Routes/root.php", 'prefix' => '', 'name' => 'front.', 'middleware' => ['front']],
            ['path' => "$pluginBasePath/$pluginCode/Routes/front.php", 'prefix' => '', 'name' => 'front.', 'middleware' => ['front']],
            ['path' => "$pluginBasePath/$pluginCode/Routes/front-api.php", 'prefix' => 'api', 'name' => 'api.', 'middleware' => ['api']],
            ['path' => "$pluginBasePath/$pluginCode/Routes/panel-api.php", 'prefix' => 'api/panel', 'name' => 'api.panel.', 'middleware' => ['panel_api']],
        ];

        foreach ($routes as $route) {
            if (file_exists($route['path'])) {
                Route::prefix($route['prefix'])
                    ->name($route['name'])
                    ->middleware($route['middleware'])
                    ->group($route['path']);
            }
        }

        app('router')->getRoutes()->refreshNameLookups();
    }

    /**
     * Get plugin resource data with explicit installed/enabled state.
     * Cannot rely on checkActive()/checkInstalled() because setting()
     * reads from config cache which is cleared mid-request.
     *
     * @param  string  $code
     * @param  bool  $installed
     * @param  bool  $enabled
     * @return array
     */
    private function getPluginResourceData(string $code, bool $installed, bool $enabled): array
    {
        $plugin = app('plugin')->getPluginOrFail($code);
        $plugin->setInstalled($installed);
        $plugin->setEnabled($enabled);

        return (new PluginResource($plugin))->toArray(request());
    }
}
