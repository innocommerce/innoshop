<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection as SupportCollection;
use InnoShop\Plugin\Models\Plugin;
use InnoShop\Plugin\Models\Setting;

class PluginRepo
{
    public static ?Collection $installedPlugins = null;

    /**
     * Settings row holding the display order of every plugin type.
     *
     * Value shape: {"payment": ["pay_pal", "bank_transfer"], "shipping": []}
     */
    public const TYPE_ORDER_SETTING = 'plugin_type_order';

    /**
     * @return self
     */
    public static function getInstance(): PluginRepo
    {
        static $instance = null;

        return $instance ??= new self;
    }

    /**
     * 获取所有已安装插件列表
     *
     * @return Collection
     */
    public function allPlugins(): Collection
    {
        if (self::$installedPlugins instanceof Collection && self::$installedPlugins->count() > 0) {
            return self::$installedPlugins;
        }

        // A filtered test run (e.g. `--filter=SomeTest`) starts before any
        // migration has run, and providers already ask for plugins while
        // booting. Report "no plugins" instead of crashing on a missing table.
        try {
            return self::$installedPlugins = Plugin::all();
        } catch (QueryException $e) {
            if (! app()->runningUnitTests()) {
                throw $e;
            }

            return self::$installedPlugins = new Collection;
        }
    }

    /**
     * Reset the cached installed plugins collection. Call after plugin
     * install/uninstall/enable/disable/priority change so subsequent reads
     * reflect DB state.
     */
    public static function resetCache(): void
    {
        self::$installedPlugins = null;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function getBuilder(array $filters = []): Builder
    {
        $builder = Plugin::query();
        $type    = $filters['type'] ?? '';
        if ($type) {
            $builder->where('type', $type);
        }

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', $code);
        }

        return fire_hook_filter('repo.plugin.builder', $builder);
    }

    /**
     * Group plugins by code.
     *
     * @return Collection
     */
    public function getPluginsGroupCode(): Collection
    {
        $allPlugins = $this->allPlugins();

        return $allPlugins->keyBy('code');
    }

    /**
     * @param  $code
     * @return mixed
     */
    public function getPluginByCode($code): mixed
    {
        return $this->getPluginsGroupCode()->get($code);
    }

    /**
     * Check plugin installed or not.
     * @param  $code
     * @return bool
     */
    public function installed($code): bool
    {
        return $this->getPluginsGroupCode()->has($code);
    }

    /**
     * Get plugin active
     *
     * @param  $pluginCode
     * @return bool
     */
    public function checkActive($pluginCode): bool
    {
        return (bool) setting("{$pluginCode}.active");
    }

    /**
     * Get plugin priority
     *
     * @param  $pluginCode
     * @return int
     */
    public function getPriority($pluginCode): int
    {
        $plugin = $this->getPluginByCode($pluginCode);
        if (empty($plugin)) {
            return 0;
        }

        return (int) $plugin->priority;
    }

    /**
     * Sort plugins by a stored code order.
     *
     * Pure function: no settings read, no DB access. Codes missing from $order
     * keep their original relative position at the tail, so a newly installed
     * plugin never disturbs an order the operator has already set up.
     *
     * @param  SupportCollection<int, Plugin>  $plugins
     * @param  array<int, string>  $order
     * @return SupportCollection<int, Plugin>
     */
    public static function applyOrder(SupportCollection $plugins, array $order): SupportCollection
    {
        if ($plugins->isEmpty() || empty($order)) {
            return $plugins;
        }

        // Laravel's sortBy is a stable asort(), so equal keys keep input order.
        return $plugins
            ->sortBy(function ($plugin) use ($order) {
                $position = array_search((string) $plugin->code, $order, true);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values();
    }

    /**
     * Stored display order for one plugin type.
     *
     * Reads the settings row directly instead of going through system_setting():
     * the latter resolves from the boot-time config snapshot, which stays stale
     * for the rest of the request right after a write.
     *
     * @return array<int, string>
     */
    public function getTypeOrder(string $type): array
    {
        return $this->getAllTypeOrders()[$type] ?? [];
    }

    /**
     * Persist the display order for one plugin type.
     *
     * Codes already stored but absent from $codes are kept at the tail, so a
     * plugin whose directory temporarily disappears comes back at the end of
     * the list instead of being dropped.
     *
     * @param  array<int, string>  $codes
     */
    public function saveTypeOrder(string $type, array $codes): void
    {
        $normalized = [];
        foreach ($codes as $code) {
            // A nested array would raise "Array to string conversion" and
            // abort the whole reorder, so unusable entries are dropped.
            if (! is_string($code) && ! is_numeric($code)) {
                continue;
            }

            $code = trim((string) $code);
            if ($code !== '' && ! in_array($code, $normalized, true)) {
                $normalized[] = $code;
            }
        }

        foreach ($this->getTypeOrder($type) as $code) {
            if (! in_array($code, $normalized, true)) {
                $normalized[] = $code;
            }
        }

        $all        = $this->getAllTypeOrders();
        $all[$type] = $normalized;

        SettingRepo::getInstance()->updateValues([self::TYPE_ORDER_SETTING => $all], 'system');

        // updateValues() nulls the whole `inno` settings snapshot; restore it
        // from the database so any later setting() read in this request does
        // not silently fall back to its default.
        config(['inno' => SettingRepo::getInstance()->groupedSettings()]);
    }

    /**
     * @param  SupportCollection<int, Plugin>  $plugins
     * @return SupportCollection<int, Plugin>
     */
    public function sortByTypeOrder(SupportCollection $plugins, string $type): SupportCollection
    {
        return self::applyOrder($plugins, $this->getTypeOrder($type));
    }

    /**
     * Every stored plugin-type order, keyed by plugin type.
     *
     * Deliberately un-memoised: a memo would have to live on the process-wide
     * PluginRepo singleton, where it goes stale as soon as anything writes the
     * setting outside saveTypeOrder() (queue workers, tests). The settings table
     * is a few dozen rows, so one lookup per call is cheaper than the footgun.
     *
     * @return array<string, array<int, string>>
     */
    public function getAllTypeOrders(): array
    {
        $setting = Setting::query()
            ->where('space', 'system')
            ->where('name', self::TYPE_ORDER_SETTING)
            ->first();

        if (empty($setting)) {
            return [];
        }

        $orders = json_decode((string) $setting->value, true);
        if (! is_array($orders)) {
            return [];
        }

        $result = [];
        foreach ($orders as $type => $codes) {
            if (! is_array($codes)) {
                continue;
            }
            $result[(string) $type] = array_values(array_filter(
                array_map(fn ($code) => (string) $code, $codes),
                fn (string $code) => $code !== ''
            ));
        }

        return $result;
    }

    /**
     * Get all shipping methods, ordered as the operator arranged them in the
     * panel (插件管理 → 配送). Falls back to install order when no order has
     * been stored yet.
     */
    public function getShippingMethods(): Collection
    {
        $allPlugins = $this->allPlugins();

        $methods = $allPlugins->where('type', 'shipping')->filter(function ($item) {
            $plugin = plugin($item->code);
            if ($plugin) {
                $item->plugin = $plugin;
            }

            return $plugin && $plugin->getEnabled();
        })->values();

        return $this->sortByTypeOrder($methods, 'shipping');
    }

    /**
     * Get all billing methods, ordered as the operator arranged them in the
     * panel (插件管理 → 支付). Falls back to install order when no order has
     * been stored yet.
     */
    public function getBillingMethods(): Collection
    {
        $allPlugins = $this->allPlugins();

        $methods = $allPlugins->where('type', 'payment')->filter(function ($item) {
            $plugin = plugin($item->code);
            if ($plugin) {
                $item->plugin = $plugin;
            }

            $available = plugin_setting_array($item->code, 'available');
            if (is_wechat_mini()) {
                $flag = in_array('wechat_mini', $available);
            } elseif (is_wechat_official()) {
                $flag = in_array('wechat_official', $available);
            } elseif (is_mobile()) {
                $flag = in_array('mobile_web', $available);
            } elseif (is_app()) {
                $flag = in_array('app', $available);
            } else {
                $flag = in_array('pc_web', $available);
            }

            return $plugin && $plugin->getEnabled() && $flag;
        })->values();

        return $this->sortByTypeOrder($methods, 'payment');
    }

    /**
     * @return array
     */
    public function shippingMethodOptions(): array
    {
        $methodItems     = [];
        $shippingMethods = $this->getShippingMethods();
        foreach ($shippingMethods as $method) {
            $plugin = $method->plugin;

            $methodItems[] = [
                'code' => $plugin->getCode(),
                'name' => $plugin->getLocaleName(),
            ];
        }

        return $methodItems;
    }

    /**
     * @return array
     */
    public function billingMethodOptions(): array
    {
        $methodItems    = [];
        $billingMethods = $this->getBillingMethods();
        foreach ($billingMethods as $method) {
            $plugin = $method->plugin;

            $methodItems[] = [
                'code' => $plugin->getCode(),
                'name' => $plugin->getLocaleName(),
            ];
        }

        return $methodItems;
    }
}
